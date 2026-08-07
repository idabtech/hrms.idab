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
        {{ !empty($companySettings['title_text']) ? $companySettings['title_text']->value : config('app.name', 'HRMGO') }}
        - {{ __('Job Requirements') }}</title>

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

    <link rel="icon"
        href="{{ $logo . '/' . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon .'?'.time() : 'favicon.png' .'?'.time()) }}"
        type="image/x-icon" />

    <!-- Google Fonts: Comfortaa & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
            --color-customColor: <?=$color ?>;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body.job-page {
            background-color: #F9FAFB !important;
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
            color: #1E293B !important;
        }
        .job-wrapper {
            padding: 40px 0;
        }
        .apply-job-wrapper {
            background: #ffffff !important;
            border: 1px solid #F1F5F9 !important;
            border-radius: 20px !important;
            box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05) !important;
            padding: 40px !important;
        }
        .btn-back-link {
            display: inline-flex;
            align-items: center;
            color: #64748B;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .btn-back-link:hover {
            color: #5E2590;
        }
        .job-headline {
            font-family: 'Comfortaa', cursive, sans-serif !important;
            font-size: 2.2rem !important;
            font-weight: 700 !important;
            line-height: 1.3 !important;
            color: #1E293B !important;
            margin-bottom: 1rem !important;
        }
        .job-badges span {
            font-family: 'Comfortaa', cursive, sans-serif !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            background-color: #F3F0FF !important;
            color: #5E2590 !important;
            border-radius: 9999px !important;
            padding: 6px 16px !important;
        }
        .job-meta-card {
            background: #ffffff !important;
            border: 1px solid #F1F5F9 !important;
            border-radius: 16px !important;
            box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05) !important;
            padding: 28px !important;
        }
        .job-meta-card h5 {
            font-family: 'Comfortaa', cursive, sans-serif !important;
            font-size: 1.15rem !important;
            font-weight: 700 !important;
            color: #1E293B !important;
            margin-bottom: 1.25rem !important;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #F3F0FF;
        }
        .job-meta-card .meta-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.85rem 0;
            border-bottom: 1px solid #F1F5F9;
        }
        .job-meta-card .meta-item:last-child {
            border-bottom: none;
        }
        .job-meta-card .meta-item span:first-child {
            color: #64748B;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .job-meta-card .meta-item span:last-child {
            color: #1E293B;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .job-section-title {
            font-family: 'Comfortaa', cursive, sans-serif !important;
            font-size: 1.3rem !important;
            font-weight: 700 !important;
            color: #1E293B !important;
            margin-bottom: 1.25rem !important;
            border-bottom: 2px solid #5E2590;
            display: inline-block;
            padding-bottom: 0.35rem;
        }
        .job-content-block {
            margin-bottom: 36px;
        }
        .job-content-block p,
        .job-content-block ul {
            font-family: 'Inter', sans-serif !important;
            font-size: 0.98rem !important;
            line-height: 1.8 !important;
            color: #475569 !important;
        }
        .btn-apply-purple {
            background-color: #5E2590 !important;
            border-color: #5E2590 !important;
            color: #ffffff !important;
            font-family: 'Comfortaa', cursive, sans-serif !important;
            font-weight: 700 !important;
            font-size: 16px !important;
            border-radius: 12px !important;
            padding: 12px 32px !important;
            transition: all 0.25s ease !important;
            display: inline-flex;
            align-items: center;
            box-shadow: 0px 4px 12px rgba(94, 37, 144, 0.2) !important;
            text-decoration: none !important;
        }
        .btn-apply-purple:hover {
            background-color: #441668 !important;
            border-color: #441668 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }
    </style>
</head>

<body class="{{ $themeColor }} global-career-body job-page">
    <div class="job-wrapper">
        <div class="container">
            <div class="apply-job-wrapper">
                <!-- Back Link -->
                <div class="mb-4">
                    <a href="{{ route('global.career') }}" class="btn-back-link">
                        <i class="ti ti-arrow-left me-1 fs-6"></i> {{ __('Back to All Openings') }}
                    </a>
                </div>

                <div class="row align-items-start gy-4">
                    <div class="col-lg-8">
                        <div class="mb-5">
                            <p class="job-headline">{{ $job->title }}</p>
                            <div class="job-badges d-flex flex-wrap gap-2 mb-3">
                                @foreach (explode(',', $job->skill) as $skill)
                                    <span>{{ trim($skill) }}</span>
                                @endforeach
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-4 text-secondary fs-7" style="color: #64748B !important;">
                                @if (!empty($job->branches) ? $job->branches->name : '')
                                    <div><i class="ti ti-map-pin me-1" style="color: #5E2590;"></i>{{ !empty($job->branches) ? $job->branches->name : '' }}</div>
                                @endif
                                @if (!empty($job->job_type))
                                    <div><i class="ti ti-briefcase me-1" style="color: #5E2590;"></i>{{ ucfirst($job->job_type) }}</div>
                                @endif
                            </div>
                            <a href="{{ route('job.apply', [$job->code, $currantLang]) }}" class="btn btn-apply-purple">
                                {{ __('Apply now') }} <i class="ti ti-send ms-2 fs-6"></i>
                            </a>
                        </div>

                        <div class="job-content-block">
                            <h3 class="job-section-title">{{ __('Requirements') }}</h3>
                            <div class="requirement-text">{!! $job->requirement !!}</div>
                        </div>

                        <div class="job-content-block">
                            <h3 class="job-section-title">{{ __('Description') }}</h3>
                            <div class="description-text">{!! $job->description !!}</div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="job-meta-card">
                            <h5>{{ __('Job Summary') }}</h5>
                            <div class="meta-item">
                                <span>{{ __('Job Title') }}</span>
                                <span>{{ $job->title }}</span>
                            </div>
                            @if (!empty($job->branches) ? $job->branches->name : '')
                                <div class="meta-item">
                                    <span>{{ __('Location') }}</span>
                                    <span>{{ !empty($job->branches) ? $job->branches->name : '' }}</span>
                                </div>
                            @endif
                            @if (!empty($job->job_type))
                                <div class="meta-item">
                                    <span>{{ __('Employment Type') }}</span>
                                    <span>{{ ucfirst($job->job_type) }}</span>
                                </div>
                            @endif
                            @if (!empty($job->salary))
                                <div class="meta-item">
                                    <span>{{ __('Salary') }}</span>
                                    <span>{{ $job->salary }}</span>
                                </div>
                            @endif
                            @if (!empty($job->experience))
                                <div class="meta-item">
                                    <span>{{ __('Experience') }}</span>
                                    <span>{{ $job->experience }}</span>
                                </div>
                            @endif
                            <div class="meta-item">
                                <span>{{ __('Skills') }}</span>
                                <span>{{ implode(', ', array_map('trim', explode(',', $job->skill))) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

</html>
