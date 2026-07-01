@php
    $plan = App\Models\Utility::getChatGPTSettings();
@endphp

{{ Form::open(['url' => 'company-policy', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">

    @if ($plan->enable_chatgpt == 'on')
        <div class="text-end">
            <a href="javascript:void(0)" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['company-policy']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate With AI') }}
            </a>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="form-icon-user">
                    {{ Form::select('branch', $branch, null, ['class' => 'form-control', 'required' => 'required']) }}
                    <div class="text-xs mt-1">
                        {{ __('Create branch.') }} <a
                            href="{{ route('branch.index') }}"><b>{{ __('Click here') }}</b></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="form-icon-user">
                    {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Company Policy Title')]) }}
                </div>

            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Enter Description')]) }}
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('attachment', __('Attachment'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="choose-file form-group ">
                    <label for="attachment">
                        <input type="file" class="form-control" name="attachment" id="attachment"
                            onchange="handleDocPreview(this)"
                            data-filename="attachment" data-preview-id="policy_attachment_preview" required>
                        <hr>
                        <div id="policy_attachment_preview" class="doc-preview-area mt-2"></div>
                        {{-- <img id="blah" width="100" src="" /> --}}
                    </label>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
