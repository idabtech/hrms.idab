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
body { font-family:'DejaVu Sans',Arial,sans-serif; font-size:9px; color:#222; background:#f0f2f5; }
.page { padding: 6px; }
.ctp-card { background:#fff; }
.ctp-accent { position:relative; }
.ctp-accent::before { content:''; position:absolute; left:0; top:0; bottom:0; width:3px; background:{{ $_themeColor }}; }
.ctp-header { padding:10px 14px 8px 17px; border-bottom:1px solid #e8eaee; }
.ctp-header h2 { font-size:11px; font-weight:bold; color:#111; margin:0; text-transform:uppercase; }
.ctp-header span { font-size:8px; color:#888; }
.ctp-header img { height:24px; }
.ctp-body { padding:8px 14px 10px 17px; }
.ctp-title { font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:{{ $_themeColor }}; border-bottom:2px solid {{ $_themeColor }}22; padding-bottom:2px; margin-bottom:3px; }
.ctp-table { width:100%; border-collapse:collapse; }
.ctp-table td { padding:2px 3px; font-size:9px; border-bottom:1px solid #f0f1f3; }
.ctp-lbl { color:#888; width:50%; font-size:8px; }
.ctp-val { text-align:right; font-weight:600; color:#222; }
.ctp-total td { font-weight:bold; color:{{ $_themeColor }}; border-top:2px solid {{ $_themeColor }} !important; padding-top:3px; font-size:9px; }
.ctp-net { padding:8px 0; margin:5px 0; border-top:2px solid #111; border-bottom:2px solid #111; }
.ctp-net-lbl { font-size:10px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#111; }
.ctp-net-val { font-size:14px; font-weight:bold; color:{{ $_themeColor }}; text-align:right; }
.ctp-stamp { text-align:center; padding:4px 0; border-top:1px solid #e8eaee; margin-top:4px; }
.ctp-stamp img { width:44px; height:44px; border-radius:3px; object-fit:contain; border:1.5px solid {{ $_themeColor }}44; }
.ctp-stamp-ph { width:44px; height:44px; border-radius:3px; border:1.5px dashed #ddd; display:inline-block; padding-top:10px; font-size:5px; color:#bbb; }
.ctp-paid { background:#111; color:#fff; font-weight:bold; }
.ctp-unpaid { background:#dc3545; color:#fff; font-weight:bold; }
.ctp-sig { margin-top:5px; }
.ctp-sig td { text-align:center; padding:2px 8px; }
.ctp-sig-line { border-top:1.5px solid #111; width:70px; margin:6px auto 2px; }
.ctp-sig-lbl { font-size:7px; color:#aaa; letter-spacing:0.5px; }
.ctp-footer { background:#111; color:#fff; text-align:center; padding:4px 14px 4px 17px; font-size:7px; line-height:1.5; }
.ctp-footer strong { color:{{ $_themeColor }}; }
</style>
</head>
<body>
<div class="page">
<div class="ctp-card ctp-accent">
    <div class="ctp-header">
        <table style="width:100%;"><tr>
            <td><h2>PAYSLIP</h2><span>{{ $slipLabel }} | {{ $companyName }}</span></td>
            <td style="text-align:right;"><img src="{{ $logoUrl }}" alt="logo"></td>
        </tr></table>
    </div>
    <div class="ctp-body">

        <table style="width:100%;border-collapse:collapse;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="ctp-title">Employee</div>
                <table class="ctp-table">
                    <tr><td class="ctp-lbl">Name</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $employee->name }}</td></tr>
                    <tr><td class="ctp-lbl">Designation</td><td class="ctp-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                    <tr><td class="ctp-lbl">ID</td><td class="ctp-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                    <tr><td class="ctp-lbl">Department</td><td class="ctp-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                    <tr><td class="ctp-lbl">DOJ</td><td class="ctp-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="ctp-title">Payment</div>
                <table class="ctp-table">
                    <tr><td class="ctp-lbl">Bank</td><td class="ctp-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                    <tr><td class="ctp-lbl">Account</td><td class="ctp-val">{{ $employee->account_number ?? '—' }}</td></tr>
                    <tr><td class="ctp-lbl">Pay Period</td><td class="ctp-val">{{ $slipLabel }}</td></tr>
                    <tr><td class="ctp-lbl">Status</td><td class="ctp-val"><span class="{{ $isPaid ? 'ctp-paid' : 'ctp-unpaid' }}" style="padding:1px 8px;border-radius:2px;font-size:8px;">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span></td></tr>
                </table>
            </td>
        </tr></table>

        @if(!$isHourly)
        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="ctp-title">Days &amp; Work</div>
                <table class="ctp-table">
                    <tr><td class="ctp-lbl">Working</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                    <tr><td class="ctp-lbl">Present</td><td class="ctp-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="ctp-lbl">Leave</td><td class="ctp-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="ctp-lbl">Absent</td><td class="ctp-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    @if($extraDays>0)
                    <tr><td class="ctp-lbl" style="color:#856404;">OT</td><td class="ctp-val" style="color:#856404;">+{{ $extraDaysFmt }}</td></tr>
                    @endif
                    <tr><td class="ctp-lbl">Hours</td><td class="ctp-val">{{ $totalWorkHours }}h</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="ctp-title">Leave</div>
                <table class="ctp-table">
                    <tr><td class="ctp-lbl" style="font-weight:bold;">This Month</td><td class="ctp-val" style="font-weight:bold;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="ctp-lbl">Paid</td><td class="ctp-val" style="color:#198754;">{{ $paidLeaveDays }}</td></tr>
                    <tr><td class="ctp-lbl">Unpaid</td><td class="ctp-val" style="color:#dc3545;">{{ $unpaidLeaveDays }}</td></tr>
                    <tr><td class="ctp-lbl" style="font-weight:bold;">Overall</td><td class="ctp-val" style="font-weight:bold;">Allocated: {{ $totalLeaveAlloc }}</td></tr>
                    <tr><td class="ctp-lbl">Remaining</td><td class="ctp-val" style="color:#0056b3;font-weight:bold;">{{ $remainingLeaves }}</td></tr>
                </table>
            </td>
        </tr></table>
        @else
        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="ctp-title">Attendance</div>
                <table class="ctp-table">
                    <tr><td class="ctp-lbl">Working</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                    <tr><td class="ctp-lbl">Present</td><td class="ctp-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="ctp-lbl">Absent</td><td class="ctp-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    <tr><td class="ctp-lbl">Hours</td><td class="ctp-val">{{ $totalWorkHours }}h</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="ctp-title">Rate</div>
                <table class="ctp-table">
                    <tr><td class="ctp-lbl">Hourly <b style="color:{{ $_themeColor }};">(A)</b></td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="ctp-lbl">Hours <b style="color:#198754;">(B)</b></td><td class="ctp-val" style="color:#198754;">{{ $totalWorkHours }}</td></tr>
                    <tr style="background:{{ $_themeColor }}08;"><td class="ctp-lbl"><strong>Gross (A&times;B)</strong></td><td class="ctp-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                </table>
            </td>
        </tr></table>
        @endif

        <div class="ctp-title" style="margin-top:5px;">Salary</div>
        <table class="ctp-table" style="width:60%;">
            <tr><td class="ctp-lbl">Type</td><td class="ctp-val">{{ $payslipTypeName }}</td></tr>
            @if($isHourly)
            <tr><td class="ctp-lbl">Hourly Rate</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
            <tr><td class="ctp-lbl">Hours</td><td class="ctp-val">{{ $totalWorkHours }}h</td></tr>
            <tr><td class="ctp-lbl"><strong>Gross Earned</strong></td><td class="ctp-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
            @else
            <tr><td class="ctp-lbl">Gross</td><td class="ctp-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
            <tr><td class="ctp-lbl">Per Day</td><td class="ctp-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
            <tr><td class="ctp-lbl">Per Hour</td><td class="ctp-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
            <tr><td class="ctp-lbl">Days Paid</td><td class="ctp-val">{{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:7px;color:#198754;">(+{{ $paidLeaveDays }})</span>@endif</td></tr>
            @endif
        </table>

        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="ctp-title">Earnings</div>
                <table class="ctp-table">
                    @foreach($earRows as $er)
                    <tr><td class="ctp-lbl">{{ $er['label'] }}</td><td class="ctp-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                    @endforeach
                    <tr class="ctp-total"><td class="ctp-lbl">Total</td><td class="ctp-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="ctp-title">Deductions</div>
                <table class="ctp-table">
                    @forelse($dedRows as $dr)
                    <tr><td class="ctp-lbl">{{ $dr['label'] }}</td><td class="ctp-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                    @empty
                    <tr><td class="ctp-lbl" style="color:#aaa;font-style:italic;">None</td><td></td></tr>
                    @endforelse
                    <tr class="ctp-total"><td class="ctp-lbl">Total</td><td class="ctp-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                </table>
            </td>
        </tr></table>

        <table style="width:100%;margin-top:4px;"><tr>
            <td class="ctp-net-lbl" style="width:50%;">Net Pay</td>
            <td class="ctp-net-val" style="width:50%;">{{ $_fmtMoney($netSalary) }}</td>
        </tr></table>

        <div class="ctp-stamp">
            @if($_stampUrl)
                <img src="{{ $_stampUrl }}" alt="Stamp">
            @else
                <div class="ctp-stamp-ph">Stamp</div>
            @endif
            <div style="font-size:6px;color:#999;margin-top:2px;">Digital Authorization</div>
        </div>

        <table class="ctp-sig" style="width:100%;"><tr>
            <td><div class="ctp-sig-line"></div><div class="ctp-sig-lbl">Employee</div></td>
            <td><div class="ctp-sig-line"></div><div class="ctp-sig-lbl">Authorized</div></td>
        </tr></table>

        <div class="ctp-footer">
            @if($companyPhone)<strong>Tel:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>Web:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>Email:</strong> {{ $companyEmail }} @endif
        </div>
    </div>
</div>
</div>
</body>
</html>
