@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
    // Employee and Payslip objects are also passed to the view.
    // (See pdf.blade.php for full variable list)
@endphp

<style>
.cpt-wrap {
    font-family: 'Segoe UI', -apple-system, Arial, sans-serif;
    font-size: 12px;
    color: #1a1a1a;
    max-width: 700px;
    margin: 0 auto;
    background: #fff;
}
.cpt-wrap * { box-sizing: border-box; }

.cpt-top {
    background: {{ $_themeColor }};
    color: #fff;
    padding: 10px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.cpt-top h2 { font-size: 14px; font-weight: 700; margin: 0; letter-spacing: 1px; }
.cpt-top span { font-size: 10px; opacity: 0.85; }

.cpt-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 2px solid {{ $_themeColor }};
    background: {{ $_bgLight }};
}
.cpt-header img { height: 36px; }
.cpt-company {
    text-align: right;
    font-size: 10px;
    color: #555;
    line-height: 1.6;
}
.cpt-company strong { color: {{ $_themeColor }}; font-size: 11px; }

.cpt-employee {
    display: flex;
    justify-content: space-between;
    padding: 8px 16px;
    background: {{ $_bgMed }};
    font-size: 11px;
}
.cpt-employee > div { flex: 1; }
.cpt-employee .e-right { text-align: right; }
.cpt-employee strong { color: {{ $_themeColor }}; }

.cpt-section { padding: 8px 16px; }
.cpt-section-title {
    font-weight: 700;
    font-size: 11px;
    color: {{ $_themeColor }};
    border-bottom: 1px solid {{ $_themeColor }}44;
    padding-bottom: 4px;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cpt-table { width: 100%; border-collapse: collapse; }
.cpt-table td {
    padding: 4px 6px;
    font-size: 11px;
    border-bottom: 1px solid #f0f0f0;
}
.cpt-table tr:last-child td { border-bottom: none; }
.cpt-label { color: #666; width: 55%; }
.cpt-value { text-align: right; font-weight: 600; }
.cpt-total td {
    font-weight: 700;
    color: {{ $_themeColor }};
    border-top: 2px solid {{ $_themeColor }} !important;
    padding-top: 6px;
    font-size: 12px;
}

.cpt-two-col {
    display: flex;
    gap: 20px;
}
.cpt-two-col > div { flex: 1; }

.cpt-net-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: {{ $_themeColor }};
    color: #fff;
    padding: 10px 16px;
    margin: 0 16px;
    border-radius: 4px;
}
.cpt-net-label { font-size: 13px; font-weight: 600; }
.cpt-net-value { font-size: 18px; font-weight: 700; }

.cpt-stamp-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 16px;
    font-size: 10px;
    color: #888;
}
.cpt-stamp-row img {
    width: 56px; height: 56px; border-radius: 50%;
    object-fit: contain; border: 2px solid {{ $_themeColor }}44;
}
.cpt-stamp-placeholder {
    width: 56px; height: 56px; border-radius: 50%;
    border: 2px dashed {{ $_themeColor }}44;
    display: flex; align-items: center; justify-content: center;
    font-size: 7px; color: {{ $_themeColor }}66;
    text-align: center; line-height: 1.2; padding: 4px;
}

