{{ Form::open(['url' => 'leavetype', 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('title', __('Name'), ['class' => 'form-label']) }}
                {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Leave Type Name')]) }}
                @error('title')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('days', __('Days Per Year'), ['class' => 'form-label']) }}
                {{ Form::number('days', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Days / Year'), 'min' => '0']) }}
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('is_paid', __('Leave Pay Type'), ['class' => 'form-label d-block']) }}
                <div class="d-flex gap-3 mt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_paid" id="is_paid_yes" value="1" checked>
                        <label class="form-check-label text-success fw-semibold" for="is_paid_yes">
                            <i class="ti ti-circle-check me-1"></i>{{ __('Paid') }}
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_paid" id="is_paid_no" value="0">
                        <label class="form-check-label text-danger fw-semibold" for="is_paid_no">
                            <i class="ti ti-circle-x me-1"></i>{{ __('Unpaid') }}
                        </label>
                    </div>
                </div>
                <small class="text-muted">{{ __('Whether leave days of this type are paid or unpaid.') }}</small>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
