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
                                        <span class="badge bg-secondary p-2 px-3 rounded">
                                            <i class="ti ti-paperclip me-1"></i>
                                            {{ $travelExpense->documents->count() }} {{ __('Files') }}
                                        </span>
                                    </td>
                                    <td class="Action">
                                        <div class="d-flex align-items-center">
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
