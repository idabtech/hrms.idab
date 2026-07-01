{{ Form::open(['route' => ['salary-revision.update', $revision->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}

<div class="modal-body">
    <div class="row">

        {{-- Current salary (read-only info) --}}
        <div class="col-12 mb-3">
            <div class="alert alert-info py-2 mb-0">
                <span class="fw-bold">{{ __('Current Salary') }}:</span>
                {{ \Auth::user()->priceFormat($employee->salary) }}
            </div>
        </div>

        {{-- Revision Type --}}
        <div class="col-12 col-md-6">
            <div class="form-group">
                {{ Form::label('revision_type', __('Revision Type'), ['class' => 'col-form-label']) }}
                <x-required></x-required>
                {{ Form::select('revision_type', $revisionTypes, $revision->revision_type, [
                    'class'    => 'form-control',
                    'id'       => 'revision_type',
                    'required' => 'required',
                ]) }}
            </div>
        </div>

        {{-- Revision Value --}}
        <div class="col-12 col-md-6">
            <div class="form-group">
                <label class="col-form-label" id="revision_value_label">
                    {{ $revision->revision_type === 'percentage' ? __('Increase Percentage') : __('Increase Amount') }}
                </label>
                <x-required></x-required>
                {{ Form::number('revision_value', $revision->revision_value, [
                    'class'    => 'form-control',
                    'id'       => 'revision_value',
                    'step'     => '0.01',
                    'min'      => '0',
                    'required' => 'required',
                    'placeholder' => '0.00',
                ]) }}
                <small class="text-muted" id="revision_value_hint">
                    @if ($revision->revision_type === 'percentage')
                        {{ __('Enter % to add. E.g. 10 means +10% of current salary.') }}
                    @else
                        {{ __('Enter the fixed amount to add to current salary.') }}
                    @endif
                </small>
            </div>
        </div>

        {{-- Preview revised salary --}}
        <div class="col-12">
            <div class="alert alert-success py-2 mb-0" id="revised_salary_preview">
                <span class="fw-bold">{{ __('Revised Salary will be') }}:</span>
                <span id="revised_salary_value">{{ \Auth::user()->priceFormat($revision->revised_salary) }}</span>
            </div>
        </div>

        {{-- Cycle --}}
        <div class="col-12 col-md-6 mt-3">
            <div class="form-group">
                {{ Form::label('cycle_months', __('Revision Cycle'), ['class' => 'col-form-label']) }}
                <x-required></x-required>
                {{ Form::select('cycle_months', $cycleOptions, $revision->cycle_months, [
                    'class'    => 'form-control',
                    'required' => 'required',
                ]) }}
                <small class="text-muted">
                    {{ __('How often this revision repeats after the first application.') }}
                </small>
            </div>
        </div>

        {{-- Effective From --}}
        <div class="col-12 col-md-6 mt-3">
            <div class="form-group">
                {{ Form::label('effective_from', __('Effective From'), ['class' => 'col-form-label']) }}
                <x-required></x-required>
                {{ Form::date('effective_from', $revision->effective_from ? $revision->effective_from->format('Y-m-d') : null, [
                    'class'    => 'form-control',
                    'required' => 'required',
                ]) }}
                <small class="text-muted">
                    {{ __('The payslip month that contains this date will use the revised salary.') }}
                </small>
            </div>
        </div>

        {{-- Note --}}
        <div class="col-12 mt-3">
            <div class="form-group">
                {{ Form::label('note', __('Note (optional)'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('note', $revision->note, [
                    'class' => 'form-control',
                    'rows'  => 2,
                    'placeholder' => __('Reason for revision…'),
                ]) }}
            </div>
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <button type="submit" class="btn btn-primary">{{ __('Update Revision') }}</button>
</div>

{{ Form::close() }}

<script>
(function () {
    var currentSalary = {{ $employee->salary }};

    function updatePreview() {
        var type  = document.getElementById('revision_type').value;
        var value = parseFloat(document.getElementById('revision_value').value) || 0;
        var label = document.getElementById('revision_value_label');
        var hint  = document.getElementById('revision_value_hint');
        var previewVal = document.getElementById('revised_salary_value');

        if (type === 'percentage') {
            label.textContent = '{{ __("Increase Percentage") }}';
            hint.textContent  = '{{ __("Enter % to add. E.g. 10 means +10% of current salary.") }}';
        } else {
            label.textContent = '{{ __("Increase Amount") }}';
            hint.textContent  = '{{ __("Enter the fixed amount to add to current salary.") }}';
        }

        if (value > 0) {
            var revised;
            if (type === 'percentage') {
                revised = currentSalary + (currentSalary * value / 100);
            } else {
                revised = currentSalary + value;
            }
            previewVal.textContent = revised.toFixed(2);
        }
    }

    document.getElementById('revision_type').addEventListener('change', updatePreview);
    document.getElementById('revision_value').addEventListener('input', updatePreview);
})();
</script>
