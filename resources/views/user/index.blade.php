@extends('layouts.admin')

@section('page-title')
    @if (\Auth::user()->type == 'super admin')
        {{ __('Manage Companies') }}
    @else
        {{ __('Manage Users') }}
    @endif
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    @if (\Auth::user()->type == 'super admin')
        <li class="breadcrumb-item">{{ __('Companies') }}</li>
    @else
        <li class="breadcrumb-item">{{ __('Users') }}</li>
    @endif
@endsection

@section('action-button')
    @if (\Auth::user()->type == 'super admin')
        <a href="javascript:void(0)" class="btn btn-sm btn-primary active me-1" id="btn-grid-view" data-bs-toggle="tooltip"
            title="{{ __('Grid View') }}" onclick="switchCompanyView('grid')">
            <i class="ti ti-layout-grid"></i>
        </a>
        <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary me-1" id="btn-table-view" data-bs-toggle="tooltip"
            title="{{ __('List View') }}" onclick="switchCompanyView('table')">
            <i class="ti ti-list"></i>
        </a>
    @endif

    @if (Gate::check('Manage Employee Last Login'))
        @can('Manage Employee Last Login')
            <a href="{{ route('lastlogin') }}" class="btn btn-primary btn-sm me-1" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('User Logs History') }}"><i class="ti ti-user-check"></i>
            </a>
        @endcan
    @endif

    @can('Create User')
        @if (\Auth::user()->type == 'super admin')
            <a href="javascript:void(0)" data-url="{{ route('user.create') }}" data-ajax-popup="true"
                data-title="{{ __('Create New Company') }}" data-size="md" data-bs-toggle="tooltip" title=""
                class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @else
            <a href="javascript:void(0)" data-url="{{ route('user.create') }}" data-ajax-popup="true"
                data-title="{{ __('Create New User') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endif
    @endcan
@endsection


