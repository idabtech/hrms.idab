@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
    // Employee and Payslip objects are also passed to the view.
    // (See pdf.blade.php for full variable list)
@endphp

<style>
.prof-wrap {
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: 12px;
    color: #222;
    max-width: 780px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid #ddd;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.prof-wrap * { box-sizing: border-box; }

.prof-border-top {
    height: 4px;
    background: {{ $_themeColor }};
}

.prof-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 18px 24px 12px;
    border-bottom: 2px solid {{ $_themeColor }};
}
.prof-header img { height: 44px; }
.prof-company {
    text-align: right;
    font-size: 11px;
    color: #555;
    line-height: 1.7;
}
.prof-company .prof-name {
    font-size: 15px;
    font-weight: 700;
    color: {{ $_themeColor }};
    letter-spacing: 0.5px;
}
.prof-title {
    text-align: center;
    padding: 10px 24px;
    background: {{ $_themeColor }};
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
}

.prof-body { padding: 14px 24px 18px; }

.prof-employee-box {
    border: 1px solid {{ $_themeColor }}44;
    border-radius: 4px;
    padding: 12px 16px;
    margin-bottom: 14px;
    background: {{ $_bgLight }};
}
.prof-employee-box table { width: 100%; border-collapse: collapse; }
.prof-employee-box td {
    padding: 3px 8px;
    font-size: 11px;
    vertical-align: top;
}
.prof-emp-label { color: #666; width: 100px; font-weight: 600; }
.prof-emp-value { color: #222; }
.prof-emp-name { font-size: 14px; font-weight: 700; color: {{ $_themeColor }}; }

.prof-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
}
.prof-table th {
    background: {{ $_themeColor }};
    color: #fff;
    padding: 6px 10px;
    font-size: 11px;
    text-align: left;
    font-weight: 600;
    letter-spacing: 0.3px;
}
.prof-table th.ra { text-align: right; }
.prof-table th.ca { text-align: center; }
.prof-table td {
    padding: 5px 10px;
    font-size: 11px;
    border-bottom: 1px solid #e8e8e8;
}
.prof-table tr:nth-child(even) td { background: #fafafa; }
.prof-table .prof-total td {
    font-weight: 700;
    color: {{ $_themeColor }};
    border-top: 2px solid {{ $_themeColor }};
    background: {{ $_bgLight }} !important;
}

.prof-two-col {
    display: flex;
    gap: 16px;
}
.prof-two-col > div { flex: 1; }

.prof-net-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 2px solid {{ $_themeColor }};
    border-radius: 4px;
    padding: 14px 20px;
    margin-top: 4px;
    margin-bottom: 12px;
}
.prof-net-label {
    font-size: 14px;
    font-weight: 700;
    color: {{ $_themeColor }};
    text-transform: uppercase;
    letter-spacing: 1px;
}
.prof-net-amount {
    font-size: 22px;
    font-weight: 700;
    color: {{ $_themeColor }};
}
.prof-net-amount span {
    font-size: 12px;
    font-weight: 400;
    color: #888;
    margin-left: 8px;
}

.prof-stamp-area {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-top: 1px solid #e8e8e8;
    margin-top: 10px;
}
.prof-stamp-area img {
    width: 64px; height: 64px;
    border-radius: 50%;
    object-fit: contain;
    border: 2px solid {{ $_themeColor }}44;
}
.prof-stamp-placeholder {
    width: 64px; height: 64px;
    border-radius: 50%;
    border: 2px dashed {{ $_themeColor }}44;
    display: flex; align-items: center; justify-content: center;
    font-size: 7px; color: {{ $_themeColor }}66;
    text-align: center; line-height: 1.2; padding: 4px;
}

.prof-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.prof-paid { background: #d4edda; color: #155724; }
.prof-unpaid { background: #f8d7da; color: #721c24; }

.prof-signatures {
    display: flex;
    justify-content: space-between;
    padding: 16px 30px 6px;
    margin-top: 10px;
}
.prof-sig-item { text-align: center; min-width: 140px; }
.prof-sig-line {
    border-top: 1.5px solid #999;
    width: 120px;
    margin: 12px auto 4px;
}
.prof-sig-label { font-size: 10px; color: #888; }

.prof-footer {
    background: {{ $_themeColor }};
    color: #fff;
    padding: 8px 24px;
    font-size: 10px;
    text-align: center;
    line-height: 1.7;
}
</style>

<div class="prof-wrap">

    <div class="prof-border-top"></div>

    {{-- Header --}}
    <div class="prof-header">
        <img src="{{ $logoUrl }}" alt="logo">
        <div class="prof-company">
            <div class="prof-name">{{ $companyName }}</div>
            {{ $companyAddr }}{{ $companyCity ? ', ' . $companyCity : '' }}<br>
            {{ $companyState }}{{ $companyZip ? ' - ' . $companyZip : '' }}
            @if ($companyPhone)<br>{{ $companyPhone }}@endif
            @if ($companyEmail) | {{ $companyEmail }}@endif
        </div>
    </div>

    {{-- Title --}}
    <div class="prof-title">{{ __('Salary Slip') }} &mdash; {{ strtoupper($slipLabel) }}</div>

    {{-- Body --}}
    <div class="prof-body">

        {{-- Employee Details --}}
        <div class="prof-employee-box">
            <table>
                <tr>
                    <td class="prof-emp-label">{{ __('Employee') }}</td>
                    <td class="prof-emp-name">{{ $employee->name }}</td>
                    <td class="prof-emp-label">{{ __('Employee ID') }}</td>
                    <td class="prof-emp-value">{{ $employee->employee_id ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="prof-emp-label">{{ __('Designation') }}</td>
                    <td class="prof-emp-value">{{ optional($employee->designation)->name ?? '—' }}</td>
                    <td class="prof-emp-label">{{ __('Department') }}</td>
                    <td class="prof-emp-value">{{ optional($employee->department)->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="prof-emp-label">{{ __('Pay Period') }}</td>
                    <td class="prof-emp-value">{{ $slipLabel }}</td>
                    <td class="prof-emp-label">{{ __('Pay Type') }}</td>
                    <td class="prof-emp-value">{{ $payslipTypeName }}</td>
                </tr>
                <tr>
                    <td class="prof-emp-label">{{ __('Pay Status') }}</td>
                    <td class="prof-emp-value">
                        <span class="prof-badge {{ $isPaid ? 'prof-paid' : 'prof-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span>
                    </td>
                    <td class="prof-emp-label">{{ __('Date of Joining') }}</td>
                    <td class="prof-emp-value">{{ $employee->company_doj ?? '—' }}</td>
                </tr>
                @if($_isUkRequest)
                <tr>
                    <td class="prof-emp-label">{{ __('NI Number') }}</td>
                    <td class="prof-emp-value">{{ $niNumber }}</td>
                    <td class="prof-emp-label">{{ __('Tax Code') }}</td>
                    <td class="prof-emp-value">{{ $taxCode }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Payment Details --}}
        <div class="prof-employee-box" style="margin-bottom:10px;">
            <table>
                <tr><td class="prof-emp-label" colspan="4" style="font-size:12px; font-weight:700; color:{{ $_themeColor }}; padding-bottom:6px;">{{ __('Payment Details') }}</td></tr>
                <tr>
                    <td class="prof-emp-label">{{ __('Bank Name') }}</td>
                    <td class="prof-emp-value">{{ $employee->bank_name ?? '—' }}</td>
                    <td class="prof-emp-label">{{ __('Account No.') }}</td>
                    <td class="prof-emp-value">{{ $employee->account_number ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="prof-emp-label">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td>
                    <td class="prof-emp-value">{{ $employee->bank_identifier_code ?? '—' }}</td>
                    <td class="prof-emp-label">{{ __('Account Holder') }}</td>
                    <td class="prof-emp-value">{{ $employee->account_holder_name ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="prof-emp-label">{{ __('Transaction') }}</td>
                    <td class="prof-emp-value">NEFT</td>
                    <td class="prof-emp-label">{{ __('Pay Period') }}</td>
                    <td class="prof-emp-value">{{ $slipLabel }}</td>
                </tr>
            </table>
        </div>

        {{-- Leave Details (monthly vs overall) --}}
        @if(!$isHourly)
        <table class="prof-table" style="margin-bottom:14px;">
            <tr><th>{{ __('Leave Details') }}</th><th class="ca">{{ __('This Month') }}</th><th class="ra">{{ __('Overall') }}</th></tr>
            <tr><td>{{ __('Total Leave') }}</td><td class="ca" style="color:#198754; font-weight:600;">{{ $approvedLeaves }}</td><td class="ra">{{ __('Allocated') }}: <strong>{{ $totalLeaveAlloc }}</strong></td></tr>
            <tr><td>{{ __('Paid Leave') }}</td><td class="ca" style="color:#0d6efd;">{{ $paidLeaveDays }}</td><td class="ra">{{ __('Used') }}: <strong>{{ $usedLeaves }}</strong></td></tr>
            <tr><td>{{ __('Unpaid Leave') }}</td><td class="ca" style="color:#dc3545;">{{ $unpaidLeaveDays }}</td><td class="ra">{{ __('Remaining') }}: <strong style="color:#0056b3;">{{ $remainingLeaves }}</strong></td></tr>
        </table>
        @endif

        {{-- Attendance Summary --}}
        <table class="prof-table" style="margin-bottom:14px;">
            <tr>
                <th>{{ __('Attendance Summary') }}</th>
                <th class="ca">{{ __('Days') }}</th>
                <th class="ra">{{ __('Rate') }}</th>
                <th class="ra">{{ __('Amount') }}</th>
            </tr>
            <tr>
                <td>{{ __('Working Days') }}</td>
                <td class="ca">{{ $officeDays }}</td>
                <td class="ra">—</td>
                <td class="ra">—</td>
            </tr>
            <tr>
                <td>{{ __('Present Days') }}</td>
                <td class="ca" style="color:#198754;">{{ $presentDaysFmt }}</td>
                <td class="ra">{{ $_fmtMoney($perDaySalary) }}</td>
                <td class="ra">—</td>
            </tr>
            <tr>
                <td>{{ __('Paid Leave') }}</td>
                <td class="ca" style="color:#0d6efd;">{{ $paidLeaveDays }}</td>
                <td class="ra">—</td>
                <td class="ra">—</td>
            </tr>
            <tr>
                <td>{{ __('Unpaid Leave') }}</td>
                <td class="ca" style="color:#e07900;">{{ $unpaidLeaveDays }}</td>
                <td class="ra">—</td>
                <td class="ra">—</td>
            </tr>
            <tr>
                <td>{{ __('Absent Days') }}</td>
                <td class="ca" style="color:#dc3545;">{{ $absentDaysFmt }}</td>
                <td class="ra">—</td>
                <td class="ra">—</td>
            </tr>
            <tr>
                <td>{{ __('Days Paid') }}</td>
                <td class="ca" style="font-weight:700;">{{ $daysPaid }}</td>
                <td class="ra">—</td>
                <td class="ra">—</td>
            </tr>
            <tr>
                <td>{{ __('Total Hours Worked') }}</td>
                <td class="ca">{{ $totalWorkHours }} hrs</td>
                <td class="ra">{{ $_fmtMoney($perHourSalary) }}/hr</td>
                <td class="ra">—</td>
            </tr>
        </table>

        {{-- Earnings & Deductions side by side --}}
        <div class="prof-two-col">
            <div>
                <table class="prof-table">
                    <tr><th colspan="2">{{ __('Earnings') }}</th></tr>
                    @foreach ($earRows as $er)
                    <tr>
                        <td>{{ $er['label'] }}</td>
                        <td class="ra">{{ $er['amount'] !== null ? $_fmtMoney($er['amount']) : '' }}</td>
                    </tr>
                    @endforeach
                    <tr class="prof-total">
                        <td>{{ __('Total Earnings') }}</td>
                        <td class="ra">{{ $_fmtMoney($totalEarnings) }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <table class="prof-table">
                    <tr><th colspan="2">{{ __('Deductions') }}</th></tr>
                    @forelse ($dedRows as $dr)
                    <tr>
                        <td>{{ $dr['label'] }}</td>
                        <td class="ra">{{ $dr['amount'] !== null ? $_fmtMoney($dr['amount']) : '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" style="color:#aaa; font-style:italic; text-align:center;">{{ __('No deductions') }}</td></tr>
                    @endforelse
                    <tr class="prof-total">
                        <td>{{ __('Total Deductions') }}</td>
                        <td class="ra">{{ $_fmtMoney($totalDeductions) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Net Pay --}}
        <div class="prof-net-section">
            <span class="prof-net-label">{{ __('Net Pay') }}</span>
            <span class="prof-net-amount">
                {{ $_fmtMoney($netSalary) }}
                <span>{{ __('only') }}</span>
            </span>
        </div>

        {{-- Stamp & Signatures --}}            <div class="prof-stamp-area">
            <div>
                <div style="font-size:9px; color:#999; margin-bottom:2px;">{{ __('Authorized Digital Stamp') }}</div>
                <div style="font-size:10px; color:#666;">{{ $companyName }}</div>
            </div>
            {{-- Always render the img so postMessage can update it dynamically --}}
            <img src="{{ $_stampUrl ?? '' }}" alt="Stamp"
                 style="{{ $_stampUrl ? 'width:64px;height:64px;border-radius:50%;object-fit:contain;border:2px solid ' . $_themeColor . '44;' : 'display:none;' }}">
            @if (!$_stampUrl)
                <div class="prof-stamp-placeholder">{{ __('Stamp') }}</div>
            @endif
        </div>

        <div class="prof-signatures">
            <div class="prof-sig-item">
                <div class="prof-sig-line"></div>
                <div class="prof-sig-label">{{ __('Employee Signature') }}</div>
            </div>
            <div class="prof-sig-item">
                <div class="prof-sig-line"></div>
                <div class="prof-sig-label">{{ __('HR Manager') }}</div>
            </div>
            <div class="prof-sig-item">
                <div class="prof-sig-line"></div>
                <div class="prof-sig-label">{{ __('Authorized Signatory') }}</div>
            </div>
        </div>

    </div>{{-- /body --}}

    {{-- Footer --}}
    <div class="prof-footer">
        @if ($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
        @if ($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
        @if ($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }} @endif
        &nbsp;&mdash;&nbsp; {{ __('This is a computer-generated document') }}
    </div>

</div>

<script>
/**
 * Listen for stamp data URL from the parent settings page (postMessage).
 * Updates the stamp image src dynamically for the live preview.
 */
window.addEventListener('message', function(e) {
    if (e.data && e.data.type === 'payslip-stamp' && e.data.dataUrl) {
        var img = document.querySelector('.prof-stamp-area img');
        if (img) { img.src = e.data.dataUrl; img.style.display = ''; img.style.width = '64px'; img.style.height = '64px'; img.style.borderRadius = '50%'; img.style.objectFit = 'contain'; img.style.border = '2px solid {{ $_themeColor }}44'; }
        var ph = document.querySelector('.prof-stamp-placeholder');
        if (ph) ph.style.display = 'none';
    }
});
</script>
