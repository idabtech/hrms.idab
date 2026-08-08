{{ Form::open(['route' => 'hr-document-library.store', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-8">
            {{ Form::label('title', __('Document Title'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('e.g. Offer Letter - Software Engineer'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
            {{ Form::text('category', null, ['class' => 'form-control', 'placeholder' => __('e.g. Offer Letter, Policy'), 'list' => 'category_list']) }}
            <datalist id="category_list">
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
            {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Brief description or notes...')]) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('document', __('Upload Word / Document File (.docx, .doc, .txt)'), ['class' => 'form-label']) }}<x-required></x-required>
            <input type="file" class="form-control" name="document" id="document" accept=".docx,.doc,.txt" required>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Save & Upload') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
