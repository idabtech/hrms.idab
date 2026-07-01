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
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}" id="stylesheet">
    @if (isset($setting['cust_darklayout']) && $setting['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}"id="main-style-link">
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
            background: #f3f6fb;
            color: #1f2a44;
        }
        .job-wrapper {
            padding: 60px 0;
        }
        .apply-job-wrapper {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(31, 41, 55, 0.08);
            padding: 40px;
        }
        .job-headline {
            font-size: 2.35rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 0.8rem;
            color: #112341;
        }
        .job-badges span {
            font-size: 0.85rem;
            font-weight: 600;
            background: rgba(49, 130, 206, 0.1);
            color: #175cd3;
            border-radius: 999px;
            padding: 0.55rem 0.95rem;
            margin-bottom: 0.5rem;
        }
        .job-meta-card {
            background: #f8fbff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 24px;
        }
        .job-meta-card h5 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .job-meta-card .meta-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.9rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .job-meta-card .meta-item:last-child {
            border-bottom: none;
        }
        .job-meta-card .meta-item span:first-child {
            color: #6b7280;
            font-size: 0.95rem;
        }
        .job-meta-card .meta-item span:last-child {
            color: #111827;
            font-weight: 600;
        }
        .job-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            border-bottom: 2px solid rgba(99, 102, 241, 0.15);
            display: inline-block;
            padding-bottom: 0.35rem;
        }
        .job-content-block {
            margin-bottom: 32px;
        }
        .job-content-block p,
        .job-content-block ul {
            font-size: 1rem;
            line-height: 1.8;
            color: #374151;
        }
        .job-content-block ul {
            padding-left: 1.25rem;
        }
        .job-content-block ul li {
            margin-bottom: 0.75rem;
        }
        .apply-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>

<body class="{{ $themeColor }} job-page">
    <div class="job-wrapper">
        <div class="container">
            <div class="apply-job-wrapper">
                <div class="row align-items-start gy-4">
                    <div class="col-lg-8">
                        <div class="mb-5">
                            <p class="job-headline">{{ $job->title }}</p>
                            <div class="job-badges d-flex flex-wrap gap-2 mb-3">
                                @foreach (explode(',', $job->skill) as $skill)
                                    <span>{{ trim($skill) }}</span>
                                @endforeach
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3 mb-4 text-secondary">
                                @if (!empty($job->branches) ? $job->branches->name : '')
                                    <div><i class="ti ti-map-pin me-2"></i>{{ !empty($job->branches) ? $job->branches->name : '' }}</div>
                                @endif
                                @if (!empty($job->job_type))
                                    <div><i class="ti ti-briefcase me-2"></i>{{ ucfirst($job->job_type) }}</div>
                                @endif
                            </div>
                            <a href="{{ route('job.apply', [$job->code, $currantLang]) }}" class="btn btn-primary btn-lg rounded apply-cta">
                                {{ __('Apply now') }} <i class="ti ti-send ms-2"></i>
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
