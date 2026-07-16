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
    font-family: 'DejaVu Sans', 'Georgia', serif;
    font-size: 9px; color:#1c1c1c; background:#1a1a2e;
}
.page { padding: 6px; }
.exe-card { background:#fff; border:1px solid #c9a862; }
.exe-accent { height:3px; background:linear-gradient(90deg,#1a1a2e,#c9a862,#1a1a2e); }
.exe-header { padding:10px 16px; border-bottom:1px solid #c9a862; background:linear-gradient(135deg,#faf8f3,#fff); }
.exe-header h2 { font-family:'Georgia',serif; font-size:15px; color:#1a1a2e; margin:0; letter-spacing:3px; }
.exe-header span { font-size:8px; color:#c9a862; letter-spacing:1px; }
.exe-header img { height:28px; }
.exe-body { padding:10px 16px; }
.exe-sec-title { font-family:'Georgia',serif; font-weight:700; font-size:9px; color:#1a1a2e; border-bottom:2px solid #c9a862; padding-bottom:2px; margin-bottom:4px; text-transform:uppercase; letter-spacing:1px; }
.exe-table { width:100%; border-collapse:collapse; }
.exe-table td { padding:2px 3px; font-size:9px; border-bottom:1px solid #f0ece0; }
.exe-lbl { color:#888; width:50%; font-style:italic; font-size:8px; }
.exe-val { text-align:right; font-weight:600; color:#333; }
.exe-mono { color:#1a1a2e; font-weight:700; }
.exe-total td { font-weight:700; color:#1a1a2e; border-top:2px solid #c9a862 !important; padding-top:4px; font-size:9.5px; }
.exe-net { background:linear-gradient(135deg,#1a1a2e,#2d2d5e); color:#c9a862; padding:8px 14px; margin:6px 0; border:1px solid #c9a862; }
.exe-net-lbl { font-family:'Georgia',serif; font-size:11px; font-weight:700; letter-spacing:1px; }
.exe-net-val { font-size:16px; font-weight:700; color:#fff; text-align:right; }
.exe-stamp { text-align:center; padding:4px 0; border-top:1px solid #e8dcc8; margin-top:4px; }
.exe-stamp img { width:48px; height:48px; border-radius:50%; object-fit:contain; border:2px solid #c9a86266; }
.exe-stamp-ph { width:48px; height:48px; border-radius:50%; border:2px dashed #c9a86266; display:inline-block; padding-top:10px; font-size:6px; color:#c9a86288; }
.exe-paid { color:#155724; font-weight:bold; }
.exe-unpaid { color:#721c24; font-weight:bold; }
.exe-sig { margin-top:6px; }
.exe-sig td { text-align:center; padding:2px 8px; }
.exe-sig-line { border-top:1px solid #c9a862; width:80px; margin:8px auto 2px; }
.exe-sig-lbl { font-size:7px; color:#c9a862; letter-spacing:0.5px; }
.exe-footer { background:#1a1a2e; color:#c9a862; text-align:center; padding:5px 16px; font-size:7px; line-height:1.5; border-top:1px solid #c9a862; }
.exe-footer strong { color:#fff; }
</style>
</head>
<body>
<div class="page">
<div class="exe-card">
    <div class="exe-accent"></div>
    <div class="exe-header">
        <table style="width:100%;"><tr>
            <td><h2>PAY SLIP</h2><span>{{ $slipLabel }}</span></td>
            <td style="text-align:right;"><img src="{{ $logoUrl }}" alt="logo"></td>
        </tr></table>
    </div>
    <div class="exe-body">

        <table style="width:100%;border-collapse:collapse;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:6px;">
                <div class="exe-sec-title">Employee</div>
                <table class="exe-table">
                    <tr><td class="exe-lbl">Name</td><td class="exe-val exe-mono">{{ $employee->name }}</td></tr>
                    <tr><td class="exe-lbl">Designation</td><td class="exe-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                    <tr><td class="exe-lbl">Employee ID</td><td class="exe-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                    <tr><td class="exe-lbl">Department</td><td class="exe-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                    <tr><td class="exe-lbl">DOJ</td><td class="exe-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:6px;">
                <div class="exe-sec-title">Payment</div>
                <table class="exe-table">
                    <tr><td class="exe-lbl">Bank Name</td><td class="exe-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                    <tr><td class="exe-lbl">Account No.</td><td class="exe-val">{{ $employee->account_number ?? '—' }}</td></tr>
                    <tr><td class="exe-lbl">{{ \App\Models\Utility::bankCodeLabel() }}</td><td class="exe-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                    <tr><td class="exe-lbl">Account Holder</td><td class="exe-val">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                    <tr><td class="exe-lbl">Pay Period</td><td class="exe-val">{{ $slipLabel }}</td></tr>
                    <tr><td class="exe-lbl">Status</td><td class="exe-val"><span class="{{ $isPaid ? 'exe-paid' : 'exe-unpaid' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span></td></tr>
                </table>
            </td>
        </tr></table>

        @if(!$isHourly)
        <table style="width:100%;border-collapse:collapse;margin-top:6px;"><tr>
            <td style="width:33%;vertical-align:top;padding-right:5px;">
                <div class="exe-sec-title">Days &amp; Work</div>
                <table class="exe-table">
                    <tr><td class="exe-lbl">Working Days</td><td class="exe-val exe-mono">{{ $officeDays }}</td></tr>
                    <tr><td class="exe-lbl">Present</td><td class="exe-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="exe-lbl">Leave</td><td class="exe-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="exe-lbl">Absent</td><td class="exe-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    @if($extraDays>0)
                    <tr><td class="exe-lbl" style="color:#856404;">Extra / OT</td><td class="exe-val" style="color:#856404;font-weight:bold;">+{{ $extraDaysFmt }}</td></tr>
                    @endif
                    <tr><td class="exe-lbl">Hours</td><td class="exe-val">{{ $totalWorkHours }} hrs</td></tr>
                </table>
            </td>
            <td style="width:33%;vertical-align:top;padding:0 5px;">
                <div class="exe-sec-title">Leave</div>
                <table class="exe-table">
                    <tr><td class="exe-lbl" style="width:35%;font-weight:bold;">This Month</td><td style="width:15%;text-align:right;font-weight:bold;">{{ $approvedLeaves }}</td><td class="exe-lbl" style="width:50%;font-weight:bold;">Overall</td></tr>
                    <tr><td class="exe-lbl">Paid</td><td style="text-align:right;color:#198754;">{{ $paidLeaveDays }}</td><td class="exe-lbl">Allocated: <strong>{{ $totalLeaveAlloc }}</strong></td></tr>
                    <tr><td class="exe-lbl">Unpaid</td><td style="text-align:right;color:#dc3545;">{{ $unpaidLeaveDays }}</td><td class="exe-lbl">Remaining: <strong>{{ $remainingLeaves }}</strong></td></tr>
                </table>
            </td>
            <td style="width:33%;vertical-align:top;padding-left:5px;">
                <div class="exe-sec-title">Salary</div>
                <table class="exe-table">
                    <tr><td class="exe-lbl">Gross</td><td class="exe-val exe-mono">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                    <tr><td class="exe-lbl">Per Day</td><td class="exe-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                    <tr><td class="exe-lbl">Per Hour</td><td class="exe-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="exe-lbl">Days Paid</td><td class="exe-val">{{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:8px;color:#198754;">(+{{ $paidLeaveDays }})</span>@endif</td></tr>
                </table>
            </td>
        </tr></table>
        @else
        <table style="width:100%;border-collapse:collapse;margin-top:6px;"><tr>
            <td style="width:33%;vertical-align:top;padding-right:5px;">
                <div class="exe-sec-title">Attendance</div>
                <table class="exe-table">
                    <tr><td class="exe-lbl">Working Days</td><td class="exe-val exe-mono">{{ $officeDays }}</td></tr>
                    <tr><td class="exe-lbl">Present</td><td class="exe-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="exe-lbl">Absent</td><td class="exe-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    <tr><td class="exe-lbl">Hours</td><td class="exe-val">{{ $totalWorkHours }} hrs</td></tr>
                </table>
            </td>
            <td style="width:33%;vertical-align:top;padding:0 5px;">
                <div class="exe-sec-title">Hourly Rate</div>
                <table class="exe-table">
                    <tr><td class="exe-lbl">Rate <b style="color:#1a1a2e;">(A)</b></td><td class="exe-val exe-mono">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="exe-lbl">Hours <b style="color:#198754;">(B)</b></td><td class="exe-val" style="color:#198754;">{{ $totalWorkHours }} hrs</td></tr>
                    <tr style="background:{{ $_themeColor }}10;"><td class="exe-lbl"><strong>Gross Earned (A &times; B)</strong></td><td class="exe-val exe-mono"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                </table>
            </td>
            <td style="width:33%;vertical-align:top;padding-left:5px;">
                <div class="exe-sec-title">Payslip</div>
                <table class="exe-table">
                    <tr><td class="exe-lbl">{{ $payslipTypeName }}</td><td class="exe-val">{{ $slipLabel }}</td></tr>
                    <tr><td class="exe-lbl">Status</td><td class="exe-val"><span class="{{ $isPaid ? 'exe-paid' : 'exe-unpaid' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span></td></tr>
                </table>
            </td>
        </tr></table>
        @endif

        <table style="width:100%;border-collapse:collapse;margin-top:6px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:6px;">
                <div class="exe-sec-title">Earnings</div>
                <table class="exe-table">
                    @foreach($earRows as $er)
                    <tr><td class="exe-lbl">{{ $er['label'] }}</td><td class="exe-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                    @endforeach
                    <tr class="exe-total"><td class="exe-lbl">Total Earnings</td><td class="exe-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:6px;">
                <div class="exe-sec-title">Deductions</div>
                <table class="exe-table">
                    @forelse($dedRows as $dr)
                    <tr><td class="exe-lbl">{{ $dr['label'] }}</td><td class="exe-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                    @empty
                    <tr><td class="exe-lbl" style="color:#aaa;font-style:italic;">No deductions</td><td></td></tr>
                    @endforelse
                    <tr class="exe-total"><td class="exe-lbl">Total Deductions</td><td class="exe-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                </table>
            </td>
        </tr></table>

        <table style="width:100%;margin-top:4px;"><tr>
            <td class="exe-net-lbl" style="width:50%;">Net Pay</td>
            <td class="exe-net-val" style="width:50%;">{{ $_fmtMoney($netSalary) }}</td>
        </tr></table>

        <div class="exe-stamp">
            @if($_stampUrl)
                <img src="{{ $_stampUrl }}" alt="Stamp">
            @else
                <div class="exe-stamp-ph">Stamp</div>
            @endif
            <div style="font-size:6px;color:#999;margin-top:2px;">Digital Authorization</div>
        </div>

        <table class="exe-sig" style="width:100%;"><tr>
            <td><div class="exe-sig-line"></div><div class="exe-sig-lbl">Employee Signature</div></td>
            <td><div class="exe-sig-line"></div><div class="exe-sig-lbl">Authorized Signatory</div></td>
        </tr></table>

        <div class="exe-footer">
            @if($companyPhone)<strong>Tel:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>Web:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>Email:</strong> {{ $companyEmail }} @endif
        </div>
    </div>
</div>
</div>
</body>
</html>
