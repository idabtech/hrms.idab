@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Order Summary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('plans.index', ['tab' => 'storage_addons']) }}">{{ __('Plans') }}</a></li>
    <li class="breadcrumb-item">{{ __('Order Summary') }}</li>
@endsection

@push('scripts')
    @if(isset($admin_payment_setting['is_stripe_enabled']) && $admin_payment_setting['is_stripe_enabled'] == 'on')
        <script src="https://js.stripe.com/v3/"></script>
    @endif
    @if(isset($admin_payment_setting['is_razorpay_enabled']) && $admin_payment_setting['is_razorpay_enabled'] == 'on')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @endif

    <script>
        $(document).ready(function () {
            // Activate first available tab automatically
            var firstTab = $('#useradd-sidenav a:first');
            if (firstTab.length > 0) {
                firstTab.addClass('active');
                var target = firstTab.attr('href');
                $(target).addClass('show active');
            }

            $(document).on('click', '#useradd-sidenav a', function (e) {
                e.preventDefault();
                $('#useradd-sidenav a').removeClass('active');
                $(this).addClass('active');
                var target = $(this).attr('href');
                $('.tab-pane').removeClass('show active');
                $(target).addClass('show active');
            });

            // Razorpay Payment Handler
            $(document).on("click", "#pay_with_razorpay", function(e) {
                e.preventDefault();
                var form = $('#razorpay-addon-payment-form');
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if (res.flag == 1) {
                            var razorPay_callback = '{{ url('/storage-addon/razorpay') }}';
                            var totalAmount = res.total_price * 100;
                            var options = {
                                "key": "{{ $admin_payment_setting['razorpay_public_key'] ?? '' }}",
                                "amount": totalAmount,
                                "name": "{{ $addon->title }}",
                                "currency": res.currency,
                                "description": "Storage Addon Purchase",
                                "handler": function(response) {
                                    window.location.href = razorPay_callback + '/' + response.razorpay_payment_id + '/{{ \Illuminate\Support\Facades\Crypt::encrypt($addon->id) }}';
                                },
                                "theme": {
                                    "color": "#528FF0"
                                }
                            };
                            var rzp1 = new Razorpay(options);
                            rzp1.open();
                        } else if (res.flag == 2) {
                            show_toastr('success', res.msg, 'success');
                            setTimeout(function() {
                                window.location.href = "{{ route('plans.index', ['tab' => 'storage_addons']) }}";
                            }, 1500);
                        } else {
                            show_toastr('Error', res.msg, 'error');
                        }
                    },
                    error: function(err) {
                        show_toastr('Error', 'Payment initialization failed', 'error');
                    }
                });
            });
        });
    </script>
@endpush

