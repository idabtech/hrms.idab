@php
    $admin_payment_setting = App\Models\Utility::getAdminPaymentSetting();
    $currency_symbol = !empty($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$';
    
    // Super Admin / Platform Settings & Logo
    $superAdmin = \App\Models\User::where('type', 'super admin')->first();
    $creatorId  = $superAdmin ? $superAdmin->id : 1;
    $settings   = \App\Models\Utility::getCompanySettings($creatorId);

    $company_name      = !empty($settings['company_name']) ? $settings['company_name'] : (env('APP_NAME') ?: 'iDAB TECH');
    $company_email     = !empty($settings['company_email']) ? $settings['company_email'] : '';
    $company_telephone = !empty($settings['company_telephone']) ? $settings['company_telephone'] : '';
    $company_address   = !empty($settings['company_address']) ? $settings['company_address'] : '';
    
    $logo_dir = \App\Models\Utility::get_file('uploads/logo/');
    $logo_name = !empty($settings['company_logo']) ? $settings['company_logo'] : 'logo-dark.png';
    $logo_path = $logo_dir . (str_ends_with($logo_dir, '/') ? '' : '/') . $logo_name;

    $isPrintMode = request()->has('print') || request()->ajax() == false && !request()->header('X-Requested-With');
    $user = \App\Models\User::find($order->user_id);
    $plan = \App\Models\Plan::find($order->plan_id);
@endphp

@if($isPrintMode && !request()->ajax())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Subscription Invoice') }} #INV-PLAN-{{ $order->id }} - {{ $company_name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons/tabler-icons.min.css') }}">
    <style>
        body {
            background-color: #f8fafc !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
            padding: 20px;
        }
        .invoice-card {
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            max-width: 900px;
            margin: 0 auto;
        }
        .invoice-header-box {
            padding: 28px 32px;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            border-radius: 12px 12px 0 0;
        }
        .invoice-logo-img {
            max-height: 55px;
            max-width: 220px;
            object-fit: contain;
        }
        .invoice-status-pill {
            font-size: 0.85rem;
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
        }
        .invoice-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            padding: 12px 16px;
            border-bottom: 2px solid #cbd5e1;
        }
        .invoice-table td {
            padding: 16px;
            vertical-align: middle;
        }
        .invoice-summary-card {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 20px 24px;
            border: 1px solid #e2e8f0;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-card {
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
            }
            @page {
                size: auto;
                margin: 12mm;
            }
        }
    </style>
</head>
<body>
@else
<style>
    .invoice-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: #1e293b;
    }
    .invoice-card {
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
    }
    .invoice-header-box {
        padding: 28px 32px;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        border-radius: 12px 12px 0 0;
    }
    .invoice-logo-img {
        max-height: 55px;
        max-width: 220px;
        object-fit: contain;
    }
    .invoice-status-pill {
        font-size: 0.85rem;
        padding: 6px 16px;
        border-radius: 50px;
        font-weight: 700;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
    }
    .invoice-table th {
        background-color: #f1f5f9;
        color: #334155;
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        padding: 12px 16px;
        border-bottom: 2px solid #cbd5e1;
    }
    .invoice-table td {
        padding: 16px;
        vertical-align: middle;
    }
    .invoice-summary-card {
        background-color: #f8fafc;
        border-radius: 10px;
        padding: 20px 24px;
        border: 1px solid #e2e8f0;
    }
</style>
@endif

