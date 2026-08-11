@extends('layouts.admin')

@section('page-title')
    {{ __('HR Document Library') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr-document-library.index') }}">{{ __('HR Document Library') }}</a></li>
    @if ($currentFolder)
        @foreach ($currentFolder->getAncestors() as $ancestor)
            <li class="breadcrumb-item">
                <a href="{{ route('hr-document-library.index', ['folder_id' => $ancestor->id]) }}">{{ $ancestor->name }}</a>
            </li>
        @endforeach
        <li class="breadcrumb-item text-primary fw-bold">{{ $currentFolder->name }}</li>
    @endif
@endsection

@section('action-button')
    <div class="d-flex align-items-center gap-2">
        @if ($currentFolder)
            <!-- Back Button -->
            <a href="{{ route('hr-document-library.index', ['folder_id' => $currentFolder->parent_id]) }}" class="btn btn-sm btn-outline-secondary me-1">
                <i class="ti ti-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        @endif

        @can('Create HR Document Library')
            <!-- Create Folder Button -->
            <a href="javascript:void(0)" data-url="{{ route('hr-document-folders.create', ['parent_id' => $currentFolder ? $currentFolder->id : '']) }}" data-ajax-popup="true"
                data-title="{{ __('Create New Folder') }}" data-size="md" data-bs-toggle="tooltip" title="{{ __('Create Folder') }}"
                class="btn btn-sm btn-outline-primary me-1">
                <i class="ti ti-folder-plus me-1"></i>{{ __('New Folder') }}
            </a>

            <!-- Upload Documents Button -->
            @if ($currentFolder || count($allFolders) > 0)
                <a href="javascript:void(0)" data-url="{{ route('hr-document-library.create', ['folder_id' => $currentFolder ? $currentFolder->id : '']) }}" data-ajax-popup="true"
                    data-title="{{ __('Upload Documents') }}" data-size="lg" data-bs-toggle="tooltip" title="{{ __('Upload Documents') }}"
                    class="btn btn-sm btn-primary">
                    <i class="ti ti-upload me-1"></i>{{ __('Upload Documents') }}
                </a>
            @endif
        @endcan
    </div>
@endsection

@section('content')
    <style>
        .folder-card {
            transition: all 0.2s ease-in-out;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            background: #ffffff;
        }
        .folder-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05) !important;
            border-color: #6366f1 !important;
        }
        .folder-card.active-folder {
            border: 2px solid #6366f1 !important;
            background-color: #f8f9ff !important;
        }
        .folder-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background-color: #fff9e6;
            color: #ffa800;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .doc-card {
            transition: all 0.2s ease-in-out;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            background: #ffffff;
        }
        .doc-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05) !important;
            border-color: #6366f1 !important;
        }
    </style>

    <!-- Folders Directory Section -->
    <div class="row mb-4">
        <div class="col-12 d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                <i class="ti ti-folder text-warning me-2 fs-3"></i>{{ __('Folders') }}
                <span class="badge bg-light-primary text-primary rounded-pill px-2 py-1 fs-7 ms-2 fw-bold">
                    {{ sprintf('%02d', count($folders)) }}
                </span>
            </h5>
        </div>

        @if(count($folders) > 0)
            @foreach($folders as $folder)
                @php
                    $isActive = ($currentFolder && $currentFolder->id == $folder->id);
                @endphp
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <div class="card folder-card {{ $isActive ? 'active-folder' : '' }} h-100 shadow-sm mb-0">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <a href="{{ route('hr-document-library.index', ['folder_id' => $folder->id]) }}" class="d-flex align-items-center text-decoration-none text-dark flex-grow-1 text-truncate me-2">
                                <div class="folder-icon-box me-3 flex-shrink-0">
                                    <i class="ti ti-folder fs-2"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-1 fw-bold text-truncate text-dark" data-bs-toggle="tooltip" title="{{ $folder->name }}">
                                        {{ $folder->name }}
                                    </h6>
                                    <small class="text-muted d-block" style="font-size: 0.78rem;">
                                        {{ $folder->documents()->count() }} {{ __('Files') }} 
                                        <span class="mx-1">•</span> 
                                        {{ \Auth::user()->dateFormat($folder->created_at) }}
                                    </small>
                                </div>
                            </a>

                            <div class="dropdown flex-shrink-0">
                                <button class="btn btn-sm btn-light btn-icon rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li>
                                        <a href="{{ route('hr-document-library.index', ['folder_id' => $folder->id]) }}" class="dropdown-item">
                                            <i class="ti ti-folder-open me-2 text-primary"></i>{{ __('Open Folder') }}
                                        </a>
                                    </li>
                                    @can('Edit HR Document Library')
                                        <li>
                                            <a href="javascript:void(0)" data-url="{{ route('hr-document-folders.edit', $folder->id) }}" data-ajax-popup="true" data-title="{{ __('Edit Folder') }}" data-size="md" class="dropdown-item">
                                                <i class="ti ti-edit me-2 text-info"></i>{{ __('Rename / Move') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('Delete HR Document Library')
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['hr-document-folders.destroy', $folder->id], 'id' => 'delete-folder-form-' . $folder->id]) !!}
                                                <a href="#" class="dropdown-item text-danger bs-pass-para">
                                                    <i class="ti ti-trash me-2"></i>{{ __('Delete Folder') }}
                                                </a>
                                            {!! Form::close() !!}
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @elseif(!$currentFolder)
            <!-- Empty State for Root when no folders exist -->
            <div class="col-12">
                <div class="card border shadow-none bg-light text-center py-5 rounded-3">
                    <div class="card-body">
                        <div class="avatar avatar-xl bg-light-primary text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <i class="ti ti-folder-plus display-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">{{ __('No Folders Created Yet') }}</h5>
                        <p class="text-muted mb-4 max-w-400 mx-auto">{{ __('Create your first HR document folder to start organizing offer letters, contracts, policies, and employee files.') }}</p>
                        @can('Create HR Document Library')
                            <a href="javascript:void(0)" data-url="{{ route('hr-document-folders.create', ['parent_id' => '']) }}" data-ajax-popup="true"
                                data-title="{{ __('Create New Folder') }}" data-size="md" class="btn btn-primary px-4">
                                <i class="ti ti-folder-plus me-1"></i>{{ __('Create First Folder') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Active Folder Documents Grid View -->
    @if($currentFolder)
        <div class="row">
            <div class="col-12 d-flex align-items-center justify-content-between mb-3">
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="ti ti-files text-primary me-2 fs-3"></i>{{ $currentFolder->name }} {{ __('Documents') }}
                    <span class="badge bg-light-primary text-primary rounded-pill px-2 py-1 fs-7 ms-2 fw-bold">
                        {{ count($documents) }}
                    </span>
                </h5>

                @can('Create HR Document Library')
                    <a href="javascript:void(0)" data-url="{{ route('hr-document-library.create', ['folder_id' => $currentFolder->id]) }}" data-ajax-popup="true"
                        data-title="{{ __('Upload Documents') }}" data-size="lg" class="btn btn-sm btn-primary rounded-3 px-3">
                        <i class="ti ti-upload me-1"></i>{{ __('Upload Files') }}
                    </a>
                @endcan
            </div>

            @if(count($documents) > 0)
                @foreach ($documents as $doc)
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card doc-card h-100 shadow-sm position-relative">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                
                                <!-- Top: Category Badge & Dropdown -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-light-info text-info fw-semibold px-2 py-1 fs-7">
                                        {{ $doc->category ?: 'General' }}
                                    </span>

                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light btn-icon rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li>
                                                <a href="{{ route('hr-document-library.download-doc', $doc->id) }}" class="dropdown-item">
                                                    <i class="ti ti-download me-2 text-primary"></i>{{ __('Download') }}
                                                </a>
                                            </li>
                                            @can('Edit HR Document Library')
                                                <li>
                                                    <a href="javascript:void(0)" data-url="{{ route('hr-document-library.edit', $doc->id) }}" data-ajax-popup="true" data-title="{{ __('Edit HR Document') }}" data-size="lg" class="dropdown-item">
                                                        <i class="ti ti-edit me-2 text-info"></i>{{ __('Edit Details') }}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('Delete HR Document Library')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['hr-document-library.destroy', $doc->id], 'id' => 'delete-doc-form-' . $doc->id]) !!}
                                                        <a href="#" class="dropdown-item text-danger bs-pass-para">
                                                            <i class="ti ti-trash me-2"></i>{{ __('Delete') }}
                                                        </a>
                                                    {!! Form::close() !!}
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </div>

                                <!-- Document Details -->
                                <div class="text-center py-2">
                                    @php
                                        $ext = strtolower(pathinfo($doc->file_name ?? '', PATHINFO_EXTENSION));
                                    @endphp
                                    <div class="avatar avatar-lg rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center
                                        {{ in_array($ext, ['pdf']) ? 'bg-light-danger text-danger' : (in_array($ext, ['doc', 'docx']) ? 'bg-light-primary text-primary' : (in_array($ext, ['jpg', 'png', 'jpeg', 'gif']) ? 'bg-light-success text-success' : (in_array($ext, ['xls', 'xlsx', 'csv']) ? 'bg-light-success text-success' : 'bg-light-info text-info'))) }}"
                                        style="width: 54px; height: 54px;">
                                        @if(in_array($ext, ['pdf']))
                                            <i class="ti ti-file-text fs-2"></i>
                                        @elseif(in_array($ext, ['doc', 'docx']))
                                            <i class="ti ti-file-description fs-2"></i>
                                        @elseif(in_array($ext, ['jpg', 'png', 'jpeg', 'gif']))
                                            <i class="ti ti-photo fs-2"></i>
                                        @elseif(in_array($ext, ['xls', 'xlsx', 'csv']))
                                            <i class="ti ti-file-spreadsheet fs-2"></i>
                                        @else
                                            <i class="ti ti-file-text fs-2"></i>
                                        @endif
                                    </div>

                                    <h6 class="mb-1 fw-bold text-dark text-truncate px-2" data-bs-toggle="tooltip" title="{{ $doc->title }}">
                                        {{ $doc->title }}
                                    </h6>

                                    @if(!empty($doc->file_name))
                                        <p class="small text-muted text-truncate mb-0 px-2" style="font-size: 0.8rem;" data-bs-toggle="tooltip" title="{{ $doc->file_name }}">
                                            <i class="ti ti-paperclip me-1"></i>{{ $doc->file_name }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Footer: Date & Quick Actions -->
                                <div class="pt-3 border-top mt-3 d-flex justify-content-between align-items-center">
                                    <small class="text-muted" style="font-size: 0.78rem;">
                                        <i class="ti ti-calendar me-1"></i>{{ \Auth::user()->dateFormat($doc->created_at) }}
                                    </small>

                                    <div class="d-flex align-items-center gap-1">
                                        <a href="{{ route('hr-document-library.download-doc', $doc->id) }}" class="btn btn-sm btn-light-primary p-1 px-2" data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        @can('Edit HR Document Library')
                                            <a href="javascript:void(0)" data-url="{{ route('hr-document-library.edit', $doc->id) }}" data-ajax-popup="true" data-title="{{ __('Edit HR Document') }}" data-size="lg" class="btn btn-sm btn-light-info p-1 px-2" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="card border shadow-none bg-light text-center py-5 rounded-3">
                        <div class="card-body">
                            <div class="avatar avatar-xl bg-light-primary text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
                                <i class="ti ti-cloud-upload display-6"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">{{ __('No Documents in this Folder') }}</h6>
                            <p class="text-muted small mb-3">{{ __('Upload employee files or contracts to this folder.') }}</p>
                            @can('Create HR Document Library')
                                <a href="javascript:void(0)" data-url="{{ route('hr-document-library.create', ['folder_id' => $currentFolder->id]) }}" data-ajax-popup="true"
                                    data-title="{{ __('Upload Documents') }}" data-size="lg" class="btn btn-sm btn-primary px-3">
                                    <i class="ti ti-upload me-1"></i>{{ __('Upload Documents Now') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
@endsection
