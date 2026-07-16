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
    color: #222;
    background: #fff;
}
.page { padding: 6px; }

.prof-border-top { height: 3px; background: {{ $_themeColor }}; }

.prof-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 14px 18px 10px;
    border-bottom: 2px solid {{ $_themeColor }};
}
.prof-header img { height: 36px; }
.prof-company { text-align: right; font-size: 9px; color: #555; line-height: 1.6; }
.prof-company .prof-name {
    font-size: 12px; font-weight: bold; color: {{ $_themeColor }}; letter-spacing: 0.5px;
}

.prof-title {
    text-align: center; padding: 8px 18px;
    background: {{ $_themeColor }}; color: #fff;
    font-size: 13px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase;
}

.prof-body { padding: 10px 18px 14px; }

.prof-employee-box {
    border: 1px solid {{ $_themeColor }}44;
    padding: 8px 12px; margin-bottom: 10px;
    background: {{ $_bgLight }};
}
.prof-employee-box table { width: 100%; border-collapse: collapse; }
.prof-employee-box td { padding: 2px 6px; font-size: 9px; vertical-align: top; }
.prof-emp-label { color: #666; width: 80px; font-weight: bold; }
.prof-emp-name { font-size: 11px; font-weight: bold; color: {{ $_themeColor }}; }

.prof-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.prof-table th {
    background: {{ $_themeColor }}; color: #fff;
    padding: 5px 8px; font-size: 9px; text-align: left; font-weight: bold;
}
.prof-table th.ra { text-align: right; }
.prof-table th.ca { text-align: center; }
.prof-table td {
    padding: 4px 8px; font-size: 9px;
    border-bottom: 1px solid #e8e8e8;
}
.prof-table .prof-total td {
    font-weight: bold; color: {{ $_themeColor }};
    border-top: 2px solid {{ $_themeColor }};
    background: {{ $_bgLight }} !important;
}

.prof-two-col > table { width: 48%; display: inline-table; vertical-align: top; }
.prof-two-col > table + table { margin-left: 4%; }

.prof-net-section {
    border: 2px solid {{ $_themeColor }};
    padding: 10px 16px; margin-top: 4px; margin-bottom: 8px;
}
.prof-net-label {
    font-size: 11px; font-weight: bold; color: {{ $_themeColor }};
    text-transform: uppercase; letter-spacing: 1px;
}
.prof-net-amount {
    font-size: 18px; font-weight: bold; color: {{ $_themeColor }};
    text-align: right;
}

.prof-stamp-area {
    display: flex; justify-content: space-between; align-items: center;
    padding: 6px 0; border-top: 1px solid #e8e8e8; margin-top: 8px;
}
.prof-stamp-area img {
    width: 52px; height: 52px; border-radius: 50%;
    object-fit: contain; border: 2px solid {{ $_themeColor }}44;
}
.prof-stamp-placeholder {
    width: 52px; height: 52px; border-radius: 50%;
    border: 2px dashed {{ $_themeColor }}44;
    display: flex; align-items: center; justify-content: center;
    font-size: 6px; color: {{ $_themeColor }}66;
}

.prof-paid { color: #155724; font-weight: bold; }
.prof-unpaid { color: #721c24; font-weight: bold; }

.prof-signatures {
    display: flex; justify-content: space-between;
    padding: 10px 20px 4px; margin-top: 8px;
}
.prof-sig-item { text-align: center; min-width: 100px; }
.prof-sig-line {
    border-top: 1px solid #999; width: 90px;
    margin: 10px auto 3px;
}
.prof-sig-label { font-size: 8px; color: #888; }

.prof-footer {
    background: {{ $_themeColor }}; color: #fff;
    padding: 6px 18px; font-size: 8px; text-align: center; line-height: 1.6;
}
</style>
</head>
<body>
<div class="page">

    <div class="prof-border-top"></div>

    <div class="prof-header">
        <img src="{{ $logoUrl }}" alt="logo">
        <div class="prof-company">
            <div class="prof-name">{{ $companyName }}</div>
            {{ $companyAddr }}{{ $companyCity ? ', ' . $companyCity : '' }}<br>
            {{ $companyState }}{{ $companyZip ? ' - ' . $companyZip : '' }}
            @if ($companyPhone)<br>{{ $companyPhone }}@endif
        </div>
    </div>

    <div class="prof-title">Salary Slip &mdash; {{ strtoupper($slipLabel) }}</div>

    <div class="prof-body">

        <div class="prof-employee-box">
            <table>
                <tr>
                    <td class="prof-emp-label">Employee</td>
                    <td class="prof-emp-name">{{ $employee->name }}</td>
                    <td class="prof-emp-label">Employee ID</td>
                    <td>{{ $employee->employee_id ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="prof-emp-label">Designation</td>
                    <td>{{ optional($employee->designation)->name ?? '—' }}</td>
                    <td class="prof-emp-label">Department</td>
                    <td>{{ optional($employee->department)->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="prof-emp-label">Pay Period</td>
                    <td>{{ $slipLabel }}</td>
                    <td class="prof-emp-label">Pay Type</td>
                    <td>{{ $payslipTypeName }}</td>
                </tr>
                <tr>
                    <td class="prof-emp-label">Pay Status</td>
                    <td><span class="{{ $isPaid ? 'prof-paid' : 'prof-unpaid' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span></td>
                    <td class="prof-emp-label">Date of Joining</td>
                    <td>{{ $employee->company_doj ?? '—' }}</td>
                </tr>
                @if($_isUkRequest)
                <tr>
                    <td class="prof-emp-label">NI Number</td>
                    <td>{{ $niNumber }}</td>
                    <td class="prof-emp-label">Tax Code</td>
                    <td>{{ $taxCode }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Payment Details --}}
        <div class="prof-employee-box" style="margin-bottom:8px;">
            <table>
                <tr><td class="prof-emp-label" colspan="4" style="font-size:10px; font-weight:bold; color:{{ $_themeColor }}; padding-bottom:4px;">Payment Details</td></tr>
                <tr>
                    <td class="prof-emp-label">Bank Name</td>
                    <td>{{ $employee->bank_name ?? '—' }}</td>
                    <td class="prof-emp-label">Account No.</td>
                    <td>{{ $employee->account_number ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="prof-emp-label">{{ \App\Models\Utility::bankCodeLabel() }}</td>
                    <td>{{ $employee->bank_identifier_code ?? '—' }}</td>
                    <td class="prof-emp-label">Account Holder</td>
                    <td>{{ $employee->account_holder_name ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="prof-emp-label">Transaction</td>
                    <td>NEFT</td>
                    <td class="prof-emp-label">Pay Period</td>
                    <td>{{ $slipLabel }}</td>
                </tr>
            </table>
        </div>

        @if(!$isHourly)
        {{-- Leave Details --}}
        <table class="prof-table" style="margin-bottom:10px;">
            <tr><th>Leave Details</th><th class="ca">This Month</th><th class="ra">Overall</th></tr>
            <tr><td>Total Leave</td><td class="ca" style="color:#198754;font-weight:bold;">{{ $approvedLeaves }}</td><td class="ra">Allocated: <strong>{{ $totalLeaveAlloc }}</strong></td></tr>
            <tr><td>Paid Leave</td><td class="ca" style="color:#0d6efd;">{{ $paidLeaveDays }}</td><td class="ra">Used: <strong>{{ $usedLeaves }}</strong></td></tr>
            <tr><td>Unpaid Leave</td><td class="ca" style="color:#dc3545;">{{ $unpaidLeaveDays }}</td><td class="ra">Remaining: <strong style="color:#0056b3;">{{ $remainingLeaves }}</strong></td></tr>
        </table>
        @endif

        {{-- Attendance Summary (shown for all employees) --}}
        <table class="prof-table" style="margin-bottom:10px;">
            <tr><th>Attendance Summary</th><th class="ca">Days</th><th class="ra">Rate</th><th class="ra">Amount</th></tr>
            <tr><td>Working Days</td><td class="ca">{{ $officeDays }}</td><td class="ra">—</td><td class="ra">—</td></tr>
            <tr><td>Present Days</td><td class="ca" style="color:#198754;">{{ $presentDaysFmt }}</td><td class="ra">{{ $_fmtMoney($perDaySalary) }}</td><td class="ra">—</td></tr>
            <tr><td>Paid Leave</td><td class="ca" style="color:#0d6efd;">{{ $paidLeaveDays }}</td><td class="ra">—</td><td class="ra">—</td></tr>
            <tr><td>Unpaid Leave</td><td class="ca" style="color:#e07900;">{{ $unpaidLeaveDays }}</td><td class="ra">—</td><td class="ra">—</td></tr>
            <tr><td>Absent Days</td><td class="ca" style="color:#dc3545;">{{ $absentDaysFmt }}</td><td class="ra">—</td><td class="ra">—</td></tr>
            <tr><td>Days Paid</td><td class="ca" style="font-weight:bold;">@php $daysPaidFmt = ($daysPaid == floor($daysPaid)) ? (int)$daysPaid : $daysPaid; @endphp{{ $daysPaidFmt }} @if($paidLeaveDays>0)<span style="font-size:7px;color:#198754;">(+{{ $paidLeaveDays }})</span>@endif</td><td class="ra">—</td><td class="ra">—</td></tr>
            <tr><td>Total Hours</td><td class="ca">{{ $totalWorkHours }} hrs</td><td class="ra">{{ $_fmtMoney($perHourSalary) }}/hr</td><td class="ra">—</td></tr>
        </table>

        <div class="prof-two-col">
            <table class="prof-table">
                <tr><th colspan="2">Earnings</th></tr>
                @foreach ($earRows as $er)
                <tr><td>{{ $er['label'] }}</td><td class="ra">{{ $er['amount'] !== null ? $_fmtMoney($er['amount']) : '' }}</td></tr>
                @endforeach
                <tr class="prof-total"><td>Total Earnings</td><td class="ra">{{ $_fmtMoney($totalEarnings) }}</td></tr>
            </table>
            <table class="prof-table">
                <tr><th colspan="2">Deductions</th></tr>
                @forelse ($dedRows as $dr)
                <tr><td>{{ $dr['label'] }}</td><td class="ra">{{ $dr['amount'] !== null ? $_fmtMoney($dr['amount']) : '—' }}</td></tr>
                @empty
                <tr><td colspan="2" style="color:#aaa;font-style:italic;text-align:center;">No deductions</td></tr>
                @endforelse
                <tr class="prof-total"><td>Total Deductions</td><td class="ra">{{ $_fmtMoney($totalDeductions) }}</td></tr>
            </table>
        </div>

        <div class="prof-net-section">
            <table style="width:100%;">
                <tr>
                    <td class="prof-net-label">Net Pay</td>
                    <td class="prof-net-amount">{{ $_fmtMoney($netSalary) }}</td>
                </tr>
            </table>
        </div>

        <div class="prof-stamp-area">
            <div>
                <div style="font-size:7px; color:#999;">Authorized Digital Stamp</div>
                <div style="font-size:8px; color:#666;">{{ $companyName }}</div>
            </div>
            @if ($_stampUrl)
                <img src="{{ $_stampUrl }}" alt="Stamp">
            @else
                <div class="prof-stamp-placeholder">Stamp</div>
            @endif
        </div>

        <div class="prof-signatures">
            <div class="prof-sig-item">
                <div class="prof-sig-line"></div>
                <div class="prof-sig-label">Employee Signature</div>
            </div>
            <div class="prof-sig-item">
                <div class="prof-sig-line"></div>
                <div class="prof-sig-label">HR Manager</div>
            </div>
            <div class="prof-sig-item">
                <div class="prof-sig-line"></div>
                <div class="prof-sig-label">Authorized Signatory</div>
            </div>
        </div>

    </div>

    <div class="prof-footer">
        @if ($companyPhone)<strong>Tel:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
        @if ($companyWeb)<strong>Web:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
        @if ($companyEmail)<strong>Email:</strong> {{ $companyEmail }} @endif
        &nbsp;&mdash;&nbsp; This is a computer-generated document
    </div>

</div>
</body>
</html>
