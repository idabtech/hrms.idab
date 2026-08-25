{{ Form::open(['url' => 'travel-expenses', 'method' => 'post', 'class' => 'needs-validation', 'novalidate', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('employee_id', __('Select Employee'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Employee')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Type'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::select('type', $types, 'travel', ['class' => 'form-control select2', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('title', __('Title / Purpose'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Title or Purpose')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0', 'placeholder' => __('Enter Amount')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::date('start_date', null, ['class' => 'form-control current_date', 'required' => 'required', 'autocomplete' => 'off']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}<x-required></x-required>
            {{ Form::date('end_date', null, ['class' => 'form-control current_date', 'required' => 'required', 'autocomplete' => 'off']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('documents', __('Bills / Documents (Multiple)'), ['class' => 'col-form-label']) }}
            <input type="file" name="documents[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            <small class="text-muted">{{ __('You can select multiple bill or document files.') }}</small>
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter Description')]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        if ($('.select2').length) {
            $('.select2').select2({
                dropdownParent: $('.modal-body')
            });
        }
        var now = new Date();
        var month = (now.getMonth() + 1);
        var day = now.getDate();
        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;
        var today = now.getFullYear() + '-' + month + '-' + day;
        $('.current_date').val(today);
    });
</script>
