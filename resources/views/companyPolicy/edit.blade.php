@php
    $plan = App\Models\Utility::getChatGPTSettings();
@endphp

{{ Form::model($companyPolicy, ['route' => ['company-policy.update', $companyPolicy->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
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
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="form-icon-user">
                    {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Policy Title')]) }}
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
                {{ Form::label('document', __('Attachment'), ['class' => 'form-label']) }}<x-required></x-required>
                <div class="choose-file form-group">
                    <label for="attachment">
                        <input type="file" class="form-control" name="attachment" id="attachment"
                            onchange="handleDocPreview(this)"
                            data-filename="attachment"
                            data-preview-id="policy_attachment_preview">
                    </label>

                    {{-- Preview area: show existing attachment if already uploaded --}}
                    @php
                        $policyPath = \App\Models\Utility::get_file('uploads/companyPolicy/');
                        $attachFile = $companyPolicy->attachment ?? null;
                        $attachExt  = $attachFile ? strtolower(pathinfo($attachFile, PATHINFO_EXTENSION)) : null;
                        $attachIsImage = in_array($attachExt, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                        $attachUrl = $attachFile ? $policyPath . $attachFile : null;

                        $attachIconMap = [
                            'pdf'  => ['icon' => 'ti ti-file-type-pdf',   'color' => '#e74c3c', 'label' => 'PDF'],
                            'doc'  => ['icon' => 'ti ti-file-type-doc',   'color' => '#2980b9', 'label' => 'DOC'],
                            'docx' => ['icon' => 'ti ti-file-type-docx',  'color' => '#2980b9', 'label' => 'DOCX'],
                            'xls'  => ['icon' => 'ti ti-file-type-xls',   'color' => '#27ae60', 'label' => 'XLS'],
                            'xlsx' => ['icon' => 'ti ti-file-type-xlsx',  'color' => '#27ae60', 'label' => 'XLSX'],
                            'csv'  => ['icon' => 'ti ti-file-spreadsheet','color' => '#27ae60', 'label' => 'CSV'],
                            'txt'  => ['icon' => 'ti ti-file-text',       'color' => '#7f8c8d', 'label' => 'TXT'],
                            'zip'  => ['icon' => 'ti ti-file-zip',        'color' => '#8e44ad', 'label' => 'ZIP'],
                            'rar'  => ['icon' => 'ti ti-file-zip',        'color' => '#8e44ad', 'label' => 'RAR'],
                        ];
                        $attachIconInfo = $attachExt && isset($attachIconMap[$attachExt])
                            ? $attachIconMap[$attachExt]
                            : ['icon' => 'ti ti-file', 'color' => '#95a5a6', 'label' => strtoupper($attachExt ?? 'FILE')];
                    @endphp

                    <div id="policy_attachment_preview" class="doc-preview-area mt-2">
                        @if ($attachFile)
                            @if ($attachIsImage)
                                <a href="{{ $attachUrl }}" target="_blank">
                                    <img src="{{ $attachUrl }}"
                                         class="img-thumbnail doc-preview-img"
                                         style="max-height:80px; max-width:120px; object-fit:cover;"
                                         alt="{{ $attachFile }}">
                                </a>
                            @else
                                <a href="{{ $attachUrl }}" target="_blank"
                                   class="doc-file-icon-link d-inline-flex align-items-center gap-1 text-decoration-none"
                                   data-bs-toggle="tooltip" title="{{ $attachFile }}">
                                    <i class="{{ $attachIconInfo['icon'] }} doc-file-icon"
                                       style="font-size:2.2rem; color:{{ $attachIconInfo['color'] }};"></i>
                                    <span class="badge"
                                          style="background:{{ $attachIconInfo['color'] }}; font-size:0.7rem;">
                                        {{ $attachIconInfo['label'] }}
                                    </span>
                                </a>
                            @endif
                        @endif
                    </div>
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
