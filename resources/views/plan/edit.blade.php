{{ Form::model($plan, ['route' => ['plans.update', $plan->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('name', __('Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control font-style', 'placeholder' => __('Enter Plan Name'), 'required' => 'required']) }}
        </div>
        @if ($plan->price > 0)
            <div class="form-group col-md-6">
                {{ Form::label('price', __('Price'), ['class' => 'col-form-label']) }}
                {{ Form::text('price', null, ['class' => 'form-control', 'id' => 'price', 'required' => 'required', 'onkeyup' => 'calculateDiscountPrice()']) }}
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('discount', __('Discount'), ['class' => 'col-form-label']) }}
            {{ Form::text('discount', null, ['class' => 'form-control', 'id' => 'discount', 'placeholder' => __('Enter Plan Discount'), 'onkeyup' => 'calculateDiscountPrice()']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('discount_price', __('Discount Price'), ['class' => 'col-form-label']) }}
            {{ Form::number('discount_price', null, ['class' => 'form-control', 'placeholder' => __('Enter Plan Discount Price')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('per_user_or_company', __('Per User/Company'), ['class' => 'col-form-label']) }}
            {!! Form::select('per_user_or_company', $arrPerUserOrCompany, null, [
                'class' => 'form-control select2',
                'required' => 'required',
            ]) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('duration', __('Duration'), ['class' => 'col-form-label']) }}
            {!! Form::select('duration', $arrDuration, null, ['class' => 'form-control select2', 'required' => 'required']) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('set_currency', __('Set Currency'), ['class' => 'col-form-label']) }}
            {!! Form::select('set_currency', $arrCurrency, $selected_country, [
                'class' => 'form-control select2',
                'required' => 'required',
            ]) !!}
        </div>


        <div class="form-group col-md-6">
            {{ Form::label('max_users', __('Maximum Users'), ['class' => 'col-form-label']) }}
            {{ Form::number('max_users', null, ['class' => 'form-control', 'required' => 'required']) }}
            <span class="small">{{ __('Note: "-1" for Unlimited') }}</span>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('max_employees', __('Maximum Employees'), ['class' => 'col-form-label']) }}
            {{ Form::number('max_employees', null, ['class' => 'form-control', 'required' => 'required']) }}
            <span class="small">{{ __('Note: "-1" for Unlimited') }}</span>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('pms', __('PMS'), ['class' => 'col-form-label']) }}
            {!! Form::select('pms', $arrPms, null, ['class' => 'form-control select2', 'required' => 'required']) !!}
        </div>
        <div class="form-group">
            {{ Form::label('description', __('Description'), ['class' => 'col-form-label']) }}
            {!! Form::textarea('description', null, ['class' => 'pc-tinymce-2', 'rows' => '2']) !!}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">

</div>
{{ Form::close() }}
<script>
    // $('#price').keyup(function(){
    //     calculateDiscountPrice();
    // })
    function calculateDiscountPrice() {
        console.log(123);
        var price = $('#price').val()
        var discount = $('#discount').val()
        if (price > 0 && discount > 0) {

            var discounted_price = (price * discount) / 100;
            var new_price = price - discounted_price;
            $('#discount_price').val(new_price)
        }
    }
</script>
<script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
<script src="{{ asset('assets/js/plugins/tinymce/tinymce.min.js') }}"></script>
<script>
    if ($(".pc-tinymce-2").length) {
        tinymce.init({
            selector: '.pc-tinymce-2', // Replace this CSS selector to match the placeholder element for TinyMCE
            plugins: [
                'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor',
                'pagebreak',
                'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen', 'media',
                'table', 'emoticons', 'template', 'help'
            ],
            toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
                'forecolor backcolor emoticons | help',
            menu: {
                favs: {
                    title: 'My Favorites',
                    items: 'code visualaid | searchreplace | emoticons'
                }
            },
            menubar: 'favs file edit view insert format tools table help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
        });
    }
</script>
