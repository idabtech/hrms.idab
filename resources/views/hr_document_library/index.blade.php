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
        <li class="breadcrumb-item text-primary fw-semibold">{{ $currentFolder->name }}</li>
    @endif
@endsection

@section('action-button')
    <div class="d-flex align-items-center gap-2">
        @if ($currentFolder)
            <!-- Back Button -->
            <a href="{{ route('hr-document-library.index', ['folder_id' => $currentFolder->parent_id]) }}" class="btn btn-sm btn-outline-secondary me-1 shadow-sm px-3 rounded-2">
                <i class="ti ti-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        @endif

        @if (\Auth::user()->type === 'super admin')
            <!-- Create Folder Button -->
            <a href="javascript:void(0)" data-url="{{ route('hr-document-folders.create', ['parent_id' => $currentFolder ? $currentFolder->id : '']) }}" data-ajax-popup="true"
                data-title="{{ __('Create New Folder') }}" data-size="md" data-bs-toggle="tooltip" title="{{ __('Create Folder') }}"
                class="btn btn-sm btn-outline-primary me-1 shadow-sm px-3 rounded-2">
                <i class="ti ti-folder-plus me-1"></i>{{ __('New Folder') }}
            </a>

            <!-- Upload Documents Button -->
            @if ($currentFolder || count($allFolders) > 0)
                <a href="javascript:void(0)" data-url="{{ route('hr-document-library.create', ['folder_id' => $currentFolder ? $currentFolder->id : '']) }}" data-ajax-popup="true"
                    data-title="{{ __('Upload Documents') }}" data-size="lg" data-bs-toggle="tooltip" title="{{ __('Upload Documents') }}"
                    class="btn btn-sm btn-primary shadow-sm px-3 rounded-2">
                    <i class="ti ti-upload me-1"></i>{{ __('Upload Documents') }}
                </a>
            @endif
        @endif
    </div>
@endsection

