@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Self Assessment') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Self Assessment') }}</li>
@endsection

@section('action-button')
    @can('Create Self Assessment')
        <a href="{{ route('self-assessments.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
            title="{{ __('Create') }}" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">

        {{-- Filter Card --}}
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['self-assessments.index'], 'method' => 'GET', 'id' => 'self_assessment_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col">
                            <div class="row">
                                @if (\Auth::user()->type != 'employee')
                                    <div class="col-xl col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                            {{ Form::select('employee_id', $employees, isset($_GET['employee_id']) ? $_GET['employee_id'] : '', ['class' => 'form-control select', 'id' => 'filter_employee_id']) }}
                                        </div>
                                    </div>
                                @endif
                                <div class="col-xl col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                        {{ Form::select('status', ['' => __('All Statuses'), 'draft' => __('Draft'), 'submitted' => __('Submitted'), 'reviewed' => __('Reviewed')], isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control select', 'id' => 'filter_status']) }}
                                    </div>
                                </div>
                                <div class="col-xl col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                        <input type="month" name="month" value="{{ isset($_GET['month']) ? $_GET['month'] : '' }}" class="form-control w-100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto mt-4">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-primary me-1"
                                        onclick="document.getElementById('self_assessment_filter').submit(); return false;"
                                        data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('self-assessments.index') }}" class="btn btn-sm btn-danger"
                                        data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-refresh text-white-off"></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        {{-- Data Table Card --}}
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Month') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Designation / Department') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Average Rating') }}</th>
                                    @if (Gate::check('Manage Self Assessment') || Gate::check('Edit Self Assessment') || Gate::check('Delete Self Assessment'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assessments as $assessment)
                                    <tr>
                                        <td>
                                            <a href="{{ route('self-assessments.show', $assessment->id) }}" class="text-primary font-weight-bold">
                                                {{ $assessment->monthLabel() }}
                                            </a>
                                        </td>
                                        <td>{{ $assessment->employee_name }}</td>
                                        <td>
                                            <div><small class="text-dark font-weight-bold">{{ $assessment->designation ?: '-' }}</small></div>
                                            <div><small class="text-muted">{{ $assessment->department ?: '-' }}</small></div>
                                        </td>
                                        <td>
                                            @if($assessment->status == 'reviewed')
                                                <span class="badge bg-success p-2 px-3 rounded">{{ __('Reviewed') }}</span>
                                            @elseif($assessment->status == 'submitted')
                                                <span class="badge bg-warning p-2 px-3 rounded">{{ __('Submitted') }}</span>
                                            @else
                                                <span class="badge bg-secondary p-2 px-3 rounded">{{ __('Draft') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($assessment->averageScore())
                                                <span class="badge bg-info p-2 px-3 rounded fs-6">{{ $assessment->averageScore() }} / 5</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <div class="d-flex align-items-center">
                                                @can('Manage Self Assessment')
                                                    <div class="action-btn bg-warning me-2">
                                                        <a href="{{ route('self-assessments.show', $assessment->id) }}"
                                                            class="mx-3 btn btn-sm align-items-center"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('View') }}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @if($assessment->isEditable())
                                                    @can('Edit Self Assessment')
                                                        <div class="action-btn bg-info me-2">
                                                            <a href="{{ route('self-assessments.edit', $assessment->id) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    @can('Delete Self Assessment')
                                                        <div class="action-btn bg-danger me-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['self-assessments.destroy', $assessment->id], 'id' => 'delete-form-' . $assessment->id]) !!}
                                                            <a href="javascript:void(0)"
                                                                class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{ $assessment->id }}').submit();">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                @endif
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
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            var table = document.querySelector("#pc-dt-simple");
            if (table && typeof simpleDatatables !== 'undefined') {
                var datatable = new simpleDatatables.DataTable(table, {
                    perPage: 10,
                    perPageSelect: [10, 25, 50, 100],
                    paging: true,
                    firstLast: true,
                    truncatePager: false
                });

                var pagerContainer = $(table).closest('.card-body').find('.dataTable-bottom');
                if (pagerContainer.length > 0 && pagerContainer.find('.dataTable-pagination-list').length === 0) {
                    var navHtml = '<nav class="dataTable-pagination"><ul class="dataTable-pagination-list">' +
                        '<li class="pager disabled"><a href="javascript:void(0)" data-page="1">‹</a></li>' +
                        '<li class="active"><a href="javascript:void(0)" data-page="1">1</a></li>' +
                        '<li class="pager disabled"><a href="javascript:void(0)" data-page="1">›</a></li>' +
                        '</ul></nav>';
                    pagerContainer.append(navHtml);
                }
            }
        });
    </script>
@endpush
