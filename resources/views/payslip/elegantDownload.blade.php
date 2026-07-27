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
body { font-family:'DejaVu Sans','Trebuchet MS',Arial,sans-serif; font-size:9px; color:#3a3a3a; background:#faf6f0; }
.page { padding: 6px; }
.elg-card { background:#fff; border:1px solid #d4c9b8; }
.elg-top { height:2px; background:{{ $_themeColor }}; }
.elg-header { padding:10px 14px; border-bottom:1px solid #e8ddd0; }
.elg-header h2 { font-size:13px; font-weight:normal; color:#5a4a3a; margin:0; letter-spacing:3px; text-transform:uppercase; }
.elg-header span { font-size:8px; color:#b8a892; letter-spacing:1px; }
.elg-header img { height:26px; }
.elg-body { padding:10px 14px; }
.elg-title { font-size:8px; font-weight:600; text-transform:uppercase; letter-spacing:1.5px; color:#b8a892; border-bottom:1px solid #e8ddd0; padding-bottom:2px; margin-bottom:3px; }
.elg-table { width:100%; border-collapse:collapse; }
.elg-table td { padding:2px 3px; font-size:9px; border-bottom:1px solid #f0ebe0; }
.elg-lbl { color:#9a8a78; width:50%; }
.elg-val { text-align:right; font-weight:600; color:#5a4a3a; }
.elg-total td { font-weight:bold; color:{{ $_themeColor }}; border-top:1.5px solid {{ $_themeColor }}66 !important; padding-top:3px; font-size:9.5px; }
.elg-net { background:linear-gradient(135deg,#5a4a3a,#7a6a58); color:#f5ece0; padding:8px 14px; margin:5px 0; }
.elg-net-lbl { font-size:10px; font-weight:normal; letter-spacing:1.5px; text-transform:uppercase; }
.elg-net-val { font-size:14px; font-weight:bold; color:#fff; text-align:right; }
.elg-stamp { text-align:center; padding:4px 0; border-top:1px solid #e8ddd0; margin-top:4px; }
.elg-stamp img { width:44px; height:44px; border-radius:50%; object-fit:contain; border:1.5px solid {{ $_themeColor }}44; }
.elg-stamp-ph { width:44px; height:44px; border-radius:50%; border:1.5px dashed #d4c9b8; display:inline-block; padding-top:10px; font-size:5px; color:#d4c9b8; }
.elg-paid { background:#e8f0e0; color:#5a7a4a; font-weight:bold; }
.elg-unpaid { background:#f8e0e0; color:#aa5a5a; font-weight:bold; }
.elg-sig { margin-top:5px; }
.elg-sig td { text-align:center; padding:2px 8px; }
.elg-sig-line { border-top:1px solid #d4c9b8; width:70px; margin:6px auto 2px; }
.elg-sig-lbl { font-size:7px; color:#c8b8a8; letter-spacing:0.5px; }
.elg-footer { background:#f0ebe5; color:#b8a892; text-align:center; padding:4px 14px; font-size:7px; line-height:1.5; border-top:1px solid #e8ddd0; }
.elg-footer strong { color:#7a6a58; }
</style>
</head>
<body>
<div class="page">
<div class="elg-card">
    <div class="elg-top"></div>
    <div class="elg-header">
        <table style="width:100%;"><tr>
            <td><h2>PAY SLIP</h2><span>{{ $slipLabel }}</span></td>
            <td style="text-align:right;"><img src="{{ $logoUrl }}" alt="logo"></td>
        </tr></table>
    </div>
    <div class="elg-body">

        @if($_showEmployeeDetails || $_showPaymentDetails)
        <table style="width:100%;border-collapse:collapse;"><tr>
            @if($_showEmployeeDetails)
            <td style="width:{{ $_showPaymentDetails ? '50%' : '100%' }};vertical-align:top;padding-right:5px;">
                <div class="elg-title">Employee</div>
                <table class="elg-table">
                    @if($_showName)
                    <tr><td class="elg-lbl">Name</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $employee->name }}</td></tr>
                    @endif
                    @if($_showDesignation)
                    <tr><td class="elg-lbl">Designation</td><td class="elg-val">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                    @endif
                    @if($_showEmployeeId)
                    <tr><td class="elg-lbl">ID</td><td class="elg-val">{{ $employee->employee_id ?? '—' }}</td></tr>
                    @endif
                    @if($_showDepartment)
                    <tr><td class="elg-lbl">Department</td><td class="elg-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                    @endif
                    @if($_showDateOfJoining)
                    <tr><td class="elg-lbl">DOJ</td><td class="elg-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                    @endif
                </table>
            </td>
            @endif
            @if($_showPaymentDetails)
            <td style="width:{{ $_showEmployeeDetails ? '50%' : '100%' }};vertical-align:top;padding-left:5px;">
                <div class="elg-title">Payment</div>
                <table class="elg-table">
                    @if($_showBankName)
                    <tr><td class="elg-lbl">Bank</td><td class="elg-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                    @endif
                    @if($_showAccountNo)
                    <tr><td class="elg-lbl">Account</td><td class="elg-val">{{ $employee->account_number ?? '—' }}</td></tr>
                    @endif
                    @if($_showPayPeriod)
                    <tr><td class="elg-lbl">Pay Period</td><td class="elg-val">{{ $slipLabel }}</td></tr>
                    @endif
                    <tr><td class="elg-lbl">Status</td><td class="elg-val"><span class="{{ $isPaid ? 'elg-paid' : 'elg-unpaid' }}" style="padding:1px 8px;border-radius:8px;font-size:8px;">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span></td></tr>
                </table>
            </td>
            @endif
        </tr></table>
        @endif

        @if(!$isHourly)
        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="elg-title">Days &amp; Work</div>
                <table class="elg-table">
                    <tr><td class="elg-lbl">Working</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                    <tr><td class="elg-lbl">Present</td><td class="elg-val" style="color:#5a7a4a;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="elg-lbl">Leave</td><td class="elg-val" style="color:#c8a050;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="elg-lbl">Absent</td><td class="elg-val" style="color:#aa5a5a;">{{ $absentDaysFmt }}</td></tr>
                    @if($extraDays>0)
                    <tr><td class="elg-lbl" style="color:#856404;">Extra/OT</td><td class="elg-val" style="color:#856404;">+{{ $extraDaysFmt }}</td></tr>
                    @endif
                    <tr><td class="elg-lbl">Hours</td><td class="elg-val">{{ $totalWorkHours }} hrs</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="elg-title">Leave</div>
                <table class="elg-table">
                    <tr><td class="elg-lbl" style="font-weight:bold;">This Month</td><td class="elg-val" style="font-weight:bold;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="elg-lbl">Paid</td><td class="elg-val" style="color:#5a7a4a;">{{ $paidLeaveDays }}</td></tr>
                    <tr><td class="elg-lbl">Unpaid</td><td class="elg-val" style="color:#aa5a5a;">{{ $unpaidLeaveDays }}</td></tr>
                    <tr><td class="elg-lbl" style="font-weight:bold;">Overall</td><td class="elg-val" style="font-weight:bold;">Allocated: {{ $totalLeaveAlloc }}</td></tr>
                    <tr><td class="elg-lbl">Remaining</td><td class="elg-val" style="color:#0056b3;">{{ $remainingLeaves }}</td></tr>
                </table>
            </td>
        </tr></table>
        @else
        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="elg-title">Attendance</div>
                <table class="elg-table">
                    <tr><td class="elg-lbl">Working</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $officeDays }}</td></tr>
                    <tr><td class="elg-lbl">Present</td><td class="elg-val" style="color:#5a7a4a;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="elg-lbl">Absent</td><td class="elg-val" style="color:#aa5a5a;">{{ $absentDaysFmt }}</td></tr>
                    <tr><td class="elg-lbl">Hours</td><td class="elg-val">{{ $totalWorkHours }} hrs</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="elg-title">Hourly Rate</div>
                <table class="elg-table">
                    <tr><td class="elg-lbl">Rate <b style="color:{{ $_themeColor }};">(A)</b></td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="elg-lbl">Hours <b style="color:#5a7a4a;">(B)</b></td><td class="elg-val" style="color:#5a7a4a;">{{ $totalWorkHours }} hrs</td></tr>
                    <tr style="background:rgba(232,221,208,0.3);"><td class="elg-lbl"><strong>Gross (A&times;B)</strong></td><td class="elg-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                </table>
            </td>
        </tr></table>
        @endif

        <div class="elg-title" style="margin-top:5px;">Salary</div>
        <table class="elg-table" style="width:60%;">
            <tr><td class="elg-lbl">Type</td><td class="elg-val">{{ $payslipTypeName }}</td></tr>
            @if($isHourly)
            <tr><td class="elg-lbl">Hourly Rate</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
            <tr><td class="elg-lbl">Hours</td><td class="elg-val">{{ $totalWorkHours }} hrs</td></tr>
            <tr><td class="elg-lbl"><strong>Gross Earned</strong></td><td class="elg-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
            @else
            <tr><td class="elg-lbl">Gross Salary</td><td class="elg-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
            <tr><td class="elg-lbl">Per Day</td><td class="elg-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
            <tr><td class="elg-lbl">Per Hour</td><td class="elg-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
            <tr><td class="elg-lbl">Days Paid</td><td class="elg-val">{{ $daysPaid == floor($daysPaid) ? (int) $daysPaid : $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:7px;color:#5a7a4a;">(+{{ $paidLeaveDays }})</span>@endif</td></tr>
            @endif
        </table>

        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="elg-title">Earnings</div>
                <table class="elg-table">
                    @foreach($earRows as $er)
                    <tr><td class="elg-lbl">{{ $er['label'] }}</td><td class="elg-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                    @endforeach
                    <tr class="elg-total"><td class="elg-lbl">Total</td><td class="elg-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="elg-title">Deductions</div>
                <table class="elg-table">
                    @forelse($dedRows as $dr)
                    <tr><td class="elg-lbl">{{ $dr['label'] }}</td><td class="elg-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                    @empty
                    <tr><td class="elg-lbl" style="color:#c8b8a8;font-style:italic;">No deductions</td><td></td></tr>
                    @endforelse
                    <tr class="elg-total"><td class="elg-lbl">Total</td><td class="elg-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                </table>
            </td>
        </tr></table>

        <table style="width:100%;margin-top:4px;"><tr>
            <td class="elg-net-lbl" style="width:50%;">Net Pay</td>
            <td class="elg-net-val" style="width:50%;">{{ $_fmtMoney($netSalary) }}</td>
        </tr></table>

        <div class="elg-stamp">
            @if($_stampUrl)
                <img src="{{ $_stampUrl }}" alt="Stamp">
            @else
                <div class="elg-stamp-ph">Stamp</div>
            @endif
            <div style="font-size:6px;color:#999;margin-top:2px;">Digital Authorization</div>
        </div>

        @if($_showSignatures)
        <table class="elg-sig" style="width:100%;"><tr>
            <td><div class="elg-sig-line"></div><div class="elg-sig-lbl">Employee Signature</div></td>
            <td><div class="elg-sig-line"></div><div class="elg-sig-lbl">Authorized Signatory</div></td>
        </tr></table>
        @endif

        @if($_showFooter)
        <div class="elg-footer">
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
