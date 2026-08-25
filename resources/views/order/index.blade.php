@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Plan Order') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Plan Order') }}</li>
@endsection

@php
    $file = \App\Models\Utility::get_file('uploads/order/');
    $admin_payment_setting = App\Models\Utility::getAdminPaymentSetting();
@endphp

@section('content')
    <div class="row">

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    {{-- <h5></h5> --}}
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Order Id') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Plan Name') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Coupon') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>{{ $order->order_id }}</td>
                                        <td>{{ $order->user_name }}</td>
                                        <td>{{ $order->plan_name }}</td>
                                        <td>{{ (!empty($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$') . $order->price }}
                                        </td>
                                        <td class="text-nowrap">
                                            @if ($order->payment_status == 'Approved' || $order->payment_status == 'approved')
                                                <span
                                                    class="badge bg-primary p-2 px-3 text-nowrap d-inline-block">{{ __('Approved') }}</span>
                                            @elseif($order->payment_status == 'success' || $order->payment_status == 'succeeded' || $order->payment_status == 'Success' || $order->payment_status == 'Succeeded')
                                                <span
                                                    class="badge bg-primary p-2 px-3 text-nowrap d-inline-block">{{ __('Success') }}</span>
                                            @elseif($order->payment_status == 'Pending' || $order->payment_status == 'pending')
                                                <span
                                                    class="badge bg-warning p-2 px-3 text-nowrap d-inline-block">{{ __('Pending') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-danger p-2 px-3 text-nowrap d-inline-block">{{ ucfirst($order->payment_status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ \Auth::user()->dateFormat($order->created_at) }}</td>
                                        <td>{{ !empty($order->total_coupon_used) ? (!empty($order->total_coupon_used->coupon_detail) ? $order->total_coupon_used->coupon_detail->code : '-') : '-' }}
                                        </td>
                                        <td>{{ $order->payment_type }}</td>
                                        <td class="Id text-center">
                                            <a href="javascript:void(0)" data-url="{{ route('plan.order.invoice', $order->id) }}"
                                                data-size="lg" data-ajax-popup="true" data-title="{{ __('Plan Order Invoice') }}"
                                                class="btn btn-sm btn-primary shadow-sm me-1" data-bs-toggle="tooltip" title="{{ __('View & Download Invoice') }}">
                                                <i class="ti ti-file-invoice me-1"></i> {{ __('Invoice') }}
                                            </a>

                                            @if (!empty($order->receipt && !empty($order->payment_type == 'STRIPE')))
                                                <a href="{{ $order->receipt }}" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="{{ __('Show Stripe Invoice') }}"
                                                    target="_blank"><i class="ti ti-external-link"></i></a>
                                            @elseif(!empty($order->receipt && !empty($order->payment_type == 'Bank Transfer')))
                                                <a href="{{ $file . '' . $order->receipt }}"
                                                    class="btn btn-sm btn-outline-secondary" target="_blank" data-bs-toggle="tooltip" title="{{ __('Show Bank Slip') }}"><i
                                                        class="ti ti-paperclip"></i></a>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <div class="dt-buttons">
                                                <span>
                                                    @if (\Auth::user()->type == 'super admin' || \Auth::user()->isSuperAdminSideUser())
                                                        @if ($order->payment_status == 'Pending' && $order->payment_type == 'Bank Transfer')
                                                            <div class="action-btn bg-success me-2">
                                                                <a href="javascript:void(0)"
                                                                    class="mx-3 btn btn-sm  align-items-center"
                                                                    data-size="lg"
                                                                    data-url="{{ URL::to('order/' . $order->id . '/action') }}"
                                                                    data-ajax-popup="true" data-size="md"
                                                                    data-bs-toggle="tooltip" title="{{ __('Manage Order') }}"
                                                                    data-title="{{ __('Order Action') }}"
                                                                    data-bs-original-title="{{ __('Manage Order') }}">
                                                                    <span class="text-white"><i
                                                                            class="ti ti-caret-right "></i></span>
                                                                </a>
                                                            </div>
                                                        @endif
                                                    @elseif(\Auth::user()->type == 'company' && $order->payment_type == 'Bank Transfer')
                                                        <div class="action-btn bg-success ">
                                                            <a href="javascript:void(0)" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="{{ URL::to('order/' . $order->id . '/action') }}"
                                                                data-ajax-popup="true" data-size="md"
                                                                data-bs-toggle="tooltip" title="{{ __('Manage Order') }}"
                                                                data-title="{{ __('Manage Order') }}"
                                                                data-bs-original-title="{{ __('Manage Order') }}">
                                                                <span class="text-white"><i
                                                                        class="ti ti-caret-right"></i></span>
                                                            </a>
                                                        </div>
                                                    @else
                                                        <p>-</p>
                                                    @endif

                                                    @php
                                                        $user = App\Models\User::find($order->user_id);
                                                    @endphp
                                                    @if (\Auth::user()->type == 'super admin' || \Auth::user()->isSuperAdminSideUser())
                                                        <div class="action-btn bg-danger me-2">
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['order.destroy', $order->id],
                                                                'id' => 'delete-form-' . $order->id,
                                                            ]) !!}
                                                            <a href="javascript:void(0)"
                                                            data-bs-trigger="hover"
                                                                class=" btn btn-sm  align-items-center bs-pass-para"
                                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                data-bs-original-title="{{ __('Delete') }}" aria-label="Delete"><span
                                                                    class="text-white"><i
                                                                        class="ti ti-trash"></i></span></a>
                                                            </form>
                                                        </div>

                                                        @foreach ($userOrders as $userOrder)
                                                            @if ($user->plan == $order->plan_id && $order->order_id == $userOrder->order_id && $order->is_refund == 0)
                                                                <div class="badge bg-warning p-2 px-3 ">
                                                                    <a href="{{ route('order.refund', [$order->id, $order->user_id]) }}"
                                                                        class="mx-3 align-items-center"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __('Refund') }}"
                                                                        data-original-title="{{ __('Refund') }}">
                                                                        <span
                                                                            class ="text-white">{{ __('Refund') }}</span>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @endif
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
@endsection
