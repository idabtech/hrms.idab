@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Company Policy') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Company Policy') }}</li>
@endsection

@section('action-button')
    @can('Create Company Policy')
        <a href="javascript:void(0)" data-url="{{ route('company-policy.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Company Policy') }}" data-size="md" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    {{-- <h5></h5> --}}
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Attachment') }}</th>
                                    @if (Gate::check('Edit Company Policy') || Gate::check('Delete Company Policy'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($companyPolicy as $policy)
                                    @php
                                        $policyPath = \App\Models\Utility::get_file('uploads/companyPolicy');
                                    @endphp
                                    <tr>
                                        <td>{{ !empty($policy->branches) ? $policy->branches->name : '-' }}</td>
                                        <td>{{ $policy->title }}</td>
                                        {{-- <td>{{ $policy->description }}</td> --}}
                                        <td class="description-col" data-description="{{ $policy->description }}"></td>
                                        <td class="Action">
                                            @if (!empty($policy->attachment))
                                                        <div class="action-btn me-2">
                                                            <a class="mx-3 btn btn-sm bg-primary align-items-center" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Download') }}"
                                                                href="{{ $policyPath . '/' . $policy->attachment }}"
                                                                download="">
                                                                <span class="text-white"><i class="ti ti-download"></i></span>
                                                            </a>
                                                        </div>
                                                        <div class="action-btn">
                                                            <a class="mx-3 btn btn-sm bg-secondary align-items-center"
                                                                href="{{ $policyPath . '/' . $policy->attachment }}"
                                                                target="_blank">
                                                                <span class="text-white"><i class="ti ti-crosshair"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-original-title="{{ __('Preview') }}"></i></span>
                                                            </a>
                                                        </div>
                                            @else
                                                <p>-</p>
                                            @endif
                                        </td>
                                        @if (Gate::check('Edit Company Policy') || Gate::check('Delete Company Policy'))
                                            <td class="Action">
                                                        @can('Edit Company Policy')
                                                            <div class="action-btn me-2">
                                                                <a href="javascript:void(0)" data-size="lg"
                                                                    class="mx-3 btn btn-sm bg-info align-items-center"
                                                                    data-url="{{ route('company-policy.edit', $policy->id) }}"
                                                                    data-ajax-popup="true" data-size="md"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-title="{{ __('Edit Company Policy') }}"
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @can('Delete Company Policy')
                                                            <div class="action-btn">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['company-policy.destroy', $policy->id],
                                                                    'id' => 'delete-form-' . $policy->id,
                                                                ]) !!}
                                                                <a href="javascript:void(0)"
                                                                data-bs-trigger="hover"
                                                                    class="btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete" aria-label="Delete"><span
                                                                        class="text-white"><i
                                                                            class="ti ti-trash"></i></span></a>
                                                                </form>
                                                            </div>
                                                        @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
    function handleDocPreview(input) {
        var previewId = input.getAttribute('data-preview-id');
        var previewArea = document.getElementById(previewId);
        if (!input.files || !input.files[0]) return;

        var file = input.files[0];
        var fileName = file.name;
        var ext = fileName.split('.').pop().toLowerCase();
        var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

        var fileIconMap = {
            'pdf':  { icon: 'ti ti-file-type-pdf',    color: '#e74c3c', label: 'PDF'  },
            'doc':  { icon: 'ti ti-file-type-doc',    color: '#2980b9', label: 'DOC'  },
            'docx': { icon: 'ti ti-file-type-docx',   color: '#2980b9', label: 'DOCX' },
            'xls':  { icon: 'ti ti-file-type-xls',    color: '#27ae60', label: 'XLS'  },
            'xlsx': { icon: 'ti ti-file-type-xlsx',   color: '#27ae60', label: 'XLSX' },
            'csv':  { icon: 'ti ti-file-spreadsheet', color: '#27ae60', label: 'CSV'  },
            'txt':  { icon: 'ti ti-file-text',        color: '#7f8c8d', label: 'TXT'  },
            'zip':  { icon: 'ti ti-file-zip',         color: '#8e44ad', label: 'ZIP'  },
            'rar':  { icon: 'ti ti-file-zip',         color: '#8e44ad', label: 'RAR'  },
        };

        previewArea.innerHTML = '';

        if (imageExts.indexOf(ext) !== -1) {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewArea.innerHTML =
                    '<img src="' + e.target.result + '" ' +
                    'class="img-thumbnail doc-preview-img" ' +
                    'style="max-height:80px; max-width:120px; object-fit:cover;" ' +
                    'alt="' + fileName + '">';
            };
            reader.readAsDataURL(file);
        } else {
            var info = fileIconMap[ext] || { icon: 'ti ti-file', color: '#95a5a6', label: ext.toUpperCase() };
            previewArea.innerHTML =
                '<span class="doc-file-icon-link d-inline-flex align-items-center gap-1">' +
                    '<i class="' + info.icon + ' doc-file-icon" style="font-size:2.2rem; color:' + info.color + ';"></i>' +
                    '<span class="badge" style="background:' + info.color + '; font-size:0.7rem;">' + info.label + '</span>' +
                '</span>' +
                '<div class="text-muted small mt-1" style="word-break:break-all; max-width:160px;">' + fileName + '</div>';
        }
    }
</script>
@endpush
