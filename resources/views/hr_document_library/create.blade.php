{{ Form::open(['route' => 'hr-document-library.store', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'hr-upload-form']) }}
<div class="modal-body">
    <div class="row">
        <!-- Target Directory Location -->
        <div class="form-group col-md-6">
            {{ Form::label('folder_id', __('Upload Location / Target Folder'), ['class' => 'form-label']) }}
            <select name="folder_id" class="form-control select2">
                <option value="" {{ (!isset($folderId) || empty($folderId)) ? 'selected' : '' }}>
                    📁 -- {{ __('Root Directory (Create Folders / Files at Root)') }} --
                </option>
                @foreach ($folders as $id => $name)
                    <option value="{{ $id }}" {{ (isset($folderId) && $folderId == $id) ? 'selected' : '' }}>
                        📂 {{ $name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1">
                {{ __('If uploading a folder, it will be created inside this location.') }}
            </small>
        </div>

        <!-- Category -->
        <div class="form-group col-md-6">
            {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
            {{ Form::text('category', null, ['class' => 'form-control', 'placeholder' => __('e.g. Offer Letter, Policy, HR Docs'), 'list' => 'category_list']) }}
            <datalist id="category_list">
                <option value="Offer Letter">
                <option value="Employment Contract">
                <option value="NOC Certificate">
                <option value="Experience Certificate">
                <option value="Company Policy">
                <option value="Warning Letter">
            </datalist>
        </div>

        <!-- Upload Mode Switcher Tabs -->
        <div class="col-md-12 mt-2">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <label class="form-label mb-0 fw-semibold">{{ __('Upload Mode') }}</label>
                <div class="btn-group btn-group-sm" role="group" id="upload-mode-group">
                    <button type="button" class="btn btn-outline-primary active" id="btn-mode-folder">
                        <i class="ti ti-folder-upload me-1"></i>{{ __('Upload Folder') }}
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btn-mode-files">
                        <i class="ti ti-file-upload me-1"></i>{{ __('Upload Files') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Dropzone Area -->
        <div class="form-group col-md-12">
            <div id="dropzone-area" class="border-2 border-dashed rounded-3 p-4 text-center cursor-pointer transition shadow-sm" style="border: 2px dashed #0d6efd; background-color: #f8f9fa;">
                <!-- Master input for form submission -->
                <input type="file" name="documents[]" id="main-file-input" class="d-none" multiple required>
                <!-- Auxiliary folder input -->
                <input type="file" id="folder-file-input" class="d-none" webkitdirectory directory multiple>

                <!-- Hidden inputs container for relative paths -->
                <div id="relative-paths-container"></div>

                <div class="py-3" id="dropzone-prompt">
                    <i class="ti ti-folder-upload text-primary mb-2" id="dropzone-icon" style="font-size: 3.8rem;"></i>
                    <h5 class="fw-bold text-dark mb-1" id="dropzone-heading">{!! __('Click or Drag & Drop a Folder here') !!}</h5>
                    <p class="text-muted small mb-0" id="dropzone-subtext">
                        {!! __('Select an entire folder from your computer. Folders & subfolders will be created automatically!') !!}
                    </p>
                </div>
            </div>

            <!-- File / Folder Preview Panel -->
            <div id="file-preview-panel" class="mt-3 d-none">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center" id="preview-summary-text">
                        <i class="ti ti-folders me-1 text-primary"></i>{{ __('Detected Items') }}
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fs-7" id="btn-add-more">
                            <i class="ti ti-plus me-1"></i>{!! __('Add More') !!}
                        </button>
                        <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 fs-7" id="btn-clear-selection">
                            <i class="ti ti-x me-1"></i>{{ __('Clear Selection') }}
                        </button>
                    </div>
                </div>
                <div id="preview-items-list" class="list-group shadow-sm overflow-auto" style="max-height: 220px;"></div>
            </div>

            <!-- Upload Progress Bar Container -->
            <div id="upload-progress-container" class="mt-3 d-none">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="fw-bold text-primary" id="progress-status-text">{{ __('Preparing upload...') }}</small>
                    <small class="text-muted fw-bold" id="progress-percentage-text">0%</small>
                </div>
                <div class="progress" style="height: 10px; border-radius: 6px; background-color: #e9ecef;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="upload-progress-bar" role="progressbar" style="width: 0%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{!! __('Upload & Create Folders') !!}" class="btn btn-primary" id="upload-submit-btn">
</div>
{{ Form::close() }}

<script>
(function () {
    const hrUploadForm = document.getElementById('hr-upload-form');
    const dropzoneArea = document.getElementById('dropzone-area');
    const mainFileInput = document.getElementById('main-file-input');
    const folderFileInput = document.getElementById('folder-file-input');
    const relPathsContainer = document.getElementById('relative-paths-container');
    const previewPanel = document.getElementById('file-preview-panel');
    const previewSummary = document.getElementById('preview-summary-text');
    const previewList = document.getElementById('preview-items-list');
    const clearBtn = document.getElementById('btn-clear-selection');
    const addMoreBtn = document.getElementById('btn-add-more');
    const submitBtn = document.getElementById('upload-submit-btn');

    const progressContainer = document.getElementById('upload-progress-container');
    const progressBar = document.getElementById('upload-progress-bar');
    const progressStatus = document.getElementById('progress-status-text');
    const progressPercent = document.getElementById('progress-percentage-text');

    const btnModeFolder = document.getElementById('btn-mode-folder');
    const btnModeFiles = document.getElementById('btn-mode-files');
    const dropzoneIcon = document.getElementById('dropzone-icon');
    const dropzoneHeading = document.getElementById('dropzone-heading');
    const dropzoneSubtext = document.getElementById('dropzone-subtext');

    let currentMode = '{{ isset($uploadType) && $uploadType == "files" ? "files" : "folder" }}';
    let selectedFilesList = []; // array of { file: File, relPath: string }

    setUploadMode(currentMode);

    // Form Submit Handler via Chunked AJAX Batches (Bypasses PHP max_file_uploads=20 limit!)
    if (hrUploadForm) {
        hrUploadForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const fileItems = selectedFilesList.filter(item => item.file !== null);
            const emptyFolderItems = selectedFilesList.filter(item => item.file === null);

            if (fileItems.length === 0 && emptyFolderItems.length === 0) {
                alert('{{ __("Please select files or folders to upload.") }}');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.value = '{{ __("Uploading...") }}';
            progressContainer.classList.remove('d-none');

            const batchSize = 10; // 10 files per request (100% safe for max_file_uploads=20)
            const totalFiles = fileItems.length;
            const totalBatches = Math.ceil(totalFiles / batchSize) || (emptyFolderItems.length > 0 ? 1 : 1);

            const folderIdVal = hrUploadForm.querySelector('[name="folder_id"]').value;
            const categoryVal = hrUploadForm.querySelector('[name="category"]').value;
            const csrfToken = hrUploadForm.querySelector('[name="_token"]').value;
            const storeUrl = hrUploadForm.action;

            let uploadedFilesCount = 0;

            try {
                for (let b = 0; b < totalBatches; b++) {
                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    formData.append('folder_id', folderIdVal);
                    formData.append('category', categoryVal);

                    // Send empty folders in the 1st batch only
                    if (b === 0 && emptyFolderItems.length > 0) {
                        emptyFolderItems.forEach(item => {
                            formData.append('folder_paths[]', item.relPath);
                        });
                    }

                    const batchSlice = fileItems.slice(b * batchSize, (b + 1) * batchSize);
                    batchSlice.forEach(item => {
                        formData.append('documents[]', item.file);
                        formData.append('relative_paths[]', item.relPath);
                    });

                    const currentPercent = Math.round((b / totalBatches) * 100);
                    progressBar.style.width = currentPercent + '%';
                    progressPercent.textContent = currentPercent + '%';
                    progressStatus.textContent = `Uploading batch ${b + 1} of ${totalBatches} (${uploadedFilesCount}/${totalFiles} files)...`;

                    const response = await fetch(storeUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();
                    if (response.ok && result.status === 'success') {
                        uploadedFilesCount += (result.count || batchSlice.length);
                    } else {
                        throw new Error(result.message || 'Upload failed');
                    }
                }

                progressBar.style.width = '100%';
                progressPercent.textContent = '100%';
                progressStatus.textContent = 'Upload Completed Successfully!';

                setTimeout(() => {
                    window.location.reload();
                }, 400);

            } catch (err) {
                alert('Upload Error: ' + err.message);
                submitBtn.disabled = false;
                submitBtn.value = '{!! __("Upload & Create Folders") !!}';
                progressContainer.classList.add('d-none');
            }
        });
    }

    // Mode Switcher Buttons
    btnModeFolder.addEventListener('click', function () {
        setUploadMode('folder');
    });

    btnModeFiles.addEventListener('click', function () {
        setUploadMode('files');
    });

    function setUploadMode(mode) {
        currentMode = mode;
        clearSelection();

        if (mode === 'folder') {
            btnModeFolder.classList.add('active');
            btnModeFiles.classList.remove('active');
            dropzoneIcon.className = 'ti ti-folder-upload text-primary mb-2';
            dropzoneHeading.innerHTML = `{!! __("Click or Drag & Drop Folders here") !!}`;
            dropzoneSubtext.innerHTML = `{!! __("Select folders from your computer. Folders & subfolders will be created automatically!") !!}`;
            submitBtn.value = `{!! __("Upload & Create Folders") !!}`;
        } else {
            btnModeFiles.classList.add('active');
            btnModeFolder.classList.remove('active');
            dropzoneIcon.className = 'ti ti-file-upload text-primary mb-2';
            dropzoneHeading.innerHTML = `{!! __("Click or Drag & Drop Files here") !!}`;
            dropzoneSubtext.innerHTML = `{!! __("Supports multiple file selection (.pdf, .docx, .png, etc.)") !!}`;
            submitBtn.value = `{!! __("Upload Files") !!}`;
        }
    }

    // Dropzone Click Trigger
    dropzoneArea.addEventListener('click', function () {
        if (currentMode === 'folder') {
            folderFileInput.click();
        } else {
            mainFileInput.click();
        }
    });

    if (addMoreBtn) {
        addMoreBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (currentMode === 'folder') {
                folderFileInput.click();
            } else {
                mainFileInput.click();
            }
        });
    }

    // File Input Changes
    folderFileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            processFileInputs(this.files);
        }
    });

    mainFileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            processFileInputs(this.files);
        }
    });

    // Drag and Drop Handling
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzoneArea.style.backgroundColor = '#e9ecef';
            dropzoneArea.style.borderColor = '#0b5ed7';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzoneArea.style.backgroundColor = '#f8f9fa';
            dropzoneArea.style.borderColor = '#0d6efd';
        }, false);
    });

    dropzoneArea.addEventListener('drop', async (e) => {
        const dt = e.dataTransfer;
        if (!dt) return;

        if (dt.items && dt.items.length > 0) {
            const entryPromises = [];
            for (let i = 0; i < dt.items.length; i++) {
                const item = dt.items[i];
                if (item.webkitGetAsEntry) {
                    const entry = item.webkitGetAsEntry();
                    if (entry) {
                        entryPromises.push(scanEntry(entry));
                    }
                }
            }

            if (entryPromises.length > 0) {
                const scannedResults = await Promise.all(entryPromises);
                const flatFiles = scannedResults.flat();
                if (flatFiles.length > 0) {
                    populateSelection(flatFiles);
                    return;
                }
            }
        }

        if (dt.files && dt.files.length > 0) {
            processFileInputs(dt.files);
        }
    });

    function isSystemFile(fileName) {
        return !fileName || fileName.startsWith('.') || fileName.startsWith('~$') || fileName === 'Thumbs.db' || fileName === 'desktop.ini';
    }

    // Batch reader for DirectoryReader (handles 100+ items per directory)
    function readAllEntries(dirReader) {
        return new Promise((resolve) => {
            let allEntries = [];
            function fetchBatch() {
                dirReader.readEntries((results) => {
                    if (!results || results.length === 0) {
                        resolve(allEntries);
                    } else {
                        allEntries = allEntries.concat(Array.from(results));
                        fetchBatch();
                    }
                }, () => resolve(allEntries));
            }
            fetchBatch();
        });
    }

    // Recursive directory scanner for webkitGetAsEntry
    async function scanEntry(entry, path = '') {
        if (entry.isFile) {
            if (isSystemFile(entry.name)) return [];
            return new Promise((resolve) => {
                entry.file((file) => {
                    const relPath = path ? `${path}/${file.name}` : (file.webkitRelativePath || file.name);
                    resolve([{ file, relPath }]);
                }, () => resolve([]));
            });
        } else if (entry.isDirectory) {
            if (isSystemFile(entry.name)) return [];
            const dirReader = entry.createReader();
            const dirPath = path ? `${path}/${entry.name}` : entry.name;
            const entries = await readAllEntries(dirReader);
            if (entries.length === 0) {
                // Return empty folder path indicator so empty folder gets created too!
                return [{ file: null, relPath: dirPath + '/' }];
            }
            const childPromises = entries.map(child => scanEntry(child, dirPath));
            const childResults = await Promise.all(childPromises);
            return childResults.flat();
        }
        return [];
    }

    function processFileInputs(files) {
        const fileList = Array.from(files)
            .filter(file => !isSystemFile(file.name))
            .map(file => {
                const relPath = file.webkitRelativePath || file.name;
                return { file, relPath };
            });
        populateSelection(fileList);
    }

    function populateSelection(newItems) {
        const existingPaths = new Set(selectedFilesList.map(i => i.relPath));
        newItems.forEach(item => {
            if (!existingPaths.has(item.relPath)) {
                selectedFilesList.push(item);
                existingPaths.add(item.relPath);
            }
        });

        relPathsContainer.innerHTML = '';
        const dataTransfer = new DataTransfer();

        selectedFilesList.forEach((item) => {
            if (item.file) {
                dataTransfer.items.add(item.file);

                // Append hidden input for file relative path
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'relative_paths[]';
                hidden.value = item.relPath;
                relPathsContainer.appendChild(hidden);
            } else {
                // Append hidden input for empty folder path
                const hiddenFolder = document.createElement('input');
                hiddenFolder.type = 'hidden';
                hiddenFolder.name = 'folder_paths[]';
                hiddenFolder.value = item.relPath;
                relPathsContainer.appendChild(hiddenFolder);
            }
        });

        mainFileInput.files = dataTransfer.files;
        renderPreview();
    }

    function clearSelection() {
        selectedFilesList = [];
        mainFileInput.value = '';
        folderFileInput.value = '';
        relPathsContainer.innerHTML = '';
        previewPanel.classList.add('d-none');
        previewList.innerHTML = '';
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', clearSelection);
    }

    function renderPreview() {
        if (selectedFilesList.length === 0) {
            previewPanel.classList.add('d-none');
            return;
        }

        previewPanel.classList.remove('d-none');

        // Extract folder names from relative paths
        const detectedFolders = new Set();
        selectedFilesList.forEach(item => {
            if (item.relPath.includes('/')) {
                const parts = item.relPath.split('/');
                parts.pop(); // remove filename
                detectedFolders.add(parts.join(' / '));
            }
        });

        if (detectedFolders.size > 0) {
            previewSummary.innerHTML = `<i class="ti ti-folders me-1 text-warning"></i>${detectedFolders.size} {{ __('Folder(s)') }}, ${selectedFilesList.length} {{ __('File(s) Selected') }}`;
        } else {
            previewSummary.innerHTML = `<i class="ti ti-files me-1 text-primary"></i>${selectedFilesList.length} {{ __('File(s) Selected') }}`;
        }

        previewList.innerHTML = '';

        // Display preview items
        selectedFilesList.slice(0, 50).forEach(item => {
            const listItem = document.createElement('div');
            listItem.className = 'list-group-item d-flex align-items-center justify-content-between py-2 px-3';
            
            const sizeMB = item.file ? (item.file.size / (1024 * 1024)).toFixed(2) : '0.00';
            const pathParts = item.relPath.split('/');
            const fileName = pathParts.pop();
            const folderBreadcrumb = pathParts.length > 0 ? pathParts.join(' / ') : '';

            listItem.innerHTML = `
                <div class="d-flex align-items-center text-truncate me-2">
                    <i class="ti ${folderBreadcrumb ? 'ti-folder-check text-warning' : 'ti-file-description text-primary'} fs-4 me-2"></i>
                    <div class="text-truncate">
                        <strong class="d-block text-truncate small text-dark">${fileName}</strong>
                        <small class="text-muted">${folderBreadcrumb ? '<i class="ti ti-folder me-1"></i>' + folderBreadcrumb + ' • ' : ''}${sizeMB} MB</small>
                    </div>
                </div>
                <span class="badge bg-light-success text-success"><i class="ti ti-check me-1"></i>Ready</span>
            `;
            previewList.appendChild(listItem);
        });

        if (selectedFilesList.length > 50) {
            const moreItem = document.createElement('div');
            moreItem.className = 'list-group-item text-center text-muted small py-2 bg-light';
            moreItem.innerHTML = `+ ${selectedFilesList.length - 50} more files...`;
            previewList.appendChild(moreItem);
        }
    }
})();
</script>
