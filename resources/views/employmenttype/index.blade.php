@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Employment Type') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employment Type') }}</li>
@endsection

@section('action-button')
    @can('Create Employment Type')
        <a href="#" data-url="{{ route('employmenttype.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Employment Type') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table datatable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th width="200px">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($employmenttypes as $employmenttype)
                                            <tr>
                                                <td>{{ $employmenttype->name }}</td>
                                                <td class="Action">
                                                    <div class="dt-buttons">
                                                        <span class="float-start">
                                                            @can('Edit Employment Type')
                                                                <div class="action-btn me-2">
                                                                    <a href="#" class="btn btn-sm align-items-center bg-info"
                                                                        data-url="{{ route('employmenttype.edit', $employmenttype->id) }}"
                                                                        data-ajax-popup="true" data-title="{{ __('Edit Employment Type') }}"
                                                                        data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('Delete Employment Type')
                                                                <div class="action-btn">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['employmenttype.destroy', $employmenttype->id],
                                                                        'id' => 'delete-form-' . $employmenttype->id,
                                                                    ]) !!}
                                                                    <a href="#" data-bs-trigger="hover"
                                                                        class="btn btn-sm align-items-center bs-pass-para bg-danger"
                                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                        <i class="ti ti-trash text-white"></i>
                                                                    </a>
                                                                    {!! Form::close() !!}
                                                                </div>
                                                            @endcan
                                                        </span>
                                                    </div>
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
        </div>
    </div>
@endsection
