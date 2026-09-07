<div class="modal-body p-4">
    @if($submission)
        <!-- Success Alert Header -->
        <div class="alert alert-success border-0 bg-light-success text-center py-3 mb-4 rounded-3">
            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-2" style="width: 44px; height: 44px;">
                <i class="ti ti-circle-check fs-2"></i>
            </div>
            <h5 class="text-success fw-bold mb-1">{{ __('HMRC FPS Submitted Successfully') }}</h5>
            <span class="badge bg-success text-white px-3 py-1 rounded-pill text-uppercase fs-7">{{ strtoupper($submission->status ?? 'SUBMITTED') }}</span>
        </div>

        <!-- HMRC Submission Reference Box -->
        <div class="mb-4">
            <label class="form-label text-muted fw-semibold small mb-1">{{ __('HMRC SUBMISSION REFERENCE NUMBER') }}</label>
            <div class="input-group">
                <input type="text" class="form-control fw-bold font-monospace bg-light" id="hmrcRefInput" value="{{ $submission->reference }}" readonly style="letter-spacing: 0.5px;">
                <button class="btn btn-primary" type="button" id="copyHmrcBtn" onclick="copyHmrcRef(this)">
                    <i class="ti ti-copy me-1"></i> {{ __('Copy') }}
                </button>
            </div>
            <small class="text-success d-none mt-1 fw-semibold" id="copySuccessMsg"><i class="ti ti-check me-1"></i> {{ __('Copied to clipboard!') }}</small>
        </div>

        <!-- Details List -->
        <div class="card border rounded-3 mb-0">
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                    <span class="text-muted fw-semibold">{{ __('Employee Name') }}</span>
                    <span class="fw-bold text-dark">{{ $employee->name }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                    <span class="text-muted fw-semibold">{{ __('National Insurance No.') }}</span>
                    <span class="badge bg-light-primary text-primary border border-primary border-opacity-25 px-2 py-1">{{ $employee->ni_number ?? __('N/A') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                    <span class="text-muted fw-semibold">{{ __('Salary Period / Month') }}</span>
                    <span class="fw-bold text-dark">{{ $salaryMonth }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                    <span class="text-muted fw-semibold">{{ __('Submission Type') }}</span>
                    <span class="badge bg-secondary">FPS (Full Payment Submission)</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                    <span class="text-muted fw-semibold">{{ __('Submitted At') }}</span>
                    <span class="fw-bold text-dark">{{ $submission->submitted_at ?? $submission->created_at }}</span>
                </li>
                <li class="list-group-item py-2.5 px-3">
                    <div class="text-muted fw-semibold mb-1">{{ __('HMRC Response') }}</div>
                    <div class="small text-muted bg-light p-2 rounded border">{{ $submission->message ?? __('FPS Submitted to HMRC') }}</div>
                </li>
            </ul>
        </div>
    @else
        <div class="alert alert-warning text-center my-3">
            <i class="ti ti-alert-triangle me-1 fs-4"></i>
            <div>{{ __('No HMRC submission record found for this payslip.') }}</div>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
</div>

<script>
function copyHmrcRef(btn) {
    var copyText = document.getElementById("hmrcRefInput");
    if (!copyText) return;
    
    copyText.select();
    copyText.setSelectionRange(0, 99999);

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(copyText.value).then(function() {
            onCopySuccess(btn);
        }).catch(function() {
            fallbackCopy(copyText, btn);
        });
    } else {
        fallbackCopy(copyText, btn);
    }
}

function fallbackCopy(copyText, btn) {
    try {
        var successful = document.execCommand('copy');
        if (successful) {
            onCopySuccess(btn);
        } else {
            prompt("Copy reference manually:", copyText.value);
        }
    } catch (err) {
        prompt("Copy reference manually:", copyText.value);
    }
}

function onCopySuccess(btn) {
    var msg = document.getElementById("copySuccessMsg");
    if (msg) {
        msg.classList.remove("d-none");
        setTimeout(function(){ msg.classList.add("d-none"); }, 3000);
    }

    if (btn) {
        var origHtml = btn.innerHTML;
        btn.innerHTML = '<i class="ti ti-check me-1"></i> {{ __("Copied!") }}';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');
        setTimeout(function() {
            btn.innerHTML = origHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
        }, 2000);
    }

    if (typeof show_toastr === 'function') {
        show_toastr('Success', '{{ __("Copied to clipboard!") }}', 'success');
    }
}
</script>
