@php
    $admin_payment_setting = App\Models\Utility::getAdminPaymentSetting();
    $currency_symbol = !empty($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$';
@endphp

<div class="modal-body p-3">
    <div class="card shadow-none border mb-0">
        <div class="card-body table-border-style p-3">
            <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0" id="my-orders-modal-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="py-3">{{ __('Order ID') }}</th>
                            <th class="py-3">{{ __('Addon Title') }}</th>
                            <th class="py-3">{{ __('Storage Amount') }}</th>
                            <th class="py-3">{{ __('Price') }}</th>
                            <th class="py-3">{{ __('Status') }}</th>
                            <th class="py-3">{{ __('Payment Type') }}</th>
                            <th class="py-3">{{ __('Start Date') }}</th>
                            <th class="py-3">{{ __('Expiry Date') }}</th>
                            <th class="py-3 text-center">{{ __('Receipt / Txn ID') }}</th>
                            <th class="py-3 text-center" width="120px">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td class="py-3"><span class="fw-bold text-dark font-monospace">{{ $order->order_id }}</span></td>
                                <td class="py-3 fw-semibold text-secondary">{{ $order->addon_title }}</td>
                                <td class="py-3"><span class="badge bg-info text-white px-3 py-2 rounded-pill">{{ $order->formatted_storage }}</span></td>
                                <td class="py-3 fw-bold text-dark">{{ $currency_symbol }}{{ number_format($order->price, 2) }}</td>
                                <td class="py-3">
                                    @if ($order->payment_status == 'Approved' || $order->payment_status == 'approved' || $order->payment_status == 'succeeded' || $order->payment_status == 'Success' || $order->payment_status == 'success')
                                        @if($order->isExpired())
                                            <span class="badge bg-secondary p-2 px-3 rounded-pill">{{ __('Expired') }}</span>
                                        @else
                                            <span class="badge bg-success p-2 px-3 rounded-pill">{{ __('Active') }}</span>
                                        @endif
                                    @elseif($order->payment_status == 'Pending' || $order->payment_status == 'pending')
                                        <span class="badge bg-warning p-2 px-3 rounded-pill">{{ __('Pending') }}</span>
                                    @else
                                        <span class="badge bg-danger p-2 px-3 rounded-pill">{{ __('Rejected') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-secondary">{{ $order->payment_type }}</td>
                                <td class="py-3 text-muted text-sm"><i class="ti ti-calendar-event me-1 text-primary"></i>{{ $order->start_date_formatted }}</td>
                                <td class="py-3 text-muted text-sm"><i class="ti ti-calendar-due me-1 text-danger"></i>{{ $order->expire_date_formatted }}</td>
                                <td class="py-3 text-center">
                                    @if(($order->payment_type == 'Bank Transfer' || $order->payment_type == 'Manual') && !empty($order->receipt))
                                        <a href="{{ asset($order->receipt) }}" class="btn btn-sm btn-outline-secondary shadow-sm" target="_blank" data-bs-toggle="tooltip" title="{{ __('Show Receipt') }}">
                                            <i class="ti ti-paperclip me-1"></i> {{ __('Slip') }}
                                        </a>
                                    @elseif(!empty($order->receipt))
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ $order->receipt }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    <a href="javascript:void(0)" data-url="{{ route('storage-addons.order.invoice', $order->id) }}"
                                        data-size="lg" data-ajax-popup="true" data-title="{{ __('Storage Addon Invoice') }}"
                                        class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="tooltip" title="{{ __('View & Download Invoice') }}">
                                        <i class="ti ti-file-invoice me-1"></i> {{ __('Invoice') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="ti ti-shopping-cart-x fs-1 d-block mb-3 text-muted"></i>
                                    <h6 class="fw-semibold">{{ __('No Storage Addon Orders Found.') }}</h6>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
