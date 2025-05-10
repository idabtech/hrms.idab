@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Plan') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Plan') }}</li>
@endsection

@section('action-button')
    @can('Create Plan')
        <a href="javascript:void(0)" data-url="{{ route('plans.create') }}" data-size="md" data-ajax-popup="true"
            data-title="{{ __('Create New Plan') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')

    <div class="row">

        @foreach ($plans as $plan)
            <div class="col-lg-6 col-md-6">
                <div class="card price-card price-1 wow animate__fadeInUp" data-wow-delay="0.2s"
                    style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
                    <div class="card-body " style="height: auto">
                        <span class="price-badge bg-primary">{{ $plan->name }}</span>

                        <div class="d-flex flex-row-reverse m-0 p-0 ">
                            @can('Edit Plan')
                                <div class="action-btn bg-primary ms-2">
                                    <a href="javascript:void(0)" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                        data-ajax-popup="true" data-title="{{ __('Edit Plan') }}"
                                        data-url="{{ route('plans.edit', $plan->id) }}" data-size="lg" data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Edit') }}" data-bs-placement="top"><span
                                            class="text-white"><i class="ti ti-pencil"></i></span></a>
                                </div>
                            @endcan

                            @if (\Auth::user()->type == 'company' && \Auth::user()->plan == $plan->id)
                                <span class="d-flex align-items-center ms-2">
                                    <i class="f-10 lh-1 fas fa-circle text-success"></i>
                                    <span class="ms-2">{{ __('Active') }}</span>
                                </span>
                            @endif
                        </div>
                        @if (isset($plan->price) && isset($plan->discount_price))
                            <span class="mb-4 f-w-600 p-price"
                                style="display: block;
