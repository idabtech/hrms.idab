{{ Form::model($employee, ['route' => ['employee.salary.update', $employee->id], 'method' => 'POST', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    @php
        // Build a JS-safe map of payslip_type_id => salary_basis
        $payslipTypeBasisMap = \App\Models\PayslipType::whereIn('id', collect($payslip_type)->keys()->toArray())
            ->pluck('salary_basis', 'id')->toArray();
        $isUk = \App\Models\Utility::isUkRequest();
    @endphp
    <div class="row">
        <div class="form-group">
            {{ Form::label('salary_type', __('Payslip Type'), ['class' => 'col-form-label']) }} <x-required></x-required>
            {{ Form::select('salary_type', $payslip_type, null, ['class' => 'form-control', 'required' => 'required', 'id' => 'salary-type-select', 'onchange' => '_updateSalaryLabel(this)']) }}
            <div class="text-xs mt-1">
                {{ __('Create payslip type.') }} <a href="{{ route('paysliptype.index') }}"><b>{{ __('Click here') }}</b></a>
            </div>
        </div>
        <div class="form-group" id="monthly-salary-wrap">
            <label for="gross-salary-input" class="col-form-label">{{ __('Gross Salary') }} <x-required></x-required></label>
            {{ Form::number('salary', null, ['class' => 'form-control', 'step' => '0.01', 'required' => 'required', 'id' => 'gross-salary-input', 'placeholder' => __('Enter gross monthly salary e.g. 35000')]) }}
            <small class="text-muted">{{ __('Total monthly salary package (CTC component)') }}</small>
        </div>
        <div class="form-group" id="basic-salary-wrap">
            <label for="salary-field-input" id="salary-field-label" class="col-form-label">{{ __('Basic Salary') }} <x-required></x-required></label>
            {{ Form::number('basic_salary', null, ['class' => 'form-control', 'step' => '0.01', 'required' => 'required', 'id' => 'salary-field-input', 'placeholder' => __('Enter basic salary e.g. 17500')]) }}
            <small class="text-muted">{{ __('Basic component of the salary (used for allowance calculations)') }}</small>
            <small id="salary-field-hint" class="text-info" style="display:none;">
                <i class="ti ti-info-circle"></i>
                {{ __('Enter the hourly rate. The payslip will calculate: hourly rate × actual hours worked = gross pay.') }}
            </small>
        </div>

        {{-- HRA and DA fields — commented out, to be removed later
        @if (!$isUk)
        <div class="form-group" id="hra-da-wrap">
            <div class="form-group" id="hra-field-wrap">
                <label for="hra-field-input" class="col-form-label">{{ __('HRA') }} (%)</label>
                {{ Form::number('hra', null, ['class' => 'form-control', 'step' => '0.01', 'id' => 'hra-field-input', 'placeholder' => __('Enter HRA percentage')]) }}
                <small id="hra-preview" class="text-muted">
                    <i class="ti ti-calculator"></i>
                    <span id="hra-preview-amount"></span>
                </small>
            </div>
            <div class="form-group" id="da-field-wrap">
                <label for="da-field-input" class="col-form-label">{{ __('DA') }} (%)</label>
                {{ Form::number('da', null, ['class' => 'form-control', 'step' => '0.01', 'id' => 'da-field-input', 'placeholder' => __('Enter DA percentage')]) }}
                <small id="da-preview" class="text-muted">
                    <i class="ti ti-calculator"></i>
                    <span id="da-preview-amount"></span>
                </small>
            </div>
        </div>
        @endif
        --}}

        <div class="form-group">
            {{ Form::label('from_account_type', __('From Account'), ['class' => 'col-form-label']) }}
            {{ Form::select('from_account_type', $from_account_type, null, ['class' => 'form-control']) }}
            <div class="text-xs mt-1">
                {{ __('Create account.') }} <a href="{{ route('accountlist.index') }}"><b>{{ __('Click here') }}</b></a>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
</div>
{{ Form::close() }}

<script>
    var _salaryBasisMap = @json($payslipTypeBasisMap);
    var _isUk = {{ $isUk ? 'true' : 'false' }};
    var _basicSalary = {{ (float) $employee->basic_salary }};

    function _updateSalaryLabel(selectEl) {
        var selectedId = parseInt(selectEl.value);
        var basis = _salaryBasisMap[selectedId] || 'monthly';
        var label = document.getElementById('salary-field-label');
        var input = document.getElementById('salary-field-input');
        var hint  = document.getElementById('salary-field-hint');
        var monthlyWrap = document.getElementById('monthly-salary-wrap');
        var basicWrap   = document.getElementById('basic-salary-wrap');
        if (!label || !input || !hint) return;
        if (basis === 'hourly') {
            // Hourly: only show the rate field (repurpose basic salary field), hide gross
            label.innerHTML    = '{{ __('Hourly Rate (per hour)') }} <span class="text-danger">*</span>';
            input.placeholder  = '{{ __('Enter rate per hour e.g. 15.50') }}';
            hint.style.display = 'block';
            if (monthlyWrap) monthlyWrap.style.display = 'none';
        } else {
            // Monthly: show both Gross Salary and Basic Salary fields
            label.innerHTML    = '{{ __('Basic Salary') }} <span class="text-danger">*</span>';
            input.placeholder  = '{{ __('Enter basic salary e.g. 17500') }}';
            hint.style.display = 'none';
            if (monthlyWrap) monthlyWrap.style.display = 'block';
        }
    }

    function _updateHraDaPreview() {
        // HRA/DA preview — commented out, to be removed later
        /*
        if (_isUk) return;
        var basicSalary = parseFloat(document.getElementById('salary-field-input').value) || _basicSalary;
        var hraEl = document.getElementById('hra-field-input');
        var daEl = document.getElementById('da-field-input');
        var hraPreview = document.getElementById('hra-preview-amount');
        var daPreview = document.getElementById('da-preview-amount');

        if (hraEl && hraPreview) {
            var hraPercent = parseFloat(hraEl.value) || 0;
            var hraAmount = (hraPercent * basicSalary / 100).toFixed(2);
            hraPreview.textContent = hraPercent > 0 ? '= ' + hraAmount : '';
        }
        if (daEl && daPreview) {
            var daPercent = parseFloat(daEl.value) || 0;
            var daAmount = (daPercent * basicSalary / 100).toFixed(2);
            daPreview.textContent = daPercent > 0 ? '= ' + daAmount : '';
        }
        */
    }

    // Initialize
    (function () {
        var sel = document.getElementById('salary-type-select');
        if (sel) {
            _updateSalaryLabel(sel);
            sel.addEventListener('change', function () { _updateSalaryLabel(this); });
        }

        if (!_isUk) {
            var salaryInput = document.getElementById('salary-field-input');
            // HRA/DA event listeners — commented out, to be removed later
            // var hraInput = document.getElementById('hra-field-input');
            // var daInput = document.getElementById('da-field-input');

            if (salaryInput) salaryInput.addEventListener('input', _updateHraDaPreview);
            // if (hraInput) hraInput.addEventListener('input', _updateHraDaPreview);
            // if (daInput) daInput.addEventListener('input', _updateHraDaPreview);

            // Show preview on load
            // _updateHraDaPreview();
        }
    })();
</script>
