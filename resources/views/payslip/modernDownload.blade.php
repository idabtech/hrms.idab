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
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 9px; color:#1a1a1a; background:#f8f9fa;
}
.page { padding: 6px; }
.mod-card { background:#fff; border-radius:8px; overflow:hidden; }
.mod-grad { background:linear-gradient(135deg,{{ $_themeColor }},{{ $_themeColor }}dd); color:#fff; padding:12px 16px; }
.mod-grad h2 { font-size:12px; font-weight:700; margin:0; letter-spacing:1px; }
.mod-grad span { font-size:9px; opacity:0.9; }
.mod-body { padding:12px 16px; }
.mod-section-title { font-weight:700; font-size:9px; color:{{ $_themeColor }}; border-bottom:2px solid {{ $_themeColor }}33; padding-bottom:2px; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.5px; }
.mod-table { width:100%; border-collapse:collapse; }
.mod-table td { padding:2px 3px; font-size:9px; border-bottom:1px solid #f0f0f0; }
.mod-lbl { color:#666; width:55%; }
.mod-val { text-align:right; font-weight:600; }
.mod-total td { font-weight:700; color:{{ $_themeColor }}; border-top:2px solid {{ $_themeColor }} !important; padding-top:4px; font-size:10px; }
.mod-net { background:linear-gradient(135deg,{{ $_themeColor }},{{ $_themeColor }}dd); color:#fff; padding:10px 16px; margin:6px 0; }
.mod-net-label { font-size:11px; font-weight:600; }
.mod-net-value { font-size:17px; font-weight:700; text-align:right; }
.mod-stamp { text-align:center; padding:6px 0; border-top:1px solid #e8e8e8; margin-top:4px; }
.mod-stamp img { width:48px; height:48px; border-radius:50%; object-fit:contain; border:2px solid {{ $_themeColor }}44; }
.mod-stamp-ph { width:48px; height:48px; border-radius:50%; border:2px dashed {{ $_themeColor }}44; display:inline-block; padding-top:10px; font-size:6px; color:{{ $_themeColor }}66; }
.mod-paid { color:#155724; font-weight:bold; }
.mod-unpaid { color:#721c24; font-weight:bold; }
.mod-sig { margin-top:8px; }
.mod-sig td { text-align:center; padding:2px 10px; }
.mod-sig-line { border-top:1px solid #999; width:90px; margin:8px auto 2px; }
.mod-sig-lbl { font-size:7px; color:#888; }
.mod-footer { background:{{ $_themeColor }}; color:#fff; text-align:center; padding:5px 16px; font-size:7px; line-height:1.5; }
</style>
</head>
<body>
<div class="page">
<div class="mod-card">
    <div class="mod-grad">
        <table style="width:100%;"><tr>
            <td><h2>PAY SLIP</h2><span>{{ $companyName }}</span></td>
            <td style="text-align:right;"><span>{{ $slipLabel }}</span><br><img src="{{ $logoUrl }}" alt="logo" style="height:28px;margin-top:2px;"></td>
        </tr></table>
    </div>
    <div class="mod-body">

        <table style="width:100%;border-collapse:collapse;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:6px;">
                <div class="mod-section-title">Employee</div>
                <table class="mod-table">
                    <tr><td class="mod-lbl">Name</td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $employee->name }}</td></tr>
                    <tr><td class="mod-lbl">Designation</td><td class="mod-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                    <tr><td class="mod-lbl">Employee ID</td><td class="mod-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                    <tr><td class="mod-lbl">Department</td><td class="mod-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                    <tr><td class="mod-lbl">DOJ</td><td class="mod-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:6px;">
                <div class="mod-section-title">Payment</div>
                <table class="mod-table">
                    <tr><td class="mod-lbl">Bank Name</td><td class="mod-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                    <tr><td class="mod-lbl">Account No.</td><td class="mod-val">{{ $employee->account_number ?? '—' }}</td></tr>
                    <tr><td class="mod-lbl">{{ \App\Models\Utility::bankCodeLabel() }}</td><td class="mod-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                    <tr><td class="mod-lbl">Pay Period</td><td class="mod-val">{{ $slipLabel }}</td></tr>
                    <tr><td class="mod-lbl">Status</td><td class="mod-val"><span class="{{ $isPaid ? 'mod-paid' : 'mod-unpaid' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span></td></tr>
                </table>
            </td>
        </tr></table>

        @if(!$isHourly)
        <table style="width:100%;border-collapse:collapse;margin-top:6px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:6px;">
                <div class="mod-section-title">Days &amp; Work</div>
                <table class="mod-table">
                    <tr><td class="mod-lbl">Working Days</td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                    <tr><td class="mod-lbl">Present</td><td class="mod-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="mod-lbl">Leave</td><td class="mod-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="mod-lbl">Absent</td><td class="mod-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    @if($extraDays>0)
                    <tr><td class="mod-lbl" style="color:#856404;">Extra / OT</td><td class="mod-val" style="color:#856404;font-weight:bold;">+{{ $extraDaysFmt }}</td></tr>
                    @endif
                    <tr><td class="mod-lbl">Total Hours</td><td class="mod-val">{{ $totalWorkHours }} hrs</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:6px;">
                <div class="mod-section-title">Leave Summary</div>
                <table class="mod-table">
                    <tr><td class="mod-lbl" style="width:40%;font-weight:bold;">This Month</td><td style="width:20%;text-align:right;font-weight:bold;">{{ $approvedLeaves }}</td><td class="mod-lbl" style="width:40%;font-weight:bold;">Overall</td></tr>
                    <tr><td class="mod-lbl">Paid</td><td style="text-align:right;color:#198754;">{{ $paidLeaveDays }}</td><td class="mod-lbl">Allocated: <strong>{{ $totalLeaveAlloc }}</strong></td></tr>
                    <tr><td class="mod-lbl">Unpaid</td><td style="text-align:right;color:#dc3545;">{{ $unpaidLeaveDays }}</td><td class="mod-lbl">Remaining: <strong>{{ $remainingLeaves }}</strong></td></tr>
                </table>
            </td>
        </tr></table>
        @else
        <table style="width:100%;border-collapse:collapse;margin-top:6px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:6px;">
                <div class="mod-section-title">Attendance</div>
                <table class="mod-table">
                    <tr><td class="mod-lbl">Working Days</td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                    <tr><td class="mod-lbl">Present</td><td class="mod-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="mod-lbl">Absent</td><td class="mod-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    <tr><td class="mod-lbl">Total Hours</td><td class="mod-val">{{ $totalWorkHours }} hrs</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:6px;">
                <div class="mod-section-title">Hourly Rate</div>
                <table class="mod-table">
                    <tr><td class="mod-lbl">Rate <b style="color:{{ $_themeColor }};">(A)</b></td><td class="mod-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="mod-lbl">Hours <b style="color:#198754;">(B)</b></td><td class="mod-val" style="color:#198754;">{{ $totalWorkHours }} hrs</td></tr>
                    <tr style="background:{{ $_themeColor }}10;"><td class="mod-lbl"><strong>Gross Earned (A &times; B)</strong></td><td class="mod-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                </table>
            </td>
        </tr></table>
        @endif

        <table style="width:100%;border-collapse:collapse;margin-top:6px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:6px;">
                <div class="mod-section-title">Earnings</div>
                <table class="mod-table">
                    @foreach($earRows as $er)
                    <tr><td class="mod-lbl">{{ $er['label'] }}</td><td class="mod-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                    @endforeach
                    <tr class="mod-total"><td class="mod-lbl">Total Earnings</td><td class="mod-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:6px;">
                <div class="mod-section-title">Deductions</div>
                <table class="mod-table">
                    @forelse($dedRows as $dr)
                    <tr><td class="mod-lbl">{{ $dr['label'] }}</td><td class="mod-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                    @empty
                    <tr><td class="mod-lbl" style="color:#aaa;font-style:italic;">No deductions</td><td></td></tr>
                    @endforelse
                    <tr class="mod-total"><td class="mod-lbl">Total Deductions</td><td class="mod-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                </table>
            </td>
        </tr></table>

        <table style="width:100%;margin-top:4px;"><tr>
            <td class="mod-net-label" style="width:50%;">Net Pay</td>
            <td class="mod-net-value" style="width:50%;">{{ $_fmtMoney($netSalary) }}</td>
        </tr></table>

        <div class="mod-stamp">
            @if($_stampUrl)
                <img src="{{ $_stampUrl }}" alt="Stamp">
            @else
                <div class="mod-stamp-ph">Stamp</div>
            @endif
            <div style="font-size:6px;color:#999;margin-top:2px;">Digital Authorization</div>
        </div>

        <table class="mod-sig" style="width:100%;"><tr>
            <td><div class="mod-sig-line"></div><div class="mod-sig-lbl">Employee Signature</div></td>
            <td><div class="mod-sig-line"></div><div class="mod-sig-lbl">Authorized Signatory</div></td>
        </tr></table>

        <div class="mod-footer">
            @if($companyPhone)<strong>Tel:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>Web:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>Email:</strong> {{ $companyEmail }} @endif
        </div>
    </div>
</div>
</div>
</body>
</html>