@section('content')
    <style>
        .folder-card {
            transition: all 0.25s ease-in-out;
            border: 1px solid #eaedf1;
            border-radius: 12px;
            background: #ffffff;
        }
        .folder-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.08) !important;
            border-color: #6366f1 !important;
        }
        .folder-card.active-folder {
            border: 2px solid #6366f1 !important;
            background-color: #f8f9ff !important;
        }
        .folder-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background-color: #fff9e6;
            color: #ffa800;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .doc-card {
            transition: all 0.25s ease-in-out;
            border: 1px solid #eaedf1;
            border-radius: 12px;
            background: #ffffff;
        }
        .doc-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.08) !important;
            border-color: #6366f1 !important;
        }
        .doc-title-text {
            font-size: 0.92rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.35;
        }
    </style>

    <!-- Folders Directory Section -->
    <div class="row mb-4">
        <div class="col-12 d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center" style="font-size: 1.1rem;">
                    <i class="ti ti-folders text-warning me-2 fs-3"></i>{{ __('Folders') }}
                </h5>
                <span class="badge bg-light-primary text-primary rounded-pill px-2.5 py-1 fs-7 fw-semibold ms-1">
                    {{ sprintf('%02d', count($folders)) }}
                </span>
            </div>
        </div>

        @if(count($folders) > 0)
            @foreach($folders as $folder)
                @php
                    $isActive = ($currentFolder && $currentFolder->id == $folder->id);
                @endphp
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <div class="card folder-card {{ $isActive ? 'active-folder' : '' }} h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <a href="{{ route('hr-document-library.index', ['folder_id' => $folder->id]) }}" class="d-flex align-items-center text-decoration-none text-dark flex-grow-1 text-truncate me-2">
                                <div class="folder-icon-box me-3 flex-shrink-0">
                                    <i class="ti ti-folder fs-2"></i>
                                </div>
                                <div class="text-truncate">
                                    <h6 class="mb-1 fw-semibold text-truncate text-dark" style="font-size: 0.92rem;" data-bs-toggle="tooltip" title="{{ $folder->name }}">
                                        {{ $folder->name }}
                                    </h6>
                                    <small class="text-muted d-block fw-normal" style="font-size: 0.78rem;">
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
                                    @if (\Auth::user()->type === 'super admin')
                                        <li>
                                            <a href="javascript:void(0)" data-url="{{ route('hr-document-folders.edit', $folder->id) }}" data-ajax-popup="true" data-title="{{ __('Edit Folder') }}" data-size="md" class="dropdown-item">
                                                <i class="ti ti-edit me-2 text-info"></i>{{ __('Rename / Move') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['hr-document-folders.destroy', $folder->id], 'id' => 'delete-folder-form-' . $folder->id]) !!}
                                                <a href="#" class="dropdown-item text-danger bs-pass-para">
                                                    <i class="ti ti-trash me-2"></i>{{ __('Delete Folder') }}
                                                </a>
                                            {!! Form::close() !!}
                                        </li>
                                    @endif
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
                        <div class="avatar avatar-xl bg-light-primary text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 68px; height: 68px;">
                            <i class="ti ti-folder-plus display-5"></i>
                        </div>
                        <h5 class="fw-semibold text-dark mb-2" style="font-size: 1.1rem;">{{ __('No Folders Created Yet') }}</h5>
                        <p class="text-muted mb-4 max-w-400 mx-auto fw-normal">{{ __('Create your first HR document folder to start organizing offer letters, contracts, policies, and employee files.') }}</p>
                        @if (\Auth::user()->type === 'super admin')
                            <a href="javascript:void(0)" data-url="{{ route('hr-document-folders.create', ['parent_id' => '']) }}" data-ajax-popup="true"
                                data-title="{{ __('Create New Folder') }}" data-size="md" class="btn btn-primary px-4 shadow-sm rounded-2">
                                <i class="ti ti-folder-plus me-1"></i>{{ __('Create First Folder') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Active Folder Documents Grid View -->
    @if($currentFolder)
        <div class="row">
            <div class="col-12 d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center" style="font-size: 1.1rem;">
                        <i class="ti ti-files text-primary me-2 fs-3"></i>{{ $currentFolder->name }} {{ __('Documents') }}
                    </h5>
                    <span class="badge bg-light-primary text-primary rounded-pill px-2.5 py-1 fs-7 fw-semibold ms-1">
                        {{ sprintf('%02d', count($documents)) }}
                    </span>
                </div>

                @if (\Auth::user()->type === 'super admin')
                    <a href="javascript:void(0)" data-url="{{ route('hr-document-library.create', ['folder_id' => $currentFolder->id]) }}" data-ajax-popup="true"
                        data-title="{{ __('Upload Documents') }}" data-size="lg" class="btn btn-sm btn-primary rounded-2 px-3 shadow-sm">
                        <i class="ti ti-upload me-1"></i>{{ __('Upload Files') }}
                    </a>
                @endif
            </div>

            @if(count($documents) > 0)
                @foreach ($documents as $doc)
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="card doc-card h-100 position-relative">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                
                                <!-- Top: Category Badge & Dropdown -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-light-primary text-primary fw-semibold px-2.5 py-1 fs-7 rounded-pill" style="font-size: 0.75rem;">
                                        {{ $doc->category ?: 'General' }}
                                    </span>

                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light btn-icon rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            @if (\Auth::user()->type === 'super admin' || \Auth::user()->can('View HR Document Library'))
                                                <li>
                                                    <a href="{{ route('hr-document-library.view-doc', $doc->id) }}" target="_blank" class="dropdown-item">
                                                        <i class="ti ti-eye me-2 text-info"></i>{{ __('View Document') }}
                                                    </a>
                                                </li>
                                            @endif
                                            @if (\Auth::user()->type === 'super admin' || \Auth::user()->can('Download HR Document Library'))
                                                <li>
                                                    <a href="{{ route('hr-document-library.download-doc', $doc->id) }}" class="dropdown-item">
                                                        <i class="ti ti-download me-2 text-primary"></i>{{ __('Download') }}
                                                    </a>
                                                </li>
                                            @endif
                                            @if (\Auth::user()->type === 'super admin')
                                                <li>
                                                    <a href="javascript:void(0)" data-url="{{ route('hr-document-library.edit', $doc->id) }}" data-ajax-popup="true" data-title="{{ __('Edit HR Document') }}" data-size="lg" class="dropdown-item">
                                                        <i class="ti ti-edit me-2 text-info"></i>{{ __('Edit Details') }}
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['hr-document-library.destroy', $doc->id], 'id' => 'delete-doc-form-' . $doc->id]) !!}
                                                        <a href="#" class="dropdown-item text-danger bs-pass-para">
                                                            <i class="ti ti-trash me-2"></i>{{ __('Delete') }}
                                                        </a>
                                                    {!! Form::close() !!}
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                                <!-- Document Details -->
                                <div class="text-start py-2">
                                    <h6 class="mb-1.5 doc-title-text text-truncate" data-bs-toggle="tooltip" title="{{ $doc->title }}">
                                        <i class="ti ti-file-text text-primary me-1 fs-5"></i>{{ $doc->title }}
                                    </h6>

                                    @if(!empty($doc->file_name))
                                        <p class="small text-muted text-truncate mb-0 fw-normal" style="font-size: 0.78rem;" data-bs-toggle="tooltip" title="{{ $doc->file_name }}">
                                            <i class="ti ti-paperclip me-1"></i>{{ $doc->file_name }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Footer: Date & Quick Actions -->
                                <div class="pt-3 border-top mt-3 d-flex justify-content-between align-items-center">
                                    <small class="text-muted fw-normal" style="font-size: 0.78rem;">
                                        <i class="ti ti-calendar me-1"></i>{{ \Auth::user()->dateFormat($doc->created_at) }}
                                    </small>

                                    <div class="d-flex align-items-center gap-1">
                                        @if (\Auth::user()->type === 'super admin' || \Auth::user()->can('View HR Document Library'))
                                            <a href="{{ route('hr-document-library.view-doc', $doc->id) }}" target="_blank" class="btn btn-sm btn-light-info p-1.5 px-2.5 rounded-2" data-bs-toggle="tooltip" title="{{ __('View Document') }}">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        @endif
                                        @if (\Auth::user()->type === 'super admin' || \Auth::user()->can('Download HR Document Library'))
                                            <a href="{{ route('hr-document-library.download-doc', $doc->id) }}" class="btn btn-sm btn-primary p-1.5 px-2.5 rounded-2 shadow-sm" data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                                <i class="ti ti-download"></i>
                                            </a>
                                        @endif
                                        @if (\Auth::user()->type === 'super admin')
                                            <a href="javascript:void(0)" data-url="{{ route('hr-document-library.edit', $doc->id) }}" data-ajax-popup="true" data-title="{{ __('Edit HR Document') }}" data-size="lg" class="btn btn-sm btn-light-secondary p-1.5 px-2.5 rounded-2" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                        @endif
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
                            <h6 class="fw-semibold text-dark mb-1" style="font-size: 1rem;">{{ __('No Documents in this Folder') }}</h6>
                            <p class="text-muted small mb-3 fw-normal">{{ __('Upload employee files or contracts to this folder.') }}</p>
                            @if (\Auth::user()->type === 'super admin')
                                <a href="javascript:void(0)" data-url="{{ route('hr-document-library.create', ['folder_id' => $currentFolder->id]) }}" data-ajax-popup="true"
                                    data-title="{{ __('Upload Documents') }}" data-size="lg" class="btn btn-sm btn-primary px-3 shadow-sm rounded-2">
                                    <i class="ti ti-upload me-1"></i>{{ __('Upload Documents Now') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
@endsection