font-size: 16px;
margin: 0px !important;
text-decoration: line-through;">{{ '₹' }}{{ number_format($plan->price) }}<small
                                    class="text-sm">/ {{ $plan->duration }}</small></span>
                        @else
                            <span class="mb-4 f-w-600 p-price">{{ '$' }}{{ $plan->price }}<small
                                    class="text-sm">/ {{ $plan->duration }}</small></span>
                        @endif


                        @if (isset($plan->discount_price))
                            @if ($selected_country == 1)
                                <span class="mb-4 f-w-600 p-price">{{ '₹' }}{{ $plan->discount_price }}<small
                                        class="text-sm">/ {{ $plan->duration }}</small></span>
                            @elseif($selected_country == 2)
                                <span class="mb-4 f-w-600 p-price">{{ '£' }}{{ $plan->discount_price }}<small
                                        class="text-sm">/ {{ $plan->duration }}</small></span>
                            @else
                                <span class="mb-4 f-w-600 p-price">{{ '$' }}{{ $plan->discount_price }}<small
                                        class="text-sm">/ {{ $plan->duration }}</small></span>
                            @endif
                        @endif


                        <div>
                            <ul class="list-unstyled" style="margin-bottom: 8px;">
                                <li>
                                    <span class="theme-avtar">
                                        <i class="text-primary ti ti-circle-plus"></i></span>
                                    {{ $plan->max_users == -1 ? __('Unlimited') : $plan->max_users }} {{ __('Users') }}
                                </li>
                                <li>
                                    <span class="theme-avtar">
                                        <i class="text-primary ti ti-circle-plus"></i></span>
                                    {{ $plan->max_employees == -1 ? __('Unlimited') : $plan->max_employees }}
                                    {{ __('Employees') }}
                                </li>
                                <li>
                                    <div class="accordion price-card " id="accordionPanelsStayOpenExample"
                                        style="margin-top: 0px; margin-right: 8px;">
                                        <div class="list-unstyled ">
                                            <span class="text-primary" style="cursor:pointer;"><i
                                                    class=" theme-avtar text-primary ti ti-circle-plus"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#panelsStayOpen-collapseOne{{ $plan->id }}"
                                                    aria-expanded="true"></i></span>
                                            Read More
                                        </div>

                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div id="panelsStayOpen-collapseOne{{ $plan->id }}" class="accordion-collapse collapse"
                            aria-labelledby="panelsStayOpen-headingOne">
                            <div class="accordion-body">
                                <p class="mb-0">
                                    {!! html_entity_decode($plan->description) !!}
                                </p>
                            </div>
                        </div>

                        <div class="row d-flex justify-content-between">
                            @if (
                                (!empty($admin_payment_setting) &&
                                    ($admin_payment_setting['is_stripe_enabled'] == 'on' ||
                                        $admin_payment_setting['is_paypal_enabled'] == 'on' ||
                                        $admin_payment_setting['is_paystack_enabled'] == 'on' ||
                                        $admin_payment_setting['is_flutterwave_enabled'] == 'on' ||
                                        $admin_payment_setting['is_razorpay_enabled'] == 'on' ||
                                        $admin_payment_setting['is_mercado_enabled'] == 'on' ||
                                        $admin_payment_setting['is_paytm_enabled'] == 'on' ||
                                        $admin_payment_setting['is_mollie_enabled'] == 'on' ||
                                        $admin_payment_setting['is_paypal_enabled'] == 'on' ||
                                        $admin_payment_setting['is_skrill_enabled'] == 'on' ||
                                        $admin_payment_setting['is_coingate_enabled'] == 'on')) ||
                                    (!empty($admin_payment_setting) && $admin_payment_setting['is_paymentwall_enabled'] == 'on'))
                                @can('Buy Plan')
                                    @if ($plan->id != \Auth::user()->plan && \Auth::user()->type != 'super admin')
                                        <div class="col-0">
                                            @if (!$plan->price == 0)
                                                <!--<div class="d-grid text-center">-->
                                                <!--    <a href="{{ route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"-->
                                                <!--        class="btn btn-primary btn-sm d-flex justify-content-center align-items-center"-->
                                                <!--        data-bs-toggle="tooltip" data-bs-placement="top"-->
                                                <!--        data-bs-original-title="{{ __('Subscribe') }}"-->
                                                <!--        title="{{ __('Subscribe') }}">{{ __('Subscribe') }}-->
                                                <!--        <i class="ti ti-arrow-narrow-right ms-1"></i></a>-->
                                                <!--    <p></p>-->
                                                <!--</div>-->
                                            @endif
                                        </div>
                                    @endif
                                @endcan
                            @endif
                            @if (\Auth::user()->type == 'company' && \Auth::user()->plan != $plan->id)
                                @if ($plan->id != 1)
                                    <div class="col-12">
                                        @if (\Auth::user()->requested_plan != $plan->id)
                                            <a href="{{ route('send.request', [\Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                                class="btn btn-primary btn-icon btn-sm"
                                                data-title="{{ __('Send Request') }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" data-bs-original-title="{{ __('Send Request') }}"
                                                title="{{ __('Send Request') }}">{{ __('Send Request') }}
                                                <span class="btn-inner--icon"><i class="ti ti-arrow-forward-up"></i></span>
                                            </a>
                                        @else
                                            <a href="{{ route('request.cancel', \Auth::user()->id) }}"
                                                class="btn btn-danger btn-icon btn-sm"
                                                data-title="{{ __('Cancel Request') }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" data-bs-original-title="{{ __('Cancel Request') }}"
                                                title="{{ __('Cancel Request') }}">{{ __('Cancel Request') }}
                                                <span class="btn-inner--icon"><i class="ti ti-shield-x"></i></span>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            @php
                                $plan_expire_date = \Auth::user()->plan_expire_date;
                            @endphp

                            @if (\Auth::user()->type == 'company' && \Auth::user()->plan == $plan->id)
                                <p class="mb-0">
                                    {{ __('Plan Expired : ') }}
                                    {{ !empty($plan_expire_date) ? \Auth::user()->dateFormat($plan_expire_date) : 'Unlimited' }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
