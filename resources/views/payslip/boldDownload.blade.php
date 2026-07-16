@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 8mm; }
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; color:#1a1a1a; background:#0d0d0d; }
.page { padding: 6px; }
.bld-card { background:#fff; }
.bld-strip { height:4px; background:{{ $_themeColor }}; }
.bld-hero { background:#111; color:#fff; padding:10px 14px; }
.bld-hero h2 { font-size:16px; font-weight:bold; color:{{ $_themeColor }}; margin:0; letter-spacing:2px; text-transform:uppercase; }
.bld-hero span { font-size:8px; opacity:0.7; }
.bld-hero img { height:26px; }
.bld-body { padding:10px 14px; }
.bld-title { font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#111; border-bottom:2px solid {{ $_themeColor }}; padding-bottom:2px; margin-bottom:3px; }
.bld-table { width:100%; border-collapse:collapse; }
.bld-table td { padding:2px 3px; font-size:9px; border-bottom:1px solid #e8e8e8; }
.bld-lbl { color:#666; font-weight:600; width:48%; }
.bld-val { text-align:right; font-weight:bold; }
.bld-total td { font-weight:bold; color:{{ $_themeColor }}; border-top:2px solid {{ $_themeColor }} !important; padding-top:3px; font-size:9.5px; }
.bld-net { background:#111; color:#fff; padding:8px 14px; margin:6px 0; border-left:3px solid {{ $_themeColor }}; }
.bld-net-lbl { font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; }
.bld-net-val { font-size:15px; font-weight:bold; color:{{ $_themeColor }}; text-align:right; }
.bld-stamp { text-align:center; padding:4px 0; border-top:2px solid #111; margin-top:4px; }
.bld-stamp img { width:44px; height:44px; border-radius:50%; object-fit:contain; border:2px solid {{ $_themeColor }}; }
.bld-stamp-ph { width:44px; height:44px; border-radius:50%; border:2px dashed {{ $_themeColor }}66; display:inline-block; padding-top:10px; font-size:5px; color:{{ $_themeColor }}88; }
.bld-paid { background:#111; color:#fff; font-weight:bold; }
.bld-unpaid { background:#dc3545; color:#fff; font-weight:bold; }
.bld-sig { margin-top:6px; }
.bld-sig td { text-align:center; padding:2px 8px; }
.bld-sig-line { border-top:2px solid #111; width:75px; margin:6px auto 2px; }
.bld-sig-lbl { font-size:7px; color:#888; font-weight:bold; text-transform:uppercase; }
.bld-footer { background:#111; color:#fff; text-align:center; padding:5px 14px; font-size:7px; line-height:1.5; }
.bld-footer strong { color:{{ $_themeColor }}; }
</style>
</head>
<body>
<div class="page">
<div class="bld-card">
    <div class="bld-strip"></div>
    <div class="bld-hero">
        <table style="width:100%;"><tr>
            <td><h2>PAY SLIP</h2><span>{{ $slipLabel }}</span></td>
            <td style="text-align:right;"><img src="{{ $logoUrl }}" alt="logo"></td>
        </tr></table>
    </div>
    <div class="bld-body">

        <table style="width:100%;border-collapse:collapse;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="bld-title">Employee</div>
                <table class="bld-table">
                    <tr><td class="bld-lbl">Name</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $employee->name }}</td></tr>
                    <tr><td class="bld-lbl">Designation</td><td class="bld-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                    <tr><td class="bld-lbl">ID</td><td class="bld-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                    <tr><td class="bld-lbl">Department</td><td class="bld-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                    <tr><td class="bld-lbl">DOJ</td><td class="bld-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="bld-title">Payment</div>
                <table class="bld-table">
                    <tr><td class="bld-lbl">Bank</td><td class="bld-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                    <tr><td class="bld-lbl">Account</td><td class="bld-val">{{ $employee->account_number ?? '—' }}</td></tr>
                    <tr><td class="bld-lbl">Pay Period</td><td class="bld-val">{{ $slipLabel }}</td></tr>
                    <tr><td class="bld-lbl">Status</td><td class="bld-val"><span class="{{ $isPaid ? 'bld-paid' : 'bld-unpaid' }}" style="padding:1px 8px;font-size:8px;">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span></td></tr>
                </table>
            </td>
        </tr></table>

        @if(!$isHourly)
        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="bld-title">Days &amp; Work</div>
                <table class="bld-table">
                    <tr><td class="bld-lbl">Working</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                    <tr><td class="bld-lbl">Present</td><td class="bld-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="bld-lbl">Leave</td><td class="bld-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="bld-lbl">Absent</td><td class="bld-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    @if($extraDays>0)
                    <tr><td class="bld-lbl" style="color:#856404;">Extra/OT</td><td class="bld-val" style="color:#856404;">+{{ $extraDaysFmt }}</td></tr>
                    @endif
                    <tr><td class="bld-lbl">Hours</td><td class="bld-val">{{ $totalWorkHours }} hrs</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="bld-title">Leave Summary</div>
                <table class="bld-table">
                    <tr><td class="bld-lbl" style="font-weight:bold;">This Month</td><td class="bld-val" style="font-weight:bold;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="bld-lbl">Paid</td><td class="bld-val" style="color:#198754;">{{ $paidLeaveDays }}</td></tr>
                    <tr><td class="bld-lbl">Unpaid</td><td class="bld-val" style="color:#dc3545;">{{ $unpaidLeaveDays }}</td></tr>
                    <tr><td class="bld-lbl" style="font-weight:bold;">Overall</td><td class="bld-val" style="font-weight:bold;">Allocated: {{ $totalLeaveAlloc }}</td></tr>
                    <tr><td class="bld-lbl">Remaining</td><td class="bld-val" style="color:#0056b3;">{{ $remainingLeaves }}</td></tr>
                </table>
            </td>
        </tr></table>
        @else
        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="bld-title">Attendance</div>
                <table class="bld-table">
                    <tr><td class="bld-lbl">Working</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                    <tr><td class="bld-lbl">Present</td><td class="bld-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="bld-lbl">Absent</td><td class="bld-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    <tr><td class="bld-lbl">Hours</td><td class="bld-val">{{ $totalWorkHours }} hrs</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="bld-title">Hourly Rate</div>
                <table class="bld-table">
                    <tr><td class="bld-lbl">Rate <b style="color:{{ $_themeColor }};">(A)</b></td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="bld-lbl">Hours <b style="color:#198754;">(B)</b></td><td class="bld-val" style="color:#198754;">{{ $totalWorkHours }} hrs</td></tr>
                    <tr style="background:{{ $_themeColor }}10;"><td class="bld-lbl"><strong>Gross (A&times;B)</strong></td><td class="bld-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                </table>
            </td>
        </tr></table>
        @endif

        <div class="bld-title" style="margin-top:5px;">Salary</div>
        <table class="bld-table" style="width:60%;">
            <tr><td class="bld-lbl">Type</td><td class="bld-val">{{ $payslipTypeName }}</td></tr>
            @if($isHourly)
            <tr><td class="bld-lbl">Hourly Rate</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
            <tr><td class="bld-lbl">Hours</td><td class="bld-val">{{ $totalWorkHours }} hrs</td></tr>
            <tr><td class="bld-lbl"><strong>Gross Earned</strong></td><td class="bld-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
            @else
            <tr><td class="bld-lbl">Gross Salary</td><td class="bld-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
            <tr><td class="bld-lbl">Per Day</td><td class="bld-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
            <tr><td class="bld-lbl">Per Hour</td><td class="bld-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
            <tr><td class="bld-lbl">Days Paid</td><td class="bld-val">{{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:7px;color:#198754;">(+{{ $paidLeaveDays }})</span>@endif</td></tr>
            @endif
        </table>

        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="bld-title">Earnings</div>
                <table class="bld-table">
                    @foreach($earRows as $er)
                    <tr><td class="bld-lbl">{{ $er['label'] }}</td><td class="bld-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                    @endforeach
                    <tr class="bld-total"><td class="bld-lbl">Total</td><td class="bld-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="bld-title">Deductions</div>
                <table class="bld-table">
                    @forelse($dedRows as $dr)
                    <tr><td class="bld-lbl">{{ $dr['label'] }}</td><td class="bld-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                    @empty
                    <tr><td class="bld-lbl" style="color:#aaa;font-style:italic;">None</td><td></td></tr>
                    @endforelse
                    <tr class="bld-total"><td class="bld-lbl">Total</td><td class="bld-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                </table>
            </td>
        </tr></table>

        <table style="width:100%;margin-top:4px;"><tr>
            <td class="bld-net-lbl" style="width:50%;">Net Pay</td>
            <td class="bld-net-val" style="width:50%;">{{ $_fmtMoney($netSalary) }}</td>
        </tr></table>

        <div class="bld-stamp">
            @if($_stampUrl)
                <img src="{{ $_stampUrl }}" alt="Stamp">
            @else
                <div class="bld-stamp-ph">Stamp</div>
            @endif
            <div style="font-size:6px;color:#999;margin-top:2px;">Digital Authorization</div>
        </div>

        <table class="bld-sig" style="width:100%;"><tr>
            <td><div class="bld-sig-line"></div><div class="bld-sig-lbl">Employee</div></td>
            <td><div class="bld-sig-line"></div><div class="bld-sig-lbl">Authorized</div></td>
        </tr></table>

        <div class="bld-footer">
            @if($companyPhone)<strong>Tel:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>Web:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>Email:</strong> {{ $companyEmail }} @endif
        </div>
    </div>
</div>
</div>
</body>
</html>
