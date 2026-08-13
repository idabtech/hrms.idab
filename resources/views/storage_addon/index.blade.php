@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Storage Addons & Orders') }}
@endsection

@section('action-button')
    @if (\Auth::user()->type == 'super admin' || \Auth::user()->can('Create Storage Addon') || \Auth::user()->can('Create Storage Addons'))
        <a href="javascript:void(0)" data-url="{{ route('storage-addons.create') }}" data-size="md" data-ajax-popup="true"
            data-title="{{ __('Create New Storage Addon') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endif
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Storage Addon List & Orders') }}</li>
@endsection

@section('content')
@php
    $admin_payment_setting = App\Models\Utility::getAdminPaymentSetting();
    $currency_symbol = !empty($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$';
@endphp
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-inline-flex bg-white p-1 rounded border shadow-sm">
                <ul class="nav nav-pills" id="adminStorageTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold py-2 px-3" id="addon-list-tab" data-bs-toggle="tab" data-bs-target="#addon-list-content" type="button" role="tab" aria-controls="addon-list-content" aria-selected="true" style="border-radius: 6px;">
                            <i class="ti ti-database me-1"></i>{{ __('Storage Addons List') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-2 px-3" id="addon-orders-tab" data-bs-toggle="tab" data-bs-target="#addon-orders-content" type="button" role="tab" aria-controls="addon-orders-content" aria-selected="false" style="border-radius: 6px;">
                            <i class="ti ti-shopping-cart me-1"></i>{{ __('Addon Orders & Requests') }}
                            @php
                                $pendingCount = $orders->where('payment_status', 'Pending')->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                            @endif
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="tab-content" id="adminStorageTabContent">
        {{-- Storage Addons List Tab --}}
        <div class="tab-pane fade show active" id="addon-list-content" role="tabpanel" aria-labelledby="addon-list-tab">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple" id="pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <th> {{ __('Title') }}</th>
                                            <th> {{ __('Price') }}</th>
                                            <th> {{ __('Storage Amount') }}</th>
                                            <th> {{ __('Addon Type') }}</th>
                                            <th> {{ __('Duration') }}</th>
                                            <th> {{ __('Status') }}</th>
                                            <th width="200px"> {{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($addons as $addon)
                                            <tr>
                                                <td>{{ $addon->title }}</td>
                                                <td>{{ $currency_symbol }}{{ number_format($addon->price, 2) }}</td>
                                                <td>{{ $addon->formatted_storage }}</td>
                                                <td>
                                                    @if($addon->addon_type === 'plan_expiry')
                                                        {{ __('Co-terminus with Active Plan Expiry') }}
                                                    @else
                                                        {{ __('Fixed Duration') }}
                                                    @endif
                                                </td>
                                                <td>{{ $addon->formatted_duration }}</td>
                                                <td>
                                                    @if($addon->is_active)
                                                        <span class="badge bg-success p-2 px-3 rounded">{{ __('Active') }}</span>
                                                    @else
                                                        <span class="badge bg-danger p-2 px-3 rounded">{{ __('Inactive') }}</span>
                                                    @endif
                                                </td>
                                                <td class="Action">
                                                    {{-- Status Toggle --}}
                                                    @if (\Auth::user()->type == 'super admin' || \Auth::user()->can('Edit Storage Addon') || \Auth::user()->can('Edit Storage Addons'))
                                                        <div class="action-btn me-2">
                                                            <a href="{{ route('storage-addons.status', $addon->id) }}"
                                                                class="btn btn-sm {{ $addon->is_active ? 'bg-warning' : 'bg-success' }} align-items-center"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ $addon->is_active ? __('Disable') : __('Enable') }}">
                                                                <span class="text-white"><i class="ti {{ $addon->is_active ? 'ti-power' : 'ti-circle-check' }}"></i></span>
                                                            </a>
                                                        </div>
                                                    @endif

                                                    {{-- Edit --}}
                                                    @if (\Auth::user()->type == 'super admin' || \Auth::user()->can('Edit Storage Addon') || \Auth::user()->can('Edit Storage Addons'))
                                                        <div class="action-btn me-2">
                                                            <a href="javascript:void(0)" class="btn btn-sm bg-info align-items-center"
                                                                data-url="{{ route('storage-addons.edit', $addon->id) }}"
                                                                data-size="md" data-ajax-popup="true"
                                                                data-title="{{ __('Edit Storage Addon') }}"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Edit') }}">
                                                                <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                            </a>
                                                        </div>
                                                    @endif

                                                    {{-- Delete --}}
                                                    @if (\Auth::user()->type == 'super admin' || \Auth::user()->can('Delete Storage Addon') || \Auth::user()->can('Delete Storage Addons'))
                                                        <div class="action-btn">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['storage-addons.destroy', $addon->id],
                                                                'id' => 'delete-form-' . $addon->id,
                                                            ]) !!}
                                                            <a href="javascript:void(0)" data-bs-trigger="hover" class="btn btn-sm bg-danger align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Delete') }}">
                                                                <span class="text-white"><i class="ti ti-trash"></i></span>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="ti ti-database-off fs-1 d-block mb-2 text-muted"></i>
                                                    <h6>{{ __('No Storage Addons Found.') }}</h6>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Addon Orders & Requests Tab --}}
        <div class="tab-pane fade" id="addon-orders-content" role="tabpanel" aria-labelledby="addon-orders-tab">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple" id="pc-dt-orders-table">
                                     <thead>
                                        <tr>
                                            <th> {{ __('Order ID') }}</th>
                                            <th> {{ __('Name') }}</th>
                                            <th> {{ __('Addon Title') }}</th>
                                            <th> {{ __('Storage Amount') }}</th>
                                            <th> {{ __('Price') }}</th>
                                            <th> {{ __('Status') }}</th>
                                            <th> {{ __('Type') }}</th>
                                            <th> {{ __('Start Date') }}</th>
                                            <th> {{ __('Expiry Date') }}</th>
                                            <th> {{ __('Receipt / Txn ID') }}</th>
                                            <th width="150px"> {{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($orders as $order)
                                            <tr>
                                                <td><span class="fw-bold">{{ $order->order_id }}</span></td>
                                                <td>{{ $order->user ? $order->user->name : '-' }}</td>
                                                <td>{{ $order->addon_title }}</td>
                                                <td><span class="badge bg-info text-white">{{ $order->formatted_storage }}</span></td>
                                                <td>{{ $currency_symbol }}{{ number_format($order->price, 2) }}</td>
                                                <td>
                                                    @if ($order->payment_status == 'Approved' || $order->payment_status == 'approved' || $order->payment_status == 'succeeded' || $order->payment_status == 'Success' || $order->payment_status == 'success')
                                                        @if($order->isExpired())
                                                            <span class="badge bg-secondary p-2 px-3 rounded">{{ __('Expired') }}</span>
                                                        @else
                                                            <span class="badge bg-success p-2 px-3 rounded">{{ __('Success') }}</span>
                                                        @endif
                                                    @elseif($order->payment_status == 'Pending' || $order->payment_status == 'pending')
                                                        <span class="badge bg-warning p-2 px-3 rounded">{{ __('Pending') }}</span>
                                                    @else
                                                        <span class="badge bg-danger p-2 px-3 rounded">{{ __('Rejected') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $order->payment_type }}</td>
                                                <td class="text-sm"><i class="ti ti-calendar-event me-1 text-primary"></i>{{ $order->start_date_formatted }}</td>
                                                <td class="text-sm"><i class="ti ti-calendar-due me-1 text-danger"></i>{{ $order->expire_date_formatted }}</td>
                                                <td class="text-center">
                                                    @if(($order->payment_type == 'Bank Transfer' || $order->payment_type == 'Manual') && !empty($order->receipt))
                                                        <a href="{{ asset($order->receipt) }}" class="btn btn-sm btn-outline-primary" target="_blank" data-bs-toggle="tooltip" title="{{ __('Show Invoice / Receipt') }}">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </a>
                                                    @elseif(!empty($order->receipt))
                                                        <span class="badge bg-light text-dark border font-monospace">{{ $order->receipt }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="Action">
                                                    @if($order->payment_status === 'Pending' && (\Auth::user()->type === 'super admin' || \Auth::user()->isSuperAdminSideUser()))
                                                        <div class="action-btn bg-success me-2">
                                                            <a href="{{ route('storage-addons.approve-order', $order->id) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Approve & Grant Storage') }}"
                                                                data-bs-original-title="{{ __('Approve') }}">
                                                                <span class="text-white"><i class="ti ti-check"></i></span>
                                                            </a>
                                                        </div>
                                                        <div class="action-btn bg-danger">
                                                            <a href="{{ route('storage-addons.reject-order', $order->id) }}"
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Reject Request') }}"
                                                                data-bs-original-title="{{ __('Reject') }}">
                                                                <span class="text-white"><i class="ti ti-x"></i></span>
                                                            </a>
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-4 text-muted">
                                                    <i class="ti ti-shopping-cart-x fs-1 d-block mb-2 text-muted"></i>
                                                    <h6>{{ __('No Storage Addon Orders or Requests Found.') }}</h6>
                                                </td>
                                            </tr>
                                        @endforelse
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

@push('scripts')
    <script>
        $(document).ready(function() {
            // Auto switch tab if tab=orders is passed
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab') === 'orders' || window.location.hash === '#orders' || window.location.hash === '#addon-orders-tab') {
                var ordersTabBtn = document.getElementById('addon-orders-tab');
                if (ordersTabBtn) {
                    var tab = new bootstrap.Tab(ordersTabBtn);
                    tab.show();
                }
            }

            if (typeof simpleDatatables !== 'undefined') {
                const ordersTableEl = document.querySelector("#pc-dt-orders-table");
                if (ordersTableEl && !ordersTableEl.classList.contains('dataTable-table')) {
                    new simpleDatatables.DataTable(ordersTableEl);
                }
            }
        });
    </script>
@endpush
