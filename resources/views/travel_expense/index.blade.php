@extends('layouts.admin')

@section('page-title')
    {{ __('Travel Expenses & Vouchers') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Travel Expenses & Vouchers') }}</li>
@endsection

@section('action-button')
    @can('Create Travel Expense')
        <a href="javascript:void(0)" data-url="{{ route('travel-expenses.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Travel Expense / Voucher') }}" data-size="lg" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
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
                {{ Form::open(['route' => ['travel-expenses.index'], 'method' => 'GET', 'id' => 'travel_expense_filter']) }}
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
                                    {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('type', $types, isset($_GET['type']) ? $_GET['type'] : '', ['class' => 'form-control select', 'id' => 'filter_type']) }}
                                </div>
                            </div>
                            <div class="col-xl col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : null, ['class' => 'form-control w-100', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <div class="col-xl col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : null, ['class' => 'form-control w-100', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="row">
                            <div class="col-auto mt-4">
                                <a href="javascript:void(0)" class="btn btn-sm btn-primary me-1"
                                    onclick="document.getElementById('travel_expense_filter').submit(); return false;"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('travel-expenses.index') }}" class="btn btn-sm btn-danger"
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
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Attachments') }}</th>
                                @if (Gate::check('Edit Travel Expense') || Gate::check('Delete Travel Expense') || Gate::check('Manage Travel Expense'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($travelExpenses as $travelExpense)
                                <tr>
                                    <td>
                                        @if($travelExpense->type == 'travel')
                                            <span class="badge bg-primary p-2 px-3 rounded">{{ __('Travel') }}</span>
                                        @else
                                            <span class="badge bg-info p-2 px-3 rounded">{{ __('Voucher') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ !empty($travelExpense->employee) ? $travelExpense->employee->name : '-' }}</td>
                                    <td>{{ $travelExpense->title }}</td>
                                    <td>{{ \Auth::user()->priceFormat($travelExpense->amount) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($travelExpense->start_date) }} - {{ \Auth::user()->dateFormat($travelExpense->end_date) }}</td>
                                    <td>
                                        @if ($travelExpense->document_requested == 1)
                                            <span class="badge bg-warning p-2 px-3 rounded" data-bs-toggle="tooltip" title="{{ __('Document Requested by HR/Company') }}">
                                                <i class="ti ti-clock me-1"></i> {{ __('Doc Requested') }}
                                            </span>
                                        @elseif ($travelExpense->documents->count() > 0)
                                            <span class="badge bg-success p-2 px-3 rounded">
                                                <i class="ti ti-paperclip me-1"></i> {{ $travelExpense->documents->count() }} {{ __('Files') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary p-2 px-3 rounded">
                                                <i class="ti ti-paperclip me-1"></i> 0 {{ __('Files') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="Action">
                                        <div class="d-flex align-items-center">
                                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || \Auth::user()->type == 'HR' || \Auth::user()->type == 'super admin')
                                                @if ($travelExpense->document_requested == 1)
                                                    <div class="action-btn bg-danger me-2">
                                                        {!! Form::open(['method' => 'POST', 'route' => ['travel-expenses.cancel-document-request', $travelExpense->id], 'id' => 'cancel-req-doc-form-' . $travelExpense->id]) !!}
                                                        <a href="javascript:void(0)"
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Cancel Document Request') }}"
                                                            data-original-title="{{ __('Cancel Document Request') }}"
                                                            data-confirm="{{ __('Cancel Request?') . '|' . __('Do you want to cancel the document request for this employee?') }}"
                                                            data-confirm-yes="document.getElementById('cancel-req-doc-form-{{ $travelExpense->id }}').submit();">
                                                            <i class="ti ti-file-off text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @else
                                                    <div class="action-btn bg-dark me-2">
                                                        {!! Form::open(['method' => 'POST', 'route' => ['travel-expenses.request-document', $travelExpense->id], 'id' => 'req-doc-form-' . $travelExpense->id]) !!}
                                                        <a href="javascript:void(0)"
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Call Request Document') }}"
                                                            data-original-title="{{ __('Call Request Document') }}"
                                                            data-confirm="{{ __('Request Document?') . '|' . __('Do you want to send a document upload request to the employee?') }}"
                                                            data-confirm-yes="document.getElementById('req-doc-form-{{ $travelExpense->id }}').submit();">
                                                            <i class="ti ti-file-upload text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endif
                                            @endif

                                            @if (\Auth::user()->type == 'employee' && $travelExpense->document_requested == 1)
                                                <div class="action-btn bg-primary me-2">
                                                    <a href="javascript:void(0)"
                                                        class="mx-3 btn btn-sm align-items-center"
                                                        data-url="{{ route('travel-expenses.upload-document-modal', $travelExpense->id) }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Upload Bills & Documents') }}"
                                                        data-size="lg"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Upload Document') }}">
                                                        <i class="ti ti-upload text-white"></i>
                                                    </a>
                                                </div>
                                            @endif

                                            @can('Manage Travel Expense')
                                                <div class="action-btn bg-warning me-2">
                                                    <a href="javascript:void(0)"
                                                        class="mx-3 btn btn-sm align-items-center"
                                                        data-url="{{ route('travel-expenses.show', $travelExpense->id) }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Details & Attachments') }}"
                                                        data-size="lg"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('View') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('Edit Travel Expense')
                                                <div class="action-btn bg-info me-2">
                                                    <a href="javascript:void(0)"
                                                        class="mx-3 btn btn-sm align-items-center"
                                                        data-url="{{ route('travel-expenses.edit', $travelExpense->id) }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Travel Expense / Voucher') }}"
                                                        data-size="lg"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('Delete Travel Expense')
                                                <div class="action-btn bg-danger">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['travel-expenses.destroy', $travelExpense->id], 'id' => 'delete-form-' . $travelExpense->id]) !!}
                                                    <a href="javascript:void(0)"
                                                        class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                        data-original-title="{{ __('Delete') }}"
                                                        data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('delete-form-{{ $travelExpense->id }}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
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
