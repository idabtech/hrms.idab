{{ Form::model($doc, ['route' => ['hr-document-library.update', $doc->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-8">
            {{ Form::label('title', __('Document Title'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('e.g. Offer Letter - Software Engineer'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
            {{ Form::text('category', null, ['class' => 'form-control', 'placeholder' => __('e.g. Offer Letter, Policy'), 'list' => 'edit_category_list']) }}
            <datalist id="edit_category_list">
                <option value="Offer Letter">
                <option value="Employment Contract">
                <option value="NOC Certificate">
                <option value="Experience Certificate">
                <option value="Company Policy">
                <option value="Warning Letter">
            </datalist>
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Brief description or notes...')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('document', __('Replace Document File (Optional)'), ['class' => 'form-label']) }}
            @if(!empty($doc->file_name))
                <small class="text-muted d-block mb-1">{{ __('Current file:') }} <strong class="text-dark">{{ $doc->file_name }}</strong></small>
            @endif
            <input type="file" class="form-control" name="document" id="document_edit" accept=".docx,.doc,.txt">
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update Document') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
