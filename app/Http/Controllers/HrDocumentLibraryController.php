<?php

namespace App\Http\Controllers;

use App\Models\HrDocumentFolder;
use App\Models\HrDocumentLibrary;
use App\Services\HrDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HrDocumentLibraryController extends Controller
{
    protected HrDocumentService $documentService;

    public function __construct(HrDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display listing of HR documents and folders.
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('Manage HR Document Library')) {
            $creatorId = Auth::user()->creatorId();
            $folderId = $request->get('folder_id', null);

            $currentFolder = null;
            if ($folderId) {
                $currentFolder = HrDocumentFolder::where('created_by', $creatorId)->find($folderId);
            }

            // Fetch folders inside current active directory
            $foldersQuery = HrDocumentFolder::where('created_by', $creatorId);
            if ($currentFolder) {
                $foldersQuery->where('parent_id', $currentFolder->id);
            } else {
                $foldersQuery->whereNull('parent_id');
            }
            $folders = $foldersQuery->orderBy('name', 'asc')->get();

            // Fetch documents inside current active directory
            $docQuery = HrDocumentLibrary::where('created_by', $creatorId);
            if ($currentFolder) {
                $docQuery->where('folder_id', $currentFolder->id);
            } else {
                $docQuery->whereNull('folder_id');
            }

            if ($request->has('category') && !empty($request->category)) {
                $docQuery->where('category', $request->category);
            }

            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $docQuery->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('file_name', 'like', '%' . $searchTerm . '%');
                });
            }

            $documents = $docQuery->orderBy('created_at', 'desc')->get();

            $categories = HrDocumentLibrary::where('created_by', $creatorId)
                ->whereNotNull('category')
                ->pluck('category', 'category')
                ->toArray();

            $allFolders = HrDocumentFolder::where('created_by', $creatorId)
                ->pluck('name', 'id')
                ->toArray();

            return view('hr_document_library.index', compact('documents', 'folders', 'currentFolder', 'categories', 'allFolders'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show creation popup modal for document upload (single or multiple).
     */
    public function create(Request $request)
    {
        if (Auth::user()->can('Create HR Document Library')) {
            $creatorId = Auth::user()->creatorId();
            $folderId = $request->get('folder_id', null);
            $folders = HrDocumentFolder::where('created_by', $creatorId)->pluck('name', 'id')->toArray();

            return view('hr_document_library.create', compact('folderId', 'folders'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Store newly uploaded HR documents (supports multiple files).
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('Create HR Document Library')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'title' => 'nullable|string|max:255',
                    'category' => 'nullable|string|max:100',
                    'folder_id' => 'required|exists:hr_document_folders,id',
                    'documents' => 'nullable|array',
                    'documents.*' => 'file|max:20480',
                    'document' => 'nullable|file|max:20480',
                ],
                [
                    'folder_id.required' => __('Please select a folder. Documents cannot be uploaded to Root directory.'),
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            if ($request->hasFile('documents')) {
                $this->documentService->storeMultipleDocuments($request);
                return redirect()->route('hr-document-library.index', ['folder_id' => $request->folder_id])
                    ->with('success', __('Documents uploaded successfully.'));
            } else {
                if (empty($request->title)) {
                    $request->merge(['title' => 'Untitled Document']);
                }
                $this->documentService->storeDocument($request);
                return redirect()->route('hr-document-library.index', ['folder_id' => $request->folder_id])
                    ->with('success', __('HR Document created successfully.'));
            }
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show popup modal to create a new Folder.
     */
    public function createFolder(Request $request)
    {
        if (Auth::user()->can('Create HR Document Library')) {
            $creatorId = Auth::user()->creatorId();
            $parentId = $request->get('parent_id', null);
            $folders = HrDocumentFolder::where('created_by', $creatorId)->pluck('name', 'id')->toArray();

            return view('hr_document_library.create_folder', compact('parentId', 'folders'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Store new folder in DB.
     */
    public function storeFolder(Request $request)
    {
        if (Auth::user()->can('Create HR Document Library')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'parent_id' => 'nullable|exists:hr_document_folders,id',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            HrDocumentFolder::create([
                'name' => $request->name,
                'parent_id' => $request->parent_id ?: null,
                'created_by' => Auth::user()->creatorId(),
            ]);

            return redirect()->route('hr-document-library.index', ['folder_id' => $request->parent_id])
                ->with('success', __('Folder created successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Edit folder popup modal.
     */
    public function editFolder($id)
    {
        if (Auth::user()->can('Edit HR Document Library')) {
            $creatorId = Auth::user()->creatorId();
            $folder = HrDocumentFolder::where('created_by', $creatorId)->findOrFail($id);
            $folders = HrDocumentFolder::where('created_by', $creatorId)
                ->where('id', '!=', $id)
                ->pluck('name', 'id')
                ->toArray();

            return view('hr_document_library.edit_folder', compact('folder', 'folders'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Update folder details.
     */
    public function updateFolder(Request $request, $id)
    {
        if (Auth::user()->can('Edit HR Document Library')) {
            $creatorId = Auth::user()->creatorId();
            $folder = HrDocumentFolder::where('created_by', $creatorId)->findOrFail($id);

            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'parent_id' => 'nullable|exists:hr_document_folders,id',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $folder->update([
                'name' => $request->name,
                'parent_id' => $request->parent_id ?: null,
            ]);

            return redirect()->route('hr-document-library.index', ['folder_id' => $folder->parent_id])
                ->with('success', __('Folder updated successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Delete folder and its contents.
     */
    public function destroyFolder($id)
    {
        if (Auth::user()->can('Delete HR Document Library')) {
            $creatorId = Auth::user()->creatorId();
            $folder = HrDocumentFolder::where('created_by', $creatorId)->findOrFail($id);
            $parentId = $folder->parent_id;

            $folder->delete();

            return redirect()->route('hr-document-library.index', ['folder_id' => $parentId])
                ->with('success', __('Folder deleted successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show edit form popup modal for document.
     */
    public function edit($id)
    {
        if (Auth::user()->can('Edit HR Document Library')) {
            $creatorId = Auth::user()->creatorId();
            $doc = HrDocumentLibrary::where('created_by', $creatorId)->findOrFail($id);
            $folders = HrDocumentFolder::where('created_by', $creatorId)->pluck('name', 'id')->toArray();

            return view('hr_document_library.edit', compact('doc', 'folders'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Update document metadata or replace file.
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->can('Edit HR Document Library')) {
            $doc = HrDocumentLibrary::where('created_by', Auth::user()->creatorId())->findOrFail($id);

            $validator = Validator::make(
                $request->all(),
                [
                    'title' => 'required|string|max:255',
                    'category' => 'nullable|string|max:100',
                    'folder_id' => 'required|exists:hr_document_folders,id',
                    'document' => 'nullable|file|max:20480',
                ],
                [
                    'folder_id.required' => __('Please select a folder. Every document must belong to a folder.'),
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $doc->folder_id = $request->folder_id ?: null;
            $this->documentService->updateDocument($request, $doc);

            return redirect()->route('hr-document-library.index', ['folder_id' => $doc->folder_id])
                ->with('success', __('HR Document updated successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Delete document.
     */
    public function destroy($id)
    {
        if (Auth::user()->can('Delete HR Document Library')) {
            $doc = HrDocumentLibrary::where('created_by', Auth::user()->creatorId())->findOrFail($id);
            $folderId = $doc->folder_id;
            $this->documentService->deleteDocument($doc);

            return redirect()->route('hr-document-library.index', ['folder_id' => $folderId])
                ->with('success', __('HR Document deleted successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Download / Open original uploaded document file.
     */
    public function downloadDoc($id)
    {
        if (Auth::user()->can('Manage HR Document Library')) {
            $doc = HrDocumentLibrary::where('created_by', Auth::user()->creatorId())->findOrFail($id);
            if (!empty($doc->file_path)) {
                $filePath = public_path($doc->file_path);
                if (!file_exists($filePath)) {
                    $filePath = storage_path('app/public/' . $doc->file_path);
                }
                if (file_exists($filePath)) {
                    return response()->download($filePath, $doc->file_name ?? ($doc->title . '.docx'));
                }
            }
        }

        return redirect()->back()->with('error', __('File not found.'));
    }
}
