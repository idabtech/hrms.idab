@extends('layouts.admin')

@section('page-title')
{{ __('Manage Monthly Attendance') }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
<li class="breadcrumb-item">{{ __('Manage Monthly Attendance Report') }}</li>
@endsection
@section('action-button')
<a href="javascript:void(0)" class="btn btn-sm btn-primary me-1" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="{{ __('Download') }}"
    data-original-title="{{ __('Download') }}">
    <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
</a>

@php
$emp = isset($_GET['employee_id']) && !empty($_GET['employee_id']) ? $_GET['employee_id'] : [];
$employees = implode(', ', $emp);
$currentMonthForDate = \Carbon\Carbon::createFromFormat('d-M-Y', '01-' . $data['curMonth'])->format('Y-m');
@endphp

<a href="{{ route('report.attendance', [isset($_GET['month']) ? $_GET['month'] : date('Y-m'), isset($_GET['branch_id']) && !empty($_GET['branch_id']) ? $_GET['branch_id'] : 0, isset($_GET['department']) && !empty($_GET['department']) ? $_GET['department'] : 0, !empty($employees) ? $employees : 0]) }}"
    class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="" data-bs-original-title="Export">
    <span class="btn-inner--icon"><i class="ti ti-file-export "></i></span>
</a>
@endsection

<style>
/* Table wrapper — horizontal scroll, no clipping */
.attendance-table-responsive {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 520px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

/* Table base */
.attendance-table-responsive table {
    border-collapse: separate;
    border-spacing: 0;
    width: max-content;   /* let table grow as wide as needed */
    min-width: 100%;
    background: #fff;
    margin-bottom: 0;
}

/* Header */
.attendance-table-responsive thead th {
    background: #f4f6fa;
    font-weight: 600;
    font-size: 13px;
    padding: 10px 8px;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
    vertical-align: middle;
    position: sticky;
    top: 0;
    z-index: 2;
}

/* Body cells */
.attendance-table-responsive tbody td {
    padding: 7px 8px;
    border-bottom: 1px solid #f0f0f0;
    white-space: nowrap;
    vertical-align: middle;
    font-size: 13px;
}

/* Alternating rows */
.attendance-table-responsive tbody tr:nth-child(even) td {
    background: #fafbfc;
}

/* Hover */
.attendance-table-responsive tbody tr:hover td {
    background: #eef4ff;
}

/* Sticky Name column — header */
.attendance-table-responsive thead th.col-name {
    position: sticky;
    left: 0;
    z-index: 4;   /* above other sticky headers */
    background: #f4f6fa;
    min-width: 160px;
    text-align: left;
    box-shadow: 2px 0 5px -2px rgba(0,0,0,0.12);
}

/* Sticky Name column — body */
.attendance-table-responsive tbody td.col-name {
    position: sticky;
    left: 0;
    z-index: 1;
    background: #fff;
    min-width: 160px;
    text-align: left;
    box-shadow: 2px 0 5px -2px rgba(0,0,0,0.08);
}

.attendance-table-responsive tbody tr:nth-child(even) td.col-name {
    background: #fafbfc;
}

.attendance-table-responsive tbody tr:hover td.col-name {
    background: #eef4ff;
}

/* Summary columns (TH, TP, TLB, TTB) */
.attendance-table-responsive th.col-summary,
.attendance-table-responsive td.col-summary {
    min-width: 80px;
    text-align: center;
}

/* Date columns — compact */
.attendance-table-responsive th.date-col,
.attendance-table-responsive td.date-col {
    min-width: 42px;
    max-width: 42px;
    width: 42px;
    padding: 7px 4px;
    text-align: center;
}

/* Status dots */
.status-dot {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.1s, box-shadow 0.1s;
}

.status-dot:hover {
    transform: scale(1.15);
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.status-dot.present {
    background: #d1f7e6;
    color: #0f9d58;
}

.status-dot.absent {
    background: #ffe0e0;
    color: #d93025;
}

.status-dot.half-day {
    background: #fff3cd;
    color: #856404;
}

.status-dot.on-leave {
    background: #e8d5f5;
    color: #6f42c1;
}

.status-dot.day-off {
    background: #e9ecef;
    color: #6c757d;
}

.status-dot.public-holiday {
    background: #fde8c8;
    color: #b45309;
}

.status-dot.unpaid {
    background: #fce4ec;
    color: #c62828;
}

.status-dot.future {
    background: transparent;
    color: #ced4da;
    cursor: default;
}

.status-dot.not-added {
    background: #f8f9fa;
    color: #adb5bd;
    border: 1px dashed #ced4da;
    cursor: default;
    font-size: 9px;
    width: 30px;
    height: 30px;
}
</style>

@push('script-page')
<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    var filename = $('#filename').val();

    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: filename,
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A2'
            }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class=" mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['report.monthly.attendance'], 'method' => 'get', 'id' => 'report_monthly_attendance']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('month', __(' Month'), ['class' => 'form-label']) }}
                                        {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'month-btn form-control', 'autocomplete' => 'off', 'placeholder' => 'Select month']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                                        {{ Form::select('branch_id', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control select branch_id', 'id' => 'branch-select branch_id']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box" id="department_div">
                                        {{ Form::label('department', __('Department'), ['class' => 'form-label']) }}
                                        <select class="form-control select department_id" name="department"
                                            id="department_id" placeholder="Select Department">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box" id="employee_div">
                                        {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                                        <select class="form-control select" name="employee_id[]" id="employee_id"
                                            placeholder="Select Employee">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="row">
                                <div class="col-auto mt-4">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-primary me-1"
                                        onclick="document.getElementById('report_monthly_attendance').submit(); return false;"
                                        data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                        data-original-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                    </a>
                                    <a href="{{ route('report.monthly.attendance') }}" class="btn btn-sm btn-danger "
                                        data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                        data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i
                                                class="ti ti-refresh text-white-off "></i></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="printableArea">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="badge theme-avtar bg-primary">
                                <i class="ti ti-report"></i>
                            </div>
                            <div class="ms-3">
                                <input type="hidden"
                                    value="{{ $data['branch'] . ' ' . __('Branch') . ' ' . $data['curMonth'] . ' ' . __('Attendance Report of') . ' ' . $data['department'] . ' ' . 'Department' }}"
                                    id="filename">
                                <h5 class="mb-0">{{ __('Report') }}</h5>
                                <div>
                                    <p class="text-muted text-sm mb-0">{{ __('Attendance Summary') }}</p>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if ($data['branch'] != 'All')
            <div class="col">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="badge theme-avtar bg-secondary">
                                    <i class="ti ti-sitemap"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ __('Branch') }}</h5>
                                    <p class="text-muted text-sm mb-0">
                                        {{ $data['branch'] }} </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if ($data['department'] != 'All')
            <div class="col">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="badge theme-avtar bg-primary">
                                    <i class="ti ti-template"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">{{ __('Department') }}</h5>
                                    <p class="text-muted text-sm mb-0">{{ $data['department'] }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="col">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="badge theme-avtar bg-secondary">
                                <i class="ti ti-sum"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-0">{{ __('Duration') }}</h5>
                                <p class="text-muted text-sm mb-0">{{ $data['curMonth'] }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card mon-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="badge theme-avtar bg-primary">
                                <i class="ti ti-file-report"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-0">{{ __('Attendance') }}</h5>
                                <div>
                                    <p class="text-muted text-sm mb-0">{{ __('Total present') }}:
                                        {{ $data['totalPresent'] }}</p>
                                    <p class="text-muted text-sm mb-0">{{ __('Total leave') }}:
                                        {{ $data['totalLeave'] }}</p>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card mon-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="badge theme-avtar bg-secondary">
                                <i class="ti ti-clock"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-0">{{ __('Overtime') }}</h5>
                                <p class="text-muted text-sm mb-0">
                                    {{ __('Total overtime in hours') }} :
                                    {{ number_format($data['totalOvertime'], 2) }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card mon-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="badge theme-avtar bg-primary">
                                <i class="ti ti-info-circle"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-0">{{ __('Early leave') }}</h5>
                                <p class="text-muted text-sm mb-0">{{ __('Total early leave in hours') }}:
                                    {{ number_format($data['totalEarlyLeave'], 2) }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card mon-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="badge theme-avtar bg-secondary">
                                <i class="ti ti-alarm"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-0">{{ __('Employee late') }}</h5>
                                <p class="text-muted text-sm mb-0">{{ __('Total late in hours') }} :
                                    {{ number_format($data['totalLate'], 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header py-2 px-3 d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-semibold" style="font-size:12px;"><strong>TH</strong> <span class="text-success">{{ __('Total Hours') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>TP</strong> <span class="text-primary">{{ __('Present') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>TL</strong> <span style="color:#6f42c1;">{{ __('Leave') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>TPL</strong> <span style="color:#0f9d58;">{{ __('Paid Leave') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>TUL</strong> <span style="color:#dc3545;">{{ __('Unpaid Leave') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>TA</strong> <span class="text-danger">{{ __('Absent') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>THD</strong> <span style="color:#856404;">{{ __('Half Day') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>TDO</strong> <span class="text-secondary">{{ __('Day Off') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>TPH</strong> <span style="color:#b45309;">{{ __('Public Holiday') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>TLB</strong> <span class="text-warning">{{ __('Lunch Break') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>TTB</strong> <span class="text-info">{{ __('Tea Break') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>TWD</strong> <span style="color:#0a5459;">{{ __('Working Days') }}</span></span>
                <span class="text-muted">·</span>
                <span class="fw-semibold" style="font-size:12px;"><strong>EXT</strong> <span style="color:#856404;">{{ __('Extra Days') }}</span></span>
                <span class="text-muted ms-auto" style="font-size:11px;">
                    <i class="ti ti-click me-1"></i>{{ __('Click a date cell to edit attendance') }}
                </span>
            </div>
            <div class="card-body p-2 mb-3">
                <div class="attendance-table-responsive">
                    <table class="table table-bordered text-center mb-0">
                        <thead>
                            <tr>
                                <th class="col-name">{{ __('Name') }}</th>
                                <th class="col-summary" title="{{ __('Total Hours') }}">{{ __('TH') }}</th>
                                <th class="col-summary" title="{{ __('Total Present') }}">{{ __('TP') }}</th>
                                <th class="col-summary" title="{{ __('Total Leave') }}" style="color:#6f42c1;">{{ __('TL') }}</th>
                                <th class="col-summary" title="{{ __('Total Paid Leave') }}" style="color:#0f9d58;">{{ __('TPL') }}</th>
                                <th class="col-summary" title="{{ __('Total Unpaid Leave') }}" style="color:#dc3545;">{{ __('TUL') }}</th>
                                <th class="col-summary" title="{{ __('Total Absent Days') }}" style="color:#dc3545;">{{ __('TA') }}</th>
                                <th class="col-summary" title="{{ __('Half Day') }}" style="color:#856404;">{{ __('THD') }}</th>
                                <th class="col-summary" title="{{ __('Day Off') }}">{{ __('TDO') }}</th>
                                <th class="col-summary" title="{{ __('Public Holiday') }}" style="color:#b45309;">{{ __('TPH') }}</th>
                                <th class="col-summary" title="{{ __('Total Lunch Break') }}">{{ __('TLB') }}</th>
                                <th class="col-summary" title="{{ __('Total Tea Break') }}">{{ __('TTB') }}</th>
                                <th class="col-summary" title="{{ __('Working Days in Month (excl. holidays & day-offs)') }}" style="color:#0a5459;">{{ __('TWD') }}</th>
                                <th class="col-summary" title="{{ __('Extra Days Worked beyond Working Days') }}" style="color:#856404;">{{ __('EXT') }}</th>
                                <th class="col-summary" title="{{ __('Effective Present (half-day=0.5) / Working Days') }}" style="color:#0f9d58;min-width:70px;">{{ __('Score') }}</th>
                                @foreach ($dates as $date)
                                    @php
                                        $dayCarbon = \Carbon\Carbon::parse($currentMonthForDate . '-' . $date);
                                        $dayName   = $dayCarbon->format('D');
                                        $isWeekend = $dayCarbon->isWeekend();
                                    @endphp
                                    <th class="date-col text-center" style="padding: 4px 2px; vertical-align: middle;">
                                        <div style="font-size: 13px; font-weight: 700; line-height: 1.1;">{{ $date }}</div>
                                        <div style="font-size: 10px; font-weight: 600; line-height: 1.1; margin-top: 2px; color: {{ $isWeekend ? '#d93025' : '#6c757d' }};">
                                            {{ $dayName }}
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employeesAttendance as $attendance)
                                <tr data-employee-row="{{ $attendance['employee_id'] }}">
                                    <td class="col-name">{{ $attendance['name'] }}</td>
                                    <td class="col-summary emp-total-hours">{{ $attendance['total_hours'] }}</td>
                                    <td class="col-summary emp-total-present">{{ $attendance['total_present'] }}</td>
                                    <td class="col-summary emp-total-leave" style="color:#6f42c1;">{{ $attendance['total_leave'] ?? 0 }}</td>
                                    <td class="col-summary emp-total-paid-leave" style="color:#0f9d58;">{{ $attendance['total_paid_leave'] ?? 0 }}</td>
                                    <td class="col-summary emp-total-unpaid-leave" style="color:#dc3545;">{{ $attendance['total_unpaid_leave'] ?? 0 }}</td>
                                    <td class="col-summary emp-total-absent" style="color:#dc3545;">{{ $attendance['total_absent'] ?? 0 }}</td>
                                    <td class="col-summary emp-total-halfday" style="color:#856404;">{{ $attendance['total_half_day'] ?? 0 }}</td>
                                    <td class="col-summary emp-total-dayoff text-secondary">{{ $attendance['total_day_off'] ?? 0 }}</td>
                                    <td class="col-summary emp-total-holiday" style="color:#b45309;">{{ $attendance['total_holiday'] ?? 0 }}</td>
                                    <td class="col-summary emp-total-lunch">{{ $attendance['total_lunch_break'] }}</td>
                                    <td class="col-summary emp-total-tea">{{ $attendance['total_tea_break'] }}</td>
                                    <td class="col-summary emp-working-days fw-semibold" style="color:#0a5459;">{{ $attendance['working_days'] ?? 0 }}</td>
                                    <td class="col-summary emp-extra-days fw-semibold" style="color:#856404;">
                                        {{ ($attendance['extra_days'] ?? 0) > 0 ? '+' . $attendance['extra_days'] : '—' }}
                                    </td>
                                    <td class="col-summary emp-score fw-semibold" style="color:#0f9d58;">{{ $attendance['attendance_score'] ?? '0/0' }}</td>
                                    @foreach ($attendance['status'] as $key => $status)
                                        <td class="date-col">
                                            @if($status)
                                                @php
                                                    $statusVal   = is_array($status) ? $status['status'] : $status;
                                                    $statusLabel = is_array($status) ? ($status['label'] ?? $statusVal) : $statusVal;
                                                    $isFutureCell= is_array($status) ? ($status['future'] ?? false) : false;
                                                    $clockIn     = is_array($status) ? ($status['clock_in'] ?? '') : '';
                                                    $clockOut    = is_array($status) ? ($status['clock_out'] ?? '') : '';
                                                    $shiftTime   = is_array($status) ? ($status['company_shift_time'] ?? '') : '';
                                                    $dotClass    = match($statusVal) {
                                                        'P'  => 'present',
                                                        'HD' => 'half-day',
                                                        'L'  => 'on-leave',
                                                        'U'  => 'unpaid',
                                                        'DO' => 'day-off',
                                                        'PH' => 'public-holiday',
                                                        'NA' => 'not-added',
                                                        default => 'absent',
                                                    };
                                                    // Any cell on/before today is editable (future cells are read-only)
                                                    $isClickable = !$isFutureCell;
                                                @endphp
                                                <span class="status-dot {{ $dotClass }} {{ $isClickable ? 'attendance-status-cell' : '' }}"
                                                    @if($isClickable)
                                                    data-employee-id="{{ $attendance['employee_id'] }}"
                                                    data-employee-name="{{ $attendance['name'] }}"
                                                    data-date="{{ $currentMonthForDate . '-' . $key }}"
                                                    data-date-label="{{ \Carbon\Carbon::parse($currentMonthForDate . '-' . $key)->format('d M Y (D)') }}"
                                                    data-status="{{ $statusVal }}"
                                                    data-clock-in="{{ $clockIn }}"
                                                    data-clock-out="{{ $clockOut }}"
                                                    data-shift="{{ $shiftTime }}"
                                                    data-leave-pay-type="{{ is_array($status) ? ($status['leave_pay_type'] ?? 'unpaid') : 'unpaid' }}"
                                                    data-use-leave-balance="{{ is_array($status) ? ($status['use_leave_balance'] ?? 0) : 0 }}"
                                                    data-leave-type-id="{{ is_array($status) ? ($status['leave_type_id'] ?? '') : '' }}"
                                                    data-sandwich-rule-id="{{ is_array($status) ? ($status['sandwich_leave_rule_id'] ?? '') : '' }}"
                                                    data-sandwich-rate="{{ is_array($status) ? ($status['sandwich_deduction_rate'] ?? '') : '' }}"
                                                    title="{{ $statusLabel }} — {{ __('Click to edit') }}"
                                                    @else
                                                    title="{{ $statusLabel }}"
                                                    @endif
                                                    style="{{ !$isClickable ? 'cursor:default;' : '' }}">
                                                    {{ $statusVal === 'NA' ? '–' : $statusVal }}
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="attendanceStatusModal" tabindex="-1" aria-labelledby="attendanceStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceStatusModalLabel">{{ __('Edit Attendance Status') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                {{-- Error Alert --}}
                <div id="attendanceErrorAlert" class="alert alert-danger alert-dismissible fade" role="alert" style="display:none;">
                    <strong>{{ __('Error:') }}</strong> <span id="attendanceErrorMessage"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <form id="attendance-status-form">
                    <input type="hidden" id="attendance-status-employee" name="employee_id">
                    <input type="hidden" id="attendance-status-date" name="date">

                    {{-- Employee (read-only) --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Employee') }}</label>
                        <input type="text" id="modal-employee-name" class="form-control" readonly>
                    </div>

                    {{-- Date (read-only) --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __('Date') }}</label>
                        <input type="text" id="modal-date-label" class="form-control" readonly>
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label for="attendance-status" class="form-label">{{ __('Status') }}</label>
                        <select class="form-control" id="attendance-status" name="status">
                            <option value="P">{{ __('Present (P)') }}</option>
                            <option value="A">{{ __('Absent (A)') }}</option>
                            <option value="HD">{{ __('Half Day (HD)') }}</option>
                            <option value="L">{{ __('Leave (L)') }}</option>
                            <option value="U">{{ __('Unpaid (U)') }}</option>
                            <option value="DO">{{ __('Day Off (DO)') }}</option>
                            <option value="PH">{{ __('Public Holiday (PH)') }}</option>
                        </select>
                    </div>

                    {{-- Leave Pay Type --}}
                    <div class="mb-3" id="leave-pay-type-group">
                        <label class="form-label d-block">{{ __('Leave Pay Type') }}</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="leave_pay_type" id="pay_unpaid" value="unpaid" checked>
                            <label class="form-check-label" for="pay_unpaid">{{ __('Unpaid (LOP)') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="leave_pay_type" id="pay_paid" value="paid">
                            <label class="form-check-label" for="pay_paid">{{ __('Paid') }}</label>
                        </div>
                    </div>

                    {{-- Deduct from Leave Balance --}}
                    <div class="mb-3" id="use-leave-balance-group">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="use_leave_balance" name="use_leave_balance" value="1">
                            <label class="form-check-label" for="use_leave_balance">{{ __('Deduct from Leave Balance?') }}</label>
                        </div>
                        <div id="leave-type-select-wrapper" style="display: none;" class="mt-2">
                            <label for="modal_leave_type_id" class="form-label">{{ __('Select Leave Type') }} <span class="text-danger">*</span></label>
                            <select name="leave_type_id" id="modal_leave_type_id" class="form-control">
                                <option value="">{{ __('Select Leave Type') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Sandwich Leave Rule (Synchronized with Edit Leave) --}}
                    <div class="mb-3" id="sandwich-rule-modal-group">
                        <label for="modal_sandwich_leave_rule_id" class="form-label">
                            {{ __('Sandwich Leave Rule') }}
                        </label>
                        <select name="sandwich_leave_rule_id" id="modal_sandwich_leave_rule_id" class="form-control">
                            <option value="">{{ __('None (Standard Leave)') }}</option>
                            @if (!empty($data['sandwich_rules']) && count($data['sandwich_rules']) > 0)
                                @foreach ($data['sandwich_rules'] as $srule)
                                    <option value="{{ $srule->id }}"
                                        data-rate="{{ $srule->deduction_rate }}"
                                        data-type="{{ $srule->rate_type }}">
                                        {{ $srule->name }} ({{ number_format($srule->deduction_rate, 2) }}{{ $srule->rate_type == 'percentage' ? '%' : 'x' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <input type="hidden" name="sandwich_deduction_rate" id="modal_sandwich_deduction_rate" value="">
                    </div>

                    {{-- Shift Selection --}}
                    <div class="mb-3">
                        <label for="company_shift_time" class="form-label">{{ __('Shift Time') }}</label>
                        @if(!empty($data['company_shifts']) && count($data['company_shifts']) > 0)
                            <select name="company_shift_time" id="company_shift_time" class="form-control">
                                <option value="">{{ __('Select Shift Time') }}</option>
                                @foreach ($data['company_shifts'] as $index => $shift)
                                    @php
                                        $name = !empty($shift['name']) ? $shift['name'] : (!empty($shift['title']) ? $shift['title'] : 'Shift ' . ($index + 1));
                                        $start = $shift['start'] ?? '';
                                        $end = $shift['end'] ?? '';
                                        $timeStr = ($start && $end) ? " ({$start} - {$end})" : "";

                                        $colorDot = '🟣 ';
                                        if (!empty($shift['color'])) {
                                            $c = strtolower(ltrim($shift['color'], '#'));
                                            if (strlen($c) === 6) {
                                                $r = hexdec(substr($c, 0, 2));
                                                $g = hexdec(substr($c, 2, 2));
                                                $b = hexdec(substr($c, 4, 2));
                                                if ($r > 180 && $g < 100 && $b < 100) { $colorDot = '🔴 '; }
                                                elseif ($g > 140 && $r < 140 && $b < 140) { $colorDot = '🟢 '; }
                                                elseif ($b > 140 && $r < 140) { $colorDot = '🔵 '; }
                                                elseif ($r > 200 && $g > 160 && $b < 100) { $colorDot = '🟡 '; }
                                                elseif ($r > 200 && $g > 100 && $b < 80) { $colorDot = '🟠 '; }
                                                elseif ($r > 100 && $b > 100 && $g < 120) { $colorDot = '🟣 '; }
                                                elseif ($r < 80 && $g < 80 && $b < 80) { $colorDot = '⚫ '; }
                                            }
                                        }

                                        $label = $colorDot . $name . $timeStr;
                                    @endphp
                                    <option value="{{ $shift['start'] . ' - ' . $shift['end'] }}"
                                            data-start="{{ $shift['start'] }}"
                                            data-end="{{ $shift['end'] }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="alert alert-info mt-2">
                                {{ __('Please set company shifts in Company Settings first.') }}
                            </div>
                        @endif
                    </div>

                    {{-- Clock In --}}
                    <div class="mb-3">
                        <label for="attendance-clock-in" class="form-label">{{ __('Clock In') }}</label>
                        <input type="time" id="attendance-clock-in" name="clock_in" class="form-control">
                    </div>

                    {{-- Clock Out --}}
                    <div class="mb-3">
                        <label for="attendance-clock-out" class="form-label">{{ __('Clock Out') }}</label>
                        <input type="time" id="attendance-clock-out" name="clock_out" class="form-control">
                    </div>

                    {{-- Passcode --}}
                    <div class="mb-3">
                        <label for="attendance-passcode" class="form-label">{{ __('Passcode') }}</label>
                        <input type="password" id="attendance-passcode" name="passcode" class="form-control" placeholder="{{ __('Enter Passcode') }}" autocomplete="new-password">
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="saveAttendanceStatusBtn">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
    $(document).ready(function() {
        var b_id = $('#branch_id').val();
        // getDepartment(b_id);
    });
    $(document).on('change', 'select[name=branch_id]', function() {
        var branch_id = $(this).val();

        getDepartment(branch_id);
    });

    function getDepartment(bid) {

        $.ajax({
            url: "{{ route('monthly.getdepartment') }}",
            type: 'POST',
            data: {
                "branch_id": bid,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {

                $('.department_id').empty();
                var emp_selct = `<select class="department_id form-control multi-select" id="choices-multiple" multiple="" required="required" name="department_id[]">
                </select>`;
                $('.department_div').html(emp_selct);

                $('.department_id').append("<option value=\"\">{{ __('Select Department') }}</option>");
                $.each(data, function(key, value) {
                    $('.department_id').append('<option value="' + key + '">' + value +
                        '</option>');
                });
                new Choices('#choices-multiple', {
                    removeItemButton: true,
                });
            }
        });
    }

    $(document).on('change', '.department_id', function() {
        var department_id = $(this).val();
        getEmployee(department_id);
    });

    function getEmployee(did) {

        $.ajax({
            url: "{{ route('monthly.getemployee') }}",
            type: 'POST',
            data: {
                "department_id": did,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {

                $('#employee_id').empty();

                $("#employee_div").html('');
                // $('#employee_div').append('<select class="form-control" id="employee_id" name="employee_id[]"  multiple></select>');
                $('#employee_div').append(
                    "<label for='employee' class='form-label'>{{ __('Employee') }}</label><select class='form-control' id='employee_id' name='employee_id[]' multiple></select>"
                );

                $('#employee_id').append("<option value=\"\">{{ __('Select Employee') }}</option>");

                $.each(data, function(key, value) {
                    $('#employee_id').append('<option value="' + key + '">' + value + '</option>');
                });

                var multipleCancelButton = new Choices('#employee_id', {
                    removeItemButton: true,
                });
            }
        });
    }
</script>

<script>
    $(document).ready(function() {
        var now = new Date();
        var month = (now.getMonth() + 1);
        if (month < 10) month = "0" + month;
        var today = now.getFullYear() + '-' + month;
        $('.current_date').val(today);
    });
</script>

<script>
    $(document).on('click', '.attendance-status-cell', function() {
        var $cell = $(this);

        $('#attendance-status-employee').val($cell.data('employee-id'));
        $('#attendance-status-date').val($cell.data('date'));
        $('#attendance-status').val($cell.data('status'));

        // Populate read-only info fields
        $('#modal-employee-name').val($cell.data('employee-name') || '');
        $('#modal-date-label').val($cell.data('date-label') || $cell.data('date'));

        // Pre-fill shift, clock in/out, leave pay type, leave balance switch
        var savedShift       = $cell.data('shift')          || '';
        var savedClockIn     = $cell.data('clock-in')       || '';
        var savedClockOut    = $cell.data('clock-out')      || '';
        var leavePayType     = $cell.data('leave-pay-type') || 'unpaid';
        var useLeaveBal      = $cell.data('use-leave-balance') || 0;
        var savedLeaveTypeId = $cell.data('leave-type-id')  || '';
        var savedSandwichId  = $cell.data('sandwich-rule-id') || '';
        var empId            = $cell.data('employee-id');
        var currentStatus    = $cell.data('status');

        $('#company_shift_time').val(savedShift);
        $('#attendance-clock-in').val(savedClockIn);
        $('#attendance-clock-out').val(savedClockOut);
        $('#attendance-passcode').val('');

        // Pre-set leave pay type radio
        $('input[name="leave_pay_type"][value="' + leavePayType + '"]').prop('checked', true);

        // Auto-check "Deduct from Leave Balance" if this is a leave status cell
        var isLeaveStatus = ['L', 'HD'].includes(currentStatus);
        var isChecked = isLeaveStatus
            ? (useLeaveBal == 1 || useLeaveBal === true || useLeaveBal === '1' || useLeaveBal === true)
            : false;

        // If leave status and useLeaveBal not explicitly set, default to true (leave = deduct balance)
        if (isLeaveStatus && (useLeaveBal === 0 || useLeaveBal === '0' || useLeaveBal === '') && savedLeaveTypeId) {
            isChecked = true;
        }
        $('#use_leave_balance').prop('checked', isChecked);

        // Show/hide the leave type select wrapper immediately based on checkbox state
        if (isChecked) {
            $('#leave-type-select-wrapper').show();
        } else {
            $('#leave-type-select-wrapper').hide();
        }

        // Pre-select Sandwich Rule
        if (savedSandwichId) {
            $('#modal_sandwich_leave_rule_id').val(savedSandwichId);
        } else {
            $('#modal_sandwich_leave_rule_id').val('');
        }

        // Load employee leave types for the select dropdown
        var $ltSel = $('#modal_leave_type_id');
        $ltSel.empty().append('<option value="">{{ __("Loading...") }}</option>');
        $.ajax({
            url: "{{ route('leave.jsoncount') }}",
            type: 'POST',
            data: { employee_id: empId, _token: "{{ csrf_token() }}" },
            success: function(data) {
                $ltSel.empty().append('<option value="">{{ __("Select Leave Type") }}</option>');
                var matchedIsPaid = null;
                $.each(data, function(k, v) {
                    var label = v.title + ' (' + v.total_leave + '/' + v.days + ' {{ __("used") }}, {{ __("Remaining") }}: ' + v.remaining + ')';
                    var isSelected = (savedLeaveTypeId && savedLeaveTypeId == v.id);
                    var selected = isSelected ? ' selected' : '';
                    $ltSel.append('<option value="' + v.id + '"' + selected + ' data-is-paid="' + (v.is_paid ? 1 : 0) + '">' + label + '</option>');
                    if (isSelected) {
                        matchedIsPaid = v.is_paid ? 1 : 0;
                    }
                });

                // After options are loaded, ALWAYS restore the saved leave_pay_type.
                // Admin may intentionally set Unpaid (LOP) on a Casual (paid) leave type,
                // so we must NEVER auto-correct the radio — just honor what was saved.
                $('input[name="leave_pay_type"][value="' + leavePayType + '"]').prop('checked', true);

                // If no leave type was previously matched (new selection), auto-set from leave type default
                if (matchedIsPaid !== null && !savedLeaveTypeId) {
                    var defaultPaid = matchedIsPaid === 1 ? 'paid' : 'unpaid';
                    $('input[name="leave_pay_type"][value="' + defaultPaid + '"]').prop('checked', true);
                }
            },
            error: function() {
                $ltSel.empty().append('<option value="">{{ __("Select Leave Type") }}</option>');
            }
        });

        function toggleLeaveOptionGroups() {
            var st = $('#attendance-status').val();
            if (['L', 'HD', 'A', 'U'].includes(st)) {
                $('#leave-pay-type-group, #use-leave-balance-group').slideDown(200);
                if ($('#use_leave_balance').is(':checked')) {
                    $('#leave-type-select-wrapper').slideDown(200);
                } else {
                    $('#leave-type-select-wrapper').slideUp(200);
                }
            } else {
                $('#leave-pay-type-group, #use-leave-balance-group, #leave-type-select-wrapper').slideUp(200);
            }
        }

        toggleLeaveOptionGroups();

        // Clear error alert
        $('#attendanceErrorAlert').hide();
        $('#attendanceErrorMessage').text('');

        $('#attendanceStatusModal').data('cell', $cell).modal('show');
    });

    $(document).on('change', '#use_leave_balance', function() {
        if ($(this).is(':checked')) {
            $('#leave-type-select-wrapper').slideDown(200);
        } else {
            $('#leave-type-select-wrapper').slideUp(200);
        }
    });

    $(document).on('change', '#modal_leave_type_id', function() {
        // Do NOT auto-change leave_pay_type when admin changes leave type.
        // Admin controls Paid/Unpaid (LOP) manually.
        // Only show sandwich rule section if needed — pay type radio is admin's choice.
    });

    $(document).on('change', '#attendance-status', function() {
        var st = $(this).val();
        if (['L', 'HD', 'A', 'U'].includes(st)) {
            $('#leave-pay-type-group, #use-leave-balance-group').slideDown(200);
            if ($('#use_leave_balance').is(':checked')) {
                $('#leave-type-select-wrapper').slideDown(200);
            }
        } else {
            $('#leave-pay-type-group, #use-leave-balance-group, #leave-type-select-wrapper').slideUp(200);
        }
    });

    // Shift select → auto-fill clock in/out times
    $(document).on('change', '#company_shift_time', function() {
        var $opt  = $(this).find('option:selected');
        var start = $opt.data('start') || '';
        var end   = $opt.data('end')   || '';
        if (start) $('#attendance-clock-in').val(start);
        if (end)   $('#attendance-clock-out').val(end);
    });

    $('#saveAttendanceStatusBtn').on('click', function() {
        var $btn = $(this);
        var $cell = $('#attendanceStatusModal').data('cell');
        var employeeId = $('#attendance-status-employee').val();
        var date = $('#attendance-status-date').val();
        var status = $('#attendance-status').val();
        var companyShiftTime = $('#company_shift_time').val();
        var passcode = $('#attendance-passcode').val();
        var clockIn  = $('#attendance-clock-in').val();
        var clockOut = $('#attendance-clock-out').val();
        var leavePayType = $('input[name="leave_pay_type"]:checked').val() || 'unpaid';
        var useLeaveBalance = $('#use_leave_balance').is(':checked') ? 1 : 0;
        var leaveTypeId = $('#modal_leave_type_id').val() || '';
        var sandwichRuleId = $('#modal_sandwich_leave_rule_id').val() || '';
        var oldStatus = $cell.data('status');

        // Hide error alert on new attempt
        $('#attendanceErrorAlert').hide();

        if (!employeeId || !date) {
            showAttendanceError('Please select employee and date');
            return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url: "{{ route('report.monthly.attendance.status') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                employee_id: employeeId,
                date: date,
                status: status,
                company_shift_time: companyShiftTime,
                passcode: passcode,
                clock_in: clockIn,
                clock_out: clockOut,
                leave_pay_type: leavePayType,
                use_leave_balance: useLeaveBalance,
                leave_type_id: leaveTypeId,
                sandwich_leave_rule_id: sandwichRuleId,
            },
            success: function(response) {
                if (response.success) {
                    var dotClassMap = {
                        'P': 'present',
                        'HD': 'half-day',
                        'L': 'on-leave',
                        'U': 'unpaid',
                        'DO': 'day-off',
                        'PH': 'public-holiday',
                        'A': 'absent'
                    };
                    var newClass = dotClassMap[status] || 'absent';

                    // Update the clicked cell dot
                    $cell.removeClass('present half-day on-leave unpaid day-off public-holiday absent not-added')
                         .addClass(newClass)
                         .data('status', status)
                         .data('clock-in', response.clock_in || '')
                         .data('clock-out', response.clock_out || '')
                         .data('shift', response.company_shift_time || '')
                         .data('leave-pay-type', response.leave_pay_type || 'unpaid')
                         .data('use-leave-balance', response.use_leave_balance || 0)
                         .data('leave-type-id', response.leave_type_id || '')
                         .data('sandwich-rule-id', response.sandwich_leave_rule_id || '')
                         .attr('data-status', status)
                         .attr('data-clock-in', response.clock_in || '')
                         .attr('data-clock-out', response.clock_out || '')
                         .attr('data-shift', response.company_shift_time || '')
                         .attr('data-leave-pay-type', response.leave_pay_type || 'unpaid')
                         .attr('data-use-leave-balance', response.use_leave_balance || 0)
                         .attr('data-leave-type-id', response.leave_type_id || '')
                         .attr('data-sandwich-rule-id', response.sandwich_leave_rule_id || '')
                         .text(status);

                    // Refresh all row summary columns without reload
                    if (response.row_summary) {
                        var $row = $cell.closest('tr[data-employee-row]');
                        var rs = response.row_summary;
                        $row.find('.emp-total-hours').text(rs.total_hours);
                        $row.find('.emp-total-present').text(rs.total_present);
                        $row.find('.emp-total-leave').text(rs.total_leave);
                        $row.find('.emp-total-paid-leave').text(rs.total_paid_leave ?? 0);
                        $row.find('.emp-total-unpaid-leave').text(rs.total_unpaid_leave ?? 0);
                        $row.find('.emp-total-absent').text(rs.total_absent ?? 0);
                        $row.find('.emp-total-halfday').text(rs.total_half_day);
                        $row.find('.emp-total-dayoff').text(rs.total_day_off);
                        $row.find('.emp-total-holiday').text(rs.total_holiday);
                        $row.find('.emp-total-lunch').text(rs.total_lunch_break);
                        $row.find('.emp-total-tea').text(rs.total_tea_break);
                        $row.find('.emp-score').text(rs.attendance_score);
                    }

                    $('#attendanceStatusModal').modal('hide');
                } else {
                    showAttendanceError(response.message || 'Failed to update attendance');
                }
            },
            error: function(xhr) {
                var errorMsg = 'An error occurred';

                if (xhr.status === 422) {
                    // Validation error
                    var errors = xhr.responseJSON.errors;
                    var errorList = [];
                    for (var field in errors) {
                        errorList.push(errors[field].join(', '));
                    }
                    errorMsg = errorList.join(' | ');
                } else if (xhr.status === 403) {
                    errorMsg = 'Permission denied';
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                showAttendanceError(errorMsg);
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    function showAttendanceError(message) {
        $('#attendanceErrorMessage').text(message);
        $('#attendanceErrorAlert').addClass('show').show();
    }
</script>
@endpush
