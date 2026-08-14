@extends('layouts.admin')

@section('page-title')
    {{ __('Super Admin Staff') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Super Admin Staff') }}</li>
@endsection

@section('action-button')
    <a href="javascript:void(0)" data-url="{{ route('superadmin-staff.create') }}" data-ajax-popup="true"
        data-title="{{ __('Create Super Admin Staff') }}" data-size="md" data-bs-toggle="tooltip" title=""
        class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus"></i>
    </a>
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
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($users) > 0)
                                    @foreach ($users as $staff)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-light-primary text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                        <i class="ti ti-user fs-5"></i>
                                                    </div>
                                                    <span class="fw-semibold text-dark">{{ $staff->name }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $staff->email }}</td>
                                            <td>
                                                <span class="badge bg-light-primary text-primary rounded-pill px-3 py-1.5 fw-semibold fs-7">
                                                    {{ ucfirst($staff->type) }}
                                                </span>
                                            </td>
                                            <td>{{ \Auth::user()->dateFormat($staff->created_at) }}</td>
                                            <td class="Action">
                                                <span>
                                                    <div class="action-btn bg-info me-2">
                                                        <a href="javascript:void(0)" class="mx-3 btn btn-sm align-items-center"
                                                            data-url="{{ route('superadmin-staff.edit', $staff->id) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Edit Staff User') }}"
                                                            data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>

                                                    <div class="action-btn bg-warning me-2">
                                                        <a href="javascript:void(0)" class="mx-3 btn btn-sm align-items-center"
                                                            data-url="{{ route('superadmin-staff.reset-password-modal', $staff->id) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Reset Password') }}"
                                                            data-bs-toggle="tooltip" title="{{ __('Reset Password') }}"
                                                            data-bs-original-title="{{ __('Reset Password') }}">
                                                            <i class="ti ti-key text-white"></i>
                                                        </a>
                                                    </div>

                                                    <div class="action-btn bg-danger">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['superadmin-staff.destroy', $staff->id], 'id' => 'delete-form-' . $staff->id]) !!}
                                                            <a href="javascript:void(0)" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                data-bs-original-title="{{ __('Delete') }}">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-0">{{ __('No Super Admin Staff members found.') }}</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
