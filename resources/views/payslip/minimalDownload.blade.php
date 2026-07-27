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
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9px; color:#333; background:#f5f5f5;
}
.page { padding: 6px; }
.min-card { background:#fff; border:1px solid #e0e0e0; }
.min-pad { padding:10px 14px; }
.min-title { font-size:8px; color:#999; letter-spacing:1.5px; text-transform:uppercase; }
.min-mono { color:{{ $_themeColor }}; font-weight:600; }
.min-grid-title { font-size:8px; text-transform:uppercase; letter-spacing:1px; color:#999; margin-bottom:3px; border-bottom:1px solid #eee; padding-bottom:2px; }
.min-table { width:100%; border-collapse:collapse; }
.min-table td { padding:2px 3px; font-size:9px; color:#444; }
.min-lbl { color:#888; width:50%; }
.min-val { text-align:right; font-weight:500; }
.min-total td { font-weight:700; color:{{ $_themeColor }}; border-top:1.5px solid #ccc !important; padding-top:3px; font-size:9px; }
.min-net { background:#222; color:#fff; padding:8px 14px; margin:5px 0; }
.min-net-lbl { font-size:10px; font-weight:500; letter-spacing:0.5px; }
.min-net-val { font-size:15px; font-weight:700; text-align:right; }
.min-stamp { text-align:center; padding:4px 0; border-top:1px solid #e8e8e8; margin-top:4px; }
.min-stamp img { width:44px; height:44px; border-radius:50%; object-fit:contain; border:1.5px solid {{ $_themeColor }}44; }
.min-stamp-ph { width:44px; height:44px; border-radius:50%; border:1.5px dashed #ccc; display:inline-block; padding-top:10px; font-size:5px; color:#bbb; }
.min-paid { color:#198754; font-weight:bold; }
.min-unpaid { color:#dc3545; font-weight:bold; }
.min-sig { margin-top:6px; }
.min-sig td { text-align:center; padding:2px 8px; }
.min-sig-line { border-top:1px solid #ccc; width:75px; margin:6px auto 2px; }
.min-sig-lbl { font-size:7px; color:#aaa; }
.min-footer { background:#fafafa; color:#999; text-align:center; padding:4px 14px; font-size:7px; border-top:1px solid #e8e8e8; }
</style>
</head>
<body>
<div class="page">
<div class="min-card">
    <div style="border-bottom:2px solid {{ $_themeColor }};padding:10px 14px;">
        <table style="width:100%;"><tr>
            <td><div class="min-title">PAY SLIP</div><div style="font-size:11px;font-weight:600;color:{{ $_themeColor }};">{{ $slipLabel }}</div></td>
            <td style="text-align:right;"><img src="{{ $logoUrl }}" alt="logo" style="height:24px;"></td>
        </tr></table>
    </div>

    <div class="min-pad">
        @if($_showEmployeeDetails)
        <div style="display:flex;justify-content:space-between;background:#fafafa;padding:6px 10px;margin-bottom:6px;">
            <div>
                @if($_showName)<strong style="font-size:10px;">{{ $employee->name }}</strong><br>@endif
                @if($_showDesignation)<span style="font-size:8px;color:#777;">{{ optional($employee->designation)->name ?? '—' }}</span>@endif
            </div>
            <div style="text-align:right;font-size:8px;color:#777;">
                @if($_showEmployeeId)ID: {{ $employee->employee_id ?? '—' }}<br>@endif
                <span style="font-weight:600;{{ $isPaid ? 'color:#198754;' : 'color:#dc3545;' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span>
            </div>
        </div>
        @endif

        @if($_showEmployeeDetails || $_showPaymentDetails)
        <table style="width:100%;border-collapse:collapse;"><tr>
            @if($_showEmployeeDetails)
            <td style="width:33%;vertical-align:top;padding-right:5px;">
                <div class="min-grid-title">Employee</div>
                <table class="min-table">
                    @if($_showDepartment)
                    <tr><td class="min-lbl">Department</td><td class="min-val">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                    @endif
                    @if($_showDateOfJoining)
                    <tr><td class="min-lbl">DOJ</td><td class="min-val">{{ $employee->company_doj ?? '—' }}</td></tr>
                    @endif
                    @if($_showPanNo)
                    <tr><td class="min-lbl">PAN No.</td><td class="min-val">{{ $employee->tax_payer_id ?? '—' }}</td></tr>
                    @endif
                </table>
            </td>
            @endif
            @if($_showPaymentDetails)
            <td style="width:33%;vertical-align:top;padding:0 5px;">
                <div class="min-grid-title">Payment</div>
                <table class="min-table">
                    @if($_showBankName)
                    <tr><td class="min-lbl">Bank</td><td class="min-val">{{ $employee->bank_name ?? '—' }}</td></tr>
                    @endif
                    @if($_showAccountNo)
                    <tr><td class="min-lbl">Account</td><td class="min-val">{{ $employee->account_number ?? '—' }}</td></tr>
                    @endif
                    @if($_showBankCode)
                    <tr><td class="min-lbl">{{ \App\Models\Utility::bankCodeLabel() }}</td><td class="min-val">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                    @endif
                </table>
            </td>
            @endif
            <td style="width:33%;vertical-align:top;padding-left:5px;">
                <div class="min-grid-title">Salary</div>
                <table class="min-table">
                    <tr><td class="min-lbl">Gross</td><td class="min-val min-mono">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                    <tr><td class="min-lbl">Per Day</td><td class="min-val">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                    <tr><td class="min-lbl">Per Hour</td><td class="min-val">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="min-lbl">Days Paid</td><td class="min-val">{{ $daysPaid }}</td></tr>
                </table>
            </td>
        </tr></table>
        @endif

        @if(!$isHourly)
        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:33%;vertical-align:top;padding-right:5px;">
                <div class="min-grid-title">Days &amp; Work</div>
                <table class="min-table">
                    <tr><td class="min-lbl">Working</td><td class="min-val min-mono">{{ $officeDays }}</td></tr>
                    <tr><td class="min-lbl">Present</td><td class="min-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="min-lbl">Leave</td><td class="min-val" style="color:#e07900;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="min-lbl">Absent</td><td class="min-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    @if($extraDays>0)
                    <tr><td class="min-lbl" style="color:#856404;">Extra/OT</td><td class="min-val" style="color:#856404;font-weight:600;">+{{ $extraDaysFmt }}</td></tr>
                    @endif
                    <tr><td class="min-lbl">Hours</td><td class="min-val">{{ $totalWorkHours }}h</td></tr>
                </table>
            </td>
            <td style="width:33%;vertical-align:top;padding:0 5px;">
                <div class="min-grid-title">Leave</div>
                <table class="min-table">
                    <tr><td class="min-lbl" style="font-weight:bold;">This Month</td><td class="min-val" style="font-weight:bold;">{{ $approvedLeaves }}</td></tr>
                    <tr><td class="min-lbl">Paid</td><td class="min-val" style="color:#198754;">{{ $paidLeaveDays }}</td></tr>
                    <tr><td class="min-lbl">Unpaid</td><td class="min-val" style="color:#dc3545;">{{ $unpaidLeaveDays }}</td></tr>
                </table>
            </td>
            <td style="width:33%;vertical-align:top;padding-left:5px;">
                <div class="min-grid-title">Summary</div>
                <table class="min-table">
                    <tr><td class="min-lbl">Allocated</td><td class="min-val">{{ $totalLeaveAlloc }}</td></tr>
                    <tr><td class="min-lbl">Remaining</td><td class="min-val" style="color:#0056b3;">{{ $remainingLeaves }}</td></tr>
                    <tr><td class="min-lbl">Used</td><td class="min-val">{{ $usedLeaves }}</td></tr>
                </table>
            </td>
        </tr></table>
        @else
        <table style="width:100%;border-collapse:collapse;margin-top:5px;"><tr>
            <td style="width:33%;vertical-align:top;padding-right:5px;">
                <div class="min-grid-title">Attendance</div>
                <table class="min-table">
                    <tr><td class="min-lbl">Working</td><td class="min-val min-mono">{{ $officeDays }}</td></tr>
                    <tr><td class="min-lbl">Present</td><td class="min-val" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                    <tr><td class="min-lbl">Absent</td><td class="min-val" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                    <tr><td class="min-lbl">Hours</td><td class="min-val">{{ $totalWorkHours }}h</td></tr>
                </table>
            </td>
            <td style="width:33%;vertical-align:top;padding:0 5px;">
                <div class="min-grid-title">Hourly Rate</div>
                <table class="min-table">
                    <tr><td class="min-lbl">Rate <b style="color:{{ $_themeColor }};">(A)</b></td><td class="min-val" style="color:{{ $_themeColor }};">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    <tr><td class="min-lbl">Hours <b style="color:#198754;">(B)</b></td><td class="min-val" style="color:#198754;">{{ $totalWorkHours }} hrs</td></tr>
                    <tr style="background:{{ $_themeColor }}10;"><td class="min-lbl"><strong>Gross (A &times; B)</strong></td><td class="min-val" style="color:{{ $_themeColor }};"><strong>{{ $_fmtMoney($_storedSalary) }}</strong></td></tr>
                </table>
            </td>
            <td style="width:33%;vertical-align:top;padding-left:5px;">
                <div class="min-grid-title">Payslip</div>
                <table class="min-table">
                    <tr><td class="min-lbl">{{ $payslipTypeName }}</td><td class="min-val">{{ $slipLabel }}</td></tr>
                    <tr><td class="min-lbl">Status</td><td class="min-val {{ $isPaid ? 'min-paid' : 'min-unpaid' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</td></tr>
                </table>
            </td>
        </tr></table>
        @endif

        <div style="height:1px;background:#e8e8e8;margin:5px 0;"></div>

        <table style="width:100%;border-collapse:collapse;"><tr>
            <td style="width:50%;vertical-align:top;padding-right:5px;">
                <div class="min-grid-title">Earnings</div>
                <table class="min-table">
                    @foreach($earRows as $er)
                    <tr><td class="min-lbl">{{ $er['label'] }}</td><td class="min-val">{{ $er['amount']!==null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                    @endforeach
                    <tr class="min-total"><td class="min-lbl">Total</td><td class="min-val">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                </table>
            </td>
            <td style="width:50%;vertical-align:top;padding-left:5px;">
                <div class="min-grid-title">Deductions</div>
                <table class="min-table">
                    @forelse($dedRows as $dr)
                    <tr><td class="min-lbl">{{ $dr['label'] }}</td><td class="min-val">{{ $dr['amount']!==null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                    @empty
                    <tr><td class="min-lbl" style="color:#aaa;font-style:italic;">None</td><td></td></tr>
                    @endforelse
                    <tr class="min-total"><td class="min-lbl">Total</td><td class="min-val">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                </table>
            </td>
        </tr></table>

        <table style="width:100%;margin-top:4px;"><tr>
            <td class="min-net-lbl" style="width:50%;">Net Pay</td>
            <td class="min-net-val" style="width:50%;">{{ $_fmtMoney($netSalary) }}</td>
        </tr></table>

        <div class="min-stamp">
            @if($_stampUrl)
                <img src="{{ $_stampUrl }}" alt="Stamp">
            @else
                <div class="min-stamp-ph">Stamp</div>
            @endif
            <div style="font-size:6px;color:#999;margin-top:2px;">Digital Authorization</div>
        </div>

        @if($_showSignatures)
        <table class="min-sig" style="width:100%;"><tr>
            <td><div class="min-sig-line"></div><div class="min-sig-lbl">Employee</div></td>
            <td><div class="min-sig-line"></div><div class="min-sig-lbl">Authorized</div></td>
        </tr></table>
        @endif
    </div>

    @if($_showFooter)
    <div class="min-footer">
        @if($companyPhone)<strong>Tel:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
        @if($companyWeb)<strong>Web:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
        @if($companyEmail)<strong>Email:</strong> {{ $companyEmail }} @endif
    </div>
    @endif
</div>
</div>
</body>
</html>
