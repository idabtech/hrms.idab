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

    $iconsMap = [
        'developer' => 'ti-code',
        'frontend' => 'ti-code',
        'backend' => 'ti-server',
        'fullstack' => 'ti-terminal-2',
        'designer' => 'ti-palette',
        'ui/ux' => 'ti-palette',
        'manager' => 'ti-briefcase',
        'hr' => 'ti-users',
        'marketing' => 'ti-speakerphone',
        'mobile' => 'ti-device-mobile',
        'flutter' => 'ti-device-mobile',
        'react' => 'ti-brand-react',
        'node' => 'ti-brand-nodejs',
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
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="{{ $themeColor }}">
    <div class="container py-4">
        <!-- Header Section -->
        <div class="career-header-section mb-4">
            <h1 class="display-5 fw-bold text-dark mb-2">
                All <span class="text-primary">Openings</span>
            </h1>
            <p class="text-muted fs-6 mb-0" style="max-width: 620px;">
                Explore exciting career opportunities and take the next step in your professional journey.
            </p>
        </div>

        <!-- Filter Bar Card -->
        <div class="filter-card mb-4">
            <form action="{{ route('global.career') }}" method="get" id="career-filter-form">
                <div class="row g-2 align-items-center">
                    <!-- Search Input -->
                    <div class="col-lg-5 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted pe-1">
                                <i class="ti ti-search fs-6"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm border-start-0 ps-2" placeholder="{{ __('Search by job title, keyword or location') }}">
                        </div>
                    </div>

                    <!-- Department/Category Dropdown -->
                    <div class="col-lg-3 col-md-3">
                        <select name="category" class="form-select form-select-sm">
                            <option value="">{{ __('Department') }}</option>
                            @foreach($categories as $catId => $catTitle)
                                <option value="{{ $catId }}" {{ request('category') == $catId ? 'selected' : '' }}>{{ $catTitle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location/Branch Dropdown -->
                    <div class="col-lg-2 col-md-3">
                        <select name="branch" class="form-select form-select-sm">
                            <option value="">{{ __('Location') }}</option>
                            @foreach($branches as $branchId => $branchName)
                                <option value="{{ $branchId }}" {{ request('branch') == $branchId ? 'selected' : '' }}>{{ $branchName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Actions Buttons -->
                    <div class="col-lg-2 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center">
                            <i class="ti ti-filter me-1"></i> {{ __('Filter') }}
                        </button>
                        @if(request()->filled('search') || request()->filled('branch') || request()->filled('category') || request()->filled('sort'))
                            <a href="{{ route('global.career') }}" class="btn btn-light btn-sm text-muted border border-secondary border-opacity-25 d-flex align-items-center justify-content-center" title="{{ __('Reset Filter') }}">
                                <i class="ti ti-rotate-clockwise"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Openings Header Bar -->
        <div class="d-flex justify-content-between align-items-center mb-3 px-1 flex-wrap gap-2">
            <span class="text-muted fw-semibold fs-7">
                Showing <strong class="text-dark">{{ $jobs->total() }}</strong> Openings
            </span>
            <div class="d-flex align-items-center">
                <label for="sort_by_select" class="text-muted fw-semibold me-2 mb-0 fs-7">{{ __('Sort by:') }}</label>
                <select id="sort_by_select" name="sort" form="career-filter-form" onchange="document.getElementById('career-filter-form').submit();" class="form-select form-select-sm custom-sort-select">
                    <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>{{ __('Newest First') }}</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>{{ __('Oldest First') }}</option>
                    <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>{{ __('Title (A-Z)') }}</option>
                </select>
            </div>
        </div>

        <!-- Openings Card List -->
        <div class="job-list-container">
            @forelse($jobs as $job)
                @php
                    $titleLower = strtolower($job->title);
                    $iconClass = 'ti-briefcase';
                    foreach ($iconsMap as $key => $icon) {
                        if (str_contains($titleLower, $key)) {
                            $iconClass = $icon;
                            break;
                        }
                    }
                    $branchName = !empty($job->branches) ? $job->branches->name : 'Surat, Gujarat';
                    $categoryTitle = !empty($job->categories) ? $job->categories->title : 'Development';
                    $companyName = !empty($job->createdBy) ? ($job->createdBy->company_name ?: $job->createdBy->name) : '';
                @endphp
                <div class="job-item-card">
                    <div class="row align-items-center gy-3">
                        <!-- Icon & Info -->
                        <div class="col-md-7 col-12 d-flex align-items-center">
                            <div class="job-icon-box me-3">
                                <i class="ti {{ $iconClass }}"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">
                                    <a href="{{ route('job.requirement', [$job->code, !empty($job->createdBy->lang) ? $job->createdBy->lang : 'en']) }}" class="text-dark text-decoration-none hover-primary">
                                        {{ $job->title }}
                                    </a>
                                </h5>
                                <div class="d-flex flex-wrap align-items-center gap-3 text-muted fs-7">
                                    <span><i class="ti ti-map-pin me-1 text-primary"></i> {{ $branchName }}</span>
                                    <span><i class="ti ti-clock me-1 text-primary"></i> Full Time</span>
                                    @if($companyName)
                                        <span><i class="ti ti-building me-1 text-primary"></i> {{ $companyName }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Badges & Action -->
                        <div class="col-md-5 col-12 d-flex align-items-center justify-content-md-end gap-3 flex-wrap">
                            <span class="badge bg-light-primary text-primary rounded-pill px-3 py-2 fw-semibold fs-7">{{ $categoryTitle }}</span>
                            <span class="badge-level-gray">Mid Level</span>
                            <a href="{{ route('job.requirement', [$job->code, !empty($job->createdBy->lang) ? $job->createdBy->lang : 'en']) }}" class="btn btn-primary rounded-3 px-4 py-2 text-white shadow-sm d-inline-flex align-items-center">
                                {{ __('Apply Now') }} <i class="ti ti-chevron-right ms-1"></i>
                            </a>
                        </div>
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