.cpt-status {
    display: inline-block;
    padding: 1px 8px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 600;
}
.cpt-paid { background: #d4edda; color: #155724; }
.cpt-unpaid { background: #f8d7da; color: #721c24; }

.cpt-footer {
    text-align: center;
    font-size: 9px;
    color: #aaa;
    padding: 10px 16px;
    border-top: 1px solid #eee;
    margin-top: 10px;
}
</style>

<div class="cpt-wrap">

    {{-- Top bar --}}
    <div class="cpt-top">
        <h2>{{ __('PAY SLIP') }}</h2>
        <span>{{ $slipLabel }}</span>
    </div>

    {{-- Header with logo & company --}}
    <div class="cpt-header">
        <img src="{{ $logoUrl }}" alt="logo">
        <div class="cpt-company">
            <strong>{{ $companyName }}</strong><br>
            @if ($companyPhone) {{ $companyPhone }} @endif
            @if ($companyEmail) | {{ $companyEmail }} @endif
        </div>
    </div>

    {{-- Employee summary --}}
    <div class="cpt-employee">
        <div>
            <strong>{{ $employee->name }}</strong><br>
            {{ optional($employee->designation)->name ?? '—' }}
            @if ($employee->employee_id) &middot; #{{ $employee->employee_id }} @endif
            <br>
            <span style="font-size:10px; color:#888;">{{ __('Department') }}: {{ optional($employee->department)->name ?? '—' }} | {{ __('DOJ') }}: {{ $employee->company_doj ?? '—' }}</span>
        </div>
        <div class="e-right">
            {{ $payslipTypeName }}<br>
            Status: <span class="cpt-status {{ $isPaid ? 'cpt-paid' : 'cpt-unpaid' }}">{{ $isPaid ? __('Paid') : __('Unpaid') }}</span><br>
            @if($employee->tax_payer_id)<span style="font-size:10px; color:#666;">{{ __('PAN') }}: {{ $employee->tax_payer_id }}</span>@endif
        </div>
    </div>

    {{-- Quick info row --}}
    <div class="cpt-section">
        <table class="cpt-table">
            <tr>
                <td class="cpt-label">{{ __('Working Days') }}</td>
                <td class="cpt-value">{{ $officeDays }}</td>
                <td class="cpt-label">{{ __('Present') }}</td>
                <td class="cpt-value" style="color:#198754;">{{ $presentDaysFmt }}</td>
            </tr>
            <tr>
                <td class="cpt-label">{{ __('Absent') }}</td>
                <td class="cpt-value" style="color:#dc3545;">{{ $absentDaysFmt }}</td>
                <td class="cpt-label">{{ __('Leaves') }}</td>
                <td class="cpt-value" style="color:#e07900;">{{ $approvedLeaves }}</td>
            </tr>
            @if (!$isHourly)
            <tr>
                <td class="cpt-label">{{ __('Per Day') }}</td>
                <td class="cpt-value">{{ $_fmtMoney($perDaySalary) }}</td>
                <td class="cpt-label">{{ __('Per Hour') }}</td>
                <td class="cpt-value">{{ $_fmtMoney($perHourSalary) }}</td>
            </tr>
            @endif
            <tr>
                <td class="cpt-label">{{ __('Hours Worked') }}</td>
                <td class="cpt-value">{{ $totalWorkHours }} hrs</td>
                <td class="cpt-label">{{ __('Department') }}</td>
                <td class="cpt-value">{{ optional($employee->department)->name ?? '—' }}</td>
            </tr>
            @if($_isUkRequest)
            <tr>
                <td class="cpt-label">{{ __('NI Number') }}</td>
                <td class="cpt-value">{{ $niNumber }}</td>
                <td class="cpt-label">{{ __('Tax Code') }}</td>
                <td class="cpt-value">{{ $taxCode }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Earnings & Deductions --}}
    <div class="cpt-section">
        <div class="cpt-two-col">
            <div>
                <div class="cpt-section-title">{{ __('Earnings') }}</div>
                <table class="cpt-table">
                    @foreach ($earRows as $er)
                    <tr>
                        <td class="cpt-label">{{ $er['label'] }}</td>
                        <td class="cpt-value">{{ $er['amount'] !== null ? $_fmtMoney($er['amount']) : '' }}</td>
                    </tr>
                    @endforeach
                    <tr class="cpt-total">
                        <td class="cpt-label">{{ __('Total') }}</td>
                        <td class="cpt-value">{{ $_fmtMoney($totalEarnings) }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <div class="cpt-section-title">{{ __('Deductions') }}</div>
                <table class="cpt-table">
                    @forelse ($dedRows as $dr)
                    <tr>
                        <td class="cpt-label">{{ $dr['label'] }}</td>
                        <td class="cpt-value">{{ $dr['amount'] !== null ? $_fmtMoney($dr['amount']) : '—' }}</td>
                    </tr>
                    @empty
                    <tr><td class="cpt-label" style="color:#aaa; font-style:italic;">{{ __('No deductions') }}</td><td></td></tr>
                    @endforelse
                    <tr class="cpt-total">
                        <td class="cpt-label">{{ __('Total') }}</td>
                        <td class="cpt-value">{{ $_fmtMoney($totalDeductions) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Net Pay + Stamp --}}
    <div class="cpt-net-row">
        <span class="cpt-net-label">{{ __('Net Pay') }}</span>
        <span class="cpt-net-value">{{ $_fmtMoney($netSalary) }}</span>
    </div>

    <div class="cpt-stamp-row">
        <div style="font-size:10px; color:#888;">
            {{ __('Authorized Digital Stamp') }} &mdash; {{ $companyName }}
        </div>
        {{-- Always render the img so postMessage can update it dynamically --}}
        <img src="{{ $_stampUrl ?? '' }}" alt="Stamp"
             style="{{ $_stampUrl ? 'width:56px;height:56px;border-radius:50%;object-fit:contain;border:2px solid ' . $_themeColor . '44;' : 'display:none;' }}">
        @if (!$_stampUrl)
            <div class="cpt-stamp-placeholder">{{ __('Stamp') }}</div>
        @endif
    </div>

    {{-- Payment Details --}}
    <div class="cpt-section" style="padding-top:4px;">
        <div class="cpt-section-title">{{ __('Payment Details') }}</div>
        <table class="cpt-table">
            <tr><td class="cpt-label">{{ __('Bank Name') }}</td><td class="cpt-value">{{ $employee->bank_name ?? '—' }}</td></tr>
            <tr><td class="cpt-label">{{ __('Account No.') }}</td><td class="cpt-value">{{ $employee->account_number ?? '—' }}</td></tr>
            <tr><td class="cpt-label">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td><td class="cpt-value">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
            <tr><td class="cpt-label">{{ __('Account Holder') }}</td><td class="cpt-value">{{ $employee->account_holder_name ?? '—' }}</td></tr>
            <tr><td class="cpt-label">{{ __('Transaction') }}</td><td class="cpt-value">NEFT</td></tr>
        </table>
    </div>

    {{-- Salary Details --}}
    <div class="cpt-section" style="padding-top:4px;">
        <div class="cpt-two-col">
            <div>
                <div class="cpt-section-title">{{ __('Salary Details') }}</div>
                <table class="cpt-table">
                    <tr><td class="cpt-label">{{ __('Gross Salary') }}</td><td class="cpt-value" style="color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                    @if (!$isHourly)
                    <tr><td class="cpt-label">{{ __('Per Day Rate') }}</td><td class="cpt-value">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                    <tr><td class="cpt-label">{{ __('Per Hour Rate') }}</td><td class="cpt-value">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    @else
                    <tr><td class="cpt-label">{{ __('Hourly Rate') }}</td><td class="cpt-value">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                    @endif
                    <tr><td class="cpt-label">{{ __('Days Paid') }}</td><td class="cpt-value" style="color:#0d6efd;">{{ $daysPaid }} @if($paidLeaveDays>0)<span style="font-size:9px;color:#198754;">(+{{ $paidLeaveDays }} leave)</span>@endif</td></tr>
                    <tr><td class="cpt-label">{{ __('Pay Period') }}</td><td class="cpt-value">{{ $slipLabel }}</td></tr>
                </table>
            </div>
            <div>
                <div class="cpt-section-title">{{ __('Leave Details') }}</div>
                <table class="cpt-table">
                    <tr><th style="text-align:left;font-size:10px;padding:2px 4px;">{{ __('This Month') }}</th><th style="text-align:right;font-size:10px;padding:2px 4px;">{{ __('Overall') }}</th></tr>
                    <tr><td class="cpt-label"><span style="color:#198754;">{{ __('Total') }}: {{ $approvedLeaves }}</span></td><td class="cpt-value">{{ __('Allocated') }}: {{ $totalLeaveAlloc }}</td></tr>
                    <tr><td class="cpt-label">{{ __('Paid') }}: {{ $paidLeaveDays }}</td><td class="cpt-value">{{ __('Used') }}: {{ $usedLeaves }}</td></tr>
                    <tr><td class="cpt-label" style="color:#dc3545;">{{ __('Unpaid') }}: {{ $unpaidLeaveDays }}</td><td class="cpt-value" style="color:#0056b3;">{{ __('Remaining') }}: {{ $remainingLeaves }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Signatures --}}
    <div style="display:flex; justify-content:space-between; padding:12px 16px 4px;">
        <div style="text-align:center; min-width:120px;">
            <div style="border-top:1.5px solid #999; width:100px; margin:10px auto 3px;"></div>
            <div style="font-size:9px; color:#888;">{{ __('Employee Signature') }}</div>
        </div>
        <div style="text-align:center; min-width:120px;">
            <div style="border-top:1.5px solid #999; width:100px; margin:10px auto 3px;"></div>
            <div style="font-size:9px; color:#888;">{{ __('Authorized Signatory') }}</div>
        </div>
    </div>

    {{-- Footer --}}
    <div style="background:{{ $_themeColor }}; color:#fff; text-align:center; padding:6px 16px; font-size:9px; line-height:1.6; margin-top:6px;">
        @if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }} &nbsp;|&nbsp; @endif
        @if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }} &nbsp;|&nbsp; @endif
        @if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }} @endif
    </div>

</div>

<script>
/**
 * Listen for stamp data URL from the parent settings page (postMessage).
 * Updates the stamp image src dynamically for the live preview.
 */
window.addEventListener('message', function(e) {
    if (e.data && e.data.type === 'payslip-stamp' && e.data.dataUrl) {
        var img = document.querySelector('.cpt-stamp-row img');
        if (img) { img.src = e.data.dataUrl; img.style.display = ''; img.style.width = '56px'; img.style.height = '56px'; img.style.borderRadius = '50%'; img.style.objectFit = 'contain'; img.style.border = '2px solid {{ $_themeColor }}44'; }
        var ph = document.querySelector('.cpt-stamp-placeholder');
        if (ph) ph.style.display = 'none';
    }
});
</script>
