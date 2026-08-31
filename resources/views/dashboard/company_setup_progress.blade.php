@php
    $onboarding = \App\Models\Utility::getCompanyOnboardingProgress();
    $percentage = $onboarding['percentage'];
    $completedCount = $onboarding['completedCount'];
    $totalCount = $onboarding['totalCount'];
    $steps = $onboarding['steps'];
    $isFullyDone = $onboarding['is_fully_done'];
@endphp

<div class="col-12 mb-4" id="company-onboarding-container">
    @if ($isFullyDone)
        <script>
            if (localStorage.getItem('company_onboarding_dismissed_{{ \Auth::id() }}') === 'true') {
                document.getElementById('company-onboarding-container').style.display = 'none';
            }
        </script>
    @endif
    <div class="card">
        <!-- System Theme Card Header -->
        <div class="card-header d-flex align-items-center justify-content-between py-3">
            <div class="d-flex align-items-center gap-2">
                <div style="border-left: 4px solid #51459d; height: 20px; border-radius: 2px;"></div>
                <h5 class="mb-0 fw-bold text-dark">{{ __('Company Setup & Profile Completion') }}</h5>
                <span class="badge {{ $isFullyDone ? 'bg-success' : 'bg-primary' }} px-3 py-1 ms-2 font-weight-bold" style="font-size: 0.8rem;">
                    {{ $completedCount }} / {{ $totalCount }} {{ __('Completed') }}
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#onboardingCollapse" aria-expanded="true" aria-controls="onboardingCollapse">
                    <i class="ti ti-chevron-up" id="onboardingToggleIcon"></i> {{ __('Toggle Details') }}
                </button>
                @if ($isFullyDone)
                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" type="button" id="dismissOnboardingBtn" title="{{ __('Close') }}">
                        <i class="ti ti-x"></i> {{ __('Close') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Progress Bar Section -->
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-semibold text-dark" style="font-size: 0.9rem;">
                        <i class="ti ti-chart-line text-primary me-1"></i> {{ __('Overall Setup Progress') }}
                    </span>
                    <span class="fw-bold {{ $isFullyDone ? 'text-success' : 'text-primary' }}" style="font-size: 1rem;">
                        {{ $percentage }}%
                    </span>
                </div>
                <div class="progress" style="height: 10px; background-color: #e9ecef; border-radius: 5px;">
                    <div class="progress-bar {{ $isFullyDone ? 'bg-success' : 'bg-primary' }}"
                         role="progressbar"
                         style="width: {{ $percentage }}%; transition: width 0.6s ease;"
                         aria-valuenow="{{ $percentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                </div>
            </div>

            <!-- Expandable Steps Grid -->
            <div class="collapse show" id="onboardingCollapse">
                @if ($isFullyDone)
                    <div class="alert alert-success d-flex align-items-center justify-content-between p-3 rounded-3 mb-0">
                        <div class="d-flex align-items-center gap-3">
                            <i class="ti ti-circle-check-filled fs-1 text-success"></i>
                            <div>
                                <h6 class="alert-heading mb-0 fw-bold">{{ __('Congratulations! Your company setup is 100% complete.') }}</h6>
                                <small>{{ __('All essential company profile settings, HRM setups, and employee registrations are done.') }}</small>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach ($steps as $step)
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="card shadow-none border mb-0 h-100 {{ $step['is_completed'] ? 'border-success' : '' }}" style="border-radius: 8px;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="badge bg-light text-muted px-2 py-1" style="font-size: 0.725rem;">
                                                    {{ $step['category'] }}
                                                </span>
                                                @if ($step['is_completed'])
                                                    <span class="badge bg-light-success text-success font-weight-bold" style="font-size: 0.75rem;">
                                                        <i class="ti ti-check"></i> {{ __('Done') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light-warning text-warning font-weight-bold" style="font-size: 0.75rem;">
                                                        {{ __('Pending') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <div class="avatar rounded-circle p-2 d-flex align-items-center justify-content-center me-1 {{ $step['is_completed'] ? 'bg-light-success text-success' : 'bg-light-primary text-primary' }}" style="width: 36px; height: 36px;">
                                                    <i class="{{ $step['icon'] }} fs-4"></i>
                                                </div>
                                                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">
                                                    {{ $step['title'] }}
                                                </h6>
                                            </div>
                                            <p class="text-muted mb-3" style="font-size: 0.8rem; line-height: 1.3;">
                                                {{ $step['desc'] }}
                                            </p>
                                        </div>
                                        <div>
                                            @if ($step['is_completed'])
                                                <a href="{{ $step['url'] }}" class="btn btn-sm btn-outline-success w-100 py-1 font-weight-bold" style="font-size: 0.8rem;">
                                                    <i class="ti ti-eye me-1"></i> {{ __('View') }}
                                                </a>
                                            @else
                                                <a href="{{ $step['url'] }}" class="btn btn-sm btn-primary w-100 py-1 font-weight-bold shadow-sm" style="font-size: 0.8rem;">
                                                    <i class="ti ti-plus me-1"></i> {{ __('Set Up Now') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var containerEl = document.getElementById('company-onboarding-container');
        var userId = "{{ \Auth::id() }}";
        var storageKey = 'company_onboarding_dismissed_' + userId;

        var dismissBtn = document.getElementById('dismissOnboardingBtn');
        if (dismissBtn && containerEl) {
            dismissBtn.addEventListener('click', function() {
                containerEl.style.transition = 'opacity 0.3s ease';
                containerEl.style.opacity = '0';
                setTimeout(function() {
                    containerEl.style.display = 'none';
                }, 300);
                localStorage.setItem(storageKey, 'true');
            });
        }

        var collapseEl = document.getElementById('onboardingCollapse');
        var iconEl = document.getElementById('onboardingToggleIcon');
        if (collapseEl && iconEl) {
            collapseEl.addEventListener('hide.bs.collapse', function () {
                iconEl.className = 'ti ti-chevron-down';
            });
            collapseEl.addEventListener('show.bs.collapse', function () {
                iconEl.className = 'ti ti-chevron-up';
            });
        }
    });
</script>
