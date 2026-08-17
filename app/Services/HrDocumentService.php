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
        $doc->created_by = (Auth::user()->type === 'super admin') ? Auth::user()->id : Auth::user()->creatorId();

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
     * Resolve or create folder hierarchy from relative path (e.g. "Vacancy/Job Offer/doc.pdf").
     * Returns the target folder_id for the file.
     */
    protected function resolveFolderFromRelativePath(?string $relativePath, ?int $baseFolderId, int $creatorId): ?int
    {
        if (empty($relativePath)) {
            return $baseFolderId;
        }

        $dirPath = dirname($relativePath);
        if ($dirPath === '.' || empty($dirPath)) {
            return $baseFolderId;
        }

        $segments = array_filter(explode('/', str_replace('\\', '/', $dirPath)));
        $currentParentId = $baseFolderId;

        foreach ($segments as $segment) {
            $segment = trim($segment);
            if (empty($segment) || $segment === '.') continue;

            $folderQuery = \App\Models\HrDocumentFolder::where('name', $segment)->where('created_by', $creatorId);
            if ($currentParentId) {
                $folderQuery->where('parent_id', $currentParentId);
            } else {
                $folderQuery->whereNull('parent_id');
            }

            $folder = $folderQuery->first();

            if (!$folder) {
                $folder = \App\Models\HrDocumentFolder::create([
                    'name'       => $segment,
                    'parent_id'  => $currentParentId,
                    'created_by' => $creatorId,
                ]);
            }

            $currentParentId = $folder->id;
        }

        return $currentParentId;
    }

    /**
     * Store multiple uploaded documents at once into a specific folder or auto-created folder hierarchy.
     */
    public function storeMultipleDocuments(Request $request): array
    {
        $createdDocs = [];
        $baseFolderId = $request->folder_id ?: null;
        $userCategory = trim($request->category ?? '');
        $description = $request->description ?? null;
        $creatorId = (Auth::user()->type === 'super admin') ? Auth::user()->id : Auth::user()->creatorId();
        $relativePaths = $request->input('relative_paths', []);
        $folderPaths = $request->input('folder_paths', []);

        $dir = 'uploads/hr_document_library/';
        $publicDir = public_path($dir);
        $storageDir = storage_path('app/public/' . $dir);
        if (!file_exists($publicDir)) @mkdir($publicDir, 0777, true);
        if (!file_exists($storageDir)) @mkdir($storageDir, 0777, true);

        // Pre-create empty folders from folder_paths
        foreach ($folderPaths as $folderPath) {
            if (!empty($folderPath)) {
                $this->resolveFolderFromRelativePath($folderPath, $baseFolderId, $creatorId);
            }
        }

        // Pre-create all folders from file relative paths
        foreach ($relativePaths as $relPath) {
            if (!empty($relPath)) {
                $this->resolveFolderFromRelativePath($relPath, $baseFolderId, $creatorId);
            }
        }

        if ($request->hasFile('documents')) {
            $files = $request->file('documents');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $index => $file) {
                if (!$file || !$file->isValid()) continue;

                $filenameWithExt = $file->getClientOriginalName();
                if (empty($filenameWithExt) || str_starts_with($filenameWithExt, '.') || str_starts_with($filenameWithExt, '~$') || in_array($filenameWithExt, ['Thumbs.db', 'desktop.ini'])) {
                    continue;
                }

                $relPath = $relativePaths[$index] ?? null;
                $targetFolderId = $this->resolveFolderFromRelativePath($relPath, $baseFolderId, $creatorId);

                // Determine Category: if user provided category use it, else fallback to folder name or 'General'
                if (!empty($userCategory)) {
                    $docCategory = $userCategory;
                } elseif ($targetFolderId) {
                    $targetFolder = \App\Models\HrDocumentFolder::find($targetFolderId);
                    $docCategory = $targetFolder ? $targetFolder->name : 'General';
                } else {
                    $docCategory = 'General';
                }

                $extension = strtolower($file->getClientOriginalExtension());
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $fileNameToStore = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename) . '_' . time() . '_' . $index . '.' . $extension;

                $file->move($publicDir, $fileNameToStore);
                @copy($publicDir . '/' . $fileNameToStore, $storageDir . '/' . $fileNameToStore);

                $doc = new HrDocumentLibrary();
                $doc->title = $filename;
                $doc->category = $docCategory;
                $doc->folder_id = $targetFolderId;
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
