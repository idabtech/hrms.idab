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
    font-family: 'DejaVu Sans', 'Georgia', 'Times New Roman', serif;
    font-size: 9px; color: #2c2c2c; background: #fcf8f0;
}
.page { padding: 8px; }
.cls-card { background:#fff; border:2px solid #d4a843; }
.cls-border-top { height:3px; background:linear-gradient(90deg,{{ $_themeColor }},#d4a843,{{ $_themeColor }}); }
.cls-header { padding:10px 16px; border-bottom:2px double #d4a843; }
.cls-header h2 { font-family:'Georgia',serif; font-size:15px; color:{{ $_themeColor }}; margin:0; letter-spacing:2px; text-transform:uppercase; }
.cls-header span { font-size:8px; color:#888; }
.cls-header img { height:28px; }
.cls-body { padding:10px 16px; }
.cls-sec-title { font-family:'Georgia',serif; font-weight:700; font-size:9px; color:{{ $_themeColor }}; border-bottom:2px solid #d4a843; padding-bottom:2px; margin-bottom:4px; text-transform:uppercase; letter-spacing:1px; }
.cls-table { width:100%; border-collapse:collapse; }
.cls-table td { padding:2px 3px; font-size:9px; border-bottom:1px solid #f0e8d5; }
.cls-lbl { color:#666; width:50%; font-style:italic; }
.cls-val { text-align:right; font-weight:600; }
.cls-total td { font-weight:700; color:{{ $_themeColor }}; border-top:2px solid #d4a843 !important; padding-top:4px; font-size:9.5px; }
.cls-net { background:linear-gradient(135deg,{{ $_themeColor }},#d4a843); color:#fff; padding:8px 14px; margin:6px 0; }
.cls-net-lbl { font-family:'Georgia',serif; font-size:11px; font-weight:700; }
.cls-net-val { font-size:16px; font-weight:700; text-align:right; }
.cls-stamp { text-align:center; padding:4px 0; border-top:1px solid #d4a84344; margin-top:4px; }
.cls-stamp img { width:48px; height:48px; border-radius:50%; object-fit:contain; border:2px solid {{ $_themeColor }}44; }
.cls-stamp-ph { width:48px; height:48px; border-radius:50%; border:2px dashed #d4a84366; display:inline-block; padding-top:10px; font-size:6px; color:#d4a84388; }
.cls-paid { color:#155724; font-weight:bold; }
.cls-unpaid { color:#721c24; font-weight:bold; }
.cls-sig { margin-top:6px; }
.cls-sig td { text-align:center; padding:2px 8px; }
.cls-sig-line { border-top:1px solid #d4a843; width:80px; margin:8px auto 2px; }
.cls-sig-lbl { font-size:7px; color:#888; font-style:italic; }
.cls-footer { background:{{ $_themeColor }}; color:#fff; text-align:center; padding:5px 16px; font-size:7px; line-height:1.5; font-style:italic; }
.cls-footer strong { font-style:normal; }
</style>
</head>
<body>
<div class="page">
<div class="cls-card">
    <div class="cls-border-top"></div>
    <div class="cls-header">
        <table style="width:100%;"><tr>
            <td><h2>PAY SLIP</h2><span>{{ $slipLabel }} &mdash; {{ $companyName }}</span></td>
            <td style="text-align:right;"><img src="{{ $logoUrl }}" alt="logo"></td>
        </tr></table>
    </div>
    <div class="cls-body">

        @if($_showEmployeeDetails || $_showPaymentDetails)
        <table style="width:100%;border-collapse:collapse;"><tr>
            @if($_showEmployeeDetails)
            <td style="width:{{ $_showPaymentDetails ? '50%' : '100%' }};vertical-align:top;padding-right:6px;">
                <div class="cls-sec-title">Employee</div>
                <table class="cls-table">
                    @if($_showName)
                    <tr><td class="cls-lbl">Name</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $employee->name }}</td></tr>
                    @endif
                    @if($_showDesignation)
                    <tr><td class="cls-lbl">Designation</td><td class="cls-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                    @endif
                    @if($_showEmployeeId)
                    <tr><td class="cls-lbl">Employee ID</td><td class="cls-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                    @endif
                    @if($_showDepartment)
                    <tr><td class="cls-lbl">Department</td><td class="cls-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                    @endif
                    @if($_showDateOfJoining)
                    <tr><td class="cls-lbl">DOJ</td><td class="cls-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                    @endif
                    @if($_showPanNo)
                    <tr><td class="cls-lbl">PAN No.</td><td class="cls-val">{{ $employee->tax_payer_id ?? '—' }}</td></tr>
                    @endif
                </table>
            </td>
            @endif
            @if($_showPaymentDetails)
            <td style="width:{{ $_showEmployeeDetails ? '50%' : '100%' }};vertical-align:top;padding-left:6px;">
                <div class="cls-sec-title">Payment</div>
                <table class="cls-table">
                    @if($_showBankName)
                    <tr><td class="cls-lbl">Bank Name</td><td class="cls-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                    @endif
                    @if($_showAccountNo)
                    <tr><td class="cls-lbl">Account No.</td><td class="cls-val">{{ $employee->account_number ?? '—' }}</td></tr>
                    @endif
                    @if($_showBankCode)
                    <tr><td class="cls-lbl">{{ \App\Models\Utility::bankCodeLabel() }}</td><td class="cls-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                    @endif
                    @if($_showAccountHolder)
                    <tr><td class="cls-lbl">Account Holder</td><td class="cls-val">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                    @endif
                    @if($_showPayPeriod)
                    <tr><td class="cls-lbl">Pay Period</td><td class="cls-val">{{ $slipLabel }}</td></tr>
                    @endif
                    <tr><td class="cls-lbl">Status</td><td class="cls-val"><span class="{{ $isPaid ? 'cls-paid' : 'cls-unpaid' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span></td></tr>
                </table>
            </td>
            @endif
        </tr></table>
        @endif

        @if(!$isHourly)
        <table style="width:100%;border-collapse:collapse;margin-top:6px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:6px;">
                <div class="cls-sec-title">Days &amp; Work</div>
                <table class="cls-table">
                    <tr><td class="cls-lbl">Working Days</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                    <tr><td class="cls-lbl">Present</td><td class="cls-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="cls-lbl">Leave</td><td class="cls-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="cls-lbl">Absent</td><td class="cls-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    @if($extraDays>0)
                    <tr><td class="cls-lbl" style="color:#856404;">Extra / OT</td><td class="cls-val" style="color:#856404;font-weight:bold;">+{{ $extraDaysFmt }}</td></tr>
                    @endif
                    <tr><td class="cls-lbl">Total Hours</td><td class="cls-val">{{ $totalWorkHours }} hrs</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:6px;">
                <div class="cls-sec-title">Leave Summary</div>
                <table class="cls-table">
                    <tr><td class="cls-lbl" style="width:35%;font-weight:bold;">This Month</td><td style="width:15%;text-align:right;font-weight:bold;">{{ $approvedLeaves }}</td><td class="cls-lbl" style="width:50%;font-weight:bold;">Overall</td></tr>
                    <tr><td class="cls-lbl">Paid</td><td style="text-align:right;color:#198754;">{{ $paidLeaveDays }}</td><td class="cls-lbl">Allocated: <strong>{{ $totalLeaveAlloc }}</strong></td></tr>
                    <tr><td class="cls-lbl">Unpaid</td><td style="text-align:right;color:#dc3545;">{{ $unpaidLeaveDays }}</td><td class="cls-lbl">Remaining: <strong>{{ $remainingLeaves }}</strong></td></tr>
                </table>
            </td>
        </tr></table>

        <div class="cls-sec-title" style="margin-top:6px;">Salary Details</div>
        <table class="cls-table" style="width:60%;">
            <tr><td class="cls-lbl">Payslip Type</td><td class="cls-val">{{ $payslipTypeName }}</td></tr>
            <tr><td class="cls-lbl">Gross Salary</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
            <tr><td class="cls-lbl">Per Day Rate</td><td class="cls-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
            <tr><td class="cls-lbl">Per Hour Rate</td><td class="cls-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
            <tr><td class="cls-lbl">Days Paid</td><td class="cls-val">{{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:8px;color:#198754;">(+{{ $paidLeaveDays }})</span>@endif</td></tr>
        </table>
        @else
        <table style="width:100%;border-collapse:collapse;margin-top:6px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:6px;">
                <div class="cls-sec-title">Attendance</div>
                <table class="cls-table">
                    <tr><td class="cls-lbl">Working Days</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                    <tr><td class="cls-lbl">Present</td><td class="cls-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="cls-lbl">Absent</td><td class="cls-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    <tr><td class="cls-lbl">Total Hours</td><td class="cls-val">{{ $totalWorkHours }} hrs</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:6px;">
                <div class="cls-sec-title">Hourly Rate</div>
                <table class="cls-table">
                    <tr><td class="cls-lbl">Rate <b style="color:{{ $_themeColor }};">(A)</b></td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="cls-lbl">Hours <b style="color:#198754;">(B)</b></td><td class="cls-val" style="color:#198754;">{{ $totalWorkHours }} hrs</td></tr>
                    <tr style="background:{{ $_themeColor }}10;"><td class="cls-lbl"><strong>Gross Earned (A &times; B)</strong></td><td class="cls-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                </table>
            </td>
        </tr></table>

        <div class="cls-sec-title" style="margin-top:6px;">Hourly Details</div>
        <table class="cls-table" style="width:60%;">
            <tr><td class="cls-lbl">Payslip Type</td><td class="cls-val">{{ $payslipTypeName }}</td></tr>
            <tr><td class="cls-lbl">Hourly Rate</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
            <tr><td class="cls-lbl">Hours</td><td class="cls-val">{{ $totalWorkHours }} hrs</td></tr>
            <tr><td class="cls-lbl">Gross Earned</td><td class="cls-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
            <tr><td class="cls-lbl">Pay Period</td><td class="cls-val">{{ $slipLabel }}</td></tr>
            <tr><td class="cls-lbl">Status</td><td class="cls-val {{ $isPaid ? 'cls-paid' : 'cls-unpaid' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</td></tr>
        </table>
        @endif

        <table style="width:100%;border-collapse:collapse;margin-top:6px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:6px;">
                <div class="cls-sec-title">Earnings</div>
                <table class="cls-table">
                    @foreach($earRows as $er)
                    <tr><td class="cls-lbl">{{ $er['label'] }}</td><td class="cls-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                    @endforeach
                    <tr class="cls-total"><td class="cls-lbl">Total Earnings</td><td class="cls-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:6px;">
                <div class="cls-sec-title">Deductions</div>
                <table class="cls-table">
                    @forelse($dedRows as $dr)
                    <tr><td class="cls-lbl">{{ $dr['label'] }}</td><td class="cls-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                    @empty
                    <tr><td class="cls-lbl" style="color:#aaa;font-style:italic;">No deductions</td><td></td></tr>
                    @endforelse
                    <tr class="cls-total"><td class="cls-lbl">Total Deductions</td><td class="cls-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                </table>
            </td>
        </tr></table>

        <table style="width:100%;margin-top:4px;"><tr>
            <td class="cls-net-lbl" style="width:50%;">Net Pay</td>
            <td class="cls-net-val" style="width:50%;">{{ $_fmtMoney($netSalary) }}</td>
        </tr></table>

        <div class="cls-stamp">
            @if($_stampUrl)
                <img src="{{ $_stampUrl }}" alt="Stamp">
            @else
                <div class="cls-stamp-ph">Stamp</div>
            @endif
            <div style="font-size:6px;color:#999;margin-top:2px;">Digital Authorization</div>
        </div>

        @if($_showSignatures)
        <table class="cls-sig" style="width:100%;"><tr>
            <td><div class="cls-sig-line"></div><div class="cls-sig-lbl">Employee Signature</div></td>
            <td><div class="cls-sig-line"></div><div class="cls-sig-lbl">Authorized Signatory</div></td>
        </tr></table>
        @endif

        @if($_showFooter)
        <div class="cls-footer">
            @if($companyPhone)<strong>Tel:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
            @if($companyWeb)<strong>Web:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
            @if($companyEmail)<strong>Email:</strong> {{ $companyEmail }} @endif
        </div>
        @endif
    </div>
</div>
</div>
</body>
</html>
