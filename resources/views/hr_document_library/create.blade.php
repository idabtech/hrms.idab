{{ Form::open(['route' => 'hr-document-library.store', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'hr-upload-form']) }}
<div class="modal-body">
    <div class="row">
        <!-- Select Folder -->
        <div class="form-group col-md-6">
            {{ Form::label('folder_id', __('Select Folder'), ['class' => 'form-label']) }}<x-required></x-required>
            <select name="folder_id" class="form-control select2" required>
                <option value="" disabled {{ (!isset($folderId) || empty($folderId)) ? 'selected' : '' }}>-- {{ __('Select Folder (Required)') }} --</option>
                @foreach ($folders as $id => $name)
                    <option value="{{ $id }}" {{ (isset($folderId) && $folderId == $id) ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Category -->
        <div class="form-group col-md-6">
            {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
            {{ Form::text('category', null, ['class' => 'form-control', 'placeholder' => __('e.g. Offer Letter, Policy'), 'list' => 'category_list']) }}
            <datalist id="category_list">
                <option value="Offer Letter">
                <option value="Employment Contract">
                <option value="NOC Certificate">
                <option value="Experience Certificate">
                <option value="Company Policy">
                <option value="Warning Letter">
            </datalist>
        </div>

        <!-- Dropzone Area -->
        <div class="form-group col-md-12 mt-2">
            {{ Form::label('documents', __('Upload Documents'), ['class' => 'form-label']) }}<x-required></x-required>
            
            <div id="dropzone-area" class="border-2 border-dashed rounded-3 p-4 text-center bg-light cursor-pointer hover-bg-light transition" style="border: 2px dashed #0d6efd; background-color: #f8f9fa;">
                <input type="file" name="documents[]" id="dropzone-file-input" class="d-none" multiple required>
                <div class="py-3">
                    <i class="ti ti-cloud-upload text-primary" style="font-size: 3.5rem;"></i>
                    <h5 class="mt-2 fw-bold text-dark">{{ __('Drag & Drop files here or click to browse') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Supports multiple file selection (.pdf, .docx, .doc, .txt, .jpg, .png, etc.)') }}</p>
                </div>
            </div>

            <!-- File Preview List -->
            <div id="file-preview-list" class="mt-3"></div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Upload Documents') }}" class="btn btn-primary" id="upload-submit-btn">
</div>
{{ Form::close() }}

<script>
    (function () {
        const dropzoneArea = document.getElementById('dropzone-area');
        const fileInput = document.getElementById('dropzone-file-input');
        const previewList = document.getElementById('file-preview-list');

        if (dropzoneArea && fileInput) {
            // Click to select files
            dropzoneArea.addEventListener('click', () => fileInput.click());

            // Drag and drop events
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

            dropzoneArea.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    updatePreview(files);
                }
            });

            fileInput.addEventListener('change', () => {
                updatePreview(fileInput.files);
            });

            function updatePreview(files) {
                previewList.innerHTML = '';
                if (files.length > 0) {
                    const heading = document.createElement('h6');
                    heading.className = 'fw-bold text-dark mb-2';
                    heading.innerHTML = `<i class="ti ti-files me-1 text-primary"></i>Selected Files (${files.length}):`;
                    previewList.appendChild(heading);

                    const listGroup = document.createElement('div');
                    listGroup.className = 'list-group shadow-sm';

                    Array.from(files).forEach((file, index) => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item d-flex align-items-center justify-content-between py-2 px-3';
                        
                        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        item.innerHTML = `
                            <div class="d-flex align-items-center text-truncate me-2">
                                <i class="ti ti-file-description text-primary fs-4 me-2"></i>
                                <div class="text-truncate">
                                    <strong class="d-block text-truncate small">${file.name}</strong>
                                    <small class="text-muted">${sizeMB} MB</small>
                                </div>
                            </div>
                            <span class="badge bg-light-success text-success"><i class="ti ti-check me-1"></i>Ready</span>
                        `;
                        listGroup.appendChild(item);
                    });

                    previewList.appendChild(listGroup);
                }
            }
        }
    })();
</script>
