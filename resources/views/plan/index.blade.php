@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Plan') }}
@endsection

@php
    $admin_payment_setting = App\Models\Utility::getAdminPaymentSetting();
@endphp

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Plan') }}</li>
@endsection

@section('action-button')
    @can('Create Plan')
        <a href="javascript:void(0)" data-url="{{ route('plans.create') }}" data-size="lg" data-ajax-popup="true"
            data-title="{{ __('Create New Plan') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan

    @if(\Auth::user()->type === 'company')
        <a href="javascript:void(0)" data-url="{{ route('storage-addons.my-orders') }}" data-size="xl" data-ajax-popup="true"
            data-title="{{ __('My Storage Addon Orders & Requests') }}" data-bs-toggle="tooltip" title="{{ __('My Orders') }}"
            class="btn btn-sm btn-primary d-none" id="my-storage-orders-btn">
            <i class="ti ti-shopping-cart me-1"></i> {{ __('My Orders') }}
        </a>
    @endif
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-inline-flex bg-white p-1 rounded border shadow-sm">
                <ul class="nav nav-pills" id="planTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold py-2 px-3" id="plans-tab" data-bs-toggle="tab" data-bs-target="#plans-content" type="button" role="tab" aria-controls="plans-content" aria-selected="true" style="border-radius: 6px;">
                            <i class="ti ti-trophy me-1"></i>{{ __('Subscription Plans') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-2 px-3" id="storage-addons-tab" data-bs-toggle="tab" data-bs-target="#storage-addons-content" type="button" role="tab" aria-controls="storage-addons-content" aria-selected="false" style="border-radius: 6px;">
                            <i class="ti ti-database me-1"></i>{{ __('Storage Addons') }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="tab-content" id="planTabContent">
        {{-- Subscription Plans Tab --}}
        <div class="tab-pane fade show active" id="plans-content" role="tabpanel" aria-labelledby="plans-tab">
            <div class="row">
                @foreach ($plans as $plan)
                    <div class="col-lg-3 col-md-4 mb-4">
                        <div class="card price-card price-1 h-100 wow animate__fadeInUp" data-wow-delay="0.2s"
                            style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <span class="price-badge bg-primary">{{ $plan->name }}</span>

                                    <div class="d-flex dt-buttons flex-row-reverse m-0 p-0">
                                        @if (\Auth::user()->type == 'super admin' && $plan->price > 0)
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['plans.destroy', $plan->id],
                                                    'id' => 'delete-form-' . $plan->id,
                                                ]) !!}
                                                <a href="javascript:void(0)" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                    aria-label="Delete"><span class="text-white"><i class="ti ti-trash"></i></span></a>
                                                </form>
                                            </div>
                                        @endif

                                        @if (\Auth::user()->type == 'super admin')
                                            @can('Edit Plan')
                                                <div class="action-btn bg-info me-1">
                                                    <a href="javascript:void(0)" class="btn btn-sm d-inline-flex align-items-center"
                                                        data-ajax-popup="true" data-title="{{ __('Edit Plan') }}"
                                                        data-url="{{ route('plans.edit', $plan->id) }}" data-size="lg" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Edit') }}" data-bs-placement="top"><span
                                                            class="text-white"><i class="ti ti-pencil"></i></span></a>
                                                </div>
                                            @endcan
                                        @endif

                                        @if (\Auth::user()->type == 'super admin' && $plan->price > 0)
                                            <div class="me-1 mt-1 justify-content-center active-tag">
                                                <div class="form-check form-switch custom-switch-v1 float-end">
                                                    <input type="checkbox" name="plan_disable" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Enable / Disable') }}"
                                                        class="form-check-input input-primary is_disable" value="1"
                                                        data-id='{{ $plan->id }}' data-name="{{ __('plan') }}"
                                                        {{ $plan->is_disable == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="plan_disable"></label>
                                                </div>
                                            </div>
                                        @endif

                                        @if (\Auth::user()->type == 'company' && \Auth::user()->plan == $plan->id)
                                            <span class="d-flex align-items-center ms-2">
                                                <i class="f-10 lh-1 fas fa-circle text-success"></i>
                                                <span class="ms-2">{{ __('Active') }}</span>
                                            </span>
                                        @endif
                                    </div>

                                    <span class="mb-4 f-w-600 p-price">{{ !empty($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ $plan->price }}<small class="text-sm">/ {{ $plan->duration }}</small></span><br>

                                    @if ($plan->price > 0)
                                        <span>{{ __('Free Trial Days :') }}
                                            <b>{{ !empty($plan->trial_days) ? $plan->trial_days : 0 }}</b>
                                        </span>
                                    @endif
                                    <p class="mb-0">
                                        {{ $plan->description }}
                                    </p>

                                    <ul class="list-unstyled my-4">
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
                                            <span class="theme-avtar">
                                                <i class="text-primary ti ti-circle-plus"></i></span>
                                            {{ $plan->storage_limit == -1 ? __('Lifetime') : $plan->storage_limit }}
                                            {{ __('MB Storage') }}
                                        </li>
                                        <li>
                                            <span class="theme-avtar">
                                                <i class="text-primary ti ti-circle-plus"></i></span>
                                            {{ $plan->enable_chatgpt == 'on' ? __('Enable Chat GPT') : __('Disable Chat GPT') }}
                                        </li>
                                    </ul>
                                </div>

                                <div>
                                    <div class="col-md-12 text-center mt-3">
                                        @can('Buy Plan')
                                            @if ($plan->id != \Auth::user()->plan && \Auth::user()->type != 'super admin')
                                                @if (!$plan->price == 0)
                                                    @if ($plan->price > 0 && \Auth::user()->trial_plan == 0 && \Auth::user()->plan != $plan->id && $plan->trial == 1)
                                                        <a href="{{ route('plans.trial', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                            class="btn btn-lg btn-primary m-1">
                                                            {{ __('Start Free Trial') }}
                                                        </a>
                                                    @endif
                                                    @if ($plan->price > 0)
                                                        <a href="{{ route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                                            class="btn btn-lg btn-primary m-1">
                                                            {{ __('Subscribe') }}
                                                        </a>
                                                    @endif
                                                @endif
                                            @endif
                                        @endcan
                                        @if (\Auth::user()->type == 'company' && \Auth::user()->plan != $plan->id)
                                            @if ($plan->id != 1)
                                                @if (\Auth::user()->requested_plan != $plan->id)
                                                <a href="{{ route('send.request', [\Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                                    class="btn btn-lg btn-primary btn-icon m-1"
                                                    data-title="{{ __('Send Request') }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" data-bs-original-title="{{ __('Send Request') }}"
                                                    title="{{ __('Send Request') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-arrow-forward-up"></i></span>
                                                </a>
                                                @else
                                                <a href="{{ route('request.cancel', \Auth::user()->id) }}"
                                                    class="btn btn-lg btn-danger btn-icon m-1"
                                                    data-title="{{ __('Cancel Request') }}" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" data-bs-original-title="{{ __('Cancel Request') }}"
                                                    title="{{ __('Cancel Request') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-shield-x"></i></span>
                                                </a>
                                                @endif
                                            @endif
                                        @endif
                                    </div>

                                    @if (\Auth::user()->type == 'company' && \Auth::user()->trial_expire_date)
                                        @if (\Auth::user()->type == 'company' && \Auth::user()->trial_plan == $plan->id)
                                            <p class="display-total-time text-dark mb-0">
                                                {{ __('Plan Trial Expired : ') }}
                                                {{ !empty(\Auth::user()->trial_expire_date) ? \Auth::user()->dateFormat(\Auth::user()->trial_expire_date) : 'lifetime' }}
                                            </p>
                                        @endif
                                    @else
                                        @if (\Auth::user()->type == 'company' && \Auth::user()->plan == $plan->id)
                                            <p class="display-total-time text-dark mb-0">
                                                {{ __('Plan Expired : ') }}
                                                {{ !empty(\Auth::user()->plan_expire_date) ? \Auth::user()->dateFormat(\Auth::user()->plan_expire_date) : 'lifetime' }}
                                            </p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Storage Addons Tab --}}
        <div class="tab-pane fade" id="storage-addons-content" role="tabpanel" aria-labelledby="storage-addons-tab">
            <div class="row">
                @forelse ($storageAddons as $addon)
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card price-card text-center shadow-sm h-100 border">
                            <div class="card-body d-flex flex-column justify-content-between p-4">
                                <div>
                                    <span class="price-badge bg-primary mb-3 d-inline-block px-3 py-1 fs-6">{{ $addon->title }}</span>
                                    <div class="my-3">
                                        <span class="p-price fw-bold text-primary fs-3">
                                            {{ !empty($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '$' }}{{ number_format($addon->price, 2) }}
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="badge bg-info text-white p-2 fs-6">
                                            <i class="ti ti-database me-1"></i> {{ $addon->formatted_storage }}
                                        </span>
                                    </div>
                                    <div class="text-muted text-sm mb-3">
                                        @if($addon->addon_type === 'plan_expiry')
                                            <span class="d-block text-primary fw-semibold"><i class="ti ti-calendar-event me-1"></i>{{ __('Co-terminus with Active Plan Expiry Date') }}</span>
                                        @else
                                            <span class="d-block text-secondary fw-semibold"><i class="ti ti-clock me-1"></i>{{ __('Valid for:') }} {{ $addon->formatted_duration }}</span>
                                        @endif
                                    </div>
                                    @if(!empty($addon->description))
                                        <p class="text-muted small mb-3">{{ $addon->description }}</p>
                                    @endif
                                </div>
                                <div class="mt-3">
                                    @if(\Auth::user()->type == 'company')
                                        <a href="{{ route('storage-addons.pay', \Illuminate\Support\Facades\Crypt::encrypt($addon->id)) }}"
                                            class="btn btn-primary w-100">
                                            <i class="ti ti-shopping-cart me-1"></i>{{ __('Buy Addon') }}
                                        </a>
                                    @elseif(\Auth::user()->type == 'super admin')
                                        <a href="{{ route('storage-addons.index') }}" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="ti ti-settings me-1"></i>{{ __('Manage Addons') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="text-muted">
                            <i class="ti ti-database-off fs-1 d-block mb-2"></i>
                            <h5>{{ __('No Storage Addons Available') }}</h5>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function toggleMyOrdersBtn() {
                if ($('#storage-addons-tab').hasClass('active')) {
                    $('#my-storage-orders-btn').removeClass('d-none');
                } else {
                    $('#my-storage-orders-btn').addClass('d-none');
                }
            }

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab') === 'storage_addons' || window.location.hash === '#storage_addons' || window.location.hash === '#storage-addons-tab') {
                var storageTabBtn = document.getElementById('storage-addons-tab');
                if (storageTabBtn) {
                    var tab = new bootstrap.Tab(storageTabBtn);
                    tab.show();
                }
            }

            // Check initial state
            toggleMyOrdersBtn();

            // Check when tab switches
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                toggleMyOrdersBtn();
            });
        });
    </script>

    <script>
        $(document).on('change', '#trial', function() {
            if ($(this).is(':checked')) {
                $('.plan_div').removeClass('d-none');
                $('#trial').attr("required", true);
                $('#trial_days').attr("required", true);

            } else {
                $('.plan_div').addClass('d-none');
                $('#trial').removeAttr("required");
                $('#trial_days').removeAttr("required");
            }
        });
    </script>

    <script>
        $(document).on("click", ".is_disable", function() {

            var id = $(this).attr('data-id');
            var is_disable = ($(this).is(':checked')) ? $(this).val() : 0;

            $.ajax({
                url: '{{ route('plan.disable') }}',
                type: 'POST',
                data: {
                    "is_disable": is_disable,
                    "id": id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    if (data.success) {
                        show_toastr('success', data.success);
                    } else {
                        show_toastr('error', data.error);

                    }

                }
            });
        });
    </script>
@endpush
