{{ Form::open(['route' => 'leave.store-document-request', 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <input type="hidden" name="leave_id" value="{{ $leave->id }}">

    {{-- Show Submitted / Uploaded Documents if any --}}
    @if($docRequest)
        <div class="card border shadow-none mb-3">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="ti ti-file-description me-1 text-primary"></i>{{ __('Current Document Request Details') }}</span>
                @if($docRequest->status === 'uploaded')
                    <span class="badge bg-success rounded-pill px-3 py-1"><i class="ti ti-check me-1"></i>{{ __('Documents Uploaded') }}</span>
                @else
                    <span class="badge bg-warning rounded-pill px-3 py-1"><i class="ti ti-clock me-1"></i>{{ __('Pending Employee Upload') }}</span>
                @endif
            </div>
            <div class="card-body p-3">
                @if(!empty($docRequest->request_reason))
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary mb-1 small">{{ __('Request Reason / Note:') }}</label>
                        <div class="p-2 bg-light rounded text-dark fs-6">{{ $docRequest->request_reason }}</div>
                    </div>
                @endif

                @if(!empty($docRequest->required_docs))
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary mb-1 small">{{ __('Required Document Guidelines:') }}</label>
                        <div class="text-primary fw-semibold small"><i class="ti ti-paperclip me-1"></i>{{ $docRequest->required_docs }}</div>
                    </div>
                @endif

                <div class="mt-3">
                    <label class="form-label fw-bold text-secondary mb-2 small">{{ __('Submitted / Uploaded Documents:') }}</label>
                    @if(!empty($docRequest->file_paths) && count($docRequest->file_paths) > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($docRequest->file_paths as $index => $path)
                                <a href="{{ asset($path) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center">
                                    <i class="ti ti-file-text me-1"></i>{{ $docRequest->file_names[$index] ?? ('Document ' . ($index + 1)) }}
                                    <i class="ti ti-external-link ms-2"></i>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-secondary py-2 px-3 mb-0 small"><i class="ti ti-info-circle me-1"></i>{{ __('No documents uploaded by employee yet.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Hide form fields if documents have already been uploaded --}}
    @if($docRequest && $docRequest->status === 'uploaded')
        <div class="alert alert-info border-0 shadow-sm mb-0">
            <i class="ti ti-info-circle me-1"></i>{{ __('Documents have already been received for this request. If you wish to send a new document request, please click "Cancel Request" below first.') }}
        </div>
    @else
        {{-- Form Fields matching standard Edit Leave style --}}
        <h6 class="fw-bold mb-3 text-dark"><i class="ti ti-file-plus me-1 text-warning"></i>{{ $docRequest ? __('Send New / Additional Document Request') : __('Request Document from Employee') }}</h6>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="request_reason" class="col-form-label fw-bold">{{ __('Reason / Note for Request') }}</label><x-required></x-required>
                    <textarea name="request_reason" id="request_reason" class="form-control" rows="3" required placeholder="{{ __('e.g., Please submit official Doctor Certificate and Medical Prescription for Sick Leave verification.') }}">{{ $docRequest->request_reason ?? '' }}</textarea>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label for="required_docs" class="col-form-label fw-bold">{{ __('Required Document Types') }}</label>
                    <input type="text" name="required_docs" id="required_docs" class="form-control" value="{{ $docRequest->required_docs ?? '' }}" placeholder="{{ __('e.g., Doctor Certificate, Medical Bills, Fitness Certificate') }}">
                    <small class="text-muted">{{ __('Specify document names or guidelines for the employee.') }}</small>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="modal-footer">
    @if($docRequest)
        <button type="button" class="btn btn-danger" onclick="if(confirm('{{ __('Are you sure you want to cancel this document request?') }}')){ document.getElementById('cancel-doc-form-{{ $leave->id }}').submit(); }">
            {{ __('Cancel Request') }}
        </button>
    @endif
    <input type="button" value="{{ __('Close') }}" class="btn btn-light" data-bs-dismiss="modal">
    @if(!$docRequest || $docRequest->status !== 'uploaded')
        <input type="submit" value="{{ __('Send Request') }}" class="btn btn-primary">
    @endif
</div>
{{ Form::close() }}

@if($docRequest)
    <form id="cancel-doc-form-{{ $leave->id }}" action="{{ route('leave.cancel-document-request', $leave->id) }}" method="POST" style="display: none;">
        @csrf
    </form>
@endif