@php
    $currency_symbol = !empty($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$';
@endphp

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                {{-- Left Side: Addon Card & Payment Sidenav --}}
                <div class="col-xl-3">
                    <div class="sticky-top">
                        {{-- Addon Summary Card --}}
                        <div class="card price-card price-1 wow animate__fadeInUp" data-wow-delay="0.2s" style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
                            <div class="card-body">
                                <span class="price-badge bg-primary">{{ $addon->title }}</span>

                                <span class="mb-4 f-w-600 p-price">{{ $currency_symbol }}{{ number_format($addon->price, 2) }}</span>

                                <p class="mb-0">
                                    {{ $addon->title }}
                                </p>
                                @if(!empty($addon->description))
                                    <p class="mb-0 text-muted text-sm">
                                        {{ $addon->description }}
                                    </p>
                                @endif

                                <ul class="list-unstyled my-4">
                                    <li>
                                        <span class="theme-avtar">
                                            <i class="text-primary ti ti-database"></i>
                                        </span>
                                        {{ $addon->formatted_storage }}
                                    </li>
                                    <li>
                                        <span class="theme-avtar">
                                            <i class="text-primary ti ti-clock"></i>
                                        </span>
                                        @if($addon->addon_type === 'plan_expiry')
                                            {{ __('Co-terminus with Active Plan Expiry') }}
                                        @else
                                            {{ __('Valid for:') }} {{ $addon->formatted_duration }}
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Payment Gateways Sidenav --}}
                        <div class="card">
                            <div class="list-group list-group-flush" id="useradd-sidenav">
                                @if(isset($admin_payment_setting['is_stripe_enabled']) && $admin_payment_setting['is_stripe_enabled'] == 'on' && !empty($admin_payment_setting['stripe_key']))
                                    <a class="list-group-item list-group-item-action border-0" href="#stripe-payment" role="tab">
                                        {{ __('Stripe') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_razorpay_enabled']) && $admin_payment_setting['is_razorpay_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#razorpay-payment" role="tab">
                                        {{ __('Razorpay') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_banktransfer_enabled']) && $admin_payment_setting['is_banktransfer_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#banktransfer-payment" role="tab">
                                        {{ __('Bank Transfer') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_manually_enabled']) && $admin_payment_setting['is_manually_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#manually-payment" role="tab">
                                        {{ __('Manually') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_paypal_enabled']) && $admin_payment_setting['is_paypal_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#paypal-payment" role="tab">
                                        {{ __('Paypal') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_paystack_enabled']) && $admin_payment_setting['is_paystack_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#paystack-payment" role="tab">
                                        {{ __('Paystack') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_flutterwave_enabled']) && $admin_payment_setting['is_flutterwave_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#flutterwave-payment" role="tab">
                                        {{ __('Flutterwave') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_paytm_enabled']) && $admin_payment_setting['is_paytm_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#paytm-payment" role="tab">
                                        {{ __('Paytm') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_mercado_enabled']) && $admin_payment_setting['is_mercado_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#mercado-payment" role="tab">
                                        {{ __('Mercado Pago') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_mollie_enabled']) && $admin_payment_setting['is_mollie_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#mollie-payment" role="tab">
                                        {{ __('Mollie') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_skrill_enabled']) && $admin_payment_setting['is_skrill_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#skrill-payment" role="tab">
                                        {{ __('Skrill') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_coingate_enabled']) && $admin_payment_setting['is_coingate_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#coingate-payment" role="tab">
                                        {{ __('Coingate') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_paymentwall_enabled']) && $admin_payment_setting['is_paymentwall_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#paymentwall-payment" role="tab">
                                        {{ __('Paymentwall') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif

                                @if(isset($admin_payment_setting['is_payfast_enabled']) && $admin_payment_setting['is_payfast_enabled'] == 'on')
                                    <a class="list-group-item list-group-item-action border-0" href="#payfast-payment" role="tab">
                                        {{ __('Payfast') }}
                                        <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Payment Forms --}}
                <div class="col-xl-9">
                    <div class="tab-content" id="payment-tab-content">
                        {{-- Stripe Pane --}}
                        @if(isset($admin_payment_setting['is_stripe_enabled']) && $admin_payment_setting['is_stripe_enabled'] == 'on' && !empty($admin_payment_setting['stripe_key']))
                            <div class="tab-pane fade" id="stripe-payment" role="tabpanel">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>{{ __('Pay Using Stripe') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        {{ Form::open(['route' => ['storage-addons.process-payment', \Illuminate\Support\Facades\Crypt::encrypt($addon->id)], 'method' => 'POST', 'id' => 'stripe-addon-payment-form']) }}
                                            <input type="hidden" name="payment_type" value="Stripe">

                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">{{ __('Card Details') }}</label>
                                                    <div id="stripe-card-element" class="form-control p-3"></div>
                                                    <div id="stripe-card-errors" class="text-danger small mt-2" role="alert"></div>
                                                </div>
                                                <div class="col-12 text-end">
                                                    <button type="submit" class="btn btn-primary" id="stripe-pay-btn">
                                                        {{ __('Pay Now') }}
                                                    </button>
                                                </div>
                                            </div>
                                        {{ Form::close() }}
                                    </div>
                                </div>
                            </div>

                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    var stripeKey = "{{ $admin_payment_setting['stripe_key'] }}";
                                    if (stripeKey && typeof Stripe !== 'undefined') {
                                        var stripe = Stripe(stripeKey);
                                        var elements = stripe.elements();
                                        var card = elements.create('card', { style: { base: { fontSize: '15px' } } });
                                        card.mount('#stripe-card-element');

                                        var form = document.getElementById('stripe-addon-payment-form');
                                        if (form) {
                                            form.addEventListener('submit', function(event) {
                                                event.preventDefault();
                                                stripe.createToken(card).then(function(result) {
                                                    if (result.error) {
                                                        document.getElementById('stripe-card-errors').textContent = result.error.message;
                                                    } else {
                                                        var hiddenInput = document.createElement('input');
                                                        hiddenInput.setAttribute('type', 'hidden');
                                                        hiddenInput.setAttribute('name', 'stripeToken');
                                                        hiddenInput.setAttribute('value', result.token.id);
                                                        form.appendChild(hiddenInput);
                                                        form.submit();
                                                    }
                                                });
                                            });
                                        }
                                    }
                                });
                            </script>
                        @endif

                        {{-- Razorpay Pane --}}
                        @if(isset($admin_payment_setting['is_razorpay_enabled']) && $admin_payment_setting['is_razorpay_enabled'] == 'on')
                            <div class="tab-pane fade" id="razorpay-payment" role="tabpanel">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>{{ __('Pay Using Razorpay') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        {{ Form::open(['route' => 'storage-addon.pay.with.razorpay', 'method' => 'POST', 'id' => 'razorpay-addon-payment-form']) }}
                                            <input type="hidden" name="addon_id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($addon->id) }}">
                                            <div class="col-12 text-end">
                                                <button type="button" id="pay_with_razorpay" class="btn btn-primary">
                                                    {{ __('Pay Now') }}
                                                </button>
                                            </div>
                                        {{ Form::close() }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Bank Transfer Pane --}}
                        @if(isset($admin_payment_setting['is_banktransfer_enabled']) && $admin_payment_setting['is_banktransfer_enabled'] == 'on')
                            <div class="tab-pane fade" id="banktransfer-payment" role="tabpanel">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>{{ __('Pay Using Bank Transfer') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        {{ Form::open(['route' => ['storage-addons.process-payment', \Illuminate\Support\Facades\Crypt::encrypt($addon->id)], 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                                            <input type="hidden" name="payment_type" value="Bank Transfer">

                                            @if(!empty($admin_payment_setting['bank_details']))
                                                <div class="card bg-light border p-3 mb-3">
                                                    <h6 class="fw-bold text-dark mb-1">{{ __('Bank Account Details') }}</h6>
                                                    <div class="text-sm text-secondary">
                                                        {!! nl2br(e($admin_payment_setting['bank_details'])) !!}
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mb-4">
                                                {{ Form::label('receipt', __('Upload Payment Receipt / Proof'), ['class' => 'form-label fw-bold']) }}
                                                {{ Form::file('receipt', ['class' => 'form-control p-2', 'accept' => 'image/*,.pdf']) }}
                                                <small class="text-muted d-block mt-1">{{ __('Upload image or PDF proof of payment. Super Admin will review and grant storage.') }}</small>
                                            </div>

                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary">
                                                    {{ __('Pay Now') }}
                                                </button>
                                            </div>
                                        {{ Form::close() }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Manual Pane --}}
                        @if(isset($admin_payment_setting['is_manually_enabled']) && $admin_payment_setting['is_manually_enabled'] == 'on')
                            <div class="tab-pane fade" id="manually-payment" role="tabpanel">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>{{ __('Pay Manually') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        {{ Form::open(['route' => ['storage-addons.process-payment', \Illuminate\Support\Facades\Crypt::encrypt($addon->id)], 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                                            <input type="hidden" name="payment_type" value="Manual">

                                            <div class="mb-4">
                                                {{ Form::label('receipt', __('Upload Payment Receipt / Proof'), ['class' => 'form-label fw-bold']) }}
                                                {{ Form::file('receipt', ['class' => 'form-control p-2', 'accept' => 'image/*,.pdf']) }}
                                            </div>

                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary">
                                                    {{ __('Pay Now') }}
                                                </button>
                                            </div>
                                        {{ Form::close() }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Other Generic Gateways --}}
                        @php
                            $genericGateways = [
                                'paypal'      => 'Paypal',
                                'paystack'    => 'Paystack',
                                'flutterwave' => 'Flutterwave',
                                'paytm'       => 'Paytm',
                                'mercado'     => 'Mercado Pago',
                                'mollie'      => 'Mollie',
                                'skrill'      => 'Skrill',
                                'coingate'    => 'Coingate',
                                'paymentwall' => 'Paymentwall',
                                'payfast'     => 'Payfast',
                            ];
                        @endphp

                        @foreach($genericGateways as $gwKey => $gwTitle)
                            @if(isset($admin_payment_setting['is_' . $gwKey . '_enabled']) && $admin_payment_setting['is_' . $gwKey . '_enabled'] == 'on')
                                <div class="tab-pane fade" id="{{ $gwKey }}-payment" role="tabpanel">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>{{ __('Pay Using') }} {{ $gwTitle }}</h5>
                                        </div>
                                        <div class="card-body">
                                            {{ Form::open(['route' => ['storage-addons.process-payment', \Illuminate\Support\Facades\Crypt::encrypt($addon->id)], 'method' => 'POST']) }}
                                                <input type="hidden" name="payment_type" value="{{ $gwTitle }}">

                                                <div class="alert alert-light border mb-4">
                                                    <p class="mb-0 text-muted">{{ __('Click Pay Now to complete payment via') }} <strong>{{ $gwTitle }}</strong>.</p>
                                                </div>

                                                <div class="col-12 text-end">
                                                    <button type="submit" class="btn btn-primary">
                                                        {{ __('Pay Now') }}
                                                    </button>
                                                </div>
                                            {{ Form::close() }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
