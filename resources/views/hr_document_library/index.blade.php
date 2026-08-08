@extends('layouts.admin')

@section('page-title')
    {{ __('HR Document Library') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('HR Document Library') }}</li>
@endsection

@section('action-button')
    @can('Create HR Document Library')
        <a href="javascript:void(0)" data-url="{{ route('hr-document-library.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create / Upload HR Document') }}" data-size="lg" data-bs-toggle="tooltip" title=""
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
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('File') }}</th>
                                    <th>{{ __('Created Date') }}</th>
                                    @if (Gate::check('Edit HR Document Library') || Gate::check('Delete HR Document Library') || Gate::check('Manage HR Document Library'))
                                        <th width="180px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $doc)
                                    <tr>
                                        <td style="max-width: 260px;">
                                            @if(strlen($doc->title) > 30)
                                                <strong class="text-primary" data-bs-toggle="tooltip" data-bs-original-title="{{ $doc->title }}" style="cursor: pointer;">
                                                    {{ Str::limit($doc->title, 30) }}
                                                </strong>
                                            @else
                                                <strong class="text-primary">{{ $doc->title }}</strong>
                                            @endif
                                            @if($doc->description)
                                                <br>
                                                <small class="text-muted" data-bs-toggle="tooltip" data-bs-original-title="{{ $doc->description }}" style="cursor: pointer;">
                                                    {{ Str::limit($doc->description, 35) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $doc->category ?: 'General' }}</span>
                                        </td>
                                        <td style="max-width: 220px;">
                                            @if(!empty($doc->file_name))
                                                @if(strlen($doc->file_name) > 25)
                                                    <span class="text-dark" data-bs-toggle="tooltip" data-bs-original-title="{{ $doc->file_name }}" style="cursor: pointer;">
                                                        <i class="ti ti-file-description me-1"></i>{{ Str::limit($doc->file_name, 25) }}
                                                    </span>
                                                @else
                                                    <span class="text-dark"><i class="ti ti-file-description me-1"></i>{{ $doc->file_name }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted"><i class="ti ti-file-text me-1"></i>{{ __('Text Template') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ \Auth::user()->dateFormat($doc->created_at) }}</td>
                                        @if (Gate::check('Edit HR Document Library') || Gate::check('Delete HR Document Library') || Gate::check('Manage HR Document Library'))
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <!-- Download Uploaded File -->
                                                    <div class="action-btn bg-primary me-2" data-bs-toggle="tooltip" title="{{ __('Download Document') }}">
                                                        <a href="{{ route('hr-document-library.download-doc', $doc->id) }}" class="mx-3 btn btn-sm align-items-center">
                                                            <i class="ti ti-download text-white"></i>
                                                        </a>
                                                    </div>

                                                    <!-- Edit HR Document Details -->
                                                    @can('Edit HR Document Library')
                                                        <div class="action-btn bg-info me-2" data-bs-toggle="tooltip" title="{{ __('Edit Document') }}">
                                                            <a href="javascript:void(0)" data-url="{{ route('hr-document-library.edit', $doc->id) }}" data-ajax-popup="true" data-title="{{ __('Edit HR Document') }}" data-size="lg" class="mx-3 btn btn-sm align-items-center">
                                                                <i class="ti ti-edit text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    <!-- Delete -->
                                                    @can('Delete HR Document Library')
                                                        <div class="action-btn bg-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['hr-document-library.destroy', $doc->id], 'id' => 'delete-form-' . $doc->id]) !!}
                                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </div>
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
