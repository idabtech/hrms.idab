<form id="form-verify-company-otp" method="POST" action="{{ route('verify.company.login.otp', $company->id) }}">
    @csrf
    <div class="modal-body">
        <p class="text-muted mb-3">
            {{ __('To access this company account, please click "Send Security OTP" to send a 6-digit code to') }}
            <strong class="text-dark">{{ $company->name }}</strong> (<span class="text-primary">{{ $company->email }}</span>).
        </p>

        <!-- Step 1: Send OTP Button -->
        <div id="company-step-send-otp-container" class="mb-3">
            <button type="button" class="btn btn-primary w-100" id="btn-send-company-initial-otp" data-url="{{ route('send.company.login.otp', $company->id) }}">
                <i class="ti ti-mail me-1"></i>{{ __('Send Security OTP to Company Email') }}
            </button>
        </div>

        <!-- Step 2: Form fields (Revealed after email is sent) -->
        <div id="company-step-otp-form-fields" class="d-none">
            <div class="alert alert-success py-2 px-3 mb-3 small" role="alert">
                <i class="ti ti-check me-1"></i>{{ __('OTP email sent to company registered address! Please enter code below.') }}
            </div>

            <div class="form-group mb-3">
                <label class="form-label">{{ __('Enter 6-Digit OTP Code') }} <x-required></x-required></label>
                <input type="text" name="otp_code" id="otp_code_input" class="form-control text-center fw-bold fs-5" 
                    placeholder="123456" maxlength="6" pattern="[0-9]{6}" autocomplete="off" style="letter-spacing: 4px;">
                <div class="invalid-feedback" id="otp-error-msg"></div>
            </div>

            <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded border mb-2">
                <span class="text-muted small">
                    <i class="ti ti-clock me-1 text-warning"></i>{{ __('OTP Expires in:') }}
                </span>
                <span class="badge bg-warning text-dark fw-bold" id="otp-timer-badge">05:00</span>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <button type="submit" class="btn btn-primary d-none" id="btn-submit-otp">
            {{ __('Verify & Login As Company') }}
        </button>
        <button type="button" class="btn btn-outline-secondary d-none" id="btn-resend-otp" data-url="{{ route('send.company.login.otp', $company->id) }}">
            {{ __('Resend OTP') }}
        </button>
    </div>
</form>

<script>
(function () {
    const sendCompanyInitialBtn = document.getElementById('btn-send-company-initial-otp');
    const companyStepSendContainer = document.getElementById('company-step-send-otp-container');
    const companyStepOtpFormFields = document.getElementById('company-step-otp-form-fields');
    const form = document.getElementById('form-verify-company-otp');

    let timeLeft = 300;
    let timerInterval = null;
    const timerBadge = document.getElementById('otp-timer-badge');
    const otpInput = document.getElementById('otp_code_input');
    const submitBtn = document.getElementById('btn-submit-otp');
    const resendBtn = document.getElementById('btn-resend-otp');
    const errorMsg = document.getElementById('otp-error-msg');

    function startCountdown() {
        timeLeft = 300;
        if (timerInterval) clearInterval(timerInterval);
        timerBadge.className = 'badge bg-warning text-dark fw-bold';
        submitBtn.disabled = false;

        timerInterval = setInterval(function () {
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                timerBadge.textContent = 'Expired';
                timerBadge.className = 'badge bg-danger text-white fw-bold';
                submitBtn.disabled = true;
                if (errorMsg) {
                    errorMsg.textContent = '{{ __("OTP has expired. Please click Resend OTP.") }}';
                    errorMsg.style.display = 'block';
                }
            } else {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerBadge.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                timeLeft--;
            }
        }, 1000);
    }

    // Step 1: Click "Send Security OTP to Company Email"
    if (sendCompanyInitialBtn) {
        sendCompanyInitialBtn.addEventListener('click', function () {
            sendCompanyInitialBtn.disabled = true;
            sendCompanyInitialBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>{{ __("Sending...") }}';

            fetch(sendCompanyInitialBtn.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    companyStepSendContainer.classList.add('d-none');
                    companyStepOtpFormFields.classList.remove('d-none');
                    submitBtn.classList.remove('d-none');
                    resendBtn.classList.remove('d-none');
                    otpInput.focus();
                    startCountdown();
                } else {
                    sendCompanyInitialBtn.disabled = false;
                    sendCompanyInitialBtn.innerHTML = '<i class="ti ti-mail me-1"></i>{{ __("Send Security OTP to Company Email") }}';
                    alert(data.message || 'Failed to send OTP email.');
                }
            })
            .catch(err => {
                sendCompanyInitialBtn.disabled = false;
                sendCompanyInitialBtn.innerHTML = '<i class="ti ti-mail me-1"></i>{{ __("Send Security OTP to Company Email") }}';
                alert('Error sending OTP: ' + err.message);
            });
        });
    }

    // Step 2: Form submission for OTP verification
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const otpValue = otpInput.value.trim();
            if (otpValue.length !== 6) {
                otpInput.classList.add('is-invalid');
                errorMsg.textContent = '{{ __("Please enter a valid 6-digit OTP code.") }}';
                errorMsg.style.display = 'block';
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __("Verifying...") }}';

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = data.redirect_url;
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '{{ __("Verify & Login As Company") }}';
                    otpInput.classList.add('is-invalid');
                    errorMsg.textContent = data.message || '{{ __("Invalid OTP code. Please try again.") }}';
                    errorMsg.style.display = 'block';
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '{{ __("Verify & Login As Company") }}';
                alert('Verification error: ' + err.message);
            });
        });
    }

    // Resend OTP Button Handler
    if (resendBtn) {
        resendBtn.addEventListener('click', function () {
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __("Sending...") }}';

            fetch(resendBtn.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                resendBtn.disabled = false;
                resendBtn.innerHTML = '{{ __("Resend OTP") }}';
                if (data.status === 'success') {
                    startCountdown();
                    otpInput.classList.remove('is-invalid');
                    if (errorMsg) errorMsg.style.display = 'none';
                    alert(data.message || '{{ __("A new OTP has been sent successfully.") }}');
                } else {
                    alert(data.message || 'Failed to resend OTP.');
                }
            })
            .catch(err => {
                resendBtn.disabled = false;
                resendBtn.innerHTML = '{{ __("Resend OTP") }}';
                alert('Error resending OTP: ' + err.message);
            });
        });
    }
})();
</script>
