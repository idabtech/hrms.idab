{{ Form::model($folder, ['route' => ['hr-document-folders.update', $folder->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('name', __('Folder Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Folder Name')]) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('parent_id', __('Parent Folder'), ['class' => 'form-label']) }}
                <select name="parent_id" class="form-control select2">
                    <option value="">{{ __('Root Directory (No Parent Folder)') }}</option>
                    @foreach ($folders as $id => $name)
                        <option value="{{ $id }}" {{ $folder->parent_id == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
