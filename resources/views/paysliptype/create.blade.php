{{ Form::open(['url' => 'paysliptype', 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Payslip Type Name')]) }}
                </div>
                @error('name')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12 mt-2">
            <div class="form-group">
                {{ Form::label('salary_basis', __('Salary Basis'), ['class' => 'form-label']) }}
                {{ Form::select('salary_basis', [
                        'monthly' => __('Monthly (fixed monthly salary)'),
                        'hourly'  => __('Hourly (paid per hour worked)'),
                    ], 'monthly', ['class' => 'form-control', 'required' => 'required']) }}
                <small class="text-muted">
                    {{ __('Monthly: employee salary is a fixed amount per month. Hourly: salary field stores the rate per hour.') }}
                </small>
                @error('salary_basis')
                    <span class="text-danger d-block mt-1"><small>{{ $message }}</small></span>
                @enderror
            </div>
        </div>
    </div>

</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
