<?php

namespace App\Http\Controllers;

use App\Models\HrDocumentFolder;
use App\Models\HrDocumentLibrary;
use App\Models\User;
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
     * Helper to scope queries by allowed creator IDs.
     * Super Admin sees ALL folders & documents from all companies/creators.
     * Company/Users see Super Admin created documents + their own company documents.
     */
    protected function applyAllowedCreatorFilter($query)
    {
        if (Auth::user()->type === 'super admin' || Auth::user()->isSuperAdminSideUser()) {
            return $query;
        }

        $superAdmin = User::where('type', 'super admin')->first();
        $superAdminId = $superAdmin ? $superAdmin->id : 1;

        $allowedIds = array_values(array_unique([
            $superAdminId,
            Auth::user()->creatorId(),
            Auth::user()->id,
        ]));

        return $query->whereIn('created_by', $allowedIds);
    }

    /**
     * Display listing of HR documents and folders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || $user->can('Manage HR Document Library')) {
            $folderId = $request->get('folder_id', null);

            $currentFolder = null;
            if ($folderId) {
                $currentFolder = $this->applyAllowedCreatorFilter(HrDocumentFolder::query())->find($folderId);
            }

            // Fetch folders inside current active directory
            $foldersQuery = $this->applyAllowedCreatorFilter(HrDocumentFolder::query());
            if ($currentFolder) {
                $foldersQuery->where('parent_id', $currentFolder->id);
            } else {
                $foldersQuery->whereNull('parent_id');
            }
            $folders = $foldersQuery->orderBy('name', 'asc')->get();

            // Fetch documents inside current active directory
            $docQuery = $this->applyAllowedCreatorFilter(HrDocumentLibrary::query());
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

            $categories = $this->applyAllowedCreatorFilter(HrDocumentLibrary::query())
                ->whereNotNull('category')
                ->pluck('category', 'category')
                ->toArray();

            $allFolders = $this->applyAllowedCreatorFilter(HrDocumentFolder::query())
                ->pluck('name', 'id')
                ->toArray();

            return view('hr_document_library.index', compact('documents', 'folders', 'currentFolder', 'categories', 'allFolders'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show creation popup modal for document upload (Super Admin Only).
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || ($user->isSuperAdminSideUser() && $user->can('Create HR Document Library'))) {
            $folderId = $request->get('folder_id', null);
            $folders = $this->applyAllowedCreatorFilter(HrDocumentFolder::query())->pluck('name', 'id')->toArray();

            return view('hr_document_library.create', compact('folderId', 'folders'));
        }

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to upload documents.'));
    }

    /**
     * Store newly uploaded HR documents.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || ($user->isSuperAdminSideUser() && $user->can('Create HR Document Library'))) {
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

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to upload documents.'));
    }

    /**
     * Show popup modal to create a new Folder.
     */
    public function createFolder(Request $request)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || ($user->isSuperAdminSideUser() && $user->can('Create HR Document Library'))) {
            $parentId = $request->get('parent_id', null);
            $folders = $this->applyAllowedCreatorFilter(HrDocumentFolder::query())->pluck('name', 'id')->toArray();

            return view('hr_document_library.create_folder', compact('parentId', 'folders'));
        }

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to create folders.'));
    }

    /**
     * Store new folder in DB.
     */
    public function storeFolder(Request $request)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || ($user->isSuperAdminSideUser() && $user->can('Create HR Document Library'))) {
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
                'created_by' => Auth::user()->id,
            ]);

            return redirect()->route('hr-document-library.index', ['folder_id' => $request->parent_id])
                ->with('success', __('Folder created successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to create folders.'));
    }

    /**
     * Edit folder popup modal.
     */
    public function editFolder($id)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || ($user->isSuperAdminSideUser() && $user->can('Edit HR Document Library'))) {
            $folder = $this->applyAllowedCreatorFilter(HrDocumentFolder::query())->findOrFail($id);
            $folders = $this->applyAllowedCreatorFilter(HrDocumentFolder::query())
                ->where('id', '!=', $id)
                ->pluck('name', 'id')
                ->toArray();

            return view('hr_document_library.edit_folder', compact('folder', 'folders'));
        }

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to edit folders.'));
    }

    /**
     * Update folder details.
     */
    public function updateFolder(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || ($user->isSuperAdminSideUser() && $user->can('Edit HR Document Library'))) {
            $folder = $this->applyAllowedCreatorFilter(HrDocumentFolder::query())->findOrFail($id);

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

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to edit folders.'));
    }

    /**
     * Delete folder and its contents.
     */
    public function destroyFolder($id)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || ($user->isSuperAdminSideUser() && $user->can('Delete HR Document Library'))) {
            $folder = $this->applyAllowedCreatorFilter(HrDocumentFolder::query())->findOrFail($id);
            $parentId = $folder->parent_id;

            $folder->delete();

            return redirect()->route('hr-document-library.index', ['folder_id' => $parentId])
                ->with('success', __('Folder deleted successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to delete folders.'));
    }

    /**
     * Show edit form popup modal for document.
     */
    public function edit($id)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || ($user->isSuperAdminSideUser() && $user->can('Edit HR Document Library'))) {
            $doc = $this->applyAllowedCreatorFilter(HrDocumentLibrary::query())->findOrFail($id);
            $folders = $this->applyAllowedCreatorFilter(HrDocumentFolder::query())->pluck('name', 'id')->toArray();

            return view('hr_document_library.edit', compact('doc', 'folders'));
        }

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to edit documents.'));
    }

    /**
     * Update document metadata or replace file.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || ($user->isSuperAdminSideUser() && $user->can('Edit HR Document Library'))) {
            $doc = $this->applyAllowedCreatorFilter(HrDocumentLibrary::query())->findOrFail($id);

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

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to edit documents.'));
    }

    /**
     * Delete document.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || ($user->isSuperAdminSideUser() && $user->can('Delete HR Document Library'))) {
            $doc = $this->applyAllowedCreatorFilter(HrDocumentLibrary::query())->findOrFail($id);
            $folderId = $doc->folder_id;
            $this->documentService->deleteDocument($doc);

            return redirect()->route('hr-document-library.index', ['folder_id' => $folderId])
                ->with('success', __('HR Document deleted successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to delete documents.'));
    }

    /**
     * Download / Open original uploaded document file.
     */
    public function downloadDoc($id)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || $user->can('Download HR Document Library')) {
            $doc = $this->applyAllowedCreatorFilter(HrDocumentLibrary::query())->findOrFail($id);
            if (!empty($doc->file_path)) {
                $filePath = public_path($doc->file_path);
                if (!file_exists($filePath)) {
                    $filePath = storage_path('app/public/' . $doc->file_path);
                }
                if (file_exists($filePath)) {
                    return response()->download($filePath, $doc->file_name ?? ($doc->title . '.docx'));
                }
            }
            return redirect()->back()->with('error', __('File not found.'));
        }

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to download documents.'));
    }

    /**
     * View/Preview original uploaded document file in new browser tab.
     */
    public function viewDoc($id)
    {
        $user = Auth::user();
        if ($user->type === 'super admin' || $user->can('View HR Document Library')) {
            $doc = $this->applyAllowedCreatorFilter(HrDocumentLibrary::query())->findOrFail($id);
            if (!empty($doc->file_path)) {
                $filePath = public_path($doc->file_path);
                if (!file_exists($filePath)) {
                    $filePath = storage_path('app/public/' . $doc->file_path);
                }
                if (file_exists($filePath)) {
                    $mimeType = @mime_content_type($filePath) ?: 'application/octet-stream';
                    return response()->file($filePath, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'inline; filename="' . ($doc->file_name ?? $doc->title) . '"',
                    ]);
                }
            }
            return redirect()->back()->with('error', __('File not found.'));
        }

        return redirect()->back()->with('error', __('Permission denied. You do not have permission to view documents.'));
    }
}
