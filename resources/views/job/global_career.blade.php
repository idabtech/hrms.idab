@php
    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $setting = App\Models\Utility::colorset();
    $color = !empty($setting['theme_color']) ? $setting['theme_color'] : 'theme-2';
    $SITE_RTL = \App\Models\Utility::getValByName('SITE_RTL');
    $company_logo_light = \App\Models\Utility::getValByName('company_logo_light');
    $company_favicon = \App\Models\Utility::getValByName('company_favicon');

    $getseo = App\Models\Utility::getSeoSetting();
    $metatitle = isset($getseo['meta_title']) ? $getseo['meta_title'] : 'All Openings - Career';
    $metadesc = isset($getseo['meta_description']) ? $getseo['meta_description'] : 'Explore exciting career opportunities and take the next step in your professional journey.';
    $meta_image = \App\Models\Utility::get_file('uploads/meta/');
    $meta_logo = isset($getseo['meta_image']) ? $getseo['meta_image'] : '';

    if (isset($setting['color_flag']) && $setting['color_flag'] == 'true') {
        $themeColor = 'custom-color';
    } else {
        $themeColor = $color;
    }

    if (!empty($company_favicon) && file_exists(storage_path('app/public/uploads/logo/' . $company_favicon))) {
        $faviconUrl = $logo . $company_favicon;
    } elseif (file_exists(storage_path('app/public/uploads/logo/8_favicon.png'))) {
        $faviconUrl = $logo . '8_favicon.png';
    } elseif (file_exists(storage_path('app/public/uploads/logo/favicon.png'))) {
        $faviconUrl = $logo . 'favicon.png';
    } else {
        $faviconUrl = asset('assets/images/favicon.png');
    }

    $iconStylesMap = [
        'developer' => ['icon' => 'ti-code', 'bg' => '#F0FDF4', 'color' => '#16A34A'],
        'frontend'  => ['icon' => 'ti-code', 'bg' => '#F0FDF4', 'color' => '#16A34A'],
        'designer'  => ['icon' => 'ti-palette', 'bg' => '#FFF7ED', 'color' => '#EA580C'],
        'ui/ux'     => ['icon' => 'ti-palette', 'bg' => '#FFF7ED', 'color' => '#EA580C'],
        'backend'   => ['icon' => 'ti-server', 'bg' => '#EFF6FF', 'color' => '#2563EB'],
        'hr'        => ['icon' => 'ti-users', 'bg' => '#FEF2F2', 'color' => '#DC2626'],
        'marketing' => ['icon' => 'ti-speakerphone', 'bg' => '#FAF5FF', 'color' => '#9333EA'],
        'mobile'    => ['icon' => 'ti-device-mobile', 'bg' => '#ECFEFF', 'color' => '#0891B2'],
        'flutter'   => ['icon' => 'ti-device-mobile', 'bg' => '#ECFEFF', 'color' => '#0891B2'],
        'fullstack' => ['icon' => 'ti-terminal-2', 'bg' => '#F0FDF4', 'color' => '#16A34A'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $SITE_RTL == 'on' ? 'rtl' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ __('All Openings') }} - {{ __('Careers') }}</title>

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

    <link rel="icon" href="{{ $faviconUrl . '?' . time() }}" type="image/x-icon" />
    <link rel="shortcut icon" href="{{ $faviconUrl . '?' . time() }}" type="image/x-icon" />

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

    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <style>
        :root {
            --color-customColor: <?= $color ?>;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="{{ $themeColor }} global-career-body">
    <div class="container py-4">
        <!-- Top Breadcrumbs (Commented for now as requested)
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0 text-muted fs-7 align-items-center">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="ti ti-home fs-6"></i></a></li>
                <li class="breadcrumb-item fw-bold" style="color: #64748B;">{{ __('Careers') }}</li>
                <li class="breadcrumb-item active fw-bold" style="color: #5E2590;" aria-current="page">{{ __('Openings') }}</li>
            </ol>
        </nav>
        -->

        <!-- Header Section -->
        <div class="career-header-section mb-4">
            <h1 class="display-4 fw-extrabold mb-2">
                All <span class="text-purple-brand">Openings</span>
            </h1>
            <p class="text-muted mb-0" style="max-width: 580px;">
                Explore exciting career opportunities and take the next step in your professional journey.
            </p>
        </div>

        <!-- Filter Card Container -->
        <div class="filter-card-container mb-4">
            <form action="{{ route('global.career') }}" method="get" id="career-filter-form">
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
                            @foreach($categories as $catId => $catTitle)
                                <option value="{{ $catId }}" {{ request('category') == $catId ? 'selected' : '' }}>{{ $catTitle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location Dropdown -->
                    <div class="filter-select-box">
                        <select name="branch" class="form-select filter-select" onchange="document.getElementById('career-filter-form').submit();">
                            <option value="">{{ __('Location') }}</option>
                            @foreach($branches as $branchId => $branchName)
                                <option value="{{ $branchId }}" {{ request('branch') == $branchId ? 'selected' : '' }}>{{ $branchName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Reset Button -->
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('global.career') }}" class="btn btn-outline-gray d-flex align-items-center justify-content-center" title="{{ __('Reset Filter') }}">
                            <i class="ti ti-rotate-clockwise me-1 fs-6"></i> {{ __('Reset') }}
                        </a>
                    </div>
                </div>

                <!-- Sub Header Bar -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2" style="border-color: #F1F5F9 !important;">
                    <span class="fs-7 fw-normal" style="color: #475569;">
                        Showing <strong style="color: #5E2590; font-weight: 600;">{{ $jobs->total() }}</strong> Openings
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

        <!-- Openings Card List -->
        <div class="job-list-container">
            @forelse($jobs as $job)
                @php
                    $titleLower = strtolower($job->title);
                    $style = ['icon' => 'ti-briefcase', 'bg' => '#F0FDF4', 'color' => '#16A34A'];
                    foreach ($iconStylesMap as $key => $mapData) {
                        if (str_contains($titleLower, $key)) {
                            $style = $mapData;
                            break;
                        }
                    }
                    $branchName = !empty($job->branches) ? $job->branches->name : 'Surat, Gujarat';
                    $categoryTitle = !empty($job->categories) ? $job->categories->title : 'Development';
                    $companyName = !empty($job->createdBy) ? ($job->createdBy->company_name ?: $job->createdBy->name) : '';
                @endphp
                <div class="job-item-card d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <!-- Icon & Details Info -->
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <div class="job-icon-box-colorful" style="background-color: {{ $style['bg'] }}; color: {{ $style['color'] }};">
                            <i class="ti {{ $style['icon'] }}"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1 job-title-heading">
                                <a href="{{ route('job.requirement', [$job->code, !empty($job->createdBy->lang) ? $job->createdBy->lang : 'en']) }}" class="text-decoration-none hover-purple" style="color: #1E293B;">
                                    {{ $job->title }}
                                </a>
                            </h5>
                            <div class="d-flex flex-wrap align-items-center gap-3 job-meta-text">
                                <span class="badge-cat-pill">{{ $categoryTitle }}</span>
                                <span><i class="ti ti-map-pin me-1" style="color: #64748B;"></i> {{ $branchName }}</span>
                                <span><i class="ti ti-clock me-1" style="color: #64748B;"></i> Full Time</span>
                                @if($companyName)
                                    <span><i class="ti ti-building me-1" style="color: #64748B;"></i> {{ $companyName }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                        <a href="{{ route('job.requirement', [$job->code, !empty($job->createdBy->lang) ? $job->createdBy->lang : 'en']) }}" class="btn btn-detail-purple">
                            {{ __('Detail') }}
                        </a>
                        <a href="{{ route('job.requirement', [$job->code, !empty($job->createdBy->lang) ? $job->createdBy->lang : 'en']) }}" class="btn btn-circle-arrow" title="{{ __('View Details') }}">
                            <i class="ti ti-chevron-right fs-6"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm p-5 text-center rounded-4 my-4">
                    <div class="py-4">
                        <i class="ti ti-briefcase-off display-4 text-muted mb-3 d-block"></i>
                        <h5 class="fw-bold text-dark mb-1">{{ __('No Openings Available') }}</h5>
                        <p class="text-muted mb-0">{{ __('There are currently no active job openings matching your search criteria.') }}</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination Section -->
        @if ($jobs->hasPages())
            <div class="d-flex justify-content-center mt-5 mb-4">
                {{ $jobs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
</body>
</html>
