{{ Form::model($employee, ['route' => ['employee.salary.update', $employee->id], 'method' => 'POST', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    @php
        // Build a JS-safe map of payslip_type_id => salary_basis
        $payslipTypeBasisMap = \App\Models\PayslipType::whereIn('id', collect($payslip_type)->keys()->toArray())
            ->pluck('salary_basis', 'id')->toArray();
    @endphp
    <div class="row">
        <div class="form-group">
            {{ Form::label('salary_type', __('Payslip Type'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::select('salary_type', $payslip_type, null, ['class' => 'form-control', 'required' => 'required', 'id' => 'salary-type-select', 'onchange' => '_updateSalaryLabel(this)']) }}
            <div class="text-xs mt-1">
                {{ __('Create payslip type.') }} <a href="{{ route('paysliptype.index') }}"><b>{{ __('Click here') }}</b></a>
            </div>
        </div>
        <div class="form-group">
            <label for="salary-field-input" id="salary-field-label" class="col-form-label">{{ __('Monthly Salary') }} *</label>
            {{ Form::number('salary', null, ['class' => 'form-control', 'step' => '0.01', 'required' => 'required', 'id' => 'salary-field-input', 'placeholder' => __('Enter monthly salary amount')]) }}
            <small id="salary-field-hint" class="text-info" style="display:none;">
                <i class="ti ti-info-circle"></i>
                {{ __('Enter the hourly rate. The payslip will calculate: hourly rate × actual hours worked = gross pay.') }}
            </small>
        </div>
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
    <button type="submit" class="btn  btn-primary">{{ __('Save') }}</button>
</div>
{{ Form::close() }}

{{-- Script is AFTER the form elements so the select exists when the IIFE runs --}}
<script>
    var _salaryBasisMap = @json($payslipTypeBasisMap);
    function _updateSalaryLabel(selectEl) {
        var selectedId = parseInt(selectEl.value);
        var basis = _salaryBasisMap[selectedId] || 'monthly';
        var label = document.getElementById('salary-field-label');
        var input = document.getElementById('salary-field-input');
        var hint  = document.getElementById('salary-field-hint');
        if (!label || !input || !hint) return;
        if (basis === 'hourly') {
            label.textContent  = '{{ __('Hourly Rate (per hour)') }} *';
            input.placeholder  = '{{ __('Enter rate per hour e.g. 15.50') }}';
            hint.style.display = 'block';
        } else {
            label.textContent  = '{{ __('Monthly Salary') }} *';
            input.placeholder  = '{{ __('Enter monthly salary amount') }}';
            hint.style.display = 'none';
        }
    }
    // Runs immediately — AJAX modal HTML is fully in the DOM at this point
    (function () {
        var sel = document.getElementById('salary-type-select');
        if (sel) {
            _updateSalaryLabel(sel);
            sel.addEventListener('change', function () { _updateSalaryLabel(this); });
        }
    })();
</script>
