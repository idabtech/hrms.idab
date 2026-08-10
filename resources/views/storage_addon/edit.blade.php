{{ Form::model($addon, ['route' => ['storage-addons.update', $addon->id], 'method' => 'PUT', 'id' => 'edit_storage_addon_form']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('title', __('Addon Title'), ['class' => 'form-label']) }} <x-required></x-required>
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. 5 GB Extra Storage')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('price', __('Price'), ['class' => 'form-label']) }} <x-required></x-required>
            {{ Form::number('price', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0', 'placeholder' => __('Enter Price')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('storage_amount', __('Storage Amount (in MB)'), ['class' => 'form-label']) }} <x-required></x-required>
            <div class="input-group">
                {{ Form::number('storage_amount', null, ['class' => 'form-control', 'required' => 'required', 'min' => '1', 'placeholder' => __('e.g. 5000')]) }}
                <span class="input-group-text">MB</span>
            </div>
            <small class="text-muted">{{ __('1 GB = 1024 MB (e.g. 5000 MB ≈ 4.88 GB)') }}</small>
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('addon_type', __('Addon Type / Expiry Mode'), ['class' => 'form-label']) }} <x-required></x-required>
            <select name="addon_type" id="addon_type_edit" class="form-control" required onchange="toggleDurationFieldsEdit(this.value)">
                <option value="plan_expiry" {{ $addon->addon_type == 'plan_expiry' ? 'selected' : '' }}>{{ __('Co-terminus with Company Active Plan Expiry Date') }}</option>
                <option value="fixed_duration" {{ $addon->addon_type == 'fixed_duration' ? 'selected' : '' }}>{{ __('Fixed Duration (Separate Validity Period)') }}</option>
            </select>
        </div>

        <div id="fixed_duration_container_edit" class="row col-md-12 pe-0" style="display: {{ $addon->addon_type == 'fixed_duration' ? 'flex' : 'none' }};">
            <div class="form-group col-md-6">
                {{ Form::label('duration_value', __('Duration Value'), ['class' => 'form-label']) }}
                {{ Form::number('duration_value', null, ['class' => 'form-control', 'min' => '1', 'placeholder' => __('e.g. 1')]) }}
            </div>
            <div class="form-group col-md-6 pe-0">
                {{ Form::label('duration_type', __('Duration Unit'), ['class' => 'form-label']) }}
                {{ Form::select('duration_type', ['year' => __('Year(s)'), 'month' => __('Month(s)'), 'day' => __('Day(s)')], null, ['class' => 'form-control']) }}
            </div>
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter Addon Description (optional)')]) }}
        </div>

        <div class="form-group col-md-12">
            <div class="form-check form-switch mt-2">
                <input type="checkbox" class="form-check-input" id="is_active_edit" name="is_active" value="1" {{ $addon->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active_edit">{{ __('Active & Enabled') }}</label>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update Addon') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
    function toggleDurationFieldsEdit(val) {
        var container = document.getElementById('fixed_duration_container_edit');
        if (val === 'fixed_duration') {
            container.style.display = 'flex';
        } else {
            container.style.display = 'none';
        }
    }
</script>
