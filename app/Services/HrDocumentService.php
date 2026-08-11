<?php

namespace App\Services;

use App\Models\HrDocumentLibrary;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrDocumentService
{
    /**
     * Store uploaded file or document metadata in local filesystem & DB.
     */
    public function storeDocument(Request $request): HrDocumentLibrary
    {
        $doc = new HrDocumentLibrary();
        $doc->title = $request->title;
        $doc->category = $request->category ?? 'General';
        $doc->folder_id = $request->folder_id ?: null;
        $doc->description = $request->description;
        $doc->content = $request->content ?? '';
        $doc->created_by = Auth::user()->creatorId();

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $extension = strtolower($file->getClientOriginalExtension());
            $filenameWithExt = $file->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $fileNameToStore = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename) . '_' . time() . '.' . $extension;
            $dir = 'uploads/hr_document_library/';

            $publicDir = public_path($dir);
            $storageDir = storage_path('app/public/' . $dir);
            if (!file_exists($publicDir)) @mkdir($publicDir, 0777, true);
            if (!file_exists($storageDir)) @mkdir($storageDir, 0777, true);

            $path = Utility::upload_file($request, 'document', $fileNameToStore, $dir, ['max:20480']);
            if ($path['flag'] != 1) {
                $file->move($publicDir, $fileNameToStore);
                @copy($publicDir . '/' . $fileNameToStore, $storageDir . '/' . $fileNameToStore);
            }

            $doc->file_name = $filenameWithExt;
            $doc->file_path = $dir . $fileNameToStore;
        }

        $doc->save();
        return $doc;
    }

    /**
     * Store multiple uploaded documents at once into a specific folder.
     */
    public function storeMultipleDocuments(Request $request): array
    {
        $createdDocs = [];
        $folderId = $request->folder_id ?: null;
        $category = $request->category ?? 'General';
        $description = $request->description ?? null;
        $creatorId = Auth::user()->creatorId();

        $dir = 'uploads/hr_document_library/';
        $publicDir = public_path($dir);
        $storageDir = storage_path('app/public/' . $dir);
        if (!file_exists($publicDir)) @mkdir($publicDir, 0777, true);
        if (!file_exists($storageDir)) @mkdir($storageDir, 0777, true);

        if ($request->hasFile('documents')) {
            $files = $request->file('documents');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $index => $file) {
                if (!$file->isValid()) continue;

                $filenameWithExt = $file->getClientOriginalName();
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $fileNameToStore = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename) . '_' . time() . '_' . $index . '.' . $extension;

                $file->move($publicDir, $fileNameToStore);
                @copy($publicDir . '/' . $fileNameToStore, $storageDir . '/' . $fileNameToStore);

                $doc = new HrDocumentLibrary();
                $doc->title = $filename;
                $doc->category = $category;
                $doc->folder_id = $folderId;
                $doc->description = $description;
                $doc->content = '';
                $doc->created_by = $creatorId;
                $doc->file_name = $filenameWithExt;
                $doc->file_path = $dir . $fileNameToStore;
                $doc->save();

                $createdDocs[] = $doc;
            }
        } elseif ($request->hasFile('document')) {
            $createdDocs[] = $this->storeDocument($request);
        }

        return $createdDocs;
    }

    /**
     * Update existing document details & file.
     */
    public function updateDocument(Request $request, HrDocumentLibrary $doc): HrDocumentLibrary
    {
        $doc->title = $request->title;
        $doc->category = $request->category ?? $doc->category;
        $doc->description = $request->description;
        if ($request->has('content')) {
            $doc->content = $request->content;
        }

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $extension = strtolower($file->getClientOriginalExtension());
            $filenameWithExt = $file->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $fileNameToStore = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename) . '_' . time() . '.' . $extension;
            $dir = 'uploads/hr_document_library/';

            $publicDir = public_path($dir);
            $storageDir = storage_path('app/public/' . $dir);
            if (!file_exists($publicDir)) @mkdir($publicDir, 0777, true);
            if (!file_exists($storageDir)) @mkdir($storageDir, 0777, true);

            $path = Utility::upload_file($request, 'document', $fileNameToStore, $dir, ['max:20480']);
            if ($path['flag'] != 1) {
                $file->move($publicDir, $fileNameToStore);
                @copy($publicDir . '/' . $fileNameToStore, $storageDir . '/' . $fileNameToStore);
            }

            $doc->file_name = $filenameWithExt;
            $doc->file_path = $dir . $fileNameToStore;
        }

        $doc->save();
        return $doc;
    }

    /**
     * Delete document and cleanup associated stored files.
     */
    public function deleteDocument(HrDocumentLibrary $doc): bool
    {
        if (!empty($doc->file_path)) {
            if (file_exists(public_path($doc->file_path))) {
                @unlink(public_path($doc->file_path));
            }
            if (file_exists(storage_path('app/public/' . $doc->file_path))) {
                @unlink(storage_path('app/public/' . $doc->file_path));
            }
        }
        return $doc->delete();
    }
}
