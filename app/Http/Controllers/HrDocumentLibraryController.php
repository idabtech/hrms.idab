<?php

namespace App\Http\Controllers;

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
     * Display listing of HR documents.
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('Manage HR Document Library')) {
            $creatorId = Auth::user()->creatorId();
            $query = HrDocumentLibrary::where('created_by', $creatorId);

            if ($request->has('category') && !empty($request->category)) {
                $query->where('category', $request->category);
            }

            $documents = $query->orderBy('created_at', 'desc')->get();
            $categories = HrDocumentLibrary::where('created_by', $creatorId)
                ->whereNotNull('category')
                ->pluck('category', 'category')
                ->toArray();

            return view('hr_document_library.index', compact('documents', 'categories'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show creation form popup modal.
     */
    public function create()
    {
        if (Auth::user()->can('Create HR Document Library')) {
            return view('hr_document_library.create');
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Store a newly uploaded HR document.
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('Create HR Document Library')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'title' => 'required|string|max:255',
                    'category' => 'nullable|string|max:100',
                    'document' => 'nullable|file|max:20480',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $this->documentService->storeDocument($request);
            return redirect()->route('hr-document-library.index')->with('success', __('HR Document created successfully.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show edit form popup modal for metadata & file replacement.
     */
    public function edit($id)
    {
        if (Auth::user()->can('Edit HR Document Library')) {
            $doc = HrDocumentLibrary::where('created_by', Auth::user()->creatorId())->findOrFail($id);
            return view('hr_document_library.edit', compact('doc'));
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
                    'document' => 'nullable|file|max:20480',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $this->documentService->updateDocument($request, $doc);
            return redirect()->route('hr-document-library.index')->with('success', __('HR Document updated successfully.'));
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
            $this->documentService->deleteDocument($doc);
            return redirect()->route('hr-document-library.index')->with('success', __('HR Document deleted successfully.'));
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