<div class="modal-body p-0 invoice-container">
    {{-- Top Action Toolbar --}}
    <div class="d-flex align-items-center justify-content-between px-4 py-3 bg-light border-bottom no-print">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-dark"><i class="ti ti-receipt me-1 text-primary fs-4"></i> {{ __('Plan Invoice') }} #INV-PLAN-{{ $order->id }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(!empty($order->receipt && !empty($order->payment_type == 'Bank Transfer')))
                <a href="{{ \App\Models\Utility::get_file('uploads/order/') . $order->receipt }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-paperclip me-1"></i> {{ __('View Payment Slip') }}
                </a>
            @endif
            <a href="{{ route('plan.order.invoice', $order->id) }}?print=1" target="_blank" class="btn btn-sm btn-primary shadow-sm" onclick="printPlanInvoice(event, this)">
                <i class="ti ti-printer me-1"></i> {{ __('Print / Download PDF') }}
            </a>
        </div>
    </div>

    {{-- Printable Invoice Body --}}
    <div class="p-4" id="printableInvoice">
        <div class="card invoice-card shadow-none mb-0">
            {{-- Clean Header with Logo and Invoice Title --}}
            <div class="invoice-header-box d-flex flex-wrap align-items-center justify-content-between gap-3">
                {{-- Super Admin / Company Logo & Name --}}
                <div>
                    @if(!empty($logo_path))
                        <img src="{{ $logo_path }}" alt="{{ $company_name }}" class="invoice-logo-img mb-2 d-block" onerror="this.style.display='none'">
                    @endif
                    <h4 class="fw-bold text-dark mb-0">{{ $company_name }}</h4>
                </div>

                {{-- Invoice Info & Status --}}
                <div class="text-end">
                    <h2 class="fw-bold text-primary mb-2 text-uppercase tracking-wide">{{ __('INVOICE') }}</h2>
                    <div class="mb-2">
                        @if ($order->payment_status == 'Approved' || $order->payment_status == 'approved' || $order->payment_status == 'succeeded' || $order->payment_status == 'Success' || $order->payment_status == 'success')
                            <span class="badge bg-success text-white invoice-status-pill shadow-sm"><i class="ti ti-circle-check me-1"></i> {{ __('PAID') }}</span>
                        @elseif($order->payment_status == 'Pending' || $order->payment_status == 'pending')
                            <span class="badge bg-warning text-dark invoice-status-pill shadow-sm"><i class="ti ti-clock me-1"></i> {{ __('PENDING') }}</span>
                        @else
                            <span class="badge bg-danger text-white invoice-status-pill shadow-sm"><i class="ti ti-circle-x me-1"></i> {{ ucfirst($order->payment_status) }}</span>
                        @endif
                    </div>
                    <div class="text-muted small">{{ __('Invoice No:') }} <strong class="text-dark">INV-PLAN-{{ $order->id }}</strong></div>
                    <div class="text-muted small">{{ __('Date:') }} <strong class="text-dark">{{ \Auth::user() ? \Auth::user()->dateFormat($order->created_at) : date('d-m-Y', strtotime($order->created_at)) }}</strong></div>
                </div>
            </div>

            <div class="card-body p-4">
                {{-- Billed From & Billed To Section --}}
                <div class="row mb-4 pb-3 border-bottom">
                    {{-- Billed From --}}
                    <div class="col-md-6 mb-3 mb-md-0">
                        <span class="text-uppercase text-muted fw-bold small d-block mb-2 tracking-wider">{{ __('BILLED FROM (PLATFORM)') }}</span>
                        <h6 class="fw-bold mb-1 text-dark fs-6">{{ $company_name }}</h6>
                        @if(!empty($company_email))
                            <div class="text-muted small mb-1"><i class="ti ti-mail me-1 text-primary"></i> {{ $company_email }}</div>
                        @endif
                        @if(!empty($company_telephone))
                            <div class="text-muted small mb-1"><i class="ti ti-phone me-1 text-primary"></i> {{ $company_telephone }}</div>
                        @endif
                        @if(!empty($company_address))
                            <div class="text-muted small"><i class="ti ti-map-pin me-1 text-primary"></i> {{ $company_address }}</div>
                        @endif
                    </div>

                    {{-- Billed To --}}
                    <div class="col-md-6 text-md-end">
                        <span class="text-uppercase text-muted fw-bold small d-block mb-2 tracking-wider">{{ __('BILLED TO (CUSTOMER)') }}</span>
                        <h6 class="fw-bold mb-1 text-dark fs-6">{{ $user ? $user->name : ($order->user_name ?? __('Company Customer')) }}</h6>
                        @if($user && !empty($user->email))
                            <div class="text-muted small mb-1"><i class="ti ti-mail me-1 text-primary"></i> {{ $user->email }}</div>
                        @endif
                        @if($user && !empty($user->phone))
                            <div class="text-muted small mb-1"><i class="ti ti-phone me-1 text-primary"></i> {{ $user->phone }}</div>
                        @endif
                        @if($user && !empty($user->address))
                            <div class="text-muted small"><i class="ti ti-map-pin me-1 text-primary"></i> {{ $user->address }}</div>
                        @endif
                    </div>
                </div>

                {{-- Order Line Items Table --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered invoice-table align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Description / Plan Name') }}</th>
                                <th class="text-center">{{ __('Billing Cycle') }}</th>
                                <th class="text-center">{{ __('Payment Gateway') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark d-block fs-6 mb-1">{{ $order->plan_name }}</span>
                                    <span class="text-muted small font-monospace"><i class="ti ti-hash me-1"></i>{{ $order->order_id }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary text-white px-3 py-2 rounded-pill fs-7 fw-semibold">{{ $plan ? ucfirst($plan->duration ?? 'Subscription') : 'Subscription' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-semibold text-secondary">{{ $order->payment_type }}</span>
                                </td>
                                <td class="text-end fw-bold text-dark fs-6">
                                    {{ $currency_symbol }}{{ number_format($order->price, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Total Calculation & Notes --}}
                <div class="row align-items-center">
                    <div class="col-md-7 mb-3 mb-md-0">
                        <div class="p-3 bg-light rounded border text-muted small">
                            <h6 class="fw-bold text-dark mb-1"><i class="ti ti-info-circle me-1 text-primary"></i> {{ __('Payment Note') }}</h6>
                            <p class="mb-0">{{ __('Thank you for subscribing. Your plan features and employee quota are active upon payment confirmation.') }}</p>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="invoice-summary-card">
                            <div class="d-flex justify-content-between mb-2 text-sm text-muted">
                                <span>{{ __('Subtotal') }}:</span>
                                <span class="fw-semibold text-dark">{{ $currency_symbol }}{{ number_format($order->price, 2) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-dark fs-6">{{ __('Total Paid') }}:</span>
                                <span class="fw-bold text-primary fs-4">{{ $currency_symbol }}{{ number_format($order->price, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Note --}}
            <div class="card-footer bg-light text-center py-3 rounded-bottom">
                <span class="text-muted small">{{ __('Computer generated invoice. No signature required.') }} &bull; {{ $company_name }}</span>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function printPlanInvoice(e, el) {
        if (e && e.preventDefault) {
            e.preventDefault();
        }
        var printUrl = "{{ route('plan.order.invoice', $order->id) }}?print=1";
        var win = window.open(printUrl, '_blank');
        if (win) {
            win.focus();
        } else {
            window.location.href = printUrl;
        }
    }
</script>

@if($isPrintMode && !request()->ajax())
<script type="text/javascript">
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 300);
    };
</script>
</body>
</html>
@endif
