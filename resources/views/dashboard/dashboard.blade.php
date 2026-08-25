@extends('layouts.admin')

@section('page-title')
    {{ __('Dashboard') }}
@endsection
@php
    use Carbon\Carbon;
    use App\Models\AttendanceRequest;
    use App\Models\Employee;
    $setting = App\Models\Utility::settings();
    $icons = \App\Models\Utility::get_file('uploads/job/icons/');
@endphp
@section('content')
    <div class="row">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        {{-- @if (session('dashboard_msg'))
        <div class="alert alert-danger" role="alert">
            {{ session('dashboard_msg') }}
        </div>
        @endif --}}

        @if (!empty($hasRequestedDocs) && $hasRequestedDocs && !empty($emp))
            <div class="col-12 mb-3">
                <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 rounded" style="background-color: #fff8e1; border: 1px solid #ffe082; color: #856404; box-shadow: 0 2px 6px rgba(255, 193, 7, 0.2);">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-file-alert fs-1 me-3 text-warning"></i>
                        <div>
                            <h6 class="alert-heading mb-1 text-dark font-weight-bold" style="font-size: 1rem;">
                                {{ __('Document Request') }}
                            </h6>
                            <span class="fw-bold" style="font-size: 0.95rem; color: #795548;">
                                {{ __('Please upload your document here') }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('employee.edit', \Crypt::encrypt($emp->id)) }}#document-card" class="btn btn-warning text-dark font-weight-bold shadow-sm">
                        <i class="ti ti-upload me-1"></i> {{ __('Upload Document') }}
                    </a>
                </div>
            </div>
        @endif

        {{-- Leave Document Pending Alert Banner for Employee --}}
        @php
            $dashEmpRecord = \App\Models\Employee::where('user_id', \Auth::id())->first();
            $pendingLeaveDocCount = 0;
            if ($dashEmpRecord) {
                $pendingLeaveDocCount = \App\Models\LeaveDocument::whereHas('leave', function($q) use ($dashEmpRecord) {
                    $q->where('employee_id', $dashEmpRecord->id);
                })->where('status', 'pending')->count();
            }
        @endphp

        @if ($pendingLeaveDocCount > 0)
            <div class="col-12 mb-3">
                <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 rounded" style="background-color: #fff8e1; border: 1px solid #ffe082; color: #856404; box-shadow: 0 2px 6px rgba(255, 193, 7, 0.2);">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-file-description fs-1 me-3 text-warning"></i>
                        <div>
                            <h6 class="alert-heading mb-1 text-dark font-weight-bold" style="font-size: 1rem;">
                                {{ __('Leave Document Request') }}
                            </h6>
                            <span class="fw-bold" style="font-size: 0.95rem; color: #795548;">
                                {{ __('Please submit document for your requested leave.') }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('leave.index') }}" class="btn btn-warning text-dark font-weight-bold shadow-sm">
                        <i class="ti ti-upload me-1"></i> {{ __('Upload Leave Document') }}
                    </a>
                </div>
            </div>
        @endif

        {{-- Travel Expense Document Request Alert Banner for Employee & Company --}}
        @can('Manage Travel Expense')
            @php
                $dashUserType = \Auth::user()->type;
                $dashEmpRecordForTravel = \App\Models\Employee::where('user_id', \Auth::id())->first();
                
                if ($dashUserType == 'employee') {
                    $pendingTravelDocRequests = \App\Models\TravelExpense::where('employee_id', $dashEmpRecordForTravel ? $dashEmpRecordForTravel->id : 0)
                        ->where('document_requested', 1)
                        ->get();
                } else {
                    $pendingTravelDocRequests = \App\Models\TravelExpense::where('created_by', \Auth::user()->creatorId())
                        ->where('document_requested', 1)
                        ->with('employee')
                        ->get();
                }
            @endphp

            @if ($pendingTravelDocRequests && $pendingTravelDocRequests->count() > 0)
                <div class="col-12 mb-3">
                    <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 rounded" style="background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; box-shadow: 0 2px 6px rgba(255, 193, 7, 0.25);">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-file-upload fs-1 me-3 text-warning"></i>
                            <div>
                                <h6 class="alert-heading mb-1 text-dark font-weight-bold" style="font-size: 1rem;">
                                    <i class="ti ti-bell-ringing text-warning me-1"></i>
                                    @if ($dashUserType == 'employee')
                                        {{ __('Travel Expense / Voucher Document Requested') }} ({{ $pendingTravelDocRequests->count() }})
                                    @else
                                        {{ __('Pending Travel Expense Document Requests') }} ({{ $pendingTravelDocRequests->count() }})
                                    @endif
                                </h6>
                                <span class="fw-bold" style="font-size: 0.92rem; color: #856404;">
                                    @if ($dashUserType == 'employee')
                                        {{ __('HR/Company has requested bill/document uploads for your travel expense(s):') }}
                                        <strong>
                                            {{ implode(', ', $pendingTravelDocRequests->pluck('title')->toArray()) }}
                                        </strong>
                                    @else
                                        {{ __('Document requests pending for employees:') }}
                                        <strong>
                                            {{ implode(', ', $pendingTravelDocRequests->map(function($t){ return ($t->employee ? $t->employee->name : 'Emp') . ' (' . $t->title . ')'; })->toArray()) }}
                                        </strong>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div>
                            @if ($dashUserType == 'employee')
                                <a href="{{ route('travel-expenses.index') }}" class="btn btn-warning text-dark font-weight-bold shadow-sm">
                                    <i class="ti ti-upload me-1"></i> {{ __('Upload Documents Now') }}
                                </a>
                            @else
                                <a href="{{ route('travel-expenses.index') }}" class="btn btn-dark text-white font-weight-bold shadow-sm">
                                    <i class="ti ti-eye me-1"></i> {{ __('View Requests') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endcan

        @if (\Auth::user()->type == 'employee' || (\Auth::user()->type != 'company' && \Auth::user()->employee))
            <div class="col-xxl-12">
                <div class="card" style="min-height: 230px;">
                    <div class="card-header">
                        <h5>{{ __('Mark Attandance') }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted pb-0-5">
                            {{ __('My Office Time: ' . ($officeTime['startTime'] ? formatTime12h($officeTime['startTime']) : 'N/A') . ' to ' . ($officeTime['endTime'] ? formatTime12h($officeTime['endTime']) : 'N/A')) }}
                        </p>
                        @php
                            $now = Carbon::now();

                            // Office shift start/end
                            [$startHour, $startMinute] = explode(':', $officeTime['startTime']);
                            [$endHour, $endMinute] = explode(':', $officeTime['endTime']);

                            $shiftStart = Carbon::today()->setTime((int) $startHour, (int) $startMinute, 0);
                            $shiftEnd = Carbon::today()->setTime((int) $endHour, (int) $endMinute, 0);

                            $loginDeadline = $shiftStart->copy()->addMinutes((int) ($setting['login_deley_min'] ?? 0));
                            $loginEarlyDeadline = $shiftStart->copy()->subMinutes((int) ($setting['login_early_time'] ?? 0));
                            $logoutAllowed = $shiftEnd->copy()->subMinutes((int) ($setting['logout_lead_time'] ?? 0));
                            $logoutLateAllowed = $shiftEnd->copy()->addMinutes((int) ($setting['logout_lead_time'] ?? 0));

                            // Employee
                            $employee = Employee::where('user_id', auth()->id())->first();
                            $employeeAttendance = $employee
                                    ?->attendances()
                                ->whereDate('date', Carbon::today())
                                ->latest()
                                ->first();

                            // Today's attendance request
                            $attendanceRequest = $employee
                                ? AttendanceRequest::where('employee_id', $employee->id)
                                    ->whereDate('requested_at', Carbon::today())
                                    ->latest()
                                    ->first()
                                : null;

                            $requestStatus = $attendanceRequest->status ?? null;

                            // Flags for button states
                            $canClockIn = !$employeeAttendance || $employeeAttendance->clock_in === null;
                            $canClockOut = ($attendanceRequest && $attendanceRequest->status == "pending") || ($employeeAttendance && $employeeAttendance->clock_out === '00:00:00');
                            $clockInLate = $now->gt($loginDeadline);
                            $clockInEarly = $now->lt($loginEarlyDeadline);
                            $clockOutLate = $now->gt($logoutLateAllowed);
                            $clockOutEarly = $now->lt($logoutAllowed);
                        @endphp

                        <div class="row">
                            {{-- Clock In Section --}}
                            <div class="col-md-4 float-right border-right">
                                <input type="hidden" id="office_start" value="{{ $officeTime['startTime'] }}">
                                <input type="hidden" id="office_end" value="{{ $officeTime['endTime'] }}">

                                @if ($canClockIn)
                                    @if ($clockInLate || $clockInEarly)
                                        {{-- Outside allowed clock-in window --}}
                                        @if (!$requestStatus)
                                            {{ Form::open(['route' => 'attendance-requests.store', 'method' => 'post']) }}
                                            <input type="hidden" name="type" value="clock_in">
                                            <button type="submit" class="btn btn-warning clock-action" data-action="clock_in"
                                                title="Request permission to clock in">
                                                {{ __('Clock In') }}
                                            </button>
                                            {{ Form::close() }}
                                        @elseif($requestStatus === 'pending')
                                            <button type="button" class="btn btn-secondary" disabled title="Your request is under review">
                                                {{ __('Pending') }}
                                            </button>
                                        @elseif($requestStatus === 'declined')
                                            <button type="button" class="btn btn-danger" disabled title="Your request was declined">
                                                {{ __('Declined') }}
                                            </button>
                                        @endif
                                    @else
                                        {{-- Within allowed clock-in window --}}
                                        {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post']) }}
                                        <button type="submit" value="0" name="in" id="clock_in" class="btn btn-primary">
                                            {{ __('CLOCK IN') }}
                                        </button>
                                        {{ Form::close() }}
                                    @endif
                                @else
                                    <button type="button" class="btn btn-primary disabled" title="You are clocked in" disabled>
                                        {{ __('CLOCK IN') }}
                                    </button>
                                @endif
                            </div>

                            {{-- Break Section --}}
                            <div class="col-md-4 float-left">
                                @if (!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                    @php
                                        $breaks = $employeeAttendance->breaks ? json_decode($employeeAttendance->breaks, true) : [];
                                        $lastBreak = !empty($breaks) ? end($breaks) : null;
                                        $breakActive = $lastBreak && ($lastBreak['end'] ?? null) === null;
                                        $breakType = $breakActive ? ($lastBreak['type'] ?? 'lunch') : null;

                                        // Get employee-specific refresh settings if available, otherwise fallback to company defaults
                                        $refreshTime = $employee->refresh_type ?? $setting['refreshtime'] ?? 'fixed';
                                        $isFlexible = $refreshTime === 'flexible';

                                        // Current time
                                        $currentTime = Carbon::now()->format('H:i:s');
                                        $currentHour = Carbon::now()->hour;
                                        $currentMinute = Carbon::now()->minute;
                                        $currentSecond = Carbon::now()->second;

                                        // Calculate allowed break seconds using employee settings first
                                        $allowedLunchSeconds = $isFlexible
                                            ? intval($employee->lunch_minutes ?? $setting['lunch_minutes'] ?? 30) * 60
                                            : intval(strtotime($employee->lunch_end ?? $setting['lunch_end'] ?? '13:00:00') - strtotime($employee->lunch_start ?? $setting['lunch_start'] ?? '12:00:00'));

                                        $allowedTeaSeconds = $isFlexible
                                            ? intval($employee->tea_minutes ?? $setting['tea_minutes'] ?? 15) * 60
                                            : intval(strtotime($employee->tea_end ?? $setting['tea_end'] ?? '16:00:00') - strtotime($employee->tea_start ?? $setting['tea_start'] ?? '15:30:00'));

                                        // For fixed mode: check if current time is within lunch/tea window
                                        $lunchStartTime = $employee->lunch_start ?? $setting['lunch_start'] ?? '12:00:00';
                                        $lunchEndTime = $employee->lunch_end ?? $setting['lunch_end'] ?? '13:00:00';
                                        $teaStartTime = $employee->tea_start ?? $setting['tea_start'] ?? '15:30:00';
                                        $teaEndTime = $employee->tea_end ?? $setting['tea_end'] ?? '16:00:00';

                                        $reference = '1970-01-01 ';
                                        $currentSeconds = strtotime($reference . $currentTime);
                                        $lunchStartSeconds = strtotime($reference . $lunchStartTime);
                                        $lunchEndSeconds = strtotime($reference . $lunchEndTime);
                                        $teaStartSeconds = strtotime($reference . $teaStartTime);
                                        $teaEndSeconds = strtotime($reference . $teaEndTime);

                                        // Check if within lunch window
                                        $lunchWithinWindow = true;
                                        $teaWithinWindow = true;

                                        if (!$isFlexible) {
                                            // Fixed mode: check if current time is within window
                                            if ($lunchEndSeconds < $lunchStartSeconds) {
                                                $lunchEndSeconds += 86400;
                                                if ($currentSeconds < $lunchStartSeconds) {
                                                    $currentSeconds += 86400;
                                                }
                                            }
                                            $lunchWithinWindow = $currentSeconds >= $lunchStartSeconds && $currentSeconds <= $lunchEndSeconds;

                                            // Reset for tea calculation
                                            $currentSeconds = strtotime($reference . $currentTime);
                                            if ($teaEndSeconds < $teaStartSeconds) {
                                                $teaEndSeconds += 86400;
                                                if ($currentSeconds < $teaStartSeconds) {
                                                    $currentSeconds += 86400;
                                                }
                                            }
                                            $teaWithinWindow = $currentSeconds >= $teaStartSeconds && $currentSeconds <= $teaEndSeconds;
                                        }

                                        // Calculate used break seconds from completed breaks (flexible mode enforcement)
                                        $usedLunchSeconds = 0;
                                        $usedTeaSeconds = 0;
                                        $todayDate = date('Y-m-d');
                                        foreach ($breaks as $b) {
                                            if (!empty($b['start']) && !empty($b['end'])) {
                                                $bSeconds = strtotime($todayDate . ' ' . $b['end']) - strtotime($todayDate . ' ' . $b['start']);
                                                if (strtolower($b['type'] ?? '') === 'lunch') {
                                                    $usedLunchSeconds += max(0, $bSeconds);
                                                } elseif (strtolower($b['type'] ?? '') === 'tea') {
                                                    $usedTeaSeconds += max(0, $bSeconds);
                                                }
                                            }
                                        }

                                        // Flexible mode: disable if quota fully used
                                        $lunchQuotaExhausted = $isFlexible && $usedLunchSeconds >= $allowedLunchSeconds;
                                        $teaQuotaExhausted   = $isFlexible && $usedTeaSeconds >= $allowedTeaSeconds;

                                        // Human-readable helpers
                                        $allowedLunchMin = round($allowedLunchSeconds / 60);
                                        $allowedTeaMin   = round($allowedTeaSeconds / 60);
                                        $usedLunchMin    = floor($usedLunchSeconds / 60);
                                        $usedTeaMin      = floor($usedTeaSeconds / 60);
                                        $remainLunchMin  = max(0, $allowedLunchMin - $usedLunchMin);
                                        $remainTeaMin    = max(0, $allowedTeaMin - $usedTeaMin);
                                    @endphp

                                    @if (!$breakActive)
                                        <div class="d-flex gap-2 flex-wrap align-items-start">
                                            {{-- Lunch In --}}
                                            <div class="text-center">
                                                {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post']) }}
                                                    <input type="hidden" name="break" value="1">
                                                    <input type="hidden" name="break_type" value="lunch">
                                                    @if ($lunchQuotaExhausted)
                                                        <button type="button" id="lunch_in" class="btn btn-warning" disabled
                                                            title="{{ __('Lunch quota exhausted') }}. {{ __('Allowed') }}: {{ $allowedLunchMin }}{{ __('min') }}, {{ __('Used') }}: {{ $usedLunchMin }}{{ __('min') }}">
                                                            {{ __('Lunch In') }}
                                                        </button>
                                                    @elseif (!$isFlexible && !$lunchWithinWindow)
                                                        <button type="button" id="lunch_in" class="btn btn-warning" disabled
                                                            title="{{ __('Lunch break not within configured time') }} ({{ $lunchStartTime }} - {{ $lunchEndTime }})">
                                                            {{ __('Lunch In') }}
                                                        </button>
                                                    @else
                                                        <button type="submit" id="lunch_in" class="btn btn-warning">{{ __('Lunch In') }}</button>
                                                    @endif
                                                {{ Form::close() }}
                                                @if ($isFlexible)
                                                    <small class="d-block text-muted mt-1" style="font-size:11px;">
                                                        {{ __('Allowed') }}: {{ $allowedLunchMin }}{{ __('min') }}<br>
                                                        {{ __('Used') }}: {{ $usedLunchMin }}{{ __('min') }}
                                                        @if ($lunchQuotaExhausted)
                                                            <br><span class="text-danger fw-bold">{{ __('Quota full') }}</span>
                                                        @else
                                                            <br><span class="text-success">{{ __('Left') }}: {{ $remainLunchMin }}{{ __('min') }}</span>
                                                        @endif
                                                    </small>
                                                @else
                                                    <small class="d-block text-muted mt-1" style="font-size:11px;">
                                                        {{-- {{ $lunchStartTime }} - {{ $lunchEndTime }}<br> --}}
                                                        @if ($lunchWithinWindow)
                                                            <span class="text-success"> {{ __('Lunch break is within the allowed time range') }}</span>
                                                        @else
                                                            <span class="text-danger">{{ __('Lunch break must be between :start and :end', [ 'start' => formatTime12h($lunchStartTime), 'end'   => formatTime12h($lunchEndTime) ]) }}</span>
                                                        @endif
                                                    </small>
                                                @endif
                                            </div>

                                            {{-- Tea In --}}
                                            <div class="text-center">
                                                {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post']) }}
                                                    <input type="hidden" name="break" value="1">
                                                    <input type="hidden" name="break_type" value="tea">
                                                    @if ($teaQuotaExhausted)
                                                        <button type="button" id="tea_in" class="btn btn-info" disabled
                                                            title="{{ __('Tea quota exhausted') }}. {{ __('Allowed') }}: {{ $allowedTeaMin }}{{ __('min') }}, {{ __('Used') }}: {{ $usedTeaMin }}{{ __('min') }}">
                                                            {{ __('Tea In') }}
                                                        </button>
                                                    @elseif (!$isFlexible && !$teaWithinWindow)
                                                        <button type="button" id="tea_in" class="btn btn-info" disabled
                                                            title="{{ __('Tea break not within configured time') }} ({{ $teaStartTime }} - {{ $teaEndTime }})">
                                                            {{ __('Tea In') }}
                                                        </button>
                                                    @else
                                                        <button type="submit" id="tea_in" class="btn btn-info">{{ __('Tea In') }}</button>
                                                    @endif
                                                {{ Form::close() }}
                                                @if ($isFlexible)
                                                    <small class="d-block text-muted mt-1" style="font-size:11px;">
                                                        {{ __('Allowed') }}: {{ $allowedTeaMin }}{{ __('min') }}<br>
                                                        {{ __('Used') }}: {{ $usedTeaMin }}{{ __('min') }}
                                                        @if ($teaQuotaExhausted)
                                                            <br><span class="text-danger fw-bold">{{ __('Quota full') }}</span>
                                                        @else
                                                            <br><span class="text-success">{{ __('Left') }}: {{ $remainTeaMin }}{{ __('min') }}</span>
                                                        @endif
                                                    </small>
                                                @else
                                                    <small class="d-block text-muted mt-1" style="font-size:11px;">
                                                        {{ formatTime12h($teaStartTime) }} - {{ formatTime12h($teaEndTime) }}<br>
                                                        @if ($teaWithinWindow)
                                                            <span class="text-success">{{ __('Tea break is within the allowed time range') }}</span>
                                                        @else
                                                            <span class="text-danger">{{ __('Tea break must be between :start and :end', [ 'start' => formatTime12h($teaStartTime), 'end'   => formatTime12h($teaEndTime) ]) }}</span>
                                                        @endif
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $allowedSeconds = $breakType === 'tea' ? $allowedTeaSeconds : $allowedLunchSeconds;
                                            $date = date('Y-m-d');
                                            $previousBreakSeconds = 0;
                                            foreach ($breaks as $index => $b) {
                                                if ($index === count($breaks) - 1 && ($b['end'] ?? null) === null) {
                                                    continue;
                                                }
                                                if (($b['type'] ?? '') === $breakType && !empty($b['start']) && !empty($b['end'])) {
                                                    $previousBreakSeconds += strtotime($date . ' ' . $b['end']) - strtotime($date . ' ' . $b['start']);
                                                }
                                            }
                                            $breakWindowStart = $breakType === 'tea' ? $teaStartTime : $lunchStartTime;
                                            $breakWindowEnd   = $breakType === 'tea' ? $teaEndTime : $lunchEndTime;
                                        @endphp
                                        {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post']) }}
                                            <input type="hidden" name="break" value="1">
                                            <input type="hidden" name="break_type" value="{{ $breakType }}">
                                            <input type="hidden" name="break_reason" class="break-reason-input" value="">
                                            <button type="button" id="break_end_request"
                                                data-break-type="{{ $breakType }}"
                                                data-break-start="{{ $lastBreak['start'] ?? '' }}"
                                                data-break-mode="{{ $refreshTime }}"
                                                data-break-end="{{ $breakWindowEnd }}"
                                                data-break-window-start="{{ $breakWindowStart }}"
                                                data-previous-break-seconds="{{ $previousBreakSeconds }}"
                                                data-allowed-seconds="{{ $allowedSeconds }}"
                                                class="btn btn-success break-end-button">
                                                {{ $breakType === 'tea' ? __('Tea Out') : __('Lunch Out') }}
                                            </button>
                                        {{ Form::close() }}
                                        @if (!$isFlexible)
                                            <small class="d-block text-muted mt-1" style="font-size:11px;">
                                                {{ __('Window') }}: {{ formatTime12h($breakWindowStart) }} - {{ formatTime12h($breakWindowEnd) }}
                                                @if ($breakType === 'lunch')
                                                    @if ($lunchWithinWindow)
                                                        <br><span class="text-success">{{ __('Lunch break is within the allowed time range') }}</span>
                                                    @else
                                                        <br><span class="text-danger">{{ __('Lunch break must be between :start and :end', [ 'start' => formatTime12h($lunchStartTime), 'end' => formatTime12h($lunchEndTime) ]) }}</span>
                                                    @endif
                                                @else
                                                    @if ($teaWithinWindow)
                                                        <br><span class="text-success">{{ __('Tea break is within the allowed time range') }}</span>
                                                    @else
                                                        <br><span class="text-danger">{{ __('Tea break must be between :start and :end', [ 'start' => formatTime12h($teaStartTime), 'end' => formatTime12h($teaEndTime) ]) }}</span>
                                                    @endif
                                                @endif
                                            </small>
                                        @endif
                                        @if (!empty($lastBreak['reason']))
                                            <div class="alert alert-info mt-2 p-2">
                                                <strong>{{ $breakType === 'tea' ? __('Extended Tea Reason') : __('Extended Lunch Reason') }}:</strong>
                                                <span>{{ $lastBreak['reason'] }}</span>
                                            </div>
                                        @endif
                                    @endif
                                @elseif (!empty($attendanceRequest) && $attendanceRequest->status == "pending" && $attendanceRequest->type !== 'clock_out' && is_null($attendanceRequest->clock_out))
                                    @php
                                        $breaks = $attendanceRequest->breaks ? json_decode($attendanceRequest->breaks, true) : [];
                                        $lastBreak = !empty($breaks) ? end($breaks) : null;
                                        $breakActive = $lastBreak && ($lastBreak['end'] ?? null) === null;
                                        $breakType = $breakActive ? ($lastBreak['type'] ?? 'lunch') : null;

                                        $companyShifts = json_decode($setting['company_shifts'] ?? '[]', true);
                                        $defaultCompanyShift = $companyShifts[0] ?? [];
                                        // Get employee-specific refresh settings if available, otherwise fallback to company defaults
                                        $refreshTime = $employee->refresh_type ?? $defaultCompanyShift['type'] ?? $setting['refreshtime'] ?? 'fixed';
                                        $isFlexible = $refreshTime === 'flexible';

                                        // Current time
                                        $currentTime = Carbon::now()->format('H:i:s');

                                        // Calculate allowed break seconds using employee settings first
                                        $allowedLunchSeconds = $isFlexible
                                            ? intval($employee->lunch_minutes ?? $defaultCompanyShift['lunch_minutes'] ?? $setting['lunch_minutes'] ?? 30) * 60
                                            : intval(strtotime($employee->lunch_end ?? $defaultCompanyShift['lunch_end'] ?? $setting['lunch_end'] ?? '13:00:00') - strtotime($employee->lunch_start ?? $defaultCompanyShift['lunch_start'] ?? $setting['lunch_start'] ?? '12:00:00'));

                                        $allowedTeaSeconds = $isFlexible
                                            ? intval($employee->tea_minutes ?? $defaultCompanyShift['tea_minutes'] ?? $setting['tea_minutes'] ?? 15) * 60
                                            : intval(strtotime($employee->tea_end ?? $defaultCompanyShift['tea_end'] ?? $setting['tea_end'] ?? '16:00:00') - strtotime($employee->tea_start ?? $defaultCompanyShift['tea_start'] ?? $setting['tea_start'] ?? '15:30:00'));

                                        // For fixed mode: check if current time is within lunch/tea window
                                        $lunchStartTime = $employee->lunch_start ?? $defaultCompanyShift['lunch_start'] ?? $setting['lunch_start'] ?? '12:00:00';
                                        $lunchEndTime = $employee->lunch_end ?? $defaultCompanyShift['lunch_end'] ?? $setting['lunch_end'] ?? '13:00:00';
                                        $teaStartTime = $employee->tea_start ?? $defaultCompanyShift['tea_start'] ?? $setting['tea_start'] ?? '15:30:00';
                                        $teaEndTime = $employee->tea_end ?? $setting['tea_end'] ?? '16:00:00';

                                        $reference = '1970-01-01 ';
                                        $currentSeconds = strtotime($reference . $currentTime);
                                        $lunchStartSeconds = strtotime($reference . $lunchStartTime);
                                        $lunchEndSeconds = strtotime($reference . $lunchEndTime);
                                        $teaStartSeconds = strtotime($reference . $teaStartTime);
                                        $teaEndSeconds = strtotime($reference . $teaEndTime);

                                        // Check if within lunch window
                                        $lunchWithinWindow = true;
                                        $teaWithinWindow = true;

                                        if (!$isFlexible) {
                                            // Fixed mode: check if current time is within window
                                            if ($lunchEndSeconds < $lunchStartSeconds) {
                                                $lunchEndSeconds += 86400;
                                                if ($currentSeconds < $lunchStartSeconds) {
                                                    $currentSeconds += 86400;
                                                }
                                            }
                                            $lunchWithinWindow = $currentSeconds >= $lunchStartSeconds && $currentSeconds <= $lunchEndSeconds;

                                            // Reset for tea calculation
                                            $currentSeconds = strtotime($reference . $currentTime);
                                            if ($teaEndSeconds < $teaStartSeconds) {
                                                $teaEndSeconds += 86400;
                                                if ($currentSeconds < $teaStartSeconds) {
                                                    $currentSeconds += 86400;
                                                }
                                            }
                                            $teaWithinWindow = $currentSeconds >= $teaStartSeconds && $currentSeconds <= $teaEndSeconds;
                                        }

                                        // Calculate used break seconds from completed breaks (flexible mode enforcement)
                                        $usedLunchSeconds = 0;
                                        $usedTeaSeconds = 0;
                                        $todayDate = date('Y-m-d');
                                        foreach ($breaks as $b) {
                                            if (!empty($b['start']) && !empty($b['end'])) {
                                                $bSeconds = strtotime($todayDate . ' ' . $b['end']) - strtotime($todayDate . ' ' . $b['start']);
                                                if (strtolower($b['type'] ?? '') === 'lunch') {
                                                    $usedLunchSeconds += max(0, $bSeconds);
                                                } elseif (strtolower($b['type'] ?? '') === 'tea') {
                                                    $usedTeaSeconds += max(0, $bSeconds);
                                                }
                                            }
                                        }

                                        // Flexible mode: disable if quota fully used
                                        $lunchQuotaExhausted = $isFlexible && $usedLunchSeconds >= $allowedLunchSeconds;
                                        $teaQuotaExhausted   = $isFlexible && $usedTeaSeconds >= $allowedTeaSeconds;

                                        // Human-readable helpers
                                        $allowedLunchMin = round($allowedLunchSeconds / 60);
                                        $allowedTeaMin   = round($allowedTeaSeconds / 60);
                                        $usedLunchMin    = floor($usedLunchSeconds / 60);
                                        $usedTeaMin      = floor($usedTeaSeconds / 60);
                                        $remainLunchMin  = max(0, $allowedLunchMin - $usedLunchMin);
                                        $remainTeaMin    = max(0, $allowedTeaMin - $usedTeaMin);
                                    @endphp

                                    @if (!$breakActive)
                                        <div class="d-flex gap-2 flex-wrap align-items-start">
                                            {{-- Lunch In --}}
                                            <div class="text-center">
                                                {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post']) }}
                                                    <input type="hidden" name="break" value="1">
                                                    <input type="hidden" name="break_type" value="lunch">
                                                    @if ($lunchQuotaExhausted)
                                                        <button type="button" id="lunch_in_request" class="btn btn-warning" disabled
                                                            title="{{ __('Lunch quota exhausted') }}. {{ __('Allowed') }}: {{ $allowedLunchMin }}{{ __('min') }}, {{ __('Used') }}: {{ $usedLunchMin }}{{ __('min') }}">
                                                            {{ __('Lunch In') }}
                                                        </button>
                                                    @elseif (!$isFlexible && !$lunchWithinWindow)
                                                        <button type="button" id="lunch_in_request" class="btn btn-warning" disabled
                                                            title="{{ __('Lunch break not within configured time') }} ({{ $lunchStartTime }} - {{ $lunchEndTime }})">
                                                            {{ __('Lunch In') }}
                                                        </button>
                                                    @else
                                                        <button type="submit" id="lunch_in_request" class="btn btn-warning">{{ __('Lunch In') }}</button>
                                                    @endif
                                                {{ Form::close() }}
                                                @if ($isFlexible)
                                                    <small class="d-block text-muted mt-1" style="font-size:11px;">
                                                        {{ __('Allowed') }}: {{ $allowedLunchMin }}{{ __('min') }}<br>
                                                        {{ __('Used') }}: {{ $usedLunchMin }}{{ __('min') }}
                                                        @if ($lunchQuotaExhausted)
                                                            <br><span class="text-danger fw-bold">{{ __('Quota full') }}</span>
                                                        @else
                                                            <br><span class="text-success">{{ __('Left') }}: {{ $remainLunchMin }}{{ __('min') }}</span>
                                                        @endif
                                                    </small>
                                                @else
                                                    <small class="d-block text-muted mt-1" style="font-size:11px;">
                                                        {{-- {{ formatTime12h($lunchStartTime) }} - {{ formatTime12h($lunchEndTime) }}<br> --}}
                                                        @if ($lunchWithinWindow)
                                                            <span class="text-success">{{ __('Lunch break is within the allowed time range') }}</span>
                                                        @else
                                                            <span class="text-danger">{{ __('Lunch break must be between :start and :end', [ 'start' => formatTime12h($lunchStartTime), 'end' => formatTime12h($lunchEndTime) ]) }}</span>
                                                        @endif
                                                    </small>
                                                @endif
                                            </div>

                                            {{-- Tea In --}}
                                            <div class="text-center">
                                                {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post']) }}
                                                    <input type="hidden" name="break" value="1">
                                                    <input type="hidden" name="break_type" value="tea">
                                                    @if ($teaQuotaExhausted)
                                                        <button type="button" id="tea_in_request" class="btn btn-info" disabled
                                                            title="{{ __('Tea quota exhausted') }}. {{ __('Allowed') }}: {{ $allowedTeaMin }}{{ __('min') }}, {{ __('Used') }}: {{ $usedTeaMin }}{{ __('min') }}">
                                                            {{ __('Tea In') }}
                                                        </button>
                                                    @elseif (!$isFlexible && !$teaWithinWindow)
                                                        <button type="button" id="tea_in_request" class="btn btn-info" disabled
                                                            title="{{ __('Tea break must be between :start and :end', [ 'start' => formatTime12h($teaStartTime), 'end' => formatTime12h($teaEndTime) ]) }}">
                                                            {{ __('Tea In') }}
                                                        </button>
                                                    @else
                                                        <button type="submit" id="tea_in_request" class="btn btn-info">{{ __('Tea In') }}</button>
                                                    @endif
                                                {{ Form::close() }}
                                                @if ($isFlexible)
                                                    <small class="d-block text-muted mt-1" style="font-size:11px;">
                                                        {{ __('Allowed') }}: {{ $allowedTeaMin }}{{ __('min') }}<br>
                                                        {{ __('Used') }}: {{ $usedTeaMin }}{{ __('min') }}
                                                        @if ($teaQuotaExhausted)
                                                            <br><span class="text-danger fw-bold">{{ __('Quota full') }}</span>
                                                        @else
                                                            <br><span class="text-success">{{ __('Left') }}: {{ $remainTeaMin }}{{ __('min') }}</span>
                                                        @endif
                                                    </small>
                                                @else
                                                    <small class="d-block text-muted mt-1" style="font-size:11px;">
                                                        {{-- {{ formatTime12h($teaStartTime) }} - {{ formatTime12h($teaEndTime) }}<br> --}}
                                                        @if ($teaWithinWindow)
                                                            <span class="text-success">{{ __('Tea break is within the allowed time range') }}</span>
                                                        @else
                                                            <span class="text-danger">{{ __('Tea break must be between :start and :end', [ 'start' => formatTime12h($teaStartTime), 'end' => formatTime12h($teaEndTime) ]) }}</span>
                                                        @endif
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $allowedSeconds = $breakType === 'tea' ? $allowedTeaSeconds : $allowedLunchSeconds;
                                            $date = date('Y-m-d');
                                            $previousBreakSeconds = 0;
                                            foreach ($breaks as $index => $b) {
                                                if ($index === count($breaks) - 1 && ($b['end'] ?? null) === null) {
                                                    continue;
                                                }
                                                if (($b['type'] ?? '') === $breakType && !empty($b['start']) && !empty($b['end'])) {
                                                    $previousBreakSeconds += strtotime($date . ' ' . $b['end']) - strtotime($date . ' ' . $b['start']);
                                                }
                                            }
                                            $breakWindowStart = $breakType === 'tea' ? $teaStartTime : $lunchStartTime;
                                            $breakWindowEnd   = $breakType === 'tea' ? $teaEndTime : $lunchEndTime;
                                        @endphp
                                        {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post']) }}
                                            <input type="hidden" name="break" value="1">
                                            <input type="hidden" name="break_type" value="{{ $breakType }}">
                                            <input type="hidden" name="break_reason" class="break-reason-input" value="">
                                            <button type="button" id="break_end_request"
                                                data-break-type="{{ $breakType }}"
                                                data-break-start="{{ $lastBreak['start'] ?? '' }}"
                                                data-break-mode="{{ $refreshTime }}"
                                                data-break-end="{{ $breakWindowEnd }}"
                                                data-break-window-start="{{ $breakWindowStart }}"
                                                data-previous-break-seconds="{{ $previousBreakSeconds }}"
                                                data-allowed-seconds="{{ $allowedSeconds }}"
                                                class="btn btn-success break-end-button">
                                                {{ $breakType === 'tea' ? __('Tea Out') : __('Lunch Out') }}
                                            </button>
                                        {{ Form::close() }}
                                        @if (!$isFlexible)
                                            <small class="d-block text-muted mt-1" style="font-size:11px;">
                                                {{-- {{ __('Window') }}: {{ formatTime12h($breakWindowStart) }} - {{ formatTime12h($breakWindowEnd) }} --}}
                                                @if ($breakType === 'lunch')
                                                    @if ($lunchWithinWindow)
                                                        <br><span class="text-success">{{ __('Lunch break is within the allowed time range') }}</span>
                                                    @else
                                                        <br><span class="text-danger">{{ __('Lunch break must be between :start and :end', [ 'start' => formatTime12h($lunchStartTime), 'end' => formatTime12h($lunchEndTime) ]) }}</span>
                                                    @endif
                                                @else
                                                    @if ($teaWithinWindow)
                                                        <br><span class="text-success">{{ __('Tea break is within the allowed time range') }}</span>
                                                    @else
                                                        <br><span class="text-danger">{{ __('Tea break must be between :start and :end', [ 'start' => formatTime12h($teaStartTime), 'end' => formatTime12h($teaEndTime) ]) }}</span>
                                                    @endif
                                                @endif
                                            </small>
                                        @endif
                                        @if (!empty($lastBreak['reason']))
                                            <div class="alert alert-info mt-2 p-2">
                                                <strong>{{ $breakType === 'tea' ? __('Extended Tea Reason') : __('Extended Lunch Reason') }}:</strong>
                                                <span>{{ $lastBreak['reason'] }}</span>
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            </div>

                            {{-- Clock Out Section --}}
                            <div class="col-md-4">
                                @if ($canClockOut)
                                    @php
                                        // Determine if we're in the allowed clock-out window
                                        $inAllowedWindow = !$clockOutEarly && !$clockOutLate;
                                    @endphp

                                    @if (!$inAllowedWindow)
                                        {{-- SCENARIO 1 & 2: Outside allowed window (too early OR too late) - Orange button with request
                                        --}}
                                        @if (isset($attendanceRequest) && $attendanceRequest->type == 'clock_in')
                                            {{-- User has pending clock-in request --}}
                                            {{-- @if (is_null($attendanceRequest->clock_out)) --}}
                                                {{ Form::open(['route' => 'attendance-requests.store', 'method' => 'post']) }}
                                                <input type="hidden" name="type" value="clock_out">
                                                <button type="submit" class="btn btn-warning clock-action" data-action="clock_out"
                                                    title="Request permission to clock out {{ $clockOutEarly ? 'early' : 'late' }}">
                                                    {{ __('Clock Out') }}
                                                </button>
                                                {{ Form::close() }}
                                            {{-- @else
                                                <button type="button" class="btn btn-secondary" disabled
                                                    title="Your clock out request is pending">
                                                    {{ __('Pending') }}
                                                </button>
                                            @endif --}}
                                        @elseif(!$attendanceRequest || $requestStatus == 'approved')
                                            {{-- No request or approved request --}}
                                            {{ Form::open(['route' => 'attendance-requests.store', 'method' => 'post']) }}
                                            <input type="hidden" name="type" value="clock_out">
                                            <button type="submit" class="btn btn-warning clock-action" data-action="clock_out"
                                                title="Request permission to clock out {{ $clockOutEarly ? 'early' : 'late' }}">
                                                {{ __('Clock Out') }}
                                            </button>
                                            {{ Form::close() }}
                                        @elseif($requestStatus === 'pending')
                                            <button type="button" class="btn btn-secondary" disabled
                                                title="Your clock out request is pending">
                                                {{ __('Pending') }}
                                            </button>
                                        @elseif($requestStatus === 'declined')
                                            <button type="button" class="btn btn-danger" disabled
                                                title="Your clock out request was declined">
                                                {{ __('Declined') }}
                                            </button>
                                        @endif
                                    @else
                                        {{-- SCENARIO 3: Within allowed window - Red button for normal clock out --}}
                                        @if (isset($attendanceRequest) && $attendanceRequest->type == 'clock_in')
                                            {{-- User clocked in late/early via request --}}
                                            @if (is_null($attendanceRequest->clock_out))
                                                @if ($canClockOut)
                                                    {{ Form::model($attendanceRequest, ['route' => ['attendancerequests.clock_out', $attendanceRequest->id], 'method' => 'PUT']) }}
                                                        <button type="submit" class="btn btn-danger clock-action" data-action="clock_out"
                                                            title="Clock out normally">
                                                            {{ __('CLOCK OUT') }}
                                                        </button>
                                                    {{ Form::close() }}
                                                @else
                                                    {{ Form::model($attendanceRequest, ['route' => ['attendancerequests.clock_out', $attendanceRequest->id], 'method' => 'PUT']) }}
                                                    <button type="submit" class="btn btn-danger clock-action" data-action="clock_out"
                                                        title="Clock out normally">
                                                        {{ __('CLOCK OUT') }}
                                                    </button>
                                                    {{ Form::close() }}
                                                @endif
                                            @else
                                                <button type="button" class="btn btn-secondary" disabled
                                                    title="Your clock out request is pending">
                                                    {{ __('Pending') }}
                                                </button>
                                            @endif
                                        @elseif($employeeAttendance)
                                            {{-- User clocked in normally --}}
                                            {{ Form::model($employeeAttendance, ['route' => ['attendanceemployee.update', $employeeAttendance->id], 'method' => 'PUT']) }}
                                            <button type="button" class="btn btn-danger clock-action" data-action="clock_out"
                                                title="Clock out normally">
                                                {{ __('CLOCK OUT') }}
                                            </button>
                                            {{ Form::close() }}
                                        @endif
                                    @endif
                                @else
                                    {{-- SCENARIO 4: Already clocked out --}}
                                    <button type="button" class="btn btn-danger disabled" disabled>
                                        {{ __('CLOCK OUT') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (\Auth::user()->type == 'employee')
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-4 col-12">
                                <h5 class="mb-0">{{ __('Calendar') }}</h5>
                                <input type="hidden" id="path_admin" value="{{ url('/') }}">
                            </div>
                            <div class="col-md-8 col-12 mt-2 mt-md-0 d-flex align-items-center justify-content-end gap-2 flex-wrap">
                                @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
                                    <select class="form-control me-2" name="calender_type" id="calender_type"
                                        style="width: 145px; display:inline-block;" onchange="get_data()">
                                        <option value="google_calender">{{ __('Google Calendar') }}</option>
                                        <option value="local_calender" selected="true">
                                            {{ __('Local Calendar') }}
                                        </option>
                                    </select>
                                @endif
                                <div class="calendar-controls-wrapper d-inline-flex align-items-center gap-1">
                                    <div class="btn-group btn-group-sm border rounded bg-white shadow-sm" role="group" aria-label="Calendar Zoom Controls">
                                        <button type="button" class="btn btn-outline-secondary btn-cal-zoom-out py-1 px-2" onclick="dashboardCalZoomOut(this, event)" title="{{ __('Zoom Out') }}">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-cal-zoom-reset fw-bold py-1 px-2 text-primary" onclick="dashboardCalZoomReset(this, event)" title="{{ __('Reset Zoom') }}">
                                            <span class="cal-zoom-level">100%</span>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-cal-zoom-in py-1 px-2" onclick="dashboardCalZoomIn(this, event)" title="{{ __('Zoom In') }}">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-cal-fit-height py-1 px-2" onclick="dashboardCalVerticalResize(this, event)" title="{{ __('Vertical Resize / Scroll Toggle') }}">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="7 8 12 3 17 8"></polyline>
                                                <polyline points="7 16 12 21 17 16"></polyline>
                                                <line x1="12" y1="3" x2="12" y2="21"></line>
                                            </svg>
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary border shadow-sm btn-cal-card-fullscreen py-1 px-2 me-1" onclick="dashboardCalExpandDashboard(this, event)" title="{{ __('Dashboard Fullscreen View') }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="15 3 21 3 21 9"></polyline>
                                            <polyline points="9 21 3 21 3 15"></polyline>
                                            <line x1="21" y1="3" x2="14" y2="10"></line>
                                            <line x1="3" y1="21" x2="10" y2="14"></line>
                                        </svg>
                                    </button>
                                     <button type="button" class="btn btn-sm btn-outline-secondary border shadow-sm btn-cal-page-fullscreen py-1 px-2 me-1" onclick="dashboardCalBrowserFullscreen(this, event)" title="{{ __('Browser Fullscreen Mode') }}">
                                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                             <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                                         </svg>
                                     </button>
                                     <button type="button" class="btn btn-sm btn-outline-secondary border shadow-sm btn-cal-reset-all py-1 px-2" onclick="dashboardCalResetAll(this, event)" title="{{ __('Reset Real Position') }}">
                                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                             <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                             <path d="M3 3v5h5"></path>
                                         </svg>
                                     </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body card-body-calendar overflow-auto">
                        <div class="calendar-zoom-wrapper">
                            <div id='event_calendar' class='calendar'></div>
                        </div>
                    </div>
                </div>
            </div>
        @if (\Auth::user()->type != 'employee' && !empty($liveStatusData))
            <div class="col-xxl-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-activity text-primary fs-2 me-2"></i>
                            <div>
                                <h5 class="mb-0 text-dark fw-bold">{{ __('Real-Time Employee Status & Live Break Monitor') }}</h5>
                                <small class="text-muted">{{ __('Live status of today\'s employee attendance & breaks') }}</small>
                            </div>
                        </div>
                        <span class="badge bg-primary px-3 py-2 rounded-pill fs-7">
                            <i class="ti ti-clock me-1"></i> {{ date('d M Y') }}
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <!-- Stat Summary Counters -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded border text-center" style="background-color: #FFF8E1; border-color: #FFE082 !important;">
                                    <span class="d-block text-warning fw-bold fs-3">{{ count($liveStatusData['on_break']) }}</span>
                                    <span class="text-dark fw-bold fs-7"><i class="ti ti-cup me-1 text-warning"></i> {{ __('On Break') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded border text-center" style="background-color: #E8F5E9; border-color: #A5D6A7 !important;">
                                    <span class="d-block text-success fw-bold fs-3">{{ count($liveStatusData['working']) }}</span>
                                    <span class="text-dark fw-bold fs-7"><i class="ti ti-user-check me-1 text-success"></i> {{ __('Clocked In / Working') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded border text-center" style="background-color: #FFEBEE; border-color: #FFCDD2 !important;">
                                    <span class="d-block text-danger fw-bold fs-3">{{ count($liveStatusData['clocked_out']) }}</span>
                                    <span class="text-dark fw-bold fs-7"><i class="ti ti-user-x me-1 text-danger"></i> {{ __('Clocked Out') }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded border text-center" style="background-color: #F5F5F5; border-color: #E0E0E0 !important;">
                                    <span class="d-block text-secondary fw-bold fs-3">{{ count($liveStatusData['not_clocked_in']) }}</span>
                                    <span class="text-dark fw-bold fs-7"><i class="ti ti-user-minus me-1 text-secondary"></i> {{ __('Not Clocked In') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status Filter Tabs -->
                        <ul class="nav nav-pills mb-3" id="live-attendance-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-item nav-link active px-3 py-2 me-2" id="pills-onbreak-tab" data-bs-toggle="pill" data-bs-target="#pills-onbreak" type="button" role="tab">
                                    <i class="ti ti-cup me-1"></i> {{ __('On Break') }} ({{ count($liveStatusData['on_break']) }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-item nav-link px-3 py-2 me-2" id="pills-working-tab" data-bs-toggle="pill" data-bs-target="#pills-working" type="button" role="tab">
                                    <i class="ti ti-user-check me-1"></i> {{ __('Working') }} ({{ count($liveStatusData['working']) }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-item nav-link px-3 py-2 me-2" id="pills-clockedout-tab" data-bs-toggle="pill" data-bs-target="#pills-clockedout" type="button" role="tab">
                                    <i class="ti ti-user-x me-1"></i> {{ __('Clocked Out') }} ({{ count($liveStatusData['clocked_out']) }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-item nav-link px-3 py-2" id="pills-notclocked-tab" data-bs-toggle="pill" data-bs-target="#pills-notclocked" type="button" role="tab">
                                    <i class="ti ti-user-minus me-1"></i> {{ __('Not Clocked In') }} ({{ count($liveStatusData['not_clocked_in']) }})
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="live-attendance-tabContent">
                            <!-- Tab 1: On Break -->
                            <div class="tab-pane fade show active" id="pills-onbreak" role="tabpanel">
                                @if(!empty($liveStatusData['on_break']) && count($liveStatusData['on_break']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('Employee') }}</th>
                                                    <th>{{ __('Department / Designation') }}</th>
                                                    <th>{{ __('Break Status') }}</th>
                                                    <th>{{ __('Break Start Time') }}</th>
                                                    <th>{{ __('Clock In Time') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($liveStatusData['on_break'] as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm me-2 rounded-circle bg-warning text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                                                    {{ strtoupper(substr($item['employee']->name ?? 'E', 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0 text-dark font-weight-bold">{{ $item['employee']->name }}</h6>
                                                                    <small class="text-muted">{{ $item['employee']->id_prefix . $item['employee']->employee_id }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>{{ $item['employee']->department->name ?? '-' }}</div>
                                                            <small class="text-muted">{{ $item['employee']->designation->name ?? '-' }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                                                <i class="ti ti-cup me-1"></i> {{ $item['label'] }}
                                                            </span>
                                                        </td>
                                                        <td class="fw-bold text-dark">
                                                            {{ !empty($item['start_time']) ? formatTime12h($item['start_time']) : '-' }}
                                                        </td>
                                                        <td>
                                                            {{ !empty($item['attendance']->clock_in) ? formatTime12h($item['attendance']->clock_in) : '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-4 text-center text-muted">
                                        <i class="ti ti-check fs-1 text-success d-block mb-2"></i>
                                        <span>{{ __('No employees are currently on break.') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Tab 2: Currently Working -->
                            <div class="tab-pane fade" id="pills-working" role="tabpanel">
                                @if(!empty($liveStatusData['working']) && count($liveStatusData['working']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('Employee') }}</th>
                                                    <th>{{ __('Department / Designation') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Clock In Time') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($liveStatusData['working'] as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm me-2 rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                                                    {{ strtoupper(substr($item['employee']->name ?? 'E', 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0 text-dark font-weight-bold">{{ $item['employee']->name }}</h6>
                                                                    <small class="text-muted">{{ $item['employee']->id_prefix . $item['employee']->employee_id }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>{{ $item['employee']->department->name ?? '-' }}</div>
                                                            <small class="text-muted">{{ $item['employee']->designation->name ?? '-' }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                                                <i class="ti ti-user-check me-1"></i> {{ __('Working') }}
                                                            </span>
                                                        </td>
                                                        <td class="fw-bold text-dark">
                                                            {{ !empty($item['clock_in_time']) ? formatTime12h($item['clock_in_time']) : '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-4 text-center text-muted">
                                        <i class="ti ti-info-circle fs-1 text-secondary d-block mb-2"></i>
                                        <span>{{ __('No employees are currently clocked in and working.') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Tab 3: Clocked Out -->
                            <div class="tab-pane fade" id="pills-clockedout" role="tabpanel">
                                @if(!empty($liveStatusData['clocked_out']) && count($liveStatusData['clocked_out']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('Employee') }}</th>
                                                    <th>{{ __('Department / Designation') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Clock Out Time') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($liveStatusData['clocked_out'] as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm me-2 rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                                                    {{ strtoupper(substr($item['employee']->name ?? 'E', 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0 text-dark font-weight-bold">{{ $item['employee']->name }}</h6>
                                                                    <small class="text-muted">{{ $item['employee']->id_prefix . $item['employee']->employee_id }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>{{ $item['employee']->department->name ?? '-' }}</div>
                                                            <small class="text-muted">{{ $item['employee']->designation->name ?? '-' }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-danger px-3 py-2 rounded-pill">
                                                                <i class="ti ti-user-x me-1"></i> {{ __('Clocked Out') }}
                                                            </span>
                                                        </td>
                                                        <td class="fw-bold text-dark">
                                                            {{ !empty($item['clock_out_time']) ? formatTime12h($item['clock_out_time']) : '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-4 text-center text-muted">
                                        <i class="ti ti-info-circle fs-1 text-secondary d-block mb-2"></i>
                                        <span>{{ __('No employees have clocked out yet today.') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Tab 4: Not Clocked In -->
                            <div class="tab-pane fade" id="pills-notclocked" role="tabpanel">
                                @if(!empty($liveStatusData['not_clocked_in']) && count($liveStatusData['not_clocked_in']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('Employee') }}</th>
                                                    <th>{{ __('Department / Designation') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($liveStatusData['not_clocked_in'] as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm me-2 rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                                                    {{ strtoupper(substr($item['employee']->name ?? 'E', 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0 text-dark font-weight-bold">{{ $item['employee']->name }}</h6>
                                                                    <small class="text-muted">{{ $item['employee']->id_prefix . $item['employee']->employee_id }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>{{ $item['employee']->department->name ?? '-' }}</div>
                                                            <small class="text-muted">{{ $item['employee']->designation->name ?? '-' }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary px-3 py-2 rounded-pill">
                                                                <i class="ti ti-user-minus me-1"></i> {{ __('Not Clocked In') }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-4 text-center text-muted">
                                        <i class="ti ti-check-all fs-1 text-success d-block mb-2"></i>
                                        <span>{{ __('All employees have clocked in today!') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-xxl-12">

                <div class="card" style="height: 402px;">
                    <div class="card-header card-body table-border-style">
                        <h5>{{ __('Meeting schedule') }}</h5>
                    </div>
                    <div class="card-body" style="height: 320px">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Meeting title') }}</th>
                                        <th>{{ __('Meeting Date') }}</th>
                                        <th>{{ __('Meeting Time') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @foreach ($meetings as $meeting)
                                        <tr>
                                            <td>{{ $meeting->title }}</td>
                                            <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                            <td>{{ \Auth::user()->timeFormat($meeting->time) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xl-12 col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-header card-body table-border-style">
                        <h5>{{ __('Announcement List') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Start Date') }}</th>
                                        <th>{{ __('End Date') }}</th>
                                        <th>{{ __('Description') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @foreach ($announcements as $announcement)
                                        <tr>
                                            <td>{{ $announcement->title }}</td>
                                            <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                            <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>
                                            {{-- <td>{{ $announcement->description }}</td> --}}
                                            <td class="description-col" data-description="{{ $announcement->description }}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
                <div class="col-xxl-12">

                    {{-- start --}}
                    <div class="row">

                        <div class="col-lg-4 col-md-6">

                            <div class="card stats-wrapper dash-info-card">
                                <div class="card-body stats">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <div class="d-flex align-items-center">
                                                <div class="badge theme-avtar bg-primary">
                                                    <i class="ti ti-users"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <small class="text-muted">{{ __('Total') }}</small>
                                                    <h6 class="m-0"><a href="{{ route('user.index') }}">{{ __('Staff') }}</a></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto text-end">
                                            <h4 class="m-0 text-primary">{{ $countUser + $countEmployee }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">

                            <div class="card stats-wrapper dash-info-card">
                                <div class="card-body stats">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <div class="d-flex align-items-center">
                                                <div class="badge theme-avtar bg-info">
                                                    <i class="ti ti-ticket"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <small class="text-muted">{{ __('Total') }}</small>
                                                    <h6 class="m-0"><a href="{{ route('ticket.index') }}">{{ __('Ticket') }}</a>
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto text-end">
                                            <h4 class="m-0 text-info"> {{ $countTicket }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">

                            <div class="card stats-wrapper dash-info-card">
                                <div class="card-body stats">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <div class="d-flex align-items-center">
                                                <div class="badge theme-avtar bg-warning">
                                                    <i class="ti ti-wallet"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <small class="text-muted">{{ __('Total') }}</small>
                                                    <h6 class="m-0"><a
                                                            href="{{ route('accountlist.index') }}">{{ __('Account Balance') }}</a>
                                                    </h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto text-end">
                                            <h4 class="m-0 text-warning">{{ \Auth::user()->priceFormat($accountBalance) }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="col-lg-4 col-md-6">
                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-primary">
                                            <i class="ti ti-briefcase"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0"><a href="{{ route('job.index') }}">{{ __('Jobs') }}</a>
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-primary">{{ $activeJob + $inActiveJOb }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-4 col-md-6">

                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-info text-white">
                                            <svg width="40" height="40" viewBox="0 0 18 18" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M5.48849 2.90453C5.41461 2.77649 5.45845 2.61333 5.58641 2.53893C5.71437 2.46521 5.87793 2.50917 5.95181 2.63721L6.34477 3.31769C6.44805 3.49661 6.31757 3.71889 6.11337 3.71889C6.02085 3.71889 5.93093 3.67109 5.88137 3.58525L5.48849 2.90453ZM2.35537 5.77045C2.42925 5.64221 2.59273 5.59845 2.72077 5.67241L3.40137 6.06529C3.63777 6.20181 3.53937 6.56433 3.26737 6.56433C3.22193 6.56433 3.17597 6.55285 3.13389 6.52873L2.45329 6.13565C2.32533 6.06165 2.28149 5.89805 2.35537 5.77045ZM18.8274 10.184C18.8274 10.3319 18.7076 10.4515 18.5599 10.4515H17.774C17.6262 10.4515 17.5065 10.3319 17.5065 10.184C17.5065 10.0361 17.6262 9.91665 17.774 9.91665H18.5599C18.7076 9.91665 18.8274 10.0361 18.8274 10.184ZM2.22601 10.4515H1.44021C1.29249 10.4515 1.17273 10.3319 1.17273 10.184C1.17273 10.0361 1.29249 9.91665 1.44021 9.91665H2.22601C2.37377 9.91665 2.49357 10.0361 2.49357 10.184C2.49361 10.3319 2.37377 10.4515 2.22601 10.4515ZM16.5008 6.43065C16.427 6.30285 16.4707 6.13921 16.5987 6.06529L17.2794 5.67241C17.4073 5.59849 17.5709 5.64221 17.6448 5.77045C17.7186 5.89805 17.6748 6.06165 17.5469 6.13561L16.8662 6.52869C16.7401 6.60125 16.5756 6.56013 16.5008 6.43065ZM9.73249 2.41001V1.62425C9.73249 1.47617 9.85229 1.35669 10 1.35669C10.1477 1.35669 10.2675 1.47613 10.2675 1.62425V2.40997C10.2675 2.55761 10.1477 2.67753 10 2.67753C9.85233 2.67753 9.73249 2.55765 9.73249 2.41001ZM13.6554 3.31769L14.0483 2.63721C14.1221 2.50917 14.2856 2.46521 14.4137 2.53893C14.5416 2.61329 14.5855 2.77649 14.5116 2.90453L14.1186 3.58525C14.0439 3.71477 13.8791 3.75581 13.7532 3.68329C13.6253 3.60913 13.5815 3.44569 13.6554 3.31769ZM8.68325 5.02077C8.68325 4.86345 8.81133 4.73541 8.96869 4.73541H11.0314C11.1888 4.73541 11.3169 4.86345 11.3169 5.02077V5.64061H8.68325V5.02077ZM13.6213 6.59969V7.12149L10.4996 8.79373C10.1774 8.96641 9.82269 8.96641 9.50057 8.79373L6.37881 7.12149V6.59969C6.37881 6.36597 6.56901 6.17549 6.80285 6.17549H13.1972C13.431 6.17549 13.6213 6.36597 13.6213 6.59969ZM13.1972 11.1137C13.431 11.1137 13.6213 10.9235 13.6213 10.6898V7.72849L10.7521 9.26525C10.2672 9.52493 9.73281 9.52489 9.24793 9.26525L6.37877 7.72849V10.6898C6.37877 10.9235 6.56897 11.1137 6.80281 11.1137H13.1972ZM5.84373 6.59969V10.6898C5.84373 11.2183 6.27401 11.6489 6.80281 11.6489H13.1971C13.726 11.6489 14.1562 11.2183 14.1562 10.6898V6.59969C14.1562 6.07113 13.726 5.64061 13.1971 5.64061H11.8518V5.02077C11.8518 4.56841 11.4838 4.20033 11.0314 4.20033H8.96865C8.51629 4.20033 8.14825 4.56841 8.14825 5.02077V5.64061H6.80285C6.27405 5.64061 5.84373 6.07113 5.84373 6.59969ZM16.8279 14.1016L12.72 16.4735C11.631 17.1021 10.9854 17.4179 9.77305 17.5969C8.49217 17.786 7.21725 17.6886 6.03037 17.5554C5.49225 17.4952 5.00033 17.4495 4.50249 17.4139V13.9486C5.50217 13.8553 6.38149 13.8701 7.50741 14.1713C7.52997 14.1771 7.55309 14.18 7.57645 14.18H10.4745C10.9154 14.18 11.2111 14.6304 11.0448 15.0312C10.9483 15.2635 10.7244 15.4141 10.4745 15.4141H7.57645C7.42873 15.4141 7.30897 15.5336 7.30897 15.6817C7.30897 15.8293 7.42873 15.949 7.57645 15.949H10.4745C10.8622 15.949 11.2156 15.7559 11.4273 15.4423C11.5269 15.4608 11.626 15.4705 11.7236 15.471C12.1909 15.4724 12.4257 15.2943 12.75 15.1067L16.2436 13.0899C16.5226 12.9287 16.8806 13.0245 17.0418 13.3036C17.2029 13.5828 17.107 13.9407 16.8279 14.1016ZM3.96757 17.8622C3.96757 17.9981 3.85713 18.1083 3.72149 18.1083H2.51929C2.38361 18.1083 2.27321 17.9981 2.27321 17.8622V13.7752C2.27321 13.6393 2.38361 13.5291 2.51929 13.5291H3.72149C3.85717 13.5291 3.96757 13.6393 3.96757 13.7752V17.8622ZM15.9761 12.6264L12.4826 14.6433C12.1629 14.828 11.9949 14.9683 11.6188 14.9302C11.6982 14.2479 11.163 13.6452 10.4745 13.6452H7.61145C6.41165 13.3287 5.47949 13.3171 4.41633 13.4194C4.28661 13.1671 4.02401 12.9943 3.72145 12.9943H2.51929C2.08857 12.9943 1.73825 13.3445 1.73825 13.7752V17.8622C1.73825 18.2931 2.08857 18.6434 2.51929 18.6434H3.72149C4.12253 18.6434 4.45377 18.3394 4.49753 17.9498C4.97673 17.9848 5.45185 18.0292 5.97089 18.0873C7.25161 18.2308 8.53929 18.3198 9.85117 18.1261C11.1998 17.9271 11.9358 17.5441 12.9876 16.9367L17.0955 14.5648C17.63 14.2564 17.8137 13.5705 17.5051 13.0359C17.1965 12.5015 16.5106 12.3176 15.9761 12.6264Z"
                                                    fill="white" />
                                            </svg>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0"><a href="{{ route('job.index') }}">{{ __('Active Jobs') }}</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-info"> {{ $activeJob }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">

                    <div class="card stats-wrapper dash-info-card">
                        <div class="card-body stats">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="badge theme-avtar bg-warning">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M19.0909 10.9091C18.8182 10.9091 18.6364 11.0909 18.6364 11.3636V17.2727C18.6364 17.5454 18.4546 17.7272 18.1819 17.7272H1.81818C1.54545 17.7272 1.36365 17.5454 1.36365 17.2727V11.3636C1.36365 11.0909 1.18182 10.9091 0.909121 10.9091C0.636426 10.9091 0.45459 11.0909 0.45459 11.3636V17.2727C0.45459 18.0454 1.04549 18.6363 1.81822 18.6363H18.1819C18.9546 18.6363 19.5455 18.0454 19.5455 17.2727V11.3636C19.5455 11.0909 19.3637 10.9091 19.0909 10.9091Z"
                                                    fill="white" />
                                                <path
                                                    d="M18.6364 4.54541H1.36363C0.590898 4.54541 0 5.13631 0 5.90904V8.81814C0 9.45447 0.454531 9.99994 1.04547 10.1363L8.18184 11.7272V13.1817C8.18184 13.4545 8.36367 13.6363 8.63637 13.6363H11.3636C11.6364 13.6363 11.8182 13.4544 11.8182 13.1817V11.7272L18.9545 10.1363C19.5455 9.99994 20 9.45447 20 8.81811V5.909C20 5.13631 19.4091 4.54541 18.6364 4.54541ZM10.9091 12.7272H9.09094V10.909H10.9091V12.7272ZM19.0909 8.81811C19.0909 9.04537 18.9545 9.22721 18.7273 9.27264L11.8182 10.8181V10.4545C11.8182 10.1817 11.6363 9.99994 11.3636 9.99994H8.63637C8.36363 9.99994 8.18184 10.1818 8.18184 10.4545V10.8181L1.27273 9.27268C1.04547 9.22721 0.909102 9.04541 0.909102 8.81814V5.90904C0.909102 5.63631 1.09094 5.45451 1.36363 5.45451H18.6364C18.9091 5.45451 19.0909 5.63635 19.0909 5.90904V8.81811Z"
                                                    fill="white" />
                                                <path
                                                    d="M12.2727 1.36365H7.72728C6.95455 1.36365 6.36365 1.95455 6.36365 2.72728V3.18181C6.36365 3.45455 6.54548 3.63634 6.81818 3.63634C7.09087 3.63634 7.27271 3.45451 7.27271 3.18181V2.72728C7.27271 2.45455 7.45455 2.27275 7.72724 2.27275H12.2727C12.5454 2.27275 12.7272 2.45458 12.7272 2.72728V3.18181C12.7272 3.45455 12.9091 3.63634 13.1818 3.63634C13.4545 3.63634 13.6363 3.45451 13.6363 3.18181V2.72728C13.6364 1.95455 13.0455 1.36365 12.2727 1.36365Z"
                                                    fill="white" />
                                                <path
                                                    d="M5.47173 16.8372C5.28087 16.8372 5.09002 16.7644 4.94449 16.6189C4.65343 16.3278 4.65343 15.8558 4.94449 15.5644L13.7272 6.78201C14.0183 6.49096 14.4903 6.49096 14.7817 6.78201C15.0727 7.07307 15.0727 7.54514 14.7817 7.8365L5.99868 16.6189C5.85315 16.7644 5.66259 16.8372 5.47173 16.8372Z"
                                                    fill="white" />
                                                <path
                                                    d="M14.2541 16.8349C14.0633 16.8349 13.8724 16.7622 13.7269 16.6166L4.94449 7.83394C4.65343 7.54288 4.65343 7.07081 4.94449 6.77945C5.23555 6.48839 5.70762 6.48839 5.99897 6.77945L14.7817 15.5622C15.0727 15.8532 15.0727 16.3253 14.7817 16.6166C14.6359 16.7622 14.445 16.8349 14.2541 16.8349Z"
                                                    fill="white" />
                                            </svg>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0"><a href="{{ route('job.index') }}">{{ __('Inactive Jobs') }}</a></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0 text-warning">{{ $inActiveJOb }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--
            </div> --}}

            {{-- end --}}
            <div class="col-xxl-12">
                <div class="row">
                    <div class="col-xl-5">

                        <div class="card">
                            <div class="card-header card-body table-border-style pb-2">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h5>{{ __('Today\'s Employee Status') }}</h5>
                                    <span class="badge bg-primary rounded-pill px-3 py-1 fs-7" id="emp-status-total-count">{{ !empty($allEmployeeStatuses) ? count($allEmployeeStatuses) : 0 }}</span>
                                </div>

                                <!-- Live Instant Search Box -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-light border-end-0"><i class="ti ti-search text-muted"></i></span>
                                    <input type="text" id="emp-status-search-input" class="form-control bg-light border-start-0 ps-0 fs-8" placeholder="{{ __('Search employee...') }}" onkeyup="filterEmployeeStatusList()">
                                </div>

                                <!-- Filter Segmented Pills -->
                                <div class="d-flex align-items-center gap-1 overflow-auto pt-1" style="white-space: nowrap; scrollbar-width: none;">
                                    <button type="button" class="btn btn-xs rounded-pill px-3 py-1 emp-filter-btn btn-primary" data-filter="all" onclick="setEmployeeStatusFilter('all', this)">
                                        {{ __('All') }} ({{ !empty($allEmployeeStatuses) ? count($allEmployeeStatuses) : 0 }})
                                    </button>
                                    <button type="button" class="btn btn-xs rounded-pill px-3 py-1 emp-filter-btn btn-light text-dark border" data-filter="working" onclick="setEmployeeStatusFilter('working', this)">
                                        <span class="d-inline-block rounded-circle bg-success me-1" style="width: 7px; height: 7px;"></span>{{ __('Working') }} ({{ !empty($liveStatusData['working']) ? count($liveStatusData['working']) : 0 }})
                                    </button>
                                    <button type="button" class="btn btn-xs rounded-pill px-3 py-1 emp-filter-btn btn-light text-dark border" data-filter="on_break" onclick="setEmployeeStatusFilter('on_break', this)">
                                        <span class="d-inline-block rounded-circle bg-warning me-1" style="width: 7px; height: 7px;"></span>{{ __('Break') }} ({{ !empty($liveStatusData['on_break']) ? count($liveStatusData['on_break']) : 0 }})
                                    </button>
                                    <button type="button" class="btn btn-xs rounded-pill px-3 py-1 emp-filter-btn btn-light text-dark border" data-filter="clocked_out" onclick="setEmployeeStatusFilter('clocked_out', this)">
                                        <span class="d-inline-block rounded-circle bg-danger me-1" style="width: 7px; height: 7px;"></span>{{ __('Clocked Out') }} ({{ !empty($liveStatusData['clocked_out']) ? count($liveStatusData['clocked_out']) : 0 }})
                                    </button>
                                    <button type="button" class="btn btn-xs rounded-pill px-3 py-1 emp-filter-btn btn-light text-dark border" data-filter="not_clocked_in" onclick="setEmployeeStatusFilter('not_clocked_in', this)">
                                        <span class="d-inline-block rounded-circle bg-secondary me-1" style="width: 7px; height: 7px;"></span>{{ __('Not Clocked In') }} ({{ !empty($liveStatusData['not_clocked_in']) ? count($liveStatusData['not_clocked_in']) : 0 }})
                                    </button>
                                </div>
                            </div>

                            <!-- Scrollable Body with Clean Row Items -->
                            <div class="card-body" style="height: 324px; overflow-y: auto;">
                                @if(!empty($allEmployeeStatuses) && count($allEmployeeStatuses) > 0)
                                    <div class="list-group list-group-flush" id="emp-status-list">
                                        @foreach($allEmployeeStatuses as $item)
                                            @php
                                                $emp = $item['employee'] ?? null;
                                            @endphp
                                            @if($emp)
                                                <div class="list-group-item px-3 py-2 border-bottom emp-status-row bg-transparent"
                                                     data-status="{{ $item['status'] }}"
                                                     data-name="{{ strtolower($emp->name) }}"
                                                     data-dept="{{ strtolower($emp->department->name ?? '') }}"
                                                     data-label="{{ strtolower($item['label'] ?? '') }}">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center me-2">
                                                            @if($item['status'] == 'working')
                                                                <div class="avatar-sm me-3 rounded-circle text-success d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; background-color: #E8F5E9; font-size: 0.95rem;">
                                                                    {{ strtoupper(substr($emp->name ?? 'E', 0, 1)) }}
                                                                </div>
                                                            @elseif($item['status'] == 'on_break')
                                                                <div class="avatar-sm me-3 rounded-circle text-warning d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; background-color: #FFF8E1; font-size: 0.95rem;">
                                                                    {{ strtoupper(substr($emp->name ?? 'E', 0, 1)) }}
                                                                </div>
                                                            @elseif($item['status'] == 'clocked_out')
                                                                <div class="avatar-sm me-3 rounded-circle text-danger d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; background-color: #FFEBEE; font-size: 0.95rem;">
                                                                    {{ strtoupper(substr($emp->name ?? 'E', 0, 1)) }}
                                                                </div>
                                                            @else
                                                                <div class="avatar-sm me-3 rounded-circle text-secondary d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; background-color: #F5F5F5; font-size: 0.95rem;">
                                                                    {{ strtoupper(substr($emp->name ?? 'E', 0, 1)) }}
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-0 text-dark fw-bold" style="font-size: 0.92rem;">{{ $emp->name }}</h6>
                                                                <small class="text-muted" style="font-size: 0.78rem;">
                                                                    {{ !empty($emp->department) ? $emp->department->name : (!empty($emp->designation) ? $emp->designation->name : ($emp->id_prefix . $emp->employee_id)) }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="text-end">
                                                            @if($item['status'] == 'working')
                                                                <span class="badge px-3 py-1.5 rounded-pill text-success border border-success-subtle shadow-sm" style="background-color: #E8F5E9; font-size: 0.74rem;">
                                                                    <i class="ti ti-user-check me-1"></i> {{ __('Clocked In') }} ({{ !empty($item['clock_in_time']) ? formatTime12h($item['clock_in_time']) : '' }})
                                                                </span>
                                                            @elseif($item['status'] == 'on_break')
                                                                <span class="badge px-3 py-1.5 rounded-pill text-dark border border-warning-subtle shadow-sm" style="background-color: #FFF8E1; font-size: 0.74rem;">
                                                                    <i class="ti ti-cup me-1 text-warning"></i> {{ $item['label'] ?? __('On Break') }} ({{ !empty($item['start_time']) ? formatTime12h($item['start_time']) : '' }})
                                                                </span>
                                                            @elseif($item['status'] == 'clocked_out')
                                                                <span class="badge px-3 py-1.5 rounded-pill text-danger border border-danger-subtle shadow-sm" style="background-color: #FFEBEE; font-size: 0.74rem;">
                                                                    <i class="ti ti-user-x me-1"></i> {{ __('Clocked Out') }} ({{ !empty($item['clock_out_time']) ? formatTime12h($item['clock_out_time']) : '' }})
                                                                </span>
                                                            @else
                                                                <span class="badge px-3 py-1.5 rounded-pill text-muted border shadow-sm" style="background-color: #F8F9FA; font-size: 0.74rem;">
                                                                    <i class="ti ti-user-minus me-1 text-secondary"></i> {{ __('Not Clocked In') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div id="emp-status-no-results" class="text-center py-4 text-muted d-none">
                                        <i class="ti ti-search-off fs-2 text-muted d-block mb-1"></i>
                                        <small class="fw-bold">{{ __('No matching employees found.') }}</small>
                                    </div>
                                @else
                                    <div class="text-center py-5 text-muted">
                                        <i class="ti ti-users fs-1 text-secondary d-block mb-2"></i>
                                        <span class="fw-bold">{{ __('No employees found.') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <script>
                            let currentEmpStatusFilter = 'all';

                            function setEmployeeStatusFilter(filterType, btnElem) {
                                currentEmpStatusFilter = filterType;
                                document.querySelectorAll('.emp-filter-btn').forEach(btn => {
                                    btn.classList.remove('btn-primary', 'btn-success', 'btn-warning', 'btn-danger', 'btn-secondary');
                                    btn.classList.add('btn-light', 'text-dark', 'border');
                                });

                                btnElem.classList.remove('btn-light', 'text-dark', 'border');
                                if (filterType === 'all') {
                                    btnElem.classList.add('btn-primary');
                                } else if (filterType === 'working') {
                                    btnElem.classList.add('btn-success');
                                } else if (filterType === 'on_break') {
                                    btnElem.classList.add('btn-warning');
                                } else if (filterType === 'clocked_out') {
                                    btnElem.classList.add('btn-danger');
                                } else if (filterType === 'not_clocked_in') {
                                    btnElem.classList.add('btn-secondary');
                                }

                                filterEmployeeStatusList();
                            }

                            function filterEmployeeStatusList() {
                                let searchInput = document.getElementById('emp-status-search-input');
                                let query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                                let rows = document.querySelectorAll('.emp-status-row');
                                let visibleCount = 0;

                                rows.forEach(row => {
                                    let rowStatus = row.getAttribute('data-status');
                                    let rowName = row.getAttribute('data-name') || '';
                                    let rowDept = row.getAttribute('data-dept') || '';
                                    let rowLabel = row.getAttribute('data-label') || '';

                                    let statusMatch = (currentEmpStatusFilter === 'all' || rowStatus === currentEmpStatusFilter);
                                    let textMatch = (query === '' || rowName.includes(query) || rowDept.includes(query) || rowLabel.includes(query));

                                    if (statusMatch && textMatch) {
                                        row.style.display = 'block';
                                        visibleCount++;
                                    } else {
                                        row.style.display = 'none';
                                    }
                                });

                                let noResultsElem = document.getElementById('emp-status-no-results');
                                if (noResultsElem) {
                                    if (visibleCount === 0 && rows.length > 0) {
                                        noResultsElem.classList.remove('d-none');
                                    } else {
                                        noResultsElem.classList.add('d-none');
                                    }
                                }

                                let totalBadge = document.getElementById('emp-status-total-count');
                                if (totalBadge) {
                                    totalBadge.innerText = visibleCount;
                                }
                            }
                        </script>

                        <div class="card">
                            <div class="card-header card-body table-border-style">
                                <h5>{{ __('Meeting schedule') }}</h5>
                            </div>
                            <div class="card-body" style="height: 324px; overflow:auto">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Time') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            @foreach ($meetings as $meeting)
                                                <tr>
                                                    <td>{{ $meeting->title }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                                    <td>{{ \Auth::user()->timeFormat($meeting->time) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if (\Auth::user()->type == 'company')
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <h5 class="mb-0">{{ __('Storage Status') }}</h5>
                                    </div>
                                    @php
                                        $total_limit = $users ? $users->total_storage_limit : ($plan ? $plan->storage_limit : 0);
                                        $used_mb = round((float)($users->storage_limit ?? 0), 2);
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary text-white fs-6">
                                            {{ $used_mb }} MB / {{ $total_limit == -1 ? __('Unlimited') : ($total_limit . ' MB') }}
                                        </span>
                                        <a href="{{ route('plans.index', ['tab' => 'storage_addons']) }}" class="btn btn-sm btn-primary text-white d-inline-flex align-items-center shadow-sm" data-bs-toggle="tooltip" title="{{ __('Buy Storage Addons') }}">
                                            <i class="ti ti-plus me-1"></i><span>{{ __('Buy Storage') }}</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body text-center p-3">
                                    <div id="device-chart" style="min-height: 200px;"></div>
                                    <div class="row text-center mt-2 pt-2 border-top">
                                        <div class="col-6 border-end">
                                            <span class="text-muted d-block text-sm">{{ __('Used Storage') }}</span>
                                            <strong class="text-primary fs-6">{{ $used_mb }} MB</strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block text-sm">{{ __('Available Storage') }}</span>
                                            <strong class="text-success fs-6">
                                                @if($total_limit == -1)
                                                    {{ __('Unlimited') }}
                                                @else
                                                    {{ max(0, round($total_limit - $used_mb, 2)) }} MB
                                                @endif
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-xl-7">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-4 col-12">
                                        <h5 class="mb-0">{{ __('Calendar') }}</h5>
                                        <input type="hidden" id="path_admin" value="{{ url('/') }}">
                                    </div>
                                    <div class="col-md-8 col-12 mt-2 mt-md-0 d-flex align-items-center justify-content-end gap-2 flex-wrap">
                                        @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
                                            <select class="form-control me-2" name="calender_type" id="calender_type"
                                                style="width: 145px; display:inline-block;" onchange="get_data()">
                                                <option value="google_calender">{{ __('Google Calendar') }}</option>
                                                <option value="local_calender" selected="true">
                                                    {{ __('Local Calendar') }}
                                                </option>
                                            </select>
                                        @endif
                                        <div class="calendar-controls-wrapper d-inline-flex align-items-center gap-1">
                                            <div class="btn-group btn-group-sm border rounded bg-white shadow-sm" role="group" aria-label="Calendar Zoom Controls">
                                                <button type="button" class="btn btn-outline-secondary btn-cal-zoom-out py-1 px-2" onclick="dashboardCalZoomOut(this, event)" title="{{ __('Zoom Out') }}">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                                    </svg>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-cal-zoom-reset fw-bold py-1 px-2 text-primary" onclick="dashboardCalZoomReset(this, event)" title="{{ __('Reset Zoom') }}">
                                                    <span class="cal-zoom-level">100%</span>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-cal-zoom-in py-1 px-2" onclick="dashboardCalZoomIn(this, event)" title="{{ __('Zoom In') }}">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                                    </svg>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-cal-fit-height py-1 px-2" onclick="dashboardCalVerticalResize(this, event)" title="{{ __('Vertical Resize / Scroll Toggle') }}">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="7 8 12 3 17 8"></polyline>
                                                        <polyline points="7 16 12 21 17 16"></polyline>
                                                        <line x1="12" y1="3" x2="12" y2="21"></line>
                                                    </svg>
                                                </button>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary border shadow-sm btn-cal-card-fullscreen py-1 px-2 me-1" onclick="dashboardCalExpandDashboard(this, event)" title="{{ __('Dashboard Fullscreen View') }}">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="15 3 21 3 21 9"></polyline>
                                                    <polyline points="9 21 3 21 3 15"></polyline>
                                                    <line x1="21" y1="3" x2="14" y2="10"></line>
                                                    <line x1="3" y1="21" x2="10" y2="14"></line>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary border shadow-sm btn-cal-page-fullscreen py-1 px-2 me-1" onclick="dashboardCalBrowserFullscreen(this, event)" title="{{ __('Browser Fullscreen Mode') }}">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#495057" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary border shadow-sm btn-cal-reset-all py-1 px-2" onclick="dashboardCalResetAll(this, event)" title="{{ __('Reset Real Position') }}">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                                    <path d="M3 3v5h5"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body card-635 card-body-calendar overflow-auto">
                                <div class="calendar-zoom-wrapper">
                                    <div id='calendar' class='calendar'></div>
                                </div>
                            </div>
                        </div>

                        @if (\Auth::user()->type == 'company')
                            <div class="card">
                                <div class="card-header card-body table-border-style">
                                    <h5>{{ __('Announcement List') }}</h5>
                                </div>
                                <div class="card-body" style="height: 324px; overflow:auto">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Title') }}</th>
                                                    <th>{{ __('Start Date') }}</th>
                                                    <th>{{ __('End Date') }}</th>
                                                    <th>{{ __('Description') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                @foreach ($announcements as $announcement)
                                                    <tr>
                                                        <td>{{ $announcement->title }}</td>
                                                        <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                                        <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>
                                                        {{-- <td>{{ $announcement->description }}</td> --}}
                                                        <td class="description-col" data-description="{{ $announcement->description }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Celebrations: Work Anniversaries & Birthdays Widget (Positioned below Status & Calendar) --}}
            @include('dashboard.celebrations_widget')

            @if (\Auth::user()->type != 'company')
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="card">
                        <div class="card-header card-body table-border-style">
                            <h5>{{ __('Announcement List') }}</h5>
                        </div>
                        <div class="card-body" style="height: 270px; overflow:auto">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Description') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($announcements as $announcement)
                                            <tr>
                                                <td>{{ $announcement->title }}</td>
                                                <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                                <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>
                                                {{-- <td>{{ $announcement->description }}</td> --}}
                                                <td class="description-col" data-description="{{ $announcement->description }}"></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <div class="modal fade" id="attendanceReasonModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="attendanceReasonForm" method="POST">
                @csrf
                <input type="hidden" name="type" id="attendance_type">
                <input type="hidden" name="mode" id="attendance_mode">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reason Required</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Please enter reason</label>
                            <textarea class="form-control" id="attendance_reason_text" name="reason" required></textarea>
                            <div class="invalid-feedback">Please enter a valid reason (cannot be empty or spaces only).</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit & Continue</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="breakReasonModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="breakReasonModalTitle">Break Reason Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" id="breakReasonLabel">Please enter reason for extended break</label>
                        <textarea id="break_reason_text" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="breakReasonSubmit" class="btn btn-primary">Submit & Continue</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Right-click context menu for calendar --}}
    @if (\Auth::user()->can('Create Leave') || \Auth::user()->can('Create Holiday') || \Auth::user()->can('Create Interview Schedule') || \Auth::user()->can('Create Event'))
        <div id="calendarContextMenu" class="dropdown-menu shadow" style="display:none; position:fixed; z-index:10000;">
            @can('Create Leave')
                <a href="javascript:void(0);" id="ctxCreateLeave" class="dropdown-item">
                    <i class="ti ti-calendar-plus me-2"></i> {{ __('Create New Leave') }}
                </a>
            @endcan
            @can('Create Holiday')
                <a href="javascript:void(0);" id="ctxCreateHoliday" class="dropdown-item">
                    <i class="ti ti-confetti me-2"></i> {{ __('Create New Holiday') }}
                </a>
            @endcan
            @can('Create Interview Schedule')
                <a href="javascript:void(0);" id="ctxCreateInterview" class="dropdown-item">
                    <i class="ti ti-user-check me-2"></i> {{ __('Create Interview Schedule') }}
                </a>
            @endcan
            @can('Create Event')
                <a href="javascript:void(0);" id="ctxCreateEvent" class="dropdown-item">
                    <i class="ti ti-calendar-event me-2"></i> {{ __('Create New Event') }}
                </a>
            @endcan
        </div>
    @endif
@endsection



@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>

    @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr'  || Auth::user()->type == 'HR')
        <script type="text/javascript">
            $(document).ready(function () {
                get_data();
            });

            function get_data() {
                var calender_type = $('#calender_type :selected').val();

                $('#calendar').removeClass('local_calender');
                $('#calendar').removeClass('google_calender');
                if (calender_type == undefined) {
                    calender_type = 'local_calender';
                }
                $('#calendar').addClass(calender_type);

                $.ajax({
                    url: $("#path_admin").val() + "/event/get_event_data",
                    method: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        'calender_type': calender_type
                    },
                    success: function (data) {

                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                dayGridMonth: "{{ __('Month') }}"
                            },
                            // slotLabelFormat: {
                            //     hour: '2-digit',
                            //     minute: '2-digit',
                            //     hour12: false,
                            // },
                            themeSystem: 'bootstrap',
                            slotDuration: '00:10:00',
                            allDaySlot: true,
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                            eventClick: function(info) {
                                if (info.event.url && info.event.url !== '0' && info.event.url !== '#') {
                                    info.jsEvent.preventDefault();
                                    var title = info.event.title;
                                    var url = info.event.url;
                                    $("#commonModal .modal-title").html(title);
                                    $("#commonModal .modal-dialog").removeClass('modal-md modal-lg modal-sm modal-xl').addClass('modal-md');
                                    $.ajax({
                                        url: url,
                                        success: function(data) {
                                            $('#commonModal .body').html(data);
                                            $("#commonModal").modal('show');
                                            taskCheckbox();
                                            common_bind("#commonModal");
                                            commonLoader();
                                            select2();
                                            cancelButtonCss();
                                            if (typeof validation === 'function') validation();
                                        },
                                        error: function(data) {
                                            data = data.responseJSON;
                                            toastrs('Error', (data && data.error) ? data.error : 'Something went wrong', 'error');
                                        }
                                    });
                                }
                            },
                        });
                        calendar.render();
                        window.currentFullCalendar = calendar;

                        // Right-click context menu for company/HR calendar
                        @if (\Auth::user()->can('Create Leave') || \Auth::user()->can('Create Holiday') || \Auth::user()->can('Create Interview Schedule') || \Auth::user()->can('Create Event'))
                        (function() {
                            var calEl = document.getElementById('calendar');
                            var ctxMenu = document.getElementById('calendarContextMenu');
                            var selectedDate = null;

                            if (!calEl || !ctxMenu) return;

                            calEl.addEventListener('contextmenu', function(e) {
                                var dayCell = e.target.closest('[data-date]');
                                if (!dayCell) return;

                                e.preventDefault();
                                selectedDate = dayCell.getAttribute('data-date');

                                ctxMenu.style.left = e.clientX + 'px';
                                ctxMenu.style.top = e.clientY + 'px';
                                ctxMenu.style.display = 'block';

                                var menuRect = ctxMenu.getBoundingClientRect();
                                if (menuRect.right > window.innerWidth) {
                                    ctxMenu.style.left = (e.clientX - menuRect.width) + 'px';
                                }
                                if (menuRect.bottom > window.innerHeight) {
                                    ctxMenu.style.top = (e.clientY - menuRect.height) + 'px';
                                }
                            });

                            document.addEventListener('click', function() {
                                ctxMenu.style.display = 'none';
                            });

                            document.addEventListener('contextmenu', function(e) {
                                if (!calEl.contains(e.target)) {
                                    ctxMenu.style.display = 'none';
                                }
                            });

                            function openCtxModal(url, title, size, type) {
                                ctxMenu.style.display = 'none';
                                $("#commonModal .modal-title").html(title);
                                $("#commonModal .modal-dialog").removeClass('modal-md modal-lg modal-sm modal-xl').addClass('modal-' + size);
                                $.ajax({
                                    url: url,
                                    success: function(data) {
                                        $('#commonModal .body').html(data);
                                        $("#commonModal").modal('show');
                                        if (selectedDate) {
                                            setTimeout(function() {
                                                if (type === 'leave' || type === 'holiday' || type === 'event') {
                                                    $('#commonModal #start_date, #commonModal input[name="start_date"]').val(selectedDate);
                                                    $('#commonModal #end_date, #commonModal input[name="end_date"]').val(selectedDate);
                                                } else if (type === 'interview') {
                                                    $('#commonModal #currentDate, #commonModal input[name="date"]').val(selectedDate);
                                                }
                                            }, 50);
                                        }
                                        taskCheckbox();
                                        common_bind("#commonModal");
                                        commonLoader();
                                        select2();
                                        cancelButtonCss();
                                        if (typeof validation === 'function') validation();
                                    },
                                    error: function(data) {
                                        data = data.responseJSON;
                                        toastrs('Error', (data && data.error) ? data.error : 'Something went wrong', 'error');
                                    }
                                });
                            }

                            var btnLeave = document.getElementById('ctxCreateLeave');
                            if (btnLeave) {
                                btnLeave.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    openCtxModal('{{ route("leave.create") }}', '{{ __("Create New Leave") }}', 'lg', 'leave');
                                });
                            }

                            var btnHoliday = document.getElementById('ctxCreateHoliday');
                            if (btnHoliday) {
                                btnHoliday.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    openCtxModal('{{ route("holiday.create") }}', '{{ __("Create New Holiday") }}', 'md', 'holiday');
                                });
                            }

                            var btnInterview = document.getElementById('ctxCreateInterview');
                            if (btnInterview) {
                                btnInterview.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    openCtxModal('{{ route("interview-schedule.create") }}', '{{ __("Create Interview Schedule") }}', 'lg', 'interview');
                                });
                            }

                            var btnEvent = document.getElementById('ctxCreateEvent');
                            if (btnEvent) {
                                btnEvent.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    openCtxModal('{{ route("event.create") }}', '{{ __("Add Event/Task") }}', 'lg', 'event');
                                });
                            }
                        })();
                        @endif
                    }
                });
            };
        </script>
    @else
        <script>
            $(document).ready(function () {
                get_data();
            });

            function get_data() {
                var calender_type = $('#calender_type :selected').val();

                $('#event_calendar').removeClass('local_calender');
                $('#event_calendar').removeClass('google_calender');
                if (calender_type == undefined) {
                    calender_type = 'local_calender';
                }
                $('#event_calendar').addClass(calender_type);

                $.ajax({
                    url: $("#path_admin").val() + "/event/get_event_data",
                    method: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        'calender_type': calender_type
                    },
                    success: function (data) {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('event_calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                dayGridMonth: "{{ __('Month') }}"
                            },
                            // slotLabelFormat: {
                            //     hour: '2-digit',
                            //     minute: '2-digit',
                            //     hour12: false,
                            // },
                            themeSystem: 'bootstrap',
                            slotDuration: '00:10:00',
                            allDaySlot: true,
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                            eventClick: function(info) {
                                if (info.event.url && info.event.url !== '0' && info.event.url !== '#') {
                                    info.jsEvent.preventDefault();
                                    var title = info.event.title;
                                    var url = info.event.url;
                                    $("#commonModal .modal-title").html(title);
                                    $("#commonModal .modal-dialog").removeClass('modal-md modal-lg modal-sm modal-xl').addClass('modal-md');
                                    $.ajax({
                                        url: url,
                                        success: function(data) {
                                            $('#commonModal .body').html(data);
                                            $("#commonModal").modal('show');
                                            taskCheckbox();
                                            common_bind("#commonModal");
                                            commonLoader();
                                            select2();
                                            cancelButtonCss();
                                            if (typeof validation === 'function') validation();
                                        },
                                        error: function(data) {
                                            data = data.responseJSON;
                                            toastrs('Error', (data && data.error) ? data.error : 'Something went wrong', 'error');
                                        }
                                    });
                                }
                            },
                        });

                        calendar.render();
                        window.currentFullCalendar = calendar;

                        // Right-click context menu for employee calendar
                        @if (\Auth::user()->can('Create Leave') || \Auth::user()->can('Create Holiday') || \Auth::user()->can('Create Interview Schedule') || \Auth::user()->can('Create Event'))
                        (function() {
                            var calEl = document.getElementById('event_calendar');
                            var ctxMenu = document.getElementById('calendarContextMenu');
                            var selectedDate = null;

                            if (!calEl || !ctxMenu) return;

                            calEl.addEventListener('contextmenu', function(e) {
                                var dayCell = e.target.closest('[data-date]');
                                if (!dayCell) return;

                                e.preventDefault();
                                selectedDate = dayCell.getAttribute('data-date');

                                ctxMenu.style.left = e.clientX + 'px';
                                ctxMenu.style.top = e.clientY + 'px';
                                ctxMenu.style.display = 'block';

                                var menuRect = ctxMenu.getBoundingClientRect();
                                if (menuRect.right > window.innerWidth) {
                                    ctxMenu.style.left = (e.clientX - menuRect.width) + 'px';
                                }
                                if (menuRect.bottom > window.innerHeight) {
                                    ctxMenu.style.top = (e.clientY - menuRect.height) + 'px';
                                }
                            });

                            document.addEventListener('click', function() {
                                ctxMenu.style.display = 'none';
                            });

                            document.addEventListener('contextmenu', function(e) {
                                if (!calEl.contains(e.target)) {
                                    ctxMenu.style.display = 'none';
                                }
                            });

                            function openCtxModal(url, title, size, type) {
                                ctxMenu.style.display = 'none';
                                $("#commonModal .modal-title").html(title);
                                $("#commonModal .modal-dialog").removeClass('modal-md modal-lg modal-sm modal-xl').addClass('modal-' + size);
                                $.ajax({
                                    url: url,
                                    success: function(data) {
                                        $('#commonModal .body').html(data);
                                        $("#commonModal").modal('show');
                                        if (selectedDate) {
                                            setTimeout(function() {
                                                if (type === 'leave' || type === 'holiday' || type === 'event') {
                                                    $('#commonModal #start_date, #commonModal input[name="start_date"]').val(selectedDate);
                                                    $('#commonModal #end_date, #commonModal input[name="end_date"]').val(selectedDate);
                                                } else if (type === 'interview') {
                                                    $('#commonModal #currentDate, #commonModal input[name="date"]').val(selectedDate);
                                                }
                                            }, 50);
                                        }
                                        taskCheckbox();
                                        common_bind("#commonModal");
                                        commonLoader();
                                        select2();
                                        cancelButtonCss();
                                        if (typeof validation === 'function') validation();
                                    },
                                    error: function(data) {
                                        data = data.responseJSON;
                                        toastrs('Error', (data && data.error) ? data.error : 'Something went wrong', 'error');
                                    }
                                });
                            }

                            var btnLeave = document.getElementById('ctxCreateLeave');
                            if (btnLeave) {
                                btnLeave.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    openCtxModal('{{ route("leave.create") }}', '{{ __("Create New Leave") }}', 'lg', 'leave');
                                });
                            }

                            var btnHoliday = document.getElementById('ctxCreateHoliday');
                            if (btnHoliday) {
                                btnHoliday.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    openCtxModal('{{ route("holiday.create") }}', '{{ __("Create New Holiday") }}', 'md', 'holiday');
                                });
                            }

                            var btnInterview = document.getElementById('ctxCreateInterview');
                            if (btnInterview) {
                                btnInterview.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    openCtxModal('{{ route("interview-schedule.create") }}', '{{ __("Create Interview Schedule") }}', 'lg', 'interview');
                                });
                            }

                            var btnEvent = document.getElementById('ctxCreateEvent');
                            if (btnEvent) {
                                btnEvent.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    openCtxModal('{{ route("event.create") }}', '{{ __("Add Event/Task") }}', 'lg', 'event');
                                });
                            }
                        })();
                        @endif
                    }
                });
            };
        </script>
        </script>
    @endif

    @if (\Auth::user()->type == 'company')
        <script>
            (function () {
                var storageVal = {{ round($storage_limit, 2) }};
                var options = {
                    series: [storageVal],
                    chart: {
                        height: 240,
                        type: 'radialBar',
                        sparkline: {
                            enabled: true
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -90,
                            endAngle: 90,
                            track: {
                                background: "#e7e7e7",
                                strokeWidth: '97%',
                                margin: 5,
                            },
                            dataLabels: {
                                name: {
                                    show: true,
                                    offsetY: 20,
                                    color: '#6c757d',
                                    fontSize: '13px'
                                },
                                value: {
                                    offsetY: -20,
                                    fontSize: '22px',
                                    fontWeight: '600',
                                    formatter: function (val) {
                                        return val + "%";
                                    }
                                }
                            }
                        }
                    },
                    grid: {
                        padding: {
                            top: -10
                        }
                    },
                    colors: [storageVal > 90 ? '#ff3a6e' : (storageVal > 75 ? '#ffa21d' : '#6FD943')],
                    labels: ['{{ __("Storage Used") }}'],
                };
                var chartElement = document.querySelector("#device-chart");
                if (chartElement) {
                    var chart = new ApexCharts(chartElement, options);
                    chart.render();
                }
            })();
        </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const attendanceButtons = document.querySelectorAll(
                '#clock_in, #clock_out, #break_start, #break_end, [type="submit"][name="in"], [type="submit"][name="out"], [type="submit"][name="break"]'
            );

            attendanceButtons.forEach(btn => {
                btn.addEventListener('click', function (e) {
                    attendanceButtons.forEach(b => {
                        if (b !== this) {
                            b.disabled = true;
                            b.classList.add("disabled");
                        }
                    });

                    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

                    setTimeout(() => {
                        this.disabled = true;
                        this.classList.add("disabled");
                    }, 100);
                }, { once: true });
            });
        });
    </script>
@endpush

{{-- Updated JavaScript for Clock Out Logic --}}
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const officeStart = document.getElementById('office_start')?.value;
        const officeEnd = document.getElementById('office_end')?.value;
        const loginEarlyTime = {{ (int) ($setting['login_early_time'] ?? 0) }};
        const loginDeadline = {{ (int) ($setting['login_deley_min'] ?? 0) }};
        const logoutDeadline = {{ (int) ($setting['logout_lead_time'] ?? 0) }};

        const routes = {
            attendanceStore: "{{ route('attendanceemployee.store') }}",
            attendanceRequestStore: "{{ route('attendance-requests.store') }}",
            attendanceRequestClockOut: "{{ !empty($attendanceRequest) && $attendanceRequest->id ? route('attendancerequests.clock_out', $attendanceRequest->id) : '' }}",
            employeeAttendanceUpdate: "{{ !empty($employeeAttendance) && $employeeAttendance->id ? route('attendanceemployee.update', $employeeAttendance->id) : '' }}",
        };

        if (!officeStart || !officeEnd) return;

        function timeToMinutes(time) {
            const [h, m] = time.split(':').map(Number);
            return h * 60 + m;
        }

        function getCurrentMinutes() {
            const now = new Date();
            return now.getHours() * 60 + now.getMinutes();
        }

        // ================= CLOCK ACTION =================
        document.body.addEventListener('click', function (event) {
            const btn = event.target.closest('.clock-action');
            if (!btn) {
                return;
            }

            event.preventDefault();
            const action = btn.dataset.action;
            const nowMin = getCurrentMinutes();

            if (action === 'clock_in') {
                const startMin = timeToMinutes(officeStart);
                const earlyThreshold = startMin - loginEarlyTime;
                const lateThreshold = startMin + loginDeadline;

                if (nowMin < earlyThreshold || nowMin > lateThreshold) {
                    openReasonModal('clock_in');
                    return;
                }

                submitNormalAttendance('in', routes.attendanceStore);
                return;
            }

            if (action === 'clock_out') {
                const endMin = timeToMinutes(officeEnd);
                const earlyThreshold = endMin - logoutDeadline;
                const lateThreshold = endMin + logoutDeadline;

                if (nowMin < earlyThreshold || nowMin > lateThreshold) {
                    openReasonModal('clock_out');
                    return;
                }

                const hasAttendanceRequest = {{ !empty($attendanceRequest) && $attendanceRequest->type == 'clock_in' ? 'true' : 'false' }};
                const hasEmployeeAttendance = {{ !empty($employeeAttendance) ? 'true' : 'false' }};

                if (hasAttendanceRequest && routes.attendanceRequestClockOut) {
                    submitClockOutUpdate(routes.attendanceRequestClockOut);
                } else if (hasEmployeeAttendance && routes.employeeAttendanceUpdate) {
                    submitClockOutUpdate(routes.employeeAttendanceUpdate);
                } else {
                    console.error('❌ No valid attendance record found');
                }
            }
        });

        // ================= MODAL =================
        function openReasonModal(type) {
            document.getElementById('attendance_type').value = type;

            const form = document.getElementById('attendanceReasonForm');
            form.action = routes.attendanceRequestStore;
            const reasonField = document.getElementById('attendance_reason_text');
            reasonField.value = '';
            reasonField.classList.remove('is-invalid');

            if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal === 'undefined') {
                form.submit();
                return;
            }

            const modal = new bootstrap.Modal(
                document.getElementById('attendanceReasonModal'),
                { backdrop: 'static', keyboard: false }
            );

            modal.show();
        }

        window.openReasonModal = openReasonModal;
        document.getElementById('attendanceReasonForm').addEventListener('submit', function (e) {
            const reasonField = document.getElementById('attendance_reason_text');
            const reason = reasonField.value.trim();

            if (!reason) {
                e.preventDefault();
                reasonField.classList.add('is-invalid');
                reasonField.focus();
                return false;
            }

            reasonField.value = reason;
            reasonField.classList.remove('is-invalid');
        });

        document.getElementById('attendance_reason_text').addEventListener('input', function () {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });

        // ================= SUBMIT =================
        function submitNormalAttendance(type, actionUrl) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;

            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="${type}" value="1">
            `;

            document.body.appendChild(form);
            form.submit();
        }

        function submitClockOutUpdate(actionUrl) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;

            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="out" value="1">
            `;

            document.body.appendChild(form);
            form.submit();
        }

        // Button disable logic
        const attendanceButtons = document.querySelectorAll(
            '#clock_in, #clock_out, [type="submit"][name="in"], [type="submit"][name="out"], [type="submit"][name="break"]'
        );

        attendanceButtons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                if (!this.classList.contains('clock-action')) {
                    attendanceButtons.forEach(b => {
                        if (b !== this) {
                            b.disabled = true;
                            b.classList.add("disabled");
                        }
                    });

                    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

                    setTimeout(() => {
                        this.disabled = true;
                        this.classList.add("disabled");
                    }, 100);
                }
            }, { once: true });
        });

        let pendingBreakForm = null;
        document.querySelectorAll('.break-end-button').forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const allowedSeconds = parseInt(this.dataset.allowedSeconds || '0', 10);
                const breakStart = this.dataset.breakStart || '';
                const breakType = this.dataset.breakType || 'lunch';
                const breakMode = this.dataset.breakMode || 'fixed';
                const breakEnd = this.dataset.breakEnd || '';
                const breakWindowStart = this.dataset.breakWindowStart || '';
                const previousBreakSeconds = parseInt(this.dataset.previousBreakSeconds || '0', 10);
                const form = this.closest('form');

                if (!form) {
                    return;
                }

                let submitBreak = true;
                if (allowedSeconds > 0 && breakStart) {
                    const [hours, minutes, seconds = '00'] = breakStart.split(':');
                    const startDate = new Date();
                    startDate.setHours(parseInt(hours, 10));
                    startDate.setMinutes(parseInt(minutes, 10));
                    startDate.setSeconds(parseInt(seconds, 10));

                    const now = new Date();
                    let elapsedSeconds = Math.floor((now - startDate) / 1000);
                    if (elapsedSeconds < 0) {
                        elapsedSeconds = 0;
                    }

                    const totalSecondsWithPrevious = previousBreakSeconds + elapsedSeconds;
                    let fixedWindowExceeded = false;

                    if (breakMode === 'fixed') {
                        // Check past end of window
                        if (breakEnd) {
                            const [endHours, endMinutes, endSeconds = '00'] = breakEnd.split(':');
                            const endDate = new Date();
                            endDate.setHours(parseInt(endHours, 10));
                            endDate.setMinutes(parseInt(endMinutes, 10));
                            endDate.setSeconds(parseInt(endSeconds, 10));

                            if (endDate < startDate) {
                                endDate.setDate(endDate.getDate() + 1);
                            }

                            if (now > endDate) {
                                fixedWindowExceeded = true;
                            }
                        }

                        // Check before start of window (clocking out before window even began)
                        if (!fixedWindowExceeded && breakWindowStart) {
                            const [wsHours, wsMinutes, wsSeconds = '00'] = breakWindowStart.split(':');
                            const windowStartDate = new Date();
                            windowStartDate.setHours(parseInt(wsHours, 10));
                            windowStartDate.setMinutes(parseInt(wsMinutes, 10));
                            windowStartDate.setSeconds(parseInt(wsSeconds, 10));

                            if (now < windowStartDate) {
                                fixedWindowExceeded = true;
                            }
                        }
                    }

                    console.log(`⏱️ Elapsed break time: ${elapsedSeconds} seconds (Allowed: ${allowedSeconds} seconds, Previous: ${previousBreakSeconds} seconds, Total: ${totalSecondsWithPrevious} seconds)`);

                    if (elapsedSeconds > allowedSeconds || totalSecondsWithPrevious > allowedSeconds || fixedWindowExceeded) {
                        submitBreak = false;
                        pendingBreakForm = form;
                        document.getElementById('break_reason_text').value = '';

                        const capitalizedType = breakType.charAt(0).toUpperCase() + breakType.slice(1);
                        document.getElementById('breakReasonModalTitle').textContent = capitalizedType + ' Break Reason Required';

                        if (fixedWindowExceeded) {
                            document.getElementById('breakReasonLabel').textContent = 'This ' + breakType + ' break is outside the configured window. Please enter a reason.';
                        } else if (totalSecondsWithPrevious > allowedSeconds) {
                            document.getElementById('breakReasonLabel').textContent = 'Total ' + breakType + ' time including earlier breaks exceeded allowed time. Please enter a reason.';
                        } else {
                            document.getElementById('breakReasonLabel').textContent = 'Please enter reason for extended ' + breakType + ' break';
                        }

                        const modal = new bootstrap.Modal(document.getElementById('breakReasonModal'), { backdrop: 'static', keyboard: false });
                        modal.show();
                    }
                }

                if (submitBreak) {
                    form.submit();
                }
            });
        });

        document.getElementById('breakReasonSubmit').addEventListener('click', function () {
            const reasonField = document.getElementById('break_reason_text');
            const reason = reasonField.value.trim();

            if (!reason) {
                reasonField.classList.add('is-invalid');
                return;
            }

            reasonField.classList.remove('is-invalid');

            if (pendingBreakForm) {
                let hidden = pendingBreakForm.querySelector('input[name="break_reason"]');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'break_reason';
                    pendingBreakForm.appendChild(hidden);
                }
                hidden.value = reason;
                pendingBreakForm.submit();
                pendingBreakForm = null;
            }
        });
    });
</script>

@push('script-page')
<script>
    // Global Calendar Control Functions
    window.dashboardCalZoomIn = function(btn, e) {
        if (e) e.preventDefault();
        var card = btn ? btn.closest('.card') : null;
        var zoomText = card ? card.querySelector('.cal-zoom-level') : null;
        var currentZoom = parseInt(zoomText ? zoomText.textContent : '100') || 100;
        if (currentZoom < 180) {
            currentZoom += 10;
            window.dashboardCalApplyZoom(card, currentZoom, zoomText);
        }
    };

    window.dashboardCalZoomOut = function(btn, e) {
        if (e) e.preventDefault();
        var card = btn ? btn.closest('.card') : null;
        var zoomText = card ? card.querySelector('.cal-zoom-level') : null;
        var currentZoom = parseInt(zoomText ? zoomText.textContent : '100') || 100;
        if (currentZoom > 50) {
            currentZoom -= 10;
            window.dashboardCalApplyZoom(card, currentZoom, zoomText);
        }
    };

    window.dashboardCalZoomReset = function(btn, e) {
        if (e) e.preventDefault();
        var card = btn ? btn.closest('.card') : null;
        var zoomText = card ? card.querySelector('.cal-zoom-level') : null;
        window.dashboardCalApplyZoom(card, 100, zoomText);
    };

    window.dashboardCalApplyZoom = function(card, zoomPercent, zoomText) {
        if (zoomText) zoomText.textContent = zoomPercent + '%';
        var scale = zoomPercent / 100;
        var zoomWrapper = card ? card.querySelector('.calendar-zoom-wrapper') : null;
        if (zoomWrapper) {
            if (zoomPercent === 100) {
                zoomWrapper.style.transform = '';
                zoomWrapper.style.transformOrigin = '';
                zoomWrapper.style.width = '';
                zoomWrapper.style.height = '';
            } else {
                zoomWrapper.style.transform = 'scale(' + scale + ')';
                zoomWrapper.style.transformOrigin = 'top left';
                zoomWrapper.style.width = (100 / scale) + '%';
                var calEl = card.querySelector('.calendar');
                if (calEl) {
                    var h = calEl.offsetHeight || 550;
                    zoomWrapper.style.height = (h * scale) + 'px';
                }
            }
        }
        window.dashboardCalTriggerUpdateSize();
    };

    window.dashboardCalVerticalResize = function(btn, e) {
        if (e) e.preventDefault();
        var card = btn ? btn.closest('.card') : null;
        var bodyCal = card ? card.querySelector('.card-body-calendar') : null;
        if (bodyCal) {
            var currentState = bodyCal.getAttribute('data-height-state') || 'default';
            if (currentState === 'default') {
                bodyCal.setAttribute('data-height-state', 'expanded');
                bodyCal.style.setProperty('max-height', '850px', 'important');
            } else if (currentState === 'expanded') {
                bodyCal.setAttribute('data-height-state', 'compact');
                bodyCal.style.setProperty('max-height', '400px', 'important');
            } else {
                bodyCal.setAttribute('data-height-state', 'default');
                bodyCal.style.setProperty('max-height', '600px', 'important');
            }
            window.dashboardCalTriggerUpdateSize();
        }
    };

    window.dashboardCalExpandDashboard = function(btn, e) {
        if (e) e.preventDefault();
        var card = btn ? btn.closest('.card') : null;
        if (!card) return;

        var parentCol = card.closest('.col-xl-7, .col-lg-7, .col-md-12, .col-12');
        var row = card.closest('.row');

        if (row && parentCol) {
            var siblings = Array.from(row.children).filter(function(child) {
                return child !== parentCol;
            });
            var isExpanded = parentCol.classList.contains('calendar-col-expanded');
            if (!isExpanded) {
                parentCol.classList.add('calendar-col-expanded');
                siblings.forEach(function(sib) {
                    sib.classList.add('calendar-sibling-hidden');
                });
                card.classList.add('calendar-card-inline-expanded');
            } else {
                parentCol.classList.remove('calendar-col-expanded');
                siblings.forEach(function(sib) {
                    sib.classList.remove('calendar-sibling-hidden');
                });
                card.classList.remove('calendar-card-inline-expanded');
            }
        } else {
            card.classList.toggle('calendar-card-inline-expanded');
        }

        window.dashboardCalTriggerUpdateSize();
    };

    window.dashboardCalResetAll = function(btn, e) {
        if (e) e.preventDefault();
        var card = btn ? btn.closest('.card') : null;
        if (!card) return;

        var zoomText = card.querySelector('.cal-zoom-level');
        window.dashboardCalApplyZoom(card, 100, zoomText);

        var bodyCal = card.querySelector('.card-body-calendar');
        if (bodyCal) {
            bodyCal.removeAttribute('data-height-state');
            bodyCal.style.removeProperty('max-height');
            bodyCal.style.height = '';
        }

        var parentCol = card.closest('.col-xl-7, .col-lg-7, .col-md-12, .col-12');
        var row = card.closest('.row');
        if (row && parentCol) {
            parentCol.classList.remove('calendar-col-expanded');
            var siblings = Array.from(row.children).filter(function(child) {
                return child !== parentCol;
            });
            siblings.forEach(function(sib) {
                sib.classList.remove('calendar-sibling-hidden');
            });
        }
        card.classList.remove('calendar-card-inline-expanded');

        if (document.fullscreenElement) {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }

        window.dashboardCalTriggerUpdateSize();
    };

    window.dashboardCalBrowserFullscreen = function(btn, e) {
        if (e) e.preventDefault();
        var card = btn ? btn.closest('.card') : null;
        if (!card) return;

        if (!document.fullscreenElement) {
            var modal = document.getElementById('commonModal');
            var ctxMenu = document.getElementById('calendarContextMenu');
            if (modal && !card.contains(modal)) card.appendChild(modal);
            if (ctxMenu && !card.contains(ctxMenu)) card.appendChild(ctxMenu);

            if (card.requestFullscreen) {
                card.requestFullscreen();
            } else if (card.webkitRequestFullscreen) {
                card.webkitRequestFullscreen();
            } else if (card.msRequestFullscreen) {
                card.msRequestFullscreen();
            }
            card.classList.add('is-browser-fullscreen');
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
        window.dashboardCalTriggerUpdateSize();
    };

    window.dashboardCalTriggerUpdateSize = function() {
        setTimeout(function() {
            if (window.currentFullCalendar && typeof window.currentFullCalendar.updateSize === 'function') {
                window.currentFullCalendar.updateSize();
            }
            window.dispatchEvent(new Event('resize'));
        }, 50);
    };

    document.addEventListener('fullscreenchange', function() {
        var activeCalCard = document.querySelector('.card.is-browser-fullscreen');
        var modal = document.getElementById('commonModal');
        var ctxMenu = document.getElementById('calendarContextMenu');

        if (!document.fullscreenElement) {
            if (activeCalCard) activeCalCard.classList.remove('is-browser-fullscreen');
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            if (ctxMenu && ctxMenu.parentElement !== document.body) {
                document.body.appendChild(ctxMenu);
            }
            if (window.currentFullCalendar && typeof window.currentFullCalendar.setOption === 'function') {
                window.currentFullCalendar.setOption('height', 'auto');
            }
        } else {
            if (window.currentFullCalendar && typeof window.currentFullCalendar.setOption === 'function') {
                window.currentFullCalendar.setOption('height', 'calc(100vh - 120px)');
            }
        }
        window.dashboardCalTriggerUpdateSize();
    });
</script>
@endpush