@php
    $logo = \App\Models\Utility::get_file('uploads/avatar/');
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
@endphp
@section('content')
    <div class="">
        @if (\Auth::user()->type == 'super admin')
            {{-- ═══ GRID VIEW SEARCH FILTER BAR ═══ --}}
            <div id="company-grid-search-bar" class="row mt-4 mb-2">
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-2" style="border-radius: 10px;">
                        <div class="card-body py-3 px-4">
                            <div class="row align-items-center">
                                <div class="col-md-6 col-sm-6 col-12">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-primary px-3">
                                            <i class="ti ti-search fs-5" id="icon-search-default"></i>
                                            <i class="ti ti-loader ti-spin fs-5 text-primary d-none" id="icon-search-loader"></i>
                                        </span>
                                        <input type="text" id="company-grid-search-input" class="form-control border-start-0 ps-1" placeholder="{{ __('Search company by name or email...') }}" style="box-shadow: none;">
                                        <button type="button" class="btn btn-outline-secondary d-none border-start-0" id="btn-clear-grid-search" onclick="clearCompanyGridSearch()">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6 col-12 text-sm-end text-start mt-sm-0 mt-2 d-flex align-items-center justify-content-sm-end justify-content-between gap-2">
                                    <button type="button" id="btn-bulk-delete-companies" onclick="submitCompanyBulkDelete()" class="btn btn-sm btn-danger shadow-sm d-none">
                                        <i class="ti ti-trash me-1"></i> {{ __('Delete Selected') }} (<span id="bulk-selected-count">0</span>)
                                    </button>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input cursor-pointer" type="checkbox" id="select-all-companies-check" onchange="toggleSelectAllCompanies(this)">
                                        <label class="form-check-label small fw-semibold text-muted cursor-pointer" for="select-all-companies-check">{{ __('Select All') }}</label>
                                    </div>
                                    <span class="badge bg-light-primary text-primary px-3 py-2.5 fw-semibold fs-7 border" id="grid-search-count-info">
                                        <i class="ti ti-building me-1"></i> {{ __('Total:') }} {{ count($users) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden Bulk Delete Form --}}
            <form id="form-bulk-delete-companies" action="{{ route('user.bulk-destroy') }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="ids" id="bulk-delete-company-ids">
            </form>

            {{-- ═══ 1. GRID VIEW (DEFAULT) ═══ --}}
            <div id="company-grid-view" class="row">
                @foreach ($users as $user)
                    <div class="col-xxl-3 col-lg-4 col-sm-6 col-12 company-card-item" data-search="{{ strtolower($user->name . ' ' . $user->email) }}">
                        <div class="card text-center position-relative">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="checkbox" class="form-check-input company-select-checkbox cursor-pointer me-1" value="{{ $user->id }}" onchange="updateCompanySelectionState()">
                                        <h6 class="mb-0">
                                            <div class="badge bg-primary p-2 px-3 ">
                                                {{ !empty($user->currentPlan) ? $user->currentPlan->name : '' }}
                                            </div>
                                        </h6>
                                    </div>
                                </div>
                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="feather icon-more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="javascript:void(0)" class="dropdown-item"
                                                data-url="{{ route('user.edit', $user->id) }}" data-size="md"
                                                data-ajax-popup="true" data-title="{{ __('Update User') }}"><i
                                                    class="ti ti-edit "></i><span
                                                    class="ms-2">{{ __('Edit') }}</span></a>

                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['user.destroy', $user->id],
                                                'id' => 'delete-form-' . $user->id,
                                            ]) !!}
                                            <a href="javascript:void(0)" class="dropdown-item bs-pass-para">
                                                <i class="ti ti-trash"></i><span class="ms-1">
                                                    @if ($user->delete_status == 0)
                                                        {{ __('Delete') }}
                                                    @else
                                                        {{ __('Restore') }}
                                                    @endif
                                                </span>
                                            </a>
                                            {!! Form::close() !!}

                                            @php
                                                $adminSettings = \App\Models\Utility::settings();
                                                $isLoginSecurityOn = ($adminSettings['login_as_company_security'] ?? 'on') === 'on';
                                            @endphp

                                            @if ($isLoginSecurityOn)
                                                <a href="javascript:void(0)" data-url="{{ route('login.with.company', $user->id) }}"
                                                    data-ajax-popup="true" data-size="md" data-title="{{ __('Login As Company Verification') }}"
                                                    class="dropdown-item" data-bs-original-title="{{ __('Login As Company') }}">
                                                    <i class="ti ti-replace"></i>
                                                    <span class="ms-1">{{ __('Login As Company') }}</span>
                                                </a>
                                            @else
                                                <a href="{{ route('login.with.company', $user->id) }}" class="dropdown-item"
                                                    data-bs-original-title="{{ __('Login As Company') }}">
                                                    <i class="ti ti-replace"></i>
                                                    <span class="ms-1">{{ __('Login As Company') }}</span>
                                                </a>
                                            @endif

                                            <a href="javascript:void(0)" class="dropdown-item" data-ajax-popup="true"
                                                data-size="md" data-title="{{ __('Change Password') }}"
                                                data-url="{{ route('user.reset', \Crypt::encrypt($user->id)) }}"><i
                                                    class="ti ti-key"></i>
                                                <span class="ms-1">{{ __('Reset Password') }}</span>
                                            </a>

                                            @if ($user->is_login_enable == 1)
                                                <a href="{{ route('user.login', \Crypt::encrypt($user->id)) }}"
                                                    class="dropdown-item">
                                                    <i class="ti ti-road-sign"></i>
                                                    <span class="text-danger ms-1"> {{ __('Login Disable') }}</span>
                                                </a>
                                            @elseif ($user->is_login_enable == 0 && $user->password == null)
                                                <a href="javascript:void(0)"
                                                    data-url="{{ route('user.reset', \Crypt::encrypt($user->id)) }}"
                                                    data-ajax-popup="true" data-size="md" class="dropdown-item login_enable"
                                                    data-title="{{ __('New Password') }}" class="dropdown-item">
                                                    <i class="ti ti-road-sign"></i>
                                                    <span class="text-success ms-1"> {{ __('Login Enable') }}</span>
                                                </a>
                                            @else
                                                <a href="{{ route('user.login', \Crypt::encrypt($user->id)) }}"
                                                    class="dropdown-item">
                                                    <i class="ti ti-road-sign"></i>
                                                    <span class="text-success ms-1"> {{ __('Login Enable') }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="avatar">
                                    <a href="{{ !empty($user->avatar) ? $profile . $user->avatar : $logo . 'avatar.png' }}"
                                        target="_blank">
                                        <img src="{{ !empty($user->avatar) ? $profile . $user->avatar : $logo . 'avatar.png' }}"
                                            class="img-fluid rounded border-2 border border-primary" width="120px"
                                            style="height: 120px">
                                    </a>
                                </div>
                                <h4 class="mt-2">{{ $user->name }}</h4>
                                <small>{{ $user->email }}</small>
                                @if (\Auth::user()->type == 'super admin')
                                    <div class="mb-0 mt-2">
                                        <div class="d-flex align-items-center gap-3 flex-wrap justify-content-center">
                                            <a href="javascript:void(0)" data-url="{{ route('plan.upgrade', $user->id) }}"
                                                class="btn btn-outline-primary" data-size="lg" data-ajax-popup="true"
                                                data-title="{{ __('Upgrade Plan') }}">{{ __('Upgrade Plan') }}
                                            </a>
                                            <a href="javascript:void(0)"
                                                data-url="{{ route('company.info', $user->id) }}" data-size="lg"
                                                data-ajax-popup="true" class="btn btn-outline-primary"
                                                data-title="{{ __('Company Info') }}">{{ __('AdminHub') }}</a>
                                        </div>
                                        <div class="row justify-content-between me-0 align-items-center row-gap-1 mb-2">
                                            <div class="col-6 text-start mt-3">
                                                <h6 class="mb-0 px-3">{{ $user->countUsers() }}</h6>
                                                <p class="text-muted text-sm mb-0">{{ __('Users') }}</p>
                                            </div>
                                            <div class="col-6 text-end mt-3">
                                                <h6 class="mb-0 px-4">{{ $user->countEmployees() }}</h6>
                                                <p class="text-muted text-sm mb-0">{{ __('Employees') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center pb-2">
                                        <span class="text-dark font-weight-500">{{ __('Plan Expired :') }}
                                            {{ !empty($user->plan_expire_date) ? \Auth::user()->dateFormat($user->plan_expire_date) : 'Lifetime' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="col-12 text-center py-5 d-none" id="company-no-results-msg">
                    <div class="alert alert-info border-0 shadow-sm d-inline-block px-4 py-3">
                        <i class="ti ti-search-off fs-3 d-block mb-2 text-info"></i>
                        <span class="fw-semibold">{{ __('No matching companies found for your search.') }}</span>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-4 col-sm-6 company-card-add-new">
                    <a href="javascript:void(0)" class="btn-addnew-project border-primary" data-ajax-popup="true"
                        data-url="{{ route('user.create') }}" data-title="{{ __('Create New Company') }}"
                        data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary">
                        <div class="bg-primary proj-add-icon my-4">
                            <i class="ti ti-plus"></i>
                        </div>
                        <h6 class="mt-4 mb-2">{{ __('New Company') }}</h6>
                        <p class="text-muted text-center">{{ __('Click here to add new company') }}</p>
                    </a>
                </div>
            </div>

            {{-- ═══ 2. TABLE (DATATABLE) VIEW ═══ --}}
            <div id="company-table-view" class="row mt-4 d-none">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th width="40px"><input type="checkbox" class="form-check-input cursor-pointer" id="table-select-all-companies" onchange="toggleSelectAllCompanies(this)"></th>
                                            <th>{{ __('Company') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Current Plan') }}</th>
                                            <th>{{ __('Users') }}</th>
                                            <th>{{ __('Employees') }}</th>
                                            <th>{{ __('Plan Expire') }}</th>
                                            <th width="240px">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            @php
                                                $adminSettings = \App\Models\Utility::settings();
                                                $isLoginSecurityOn = ($adminSettings['login_as_company_security'] ?? 'on') === 'on';
                                            @endphp
                                            <tr>
                                                <td><input type="checkbox" class="form-check-input company-select-checkbox cursor-pointer" value="{{ $user->id }}" onchange="updateCompanySelectionState()"></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <img src="{{ !empty($user->avatar) ? $profile . $user->avatar : $logo . 'avatar.png' }}"
                                                                class="rounded-circle border" width="36px" height="36px" style="object-fit: cover;">
                                                        </div>
                                                        <span class="fw-semibold text-dark">{{ $user->name }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <span class="badge bg-primary p-2 px-3">
                                                        {{ !empty($user->currentPlan) ? $user->currentPlan->name : __('Free Plan') }}
                                                    </span>
                                                </td>
                                                <td>{{ $user->countUsers() }}</td>
                                                <td>{{ $user->countEmployees() }}</td>
                                                <td>
                                                    {{ !empty($user->plan_expire_date) ? \Auth::user()->dateFormat($user->plan_expire_date) : __('Lifetime') }}
                                                </td>
                                                <td class="Action">
                                                    <span>
                                                        <div class="action-btn bg-info me-2">
                                                            <a href="javascript:void(0)" class="mx-3 btn btn-sm align-items-center"
                                                                data-url="{{ route('user.edit', $user->id) }}" data-size="md"
                                                                data-ajax-popup="true" data-title="{{ __('Update User') }}"
                                                                data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>

                                                        <div class="action-btn bg-primary me-2">
                                                            <a href="javascript:void(0)" class="mx-3 btn btn-sm align-items-center"
                                                                data-url="{{ route('plan.upgrade', $user->id) }}" data-size="lg"
                                                                data-ajax-popup="true" data-title="{{ __('Upgrade Plan') }}"
                                                                data-bs-toggle="tooltip" title="{{ __('Upgrade Plan') }}">
                                                                <i class="ti ti-trophy text-white"></i>
                                                            </a>
                                                        </div>

                                                        <div class="action-btn bg-secondary me-2">
                                                            <a href="javascript:void(0)" class="mx-3 btn btn-sm align-items-center"
                                                                data-url="{{ route('company.info', $user->id) }}" data-size="lg"
                                                                data-ajax-popup="true" data-title="{{ __('Company Info') }}"
                                                                data-bs-toggle="tooltip" title="{{ __('AdminHub') }}">
                                                                <i class="ti ti-info-circle text-white"></i>
                                                            </a>
                                                        </div>

                                                        @if ($isLoginSecurityOn)
                                                            <div class="action-btn bg-dark me-2">
                                                                <a href="javascript:void(0)" data-url="{{ route('login.with.company', $user->id) }}"
                                                                    data-ajax-popup="true" data-size="md" data-title="{{ __('Login As Company Verification') }}"
                                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('Login As Company') }}">
                                                                    <i class="ti ti-replace text-white"></i>
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="action-btn bg-dark me-2">
                                                                <a href="{{ route('login.with.company', $user->id) }}" class="mx-3 btn btn-sm align-items-center"
                                                                    data-bs-toggle="tooltip" title="{{ __('Login As Company') }}">
                                                                    <i class="ti ti-replace text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endif

                                                        <div class="action-btn bg-warning me-2">
                                                            <a href="javascript:void(0)" class="mx-3 btn btn-sm align-items-center"
                                                                data-url="{{ route('user.reset', \Crypt::encrypt($user->id)) }}"
                                                                data-ajax-popup="true" data-size="md" data-title="{{ __('Change Password') }}"
                                                                data-bs-toggle="tooltip" title="{{ __('Reset Password') }}">
                                                                <i class="ti ti-key text-white"></i>
                                                            </a>
                                                        </div>

                                                        <div class="action-btn bg-danger">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['user.destroy', $user->id], 'id' => 'delete-form-table-' . $user->id]) !!}
                                                            <a href="javascript:void(0)" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="{{ $user->delete_status == 0 ? __('Delete') : __('Restore') }}">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row mt-4">
                @foreach ($users as $user)
                    @php
                        $employeeRecord = \App\Models\Employee::where('user_id', $user->id)->first();
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-sm-6 col-12">
                        <div class="card text-center">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <div class="badge p-2 px-3 bg-primary">{{ ucfirst($user->type) }}</div>
                                    </h6>
                                </div>

                                @if (Gate::check('Edit User') || Gate::check('Delete User') || Gate::check('Reset Password User') || Gate::check('Login Enable Disable User'))
                                    <div class="card-header-right">
                                        <div class="btn-group card-option">
                                            @if ($user->is_active == 1 && $user->is_disable == 1)
                                                <button type="button" class="btn dropdown-toggle"
                                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="feather icon-more-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    @can('Edit User')
                                                        <a href="javascript:void(0)" class="dropdown-item"
                                                            data-url="{{ route('user.edit', $user->id) }}" data-size="md"
                                                            data-ajax-popup="true" data-title="{{ __('Update User') }}"><i
                                                                class="ti ti-edit "></i><span
                                                                class="ms-2">{{ __('Edit') }}</span></a>
                                                    @endcan

                                                    @if ($employeeRecord && \Auth::user()->can('Edit Employee'))
                                                        <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employeeRecord->id)) }}"
                                                            class="dropdown-item">
                                                            <i class="ti ti-id"></i>
                                                            <span class="ms-2">{{ __('Edit Employee Profile') }}</span>
                                                        </a>
                                                    @endif

                                                    @can('Delete User')
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['user.destroy', $user->id],
                                                            'id' => 'delete-form-' . $user->id,
                                                        ]) !!}
                                                        <a href="javascript:void(0)" class="bs-pass-para dropdown-item"
                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="delete-form-{{ $user->id }}"
                                                            title="{{ __('Delete') }}" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"><i class="ti ti-trash"></i><span
                                                                class="ms-2">{{ __('Delete') }}</span></a>
                                                        {!! Form::close() !!}
                                                    @endcan

                                                    @can('Reset Password User')
                                                        <a href="javascript:void(0)" class="dropdown-item"
                                                            data-ajax-popup="true" data-size="md"
                                                            data-title="{{ __('Change Password') }}"
                                                            data-url="{{ route('user.reset', \Crypt::encrypt($user->id)) }}"><i
                                                                class="ti ti-key"></i>
                                                            <span class="ms-1">{{ __('Reset Password') }}</span></a>
                                                    @endcan

                                                    @can('Login Enable Disable User')
                                                        @if ($user->is_login_enable == 1)
                                                            <a href="{{ route('user.login', \Crypt::encrypt($user->id)) }}"
                                                                class="dropdown-item">
                                                                <i class="ti ti-road-sign"></i>
                                                                <span class="text-danger ms-1">
                                                                    {{ __('Login Disable') }}</span>
                                                            </a>
                                                        @elseif ($user->is_login_enable == 0 && $user->password == null)
                                                            <a href="javascript:void(0)"
                                                                data-url="{{ route('user.reset', \Crypt::encrypt($user->id)) }}"
                                                                data-ajax-popup="true" data-size="md"
                                                                class="dropdown-item login_enable"
                                                                data-title="{{ __('New Password') }}" class="dropdown-item">
                                                                <i class="ti ti-road-sign"></i>
                                                                <span class="text-success ms-1">
                                                                    {{ __('Login Enable') }}</span>
                                                            </a>
                                                        @else
                                                            <a href="{{ route('user.login', \Crypt::encrypt($user->id)) }}"
                                                                class="dropdown-item">
                                                                <i class="ti ti-road-sign"></i>
                                                                <span class="text-success ms-1">
                                                                    {{ __('Login Enable') }}</span>
                                                            </a>
                                                        @endif
                                                    @endcan
                                                </div>
                                            @else
                                                <i class="ti ti-lock"></i>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                            </div>
                            <div class="card-body">
                                <div class="avatar">
                                    <a href="{{ !empty($user->avatar) ? $profile . $user->avatar : $logo . 'avatar.png' }}"
                                        target="_blank">
                                        <img src="{{ !empty($user->avatar) ? $profile . $user->avatar : $logo . 'avatar.png' }}"
                                            class="img-fluid rounded border-2 border border-primary" width="120px"
                                            style="height: 120px">
                                    </a>

                                </div>
                                <h4 class="mt-2 text-primary">{{ $user->name }}</h4>
                                <small class="">{{ $user->email }}</small>

                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <a href="javascript:void(0)" class="btn-addnew-project border-primary" data-ajax-popup="true"
                        data-url="{{ route('user.create') }}" data-title="{{ __('Create New User') }}"
                        data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary">
                        <div class="bg-primary proj-add-icon">
                            <i class="ti ti-plus"></i>
                        </div>
                        <h6 class="mt-4 mb-2">{{ __('New User') }}</h6>
                        <p class="text-muted text-center">{{ __('Click here to add new user') }}</p>
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    {{-- Company Bulk Selection & Deletion Script --}}
    <script>
        function updateCompanySelectionState() {
            var selectedIds = [];
            $('.company-select-checkbox:checked').each(function() {
                var val = $(this).val();
                if (selectedIds.indexOf(val) === -1) {
                    selectedIds.push(val);
                }
            });

            $('#bulk-selected-count').text(selectedIds.length);
            if (selectedIds.length > 0) {
                $('#btn-bulk-delete-companies').removeClass('d-none');
            } else {
                $('#btn-bulk-delete-companies').addClass('d-none');
            }
        }

        function toggleSelectAllCompanies(masterCheck) {
            var isChecked = $(masterCheck).is(':checked');
            $('.company-select-checkbox').prop('checked', isChecked);
            $('#select-all-companies-check').prop('checked', isChecked);
            $('#table-select-all-companies').prop('checked', isChecked);
            updateCompanySelectionState();
        }

        function submitCompanyBulkDelete() {
            var selectedIds = [];
            $('.company-select-checkbox:checked').each(function() {
                var val = $(this).val();
                if (selectedIds.indexOf(val) === -1) {
                    selectedIds.push(val);
                }
            });

            if (selectedIds.length === 0) return;

            var titleText = '{{ __("Are you sure?") }}';
            var bodyText = '{{ __("Are you sure you want to delete the selected ") }}' + selectedIds.length + ' {{ __("companies? This action cannot be undone and will delete all associated employees and users.") }}';

            if (typeof Swal !== 'undefined') {
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success mx-2',
                        cancelButton: 'btn btn-danger mx-2'
                    },
                    buttonsStyling: false
                });

                swalWithBootstrapButtons.fire({
                    title: titleText,
                    text: bodyText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __("Yes, Delete All") }}',
                    cancelButtonText: '{{ __("Cancel") }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#bulk-delete-company-ids').val(selectedIds.join(','));
                        $('#form-bulk-delete-companies').submit();
                    }
                });
            } else if (confirm(bodyText)) {
                $('#bulk-delete-company-ids').val(selectedIds.join(','));
                $('#form-bulk-delete-companies').submit();
            }
        }
    </script>

    {{-- View Switcher & Live Search Script --}}
    <script>
        function switchCompanyView(view) {
            if (view === 'table') {
                $('#company-grid-view').addClass('d-none');
                $('#company-grid-search-bar').addClass('d-none');
                $('#company-table-view').removeClass('d-none');
                $('#btn-grid-view').removeClass('btn-primary active').addClass('btn-outline-primary');
                $('#btn-table-view').removeClass('btn-outline-primary').addClass('btn-primary active');
                localStorage.setItem('company_view_pref', 'table');
            } else {
                $('#company-table-view').addClass('d-none');
                $('#company-grid-search-bar').removeClass('d-none');
                $('#company-grid-view').removeClass('d-none');
                $('#btn-table-view').removeClass('btn-primary active').addClass('btn-outline-primary');
                $('#btn-grid-view').removeClass('btn-outline-primary').addClass('btn-primary active');
                localStorage.setItem('company_view_pref', 'grid');
            }
        }

        var gridSearchTimeout = null;

        $(document).on('keyup input', '#company-grid-search-input', function() {
            var $input = $(this);
            var query = $.trim($input.val()).toLowerCase();

            // Show spinning loader icon while searching
            $('#icon-search-default').addClass('d-none');
            $('#icon-search-loader').removeClass('d-none');

            if (gridSearchTimeout) {
                clearTimeout(gridSearchTimeout);
            }

            gridSearchTimeout = setTimeout(function() {
                var visibleCount = 0;

                if (query.length > 0) {
                    $('#btn-clear-grid-search').removeClass('d-none');
                } else {
                    $('#btn-clear-grid-search').addClass('d-none');
                }

                $('.company-card-item').each(function() {
                    var searchData = $(this).attr('data-search') || '';
                    if (searchData.indexOf(query) !== -1) {
                        $(this).removeClass('d-none');
                        visibleCount++;
                    } else {
                        $(this).addClass('d-none');
                    }
                });

                if (visibleCount === 0) {
                    $('#company-no-results-msg').removeClass('d-none');
                    $('.company-card-add-new').addClass('d-none');
                } else {
                    $('#company-no-results-msg').addClass('d-none');
                    $('.company-card-add-new').removeClass('d-none');
                }

                $('#grid-search-count-info').html('<i class="ti ti-building me-1"></i> {{ __("Found:") }} <strong>' + visibleCount + '</strong> {{ __("companies") }}');

                // Hide spinner and restore search icon
                $('#icon-search-loader').addClass('d-none');
                $('#icon-search-default').removeClass('d-none');
            }, 180);
        });

        function clearCompanyGridSearch() {
            $('#company-grid-search-input').val('').trigger('input');
        }

        $(document).ready(function() {
            var pref = localStorage.getItem('company_view_pref') || 'grid';
            switchCompanyView(pref);
        });
    </script>

    {{-- Password  --}}
    <script>
        $(document).on('change', '#password_switch', function() {
            if ($(this).is(':checked')) {
                $('.ps_div').removeClass('d-none');
                $('#password').attr("required", true);

            } else {
                $('.ps_div').addClass('d-none');
                $('#password').val(null);
                $('#password').removeAttr("required");
            }
        });
        $(document).on('click', '.login_enable', function() {
            setTimeout(function() {
                $('.modal-body').append($('<input>', {
                    type: 'hidden',
                    val: 'true',
                    name: 'login_enable'
                }));
            }, 2000);
        });
    </script>
@endpush
