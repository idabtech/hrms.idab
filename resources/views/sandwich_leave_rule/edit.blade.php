{{ Form::model($sandwichLeaveRule, ['route' => ['sandwich-leave-rule.update', $sandwichLeaveRule->id], 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Rule Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g., Weekend Sandwich Rule 1.5%')]) }}
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="form-group">
                {{ Form::label('deduction_rate', __('Deduction Rate / Percentage'), ['class' => 'form-label']) }}
                {{ Form::number('deduction_rate', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0']) }}
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="form-group">
                {{ Form::label('rate_type', __('Rate Type'), ['class' => 'form-label']) }}
                <select name="rate_type" class="form-select" required>
                    <option value="percentage" {{ $sandwichLeaveRule->rate_type == 'percentage' ? 'selected' : '' }}>{{ __('Percentage (%)') }}</option>
                    <option value="multiplier" {{ $sandwichLeaveRule->rate_type == 'multiplier' ? 'selected' : '' }}>{{ __('Multiplier (x)') }}</option>
                </select>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('description', __('Description / Notes'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3']) }}
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('is_active', __('Status'), ['class' => 'form-label d-block']) }}
                <div class="form-check form-switch mt-1">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active_edit" value="1" {{ $sandwichLeaveRule->is_active ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold text-success" for="is_active_edit">
                        {{ __('Active') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
