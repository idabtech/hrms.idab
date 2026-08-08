@php
 $logo = \App\Models\Utility::get_file('uploads/logo/');
$setting = App\Models\Utility::colorset();
$color = !empty($setting['theme_color']) ? $setting['theme_color'] : 'theme-2';
$SITE_RTL = \App\Models\Utility::getValByName('SITE_RTL');
$company_logo_light = \App\Models\Utility::getValByName('company_logo_light');
$company_favicon = \App\Models\Utility::getValByName('company_favicon');

$getseo = App\Models\Utility::getSeoSetting();
$metatitle = isset($getseo['meta_title']) ? $getseo['meta_title'] : '';
$metadesc = isset($getseo['meta_description']) ? $getseo['meta_description'] : '';
$meta_image = \App\Models\Utility::get_file('uploads/meta/');
$meta_logo = isset($getseo['meta_image']) ? $getseo['meta_image'] : '';
$enable_cookie = \App\Models\Utility::getCookieSetting('enable_cookie');

if (isset($setting['color_flag']) && $setting['color_flag'] == 'true') {
$themeColor = 'custom-color';
} else {
$themeColor = $color;
}

@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $SITE_RTL == 'on' ? 'rtl' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        {{ !empty($companySettings['title_text']) ? $companySettings['title_text']->value : config('app.name', 'HRMGo SaaS') }}
        - {{ __('Career') }}
    </title>

    <!-- SEO META -->
    <meta name="title" content="{{ $metatitle }}">
    <meta name="description" content="{{ $metadesc }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="{{ $metatitle }}">
    <meta property="og:description" content="{{ $metadesc }}">
    <meta property="og:image"
        content="{{ isset($meta_logo) && !empty(asset('storage/uploads/meta/' . $meta_logo)) ? asset('storage/uploads/meta/' . $meta_logo) : 'hrmgo.png' }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title" content="{{ $metatitle }}">
    <meta property="twitter:description" content="{{ $metadesc }}">
    <meta property="twitter:image"
        content="{{ isset($meta_logo) && !empty(asset('storage/uploads/meta/' . $meta_logo)) ? asset('storage/uploads/meta/' . $meta_logo) : 'hrmgo.png' }}">


    <!-- Google Fonts: Comfortaa & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="icon"
        href="{{ $logo . '/' . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon .'?'.time() : 'favicon.png' .'?'.time()) }}"
        type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}" id="stylesheet">
    @if (isset($setting['cust_darklayout']) && $setting['cust_darklayout'] == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif

    @if (isset($setting['cust_darklayout']) && $setting['cust_darklayout'] == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/custom-dark.css') }}">
    @endif

    <style>
        :root {
            --color-customColor: <?= $color ?>;
        }

        .placedjob-section .section-title {
            text-align: -webkit-center !important;
        }

        .job-banner {
            position: relative;
            overflow: hidden;
            padding: 140px 0 90px;
            min-height: 520px;
        }

        .job-banner .job-banner-bg {
            position: absolute;
            inset: 0;
            background-image: url('/assets/images/pages/careerpage.webp');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: brightness(0.55);
            z-index: 1;
        }

        .job-banner::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 2;
        }

        .job-banner-content {
            position: relative;
            z-index: 3;
            max-width: 760px;
            margin: 0 auto;
        }

        .job-banner-content h1,
        .job-banner-content p,
        .job-banner-content .lead {
            color: #ffffff;
        }

        .company-detail-bar .company-logo {
            width: 100px;
            height: 100px;
            min-width: 100px;
            border-radius: 20px;
            overflow: hidden;
            background: rgba(255,255,255,0.9);
            display: grid;
            place-items: center;
        }

        .company-detail-bar .company-detail-label {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .company-detail-bar .company-detail-value {
            font-weight: 600;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">

    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="{{ $themeColor }} global-career-body">
    <div class="job-wrapper">
        <div class="job-content">
            <section id="job-listings" class="placedjob-section">
                <div class="container">
                    <!-- <div class="section-title mb-5"> 
                        @php
                            $totaljob = \App\Models\Job::where('created_by', '=', $id)->where('status', 'active')->count();
                        @endphp
                        <h2 class="h1 mb-3">
                            <span class="text-primary">+{{ $totaljob }}</span>
                            {{ __('Job openings') }}
                        </h2>

                        <p class="mb-0">
                            {{ __('Always looking for better ways to do things, innovate') }} <br>
                            {{ __('and help people achieve their goals') }}.
                        </p>
                    </div> -->
                    <div class="company-detail-bar mb-5">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="row align-items-center gx-4 gy-3">
                                    <div class="col-auto">
                                        <div class="company-logo shadow-sm">
                                            <img src="{{ $logo . '/' . (isset($company_logo_light) && !empty($company_logo_light) ? $company_logo_light .'?'.time() : 'logo-light.png' .'?'.time()) }}"
                                                alt="company logo" class="img-fluid" style="max-height: 78px;">
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <h3 class="mb-1 fw-bold">
                                            @if(!empty($company) && !empty($company->company_name))
                                            {{ $company->company_name }}
                                            @elseif(!empty($company) && !empty($company->name))
                                            {{ $company->name }}
                                            @else
                                            {{ __('Company') }}
                                            @endif
                                        </h3>
                                        @if(!empty($companySettings['title_text']))
                                        <p class="mb-2 text-muted">{{ $companySettings['title_text']->value }}</p>
                                        @endif
                                        <div class="d-flex flex-wrap gap-3 small text-muted">
                                            @if(!empty($company) && !empty($company->email))
                                            <span><i class="ti ti-mail me-1"></i> {{ $company->email }}</span>
                                            @endif
                                            @if(!empty($companySettings['company_telephone']->value))
                                            <span><i class="ti ti-phone me-1"></i> {{ $companySettings['company_telephone']->value }}</span>
                                            @elseif(!empty($company) && !empty($company->phone))
                                            <span><i class="ti ti-phone me-1"></i> {{ $company->phone }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="row gx-3 gy-2">
                                            @if(!empty($companySettings['company_name']->value))
                                            <div class="col-6 company-detail-label">{{ __('Company Name') }}</div>
                                            <div class="col-6 company-detail-value">{{ $companySettings['company_name']->value }}</div>
                                            @endif
                                            @if(!empty($companySettings['company_address']->value))
                                            <div class="col-6 company-detail-label">{{ __('Address') }}</div>
                                            <div class="col-6 company-detail-value">{{ $companySettings['company_address']->value }}</div>
                                            @endif
                                            @if(!empty($companySettings['company_city']->value) || !empty($companySettings['company_state']->value) || !empty($companySettings['company_zipcode']->value) || !empty($companySettings['company_country']->value))
                                            <div class="col-6 company-detail-label">{{ __('Location') }}</div>
                                            <div class="col-6 company-detail-value">
                                                {{ !empty($companySettings['company_city']->value) ? $companySettings['company_city']->value : '' }}
                                                {{ !empty($companySettings['company_state']->value) ? ', '.$companySettings['company_state']->value : '' }}
                                                {{ !empty($companySettings['company_zipcode']->value) ? ' - '.$companySettings['company_zipcode']->value : '' }}
                                                {{ !empty($companySettings['company_country']->value) ? ', '.$companySettings['company_country']->value : '' }}
                                            </div>
                                            @endif
                                            @if(!empty($companySettings['company_telephone']->value) || (!empty($company) && !empty($company->phone)))
                                            <div class="col-6 company-detail-label">{{ __('Telephone') }}</div>
                                            <div class="col-6 company-detail-value">{{ !empty($companySettings['company_telephone']->value) ? $companySettings['company_telephone']->value : $company->phone }}</div>
                                            @endif
                                            @if(!empty($company) && !empty($company->website))
                                            <div class="col-6 company-detail-label">{{ __('Website') }}</div>
                                            <div class="col-6 company-detail-value">{{ $company->website }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Filter Card Container -->
                    <div class="filter-card-container mb-4">
                        <form action="{{ route('career', [$id, $currantLang]) }}" method="get" id="career-filter-form">
                            <div class="d-flex align-items-center flex-wrap gap-3 mb-3">
                                <!-- Search Input -->
                                <div class="search-col-box">
                                    <div class="input-group search-input-group">
                                        <span class="input-group-text">
                                            <i class="ti ti-search fs-6"></i>
                                        </span>
                                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Search by job title, keyword or location') }}">
                                    </div>
                                </div>

                                <!-- Department Dropdown -->
                                <div class="filter-select-box">
                                    <select name="category" class="form-select filter-select" onchange="document.getElementById('career-filter-form').submit();">
                                        <option value="">{{ __('Department') }}</option>
                                        @foreach($categories as $key => $categoryName)
                                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $categoryName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Location Dropdown -->
                                <div class="filter-select-box">
                                    <select name="branch" class="form-select filter-select" onchange="document.getElementById('career-filter-form').submit();">
                                        <option value="">{{ __('Location') }}</option>
                                        @foreach($branches as $key => $branchName)
                                            <option value="{{ $key }}" {{ request('branch') == $key ? 'selected' : '' }}>{{ $branchName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Reset Button -->
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('career', [$id, $currantLang]) }}" class="btn btn-outline-gray d-flex align-items-center justify-content-center" title="{{ __('Reset Filter') }}">
                                        <i class="ti ti-rotate-clockwise me-1 fs-6"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>

                            <!-- Sub Header Bar -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2" style="border-color: #F1F5F9 !important;">
                                <span class="fs-7 fw-normal" style="color: #475569;">
                                    Showing <strong style="color: #5E2590; font-weight: 600;">{{ is_object($jobs) && method_exists($jobs, 'total') ? $jobs->total() : (is_countable($jobs) ? count($jobs) : 0) }}</strong> Openings
                                </span>
                                <div class="d-flex align-items-center fs-7" style="color: #64748B;">
                                    <span class="me-2">{{ __('Sort by:') }}</span>
                                    <select id="sort_by_select" name="sort" onchange="document.getElementById('career-filter-form').submit();" class="form-select form-select-sm custom-sort-dropdown">
                                        <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>{{ __('Newest First') }}</option>
                                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>{{ __('Oldest First') }}</option>
                                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>{{ __('Title (A-Z)') }}</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="row g-4">
                        @foreach ($jobs as $job)
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 job-card">
                            <div class="job-card-body">
                                <div class="d-flex mb-3 align-items-center justify-content-between">
                                    <img src="{{ asset('/storage/uploads/job/figma.png') }}" alt="">
                                    @if (!empty($job->branches) ? $job->branches->name : '')
                                    <span class="text-muted small">{{ !empty($job->branches) ? $job->branches->name : '' }} <i
                                            class="ti ti-map-pin ms-1"></i></span>
                                    @endif
                                </div>
                                <h5 class="mb-3">
                                    <a href="{{ route('job.requirement', [$job->code, !empty($job) ? (!empty($job->createdBy->lang) ? $job->createdBy->lang : 'en') : 'en']) }}"
                                        class="text-dark">{{ $job->title }}</a>
                                </h5>
                                <div class="job-card-details mb-4">
                                    <span><i class="ti ti-circle-plus me-2"></i>
                                        {{ $job->position }} {{ __('position available') }}</span>
                                </div>

                                <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
                                    @foreach (explode(',', $job->skill) as $skill)
                                        @if(!empty(trim($skill)))
                                            <span class="badge rounded-pill px-3 py-2 bg-light text-primary border border-primary">{{ ucwords(str_replace('_', ' ', trim($skill))) }}</span>
                                        @endif
                                    @endforeach
                                </div>

                                <div class="job-card-footer mt-auto">
                                    <a href="{{ route('job.requirement', [$job->code, !empty($job) ? (!empty($job->createdBy->lang) ? $job->createdBy->lang : 'en') : 'en']) }}"
                                        class="btn btn-primary w-100">{{ __('Detail') }}</a>
                                </div>

                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script src="{{ asset('js/site.core.js') }}"></script>
    <script src="{{ asset('js/site.js') }}"></script>
    <script src="{{ asset('js/demo.js') }} "></script>

    @stack('custom-scripts')
    @if($enable_cookie['enable_cookie'] == 'on')
    @include('layouts.cookie_consent')
    @endif

</body>

