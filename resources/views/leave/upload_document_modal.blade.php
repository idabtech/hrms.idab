{{ Form::open(['route' => 'leave.store-document-upload', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <input type="hidden" name="leave_id" value="{{ $leave->id }}">

    @if($docRequest && !empty($docRequest->request_reason))
        <div class="alert alert-info border-0 shadow-sm mb-3">
            <h6 class="alert-heading fw-bold mb-1"><i class="ti ti-info-circle me-1"></i>{{ __('Request Note from HR/Admin:') }}</h6>
            <p class="mb-0 text-dark">{{ $docRequest->request_reason }}</p>
            @if(!empty($docRequest->required_docs))
                <small class="d-block mt-2 text-primary fw-semibold"><i class="ti ti-file-description me-1"></i>{{ __('Required:') }} {{ $docRequest->required_docs }}</small>
            @endif
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="documents" class="col-form-label fw-bold">{{ __('Select Documents') }}</label><x-required></x-required>
                <input type="file" name="documents[]" id="documents" class="form-control" multiple required accept=".pdf,.png,.jpg,.jpeg,.doc,.docx">
                <small class="text-muted d-block mt-1">{{ __('You can select multiple files at once. Allowed formats: PDF, PNG, JPG, JPEG, DOC, DOCX. Max file size: 10MB.') }}</small>
            </div>
        </div>
    </div>

    @if($docRequest && !empty($docRequest->file_paths))
        <div class="mb-3">
            <label class="form-label fw-bold small text-secondary">{{ __('Previously Uploaded Files:') }}</label>
            <ul class="list-group list-group-flush border rounded">
                @foreach($docRequest->file_paths as $index => $path)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-dark small"><i class="ti ti-paperclip me-1 text-primary"></i>{{ $docRequest->file_names[$index] ?? ('Document ' . ($index + 1)) }}</span>
                        <a href="{{ asset($path) }}" target="_blank" class="btn btn-xs btn-outline-primary py-1 px-2 rounded">
                            <i class="ti ti-eye me-1"></i>{{ __('View') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <input type="submit" value="{{ __('Upload Documents') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
