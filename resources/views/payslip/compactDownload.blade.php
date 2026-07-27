@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
    // Employee and Payslip objects are also passed to the view.
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 8mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 9px;
    color: #1a1a1a;
    background: #fff;
}
.page { padding: 6px; }

.cpt-top {
    background: {{ $_themeColor }}; color: #fff;
    padding: 8px 14px;
}
.cpt-top h2 { font-size: 12px; font-weight: bold; margin: 0; }
.cpt-top span { font-size: 9px; opacity: 0.85; }

.cpt-header {
    padding: 8px 14px;
    border-bottom: 2px solid {{ $_themeColor }};
    background: {{ $_bgLight }};
}
.cpt-header img { height: 30px; }
.cpt-company { text-align: right; font-size: 8px; color: #555; line-height: 1.6; }
.cpt-company strong { color: {{ $_themeColor }}; font-size: 9px; }

.cpt-employee {
    display: flex; justify-content: space-between;
    padding: 6px 14px; background: {{ $_bgMed }}; font-size: 9px;
}
.cpt-employee > div { flex: 1; }
.cpt-employee .e-right { text-align: right; }
.cpt-employee strong { color: {{ $_themeColor }}; }

.cpt-section { padding: 6px 14px; }
.cpt-section-title {
    font-weight: bold; font-size: 9px; color: {{ $_themeColor }};
    border-bottom: 1px solid {{ $_themeColor }}44;
    padding-bottom: 3px; margin-bottom: 4px;
    text-transform: uppercase; letter-spacing: 0.5px;
}

.cpt-table { width: 100%; border-collapse: collapse; }
.cpt-table td {
    padding: 3px 5px; font-size: 9px;
    border-bottom: 1px solid #f0f0f0;
}
.cpt-label { color: #666; width: 55%; }
.cpt-value { text-align: right; font-weight: bold; }
.cpt-total td {
    font-weight: bold; color: {{ $_themeColor }};
    border-top: 2px solid {{ $_themeColor }} !important;
    padding-top: 5px; font-size: 10px;
}

.cpt-two-col > table { width: 48%; display: inline-table; vertical-align: top; }
.cpt-two-col > table + table { margin-left: 4%; }

.cpt-net-row {
    background: {{ $_themeColor }}; color: #fff;
    padding: 8px 14px; margin: 0 14px;
}
.cpt-net-label { font-size: 11px; font-weight: bold; }
.cpt-net-value { font-size: 15px; font-weight: bold; text-align: right; }

.cpt-stamp-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 6px 14px; font-size: 8px; color: #888;
}
.cpt-stamp-row img {
    width: 48px; height: 48px; border-radius: 50%;
    object-fit: contain; border: 2px solid {{ $_themeColor }}44;
}
.cpt-stamp-placeholder {
    width: 48px; height: 48px; border-radius: 50%;
    border: 2px dashed {{ $_themeColor }}44;
    display: flex; align-items: center; justify-content: center;
    font-size: 6px; color: {{ $_themeColor }}66;
    text-align: center;
}

.cpt-paid { color: #155724; font-weight: bold; }
.cpt-unpaid { color: #721c24; font-weight: bold; }

.cpt-footer {
    text-align: center; font-size: 7.5px; color: #aaa;
    padding: 8px 14px; border-top: 1px solid #eee; margin-top: 8px;
}
</style>
</head>
<body>
<div class="page">

    <div class="cpt-top"><h2>PAY SLIP</h2> <span>{{ $slipLabel }}</span></div>

    <div class="cpt-header">
        <img src="{{ $logoUrl }}" alt="logo">
        <div class="cpt-company">
            <strong>{{ $companyName }}</strong><br>
            @if ($companyPhone) {{ $companyPhone }} @endif
            @if ($companyEmail) | {{ $companyEmail }} @endif
        </div>
    </div>

    @if($_showEmployeeDetails)
    <div class="cpt-employee">
        <div>
            @if($_showName)<strong>{{ $employee->name }}</strong><br>@endif
            @if($_showDesignation){{ optional($employee->designation)->name ?? '—' }}@endif
            @if($_showEmployeeId && $employee->employee_id) #{{ $employee->employee_id }} @endif
        </div>
        <div class="e-right">
            {{ $payslipTypeName }}<br>
            Status: <span class="{{ $isPaid ? 'cpt-paid' : 'cpt-unpaid' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span>
        </div>
    </div>
    @endif

    <div class="cpt-section">
        <table class="cpt-table">
            <tr>
                <td class="cpt-label">Working Days</td><td class="cpt-value">{{ $officeDays }}</td>
                <td class="cpt-label">Present</td><td class="cpt-value" style="color:#198754;">{{ $presentDaysFmt }}</td>
            </tr>
            <tr>
                <td class="cpt-label">Absent</td><td class="cpt-value" style="color:#dc3545;">{{ $absentDaysFmt }}</td>
                <td class="cpt-label">Leaves</td><td class="cpt-value" style="color:#e07900;">{{ $approvedLeaves }}</td>
            </tr>
            @if (!$isHourly)
            <tr>
                <td class="cpt-label">Per Day</td><td class="cpt-value">{{ $_fmtMoney($perDaySalary) }}</td>
                <td class="cpt-label">Per Hour</td><td class="cpt-value">{{ $_fmtMoney($perHourSalary) }}</td>
            </tr>
            @endif
            <tr>
                <td class="cpt-label">Hours Worked</td><td class="cpt-value">{{ $totalWorkHours }} hrs</td>
                <td class="cpt-label">Department</td><td class="cpt-value">{{ optional($employee->department)->name ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="cpt-section">
        <table class="cpt-table" style="width:48%; display:inline-table; vertical-align:top;">
            <tr><td class="cpt-section-title" colspan="2">Earnings</td></tr>
            @foreach ($earRows as $er)
            <tr><td class="cpt-label">{{ $er['label'] }}</td><td class="cpt-value">{{ $er['amount'] !== null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
            @endforeach
            <tr class="cpt-total"><td class="cpt-label">Total</td><td class="cpt-value">{{ $_fmtMoney($totalEarnings) }}</td></tr>
        </table>
        <table class="cpt-table" style="width:48%; display:inline-table; vertical-align:top; margin-left:4%;">
            <tr><td class="cpt-section-title" colspan="2">Deductions</td></tr>
            @forelse ($dedRows as $dr)
            <tr><td class="cpt-label">{{ $dr['label'] }}</td><td class="cpt-value">{{ $dr['amount'] !== null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
            @empty
            <tr><td class="cpt-label" style="color:#aaa;font-style:italic;">No deductions</td><td></td></tr>
            @endforelse
            <tr class="cpt-total"><td class="cpt-label">Total</td><td class="cpt-value">{{ $_fmtMoney($totalDeductions) }}</td></tr>
        </table>
    </div>

    <table class="cpt-net-row" style="width:auto;">
        <tr>
            <td class="cpt-net-label">Net Pay</td>
            <td class="cpt-net-value">{{ $_fmtMoney($netSalary) }}</td>
        </tr>
    </table>

    <div class="cpt-stamp-row">
        <div>Authorized Digital Stamp &mdash; {{ $companyName }}</div>
        @if ($_stampUrl)
            <img src="{{ $_stampUrl }}" alt="Stamp">
        @else
            <div class="cpt-stamp-placeholder">Stamp</div>
        @endif
    </div>

    @if($_showFooter)
    <div class="cpt-footer">This is a computer-generated payslip. No signature is required.</div>
    @endif

</div>
</body>
</html>
