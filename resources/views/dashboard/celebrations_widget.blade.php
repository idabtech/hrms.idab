@if(\Auth::check() && (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || (\Auth::user()->type != 'super admin' && \Auth::user()->type != 'employee')))
<div class="col-12">
    <div class="row">
        {{-- ═══ 1. WORK ANNIVERSARIES CARD ═══ --}}
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">{{ __('Work Anniversaries') }}</h5>
                    <div class="d-flex align-items-center gap-1">
                        @if(($celebrationSummary['today_anniversaries_count'] ?? 0) > 0)
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1 font-weight-bold">
                                🎉 {{ $celebrationSummary['today_anniversaries_count'] }} {{ __('Today') }}
                            </span>
                        @endif
                        <span class="badge bg-primary rounded-pill px-3 py-1">
                            {{ $celebrationSummary['this_month_anniversaries_count'] ?? 0 }} {{ __('This Month') }}
                        </span>
                    </div>
                </div>
                <div class="card-body" style="height: 324px; overflow-y: auto;">
                    @if(!empty($anniversaryData) && count($anniversaryData) > 0)
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Milestone') }}</th>
                                        <th class="text-end">{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($anniversaryData as $item)
                                        @php
                                            $emp = $item['employee'];
                                            $rowBg = $item['is_today'] ? 'style="background-color: #fff8e1;"' : ($item['is_this_month'] ? 'style="background-color: #f8f9fa;"' : '');
                                        @endphp
                                        <tr {!! $rowBg !!}>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3 rounded-circle {{ $item['is_today'] ? 'bg-warning text-dark' : 'bg-primary text-white' }} d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                                        {{ strtoupper(substr($emp->name ?? 'E', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-dark fw-bold" style="font-size: 0.9rem;">{{ $emp->name }}</h6>
                                                        <small class="text-muted" style="font-size: 0.78rem;">
                                                            {{ $emp->department->name ?? '' }} @if(!empty($emp->designation)) &bull; {{ $emp->designation->name }} @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($item['is_today'])
                                                    <span class="badge bg-warning text-dark fw-bold px-2.5 py-1">
                                                        🏅 {{ $item['years_completed'] }} {{ __('Years Completed Today!') }}
                                                    </span>
                                                @elseif($item['is_this_month'])
                                                    <span class="badge bg-light-warning text-warning border border-warning-subtle fw-semibold px-2.5 py-1">
                                                        🏅 {{ $item['years_completed'] }} {{ __('Years') }} (In {{ $item['days_left'] }} days)
                                                    </span>
                                                @else
                                                    <small class="text-muted fw-semibold">
                                                        🏅 {{ $item['years_completed'] }} {{ __('Years') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($item['is_today'])
                                                    <span class="badge bg-danger text-white px-2.5 py-1 fw-bold">
                                                        🎉 {{ __('Today') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark border px-2 py-1 fw-semibold">
                                                        {{ $item['formatted_date'] }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-award fs-1 d-block mb-2"></i>
                            {{ __('No employee work anniversaries found.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══ 2. EMPLOYEE BIRTHDAYS CARD ═══ --}}
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">{{ __('Employee Birthdays') }}</h5>
                    <div class="d-flex align-items-center gap-1">
                        @if(($celebrationSummary['today_birthdays_count'] ?? 0) > 0)
                            <span class="badge bg-danger text-white rounded-pill px-3 py-1 font-weight-bold">
                                🎂 {{ $celebrationSummary['today_birthdays_count'] }} {{ __('Today') }}
                            </span>
                        @endif
                        <span class="badge bg-info rounded-pill px-3 py-1">
                            {{ $celebrationSummary['this_month_birthdays_count'] ?? 0 }} {{ __('This Month') }}
                        </span>
                    </div>
                </div>
                <div class="card-body" style="height: 324px; overflow-y: auto;">
                    @if(!empty($birthdayData) && count($birthdayData) > 0)
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Birthday') }}</th>
                                        <th class="text-end">{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($birthdayData as $item)
                                        @php
                                            $emp = $item['employee'];
                                            $rowBg = $item['is_today'] ? 'style="background-color: #ffeef0;"' : ($item['is_this_month'] ? 'style="background-color: #f8f9fa;"' : '');
                                        @endphp
                                        <tr {!! $rowBg !!}>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3 rounded-circle {{ $item['is_today'] ? 'bg-danger text-white' : 'bg-info text-white' }} d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                                        {{ strtoupper(substr($emp->name ?? 'E', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-dark fw-bold" style="font-size: 0.9rem;">{{ $emp->name }}</h6>
                                                        <small class="text-muted" style="font-size: 0.78rem;">
                                                            {{ $emp->department->name ?? '' }} @if(!empty($emp->designation)) &bull; {{ $emp->designation->name }} @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($item['is_today'])
                                                    <span class="badge bg-danger text-white fw-bold px-2.5 py-1">
                                                        🎂 {{ __('Turning') }} {{ $item['age'] }} {{ __('Today!') }}
                                                    </span>
                                                @elseif($item['is_this_month'])
                                                    <span class="badge bg-light-danger text-danger border border-danger-subtle fw-semibold px-2.5 py-1">
                                                        🎂 {{ __('Turning') }} {{ $item['age'] }} (In {{ $item['days_left'] }} days)
                                                    </span>
                                                @else
                                                    <small class="text-muted fw-semibold">
                                                        🎂 {{ $item['full_date_label'] }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($item['is_today'])
                                                    <span class="badge bg-danger text-white px-2.5 py-1 fw-bold">
                                                        🎂 {{ __('Today') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark border px-2 py-1 fw-semibold">
                                                        {{ $item['formatted_date'] }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-cake fs-1 d-block mb-2"></i>
                            {{ __('No employee birthdays found.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif
