@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
    // Employee and Payslip objects are also passed to the view.
    // Variables available: slipYear, slipMonth, slipLabel, slipShort, payDate,
    // logoUrl, downloadUrl, _themeColor, _bgRgba, _bgLight, _bgMed,
    // _currSymbol, _currPos, _fmtMoney, _storedSalary, _basicSalary, _salary,
    // perDaySalary, perHourSalary, officeDays, presentDays, presentDaysFmt,
    // approvedLeaves, absentDays, absentDaysFmt, extraDays, extraDaysFmt,
    // totalWorkHours, avgHrsPerDay, totalLeaveAlloc, remainingLeaves, usedLeaves,
    // paidLeaveDays, unpaidLeaveDays, unpaidLeaveDeduction, daysPaid, isHourly,
    // hoursPerDay, payslipTypeName, isPaid, earRows, dedRows,
    // totalEarnings, totalDeductions, netSalary, companyName, companyAddr,
    // companyCity, companyState, companyZip, companyPhone, companyEmail,
    // companyWeb, _stampUrl, _isUkRequest, taxCode, niNumber, niTableLetter,
    // paymentMethod, worksNo, sortCode,
    // ytdTaxableGross, ytdIncomeTax, ytdEmployeeNI, ytdEmployerNI,
    // ytdStatutoryPay, ytdEmployeePension, ytdEmployerPension,
    // ytdLabelIncomeTax, ytdLabelEmployeeNI, ytdLabelEmployerNI,
    // ytdLabelStatutoryPay, ytdLabelEmployeePension, ytdLabelEmployerPension,
    // ytdPensionRows, previewMode
@endphp

<style>
/* ══ UK Payslip — fluid, fits any modal width ══ */
.ukp-wrap {
    background:{{ $_bgRgba }};
    padding:12px 14px;
    font-family:'Segoe UI',Arial,sans-serif;
    font-size:12px;
    color:#111;
    line-height:1.55;
    width:100%;
}
.ukp-wrap * { box-sizing:border-box; margin:0; padding:0; }

/* action bar */
.ukp-bar { display:flex; justify-content:flex-end; gap:8px; margin-bottom:10px; }

/* standalone employee name/dept box */
.ukp-emp-box {
    background:#fff;
    border:2px solid {{ $_themeColor }};
    padding:9px 12px;
    margin-bottom:4px;
    width:100%;
}
.ukp-emp-name { font-weight:700; font-size:13px; margin-bottom:2px; }
.ukp-emp-dept { font-size:11.5px; color:#444; }

/* outer big box */
.ukp-outer {
    background:#fff;
    border:2px solid {{ $_themeColor }};
    width:100%;
    border-collapse:collapse;
}

/* company name header */
.ukp-company-row {
    text-align:center;
    font-weight:700;
    font-size:13px;
    padding:9px 12px;
    border-bottom:2px solid {{ $_themeColor }};
    width:100%;
}

/* two-column layout using a real HTML table — 100% wide, fixed layout */
.ukp-body {
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}
.ukp-body td { vertical-align:top; }

/* left column: 47% + right border as divider */
.ukp-left {
    width:47%;
    border-right:2px solid {{ $_themeColor }};
    padding:10px 12px;
}

/* right column: remaining */
.ukp-right {
    padding:10px 12px;
}

/* divider between employee-details and YTD */
.ukp-hr {
    border:none;
    border-top:2px solid {{ $_themeColor }};
    margin:8px 0;
    display:block;
    height:0;
}

/* employee name heading inside left col */
.kv-name { font-weight:700; font-size:12.5px; margin-bottom:6px; display:block; }

/* key-value table: fixed layout, label 57% / value 43% */
.kv-tbl { width:100%; border-collapse:collapse; table-layout:fixed; font-size:11.5px; }
.kv-tbl td { padding:1.5px 0; vertical-align:top; }
.kv-tbl .k { width:57%; color:#333; word-wrap:break-word; overflow-wrap:break-word; }
.kv-tbl .v { width:43%; text-align:right; color:#111; word-wrap:break-word; overflow-wrap:break-word; }

/* section title */
.sec-title { font-weight:700; font-size:12px; text-align:center; margin-bottom:6px; display:block; }

/* income/deduction list: fixed layout, desc 65% / amt 35% */
.ls-tbl { width:100%; border-collapse:collapse; table-layout:fixed; font-size:11.5px; }
.ls-tbl td { padding:1.5px 0; vertical-align:top; }
.ls-tbl .ld { width:65%; color:#333; word-wrap:break-word; overflow-wrap:break-word; }
.ls-tbl .la { width:35%; text-align:right; color:#111; word-wrap:break-word; overflow-wrap:break-word; }

/* total row */
.ls-tot { width:100%; border-collapse:collapse; table-layout:fixed; font-size:12px; margin-top:4px; }
.ls-tot td { padding:3px 0; font-weight:700; border-top:2px solid {{ $_themeColor }}; vertical-align:top; }
.ls-tot .la { width:35%; text-align:right; word-wrap:break-word; overflow-wrap:break-word; }

/* gap between income and deductions blocks */
.sec-gap { height:12px; display:block; }

/* net pay */
.net-tbl { width:100%; border-collapse:collapse; table-layout:fixed; font-size:12.5px; margin-top:8px; }
.net-tbl td { padding:4px 0; font-weight:700; border-top:2px solid {{ $_themeColor }}; vertical-align:middle; }
.net-tbl .nl { font-size:13.5px; }
.net-tbl .nv { width:40%; text-align:right; font-size:14px; word-wrap:break-word; overflow-wrap:break-word; }

/* badges */
.badge-paid   { background:#198754; color:#fff; padding:1px 7px; border-radius:10px; font-size:10.5px; }
.badge-unpaid { background:#dc3545; color:#fff; padding:1px 7px; border-radius:10px; font-size:10.5px; }

/* stamp */
.stamp-wrap  { text-align:center; padding:10px 0 4px; }
.stamp-ring  { display:inline-block; width:72px; height:72px; border-radius:50%; border:2.5px solid {{ $_themeColor }}; color:{{ $_themeColor }}; font-size:7px; font-weight:700; text-align:center; line-height:1.4; padding:14px 4px; }
.stamp-img   { display:inline-block; width:74px; height:74px; border-radius:50%; object-fit:contain; border:2.5px solid {{ $_themeColor }}44; }
.stamp-label { font-size:10px; color:#888; margin-top:4px; }

/* footer */
.ukp-footer     { background:{{ $_themeColor }}; margin-top:6px; }
.ukp-footer-row { width:100%; border-collapse:collapse; }
.ukp-footer-row td { border:none; padding:5px 12px; color:#fff; font-size:10.5px; }
</style>

<div class="ukp-wrap">

    {{-- Action bar --}}
    @if(!($previewMode ?? false))
    <div class="ukp-bar">
        <a href="{{ $downloadUrl }}" class="btn btn-sm btn-primary p-1">
            <i class="fa fa-download me-1"></i>{{ __('Download PDF') }}
        </a>
        @if(\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
        <button type="button" class="btn btn-sm btn-warning p-1"
                onclick="payslipEmailSendUk({{ $employee->id }},'{{ $payslip->salary_month }}')">
            <i class="fa fa-paper-plane me-1"></i>{{ __('Send Email') }}
        </button>
        @endif
    </div>
    @endif

    {{-- ═══ Standalone: Employee name + Dept ═══ --}}
    @if($_showEmployeeDetails)
    <div class="ukp-emp-box">
        @if($_showName)
        <div class="ukp-emp-name">{{ $employee->name }}</div>
        @endif
        @if($_showDepartment)
        <div class="ukp-emp-dept">{{ __('Department') }}: {{ optional($employee->department)->name ?? '' }}</div>
        @endif
    </div>
    @endif

    {{-- ═══ Outer box ═══ --}}
    <table class="ukp-outer" cellpadding="0" cellspacing="0">
        <tbody>

            {{-- Company name row --}}
            <tr>
                <td class="ukp-company-row">{{ strtoupper($companyName) }}</td>
            </tr>

            {{-- Two-column row --}}
            <tr>
                <td style="padding:0;">
                    <table class="ukp-body" cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr>

                                {{-- LEFT: employee details + YTD --}}
                                @if($_showEmployeeDetails)
                                <td class="ukp-left">

                                    @if($_showName)
                                    <span class="kv-name">{{ $employee->name }}</span>
                                    @endif
                                    <table class="kv-tbl">
                                        @if($_showDesignation)
                                        <tr><td class="k">{{ __('Designation') }}</td><td class="v">{{ optional($employee->designation)->name ?? '—' }}</td></tr>
                                        @endif
                                        @if($_showDepartment)
                                        <tr><td class="k">{{ __('Department') }}</td><td class="v">{{ optional($employee->department)->name ?? '—' }}</td></tr>
                                        @endif
                                        @if($_showPayPeriod)
                                        <tr><td class="k">{{ __('Pay Period') }}</td><td class="v">{{ $slipShort }}</td></tr>
                                        @endif
                                        <tr><td class="k">{{ __('Pay Date') }}</td><td class="v">{{ $payDate }}</td></tr>
                                        <tr><td class="k">{{ __('Pay Type') }}</td><td class="v">{{ $payslipTypeName }}</td></tr>
                                        @if($_isUkRequest)
                                        <tr><td class="k">{{ __('Payment Method') }}</td><td class="v">{{ $paymentMethod }}</td></tr>
                                        @endif
                                        <tr><td class="k">{{ __('Works No') }}</td><td class="v">{{ $worksNo }}</td></tr>
                                        @if($_isUkRequest && $_showTaxCode)
                                        <tr><td class="k">{{ __('Tax Code') }}</td><td class="v">{{ $taxCode }}</td></tr>
                                        @endif
                                        @if($_isUkRequest && $_showNiNumber)
                                        <tr><td class="k">{{ __('NI Number') }}</td><td class="v">{{ $niNumber }}</td></tr>
                                        @endif
                                        @if($_isUkRequest)
                                        <tr><td class="k">{{ __('NI Table Letter') }}</td><td class="v">{{ $niTableLetter }}</td></tr>
                                        @endif
                                        @if($_showPanNo)
                                        <tr><td class="k">{{ __('PAN / Tax ID') }}</td><td class="v">{{ $employee->tax_payer_id ?? '—' }}</td></tr>
                                        @endif
                                        @if($_showDateOfJoining)
                                        <tr><td class="k">{{ __('Date of Joining') }}</td><td class="v">{{ $employee->company_doj ?? '—' }}</td></tr>
                                        @endif
                                        <tr>
                                            <td class="k">{{ __('Pay Status') }}</td>
                                            <td class="v">
                                                @if($isPaid)<span class="badge-paid">{{ __('Paid') }}</span>
                                                @else<span class="badge-unpaid">{{ __('Unpaid') }}</span>@endif
                                            </td>
                                        </tr>
                                    </table>

                                    @if($_isUkRequest)
                                    <span class="ukp-hr"></span>

                                    <span class="sec-title">{{ __('Year To Date') }}</span>
                                    <table class="kv-tbl">
                                        <tr><td class="k">{{ __('Taxable Gross') }}</td><td class="v">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                                        @if($ytdIncomeTax > 0)
                                        <tr><td class="k">{{ $ytdLabelIncomeTax }}</td><td class="v">{{ $_fmtMoney($ytdIncomeTax) }}</td></tr>
                                        @endif
                                        @if($ytdEmployeeNI > 0)
                                        <tr><td class="k">{{ $ytdLabelEmployeeNI }}</td><td class="v">{{ $_fmtMoney($ytdEmployeeNI) }}</td></tr>
                                        @endif
                                        @if($ytdEmployerNI > 0)
                                        <tr><td class="k">{{ $ytdLabelEmployerNI }}</td><td class="v">{{ $_fmtMoney($ytdEmployerNI) }}</td></tr>
                                        @endif
                                        @if($ytdStatutoryPay > 0)
                                        <tr><td class="k">{{ $ytdLabelStatutoryPay }}</td><td class="v">{{ $_fmtMoney($ytdStatutoryPay) }}</td></tr>
                                        @endif
                                        @php $ytdPensionRows = $payslipDetail['ytd_pension_rows'] ?? []; @endphp
                                        @foreach($ytdPensionRows as $_ytdPen)
                                        @if(($_ytdPen['amount'] ?? 0) > 0)
                                        <tr><td class="k">{{ $_ytdPen['label'] ?? __('Pension') }}</td><td class="v">{{ $_fmtMoney($_ytdPen['amount']) }}</td></tr>
                                        @endif
                                        @endforeach
                                    </table>
                                    @endif

                                </td>{{-- /left --}}
                                @else
                                <td style="display:none;"></td>
                                @endif

                                {{-- RIGHT: income + deductions + net pay --}}
                                <td class="ukp-right">

                                    <span class="sec-title">{{ __('Income') }}</span>
                                    <table class="ls-tbl">
                                        @foreach($earRows as $er)
                                        @if(($er['label'] ?? '') !== '')
                                        <tr>
                                            <td class="ld">{{ $er['label'] }}</td>
                                            <td class="la">{{ $er['amount'] !== null ? $_fmtMoney($er['amount']) : '' }}</td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </table>
                                    <table class="ls-tot">
                                        <tr><td>{{ __('Total Income') }}</td><td class="la">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                                    </table>

                                    <span class="sec-gap"></span>

                                    <span class="sec-title">{{ __('Deductions') }}</span>
                                    <table class="ls-tbl">
                                        @if(count(array_filter($dedRows, fn($r) => ($r['label'] ?? '') !== '')) > 0)
                                            @foreach($dedRows as $dr)
                                            @if(($dr['label'] ?? '') !== '')
                                            <tr>
                                                <td class="ld">{{ $dr['label'] }}</td>
                                                <td class="la">{{ $dr['amount'] !== null ? $_fmtMoney($dr['amount']) : '—' }}</td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        @else
                                            <tr><td colspan="2" style="color:#999;font-style:italic;">{{ __('No deductions') }}</td></tr>
                                        @endif
                                    </table>
                                    <table class="ls-tot">
                                        <tr><td>{{ __('Total Deductions') }}</td><td class="la">{{ $_fmtMoney($totalDeductions) }}</td></tr>
                                    </table>

                                    <span class="sec-gap"></span>

                                    <table class="net-tbl">
                                        <tr>
                                            <td class="nl">{{ __('Net Pay') }}</td>
                                            <td class="nv">{{ $_fmtMoney($netSalary) }}</td>
                                        </tr>
                                    </table>

                                    {{-- ── Stamp (below Net Pay) ── --}}
                                    <div class="stamp-wrap">
                                        {{-- Always render the img so postMessage can update it dynamically --}}
                                        <img src="{{ $_stampUrl ?? '' }}" alt="Stamp" class="stamp-img"
                                             style="{{ $_stampUrl ? '' : 'display:none;' }}">
                                        @if(!$_stampUrl)
                                            <div class="stamp-ring">
                                                {{ $companyName }}<br>
                                                <span style="font-size:6px; letter-spacing:0.8px;">{{ strtoupper($slipShort) }}</span>
                                            </div>
                                        @endif
                                        <div class="stamp-label">{{ __('Digital Authorization') }}</div>
                                    </div>

                                </td>{{-- /right --}}

                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

        </tbody>
    </table>{{-- /.ukp-outer --}}

    {{-- ═══ Additional Sections (Payment, Attendance, Salary) ═══ --}}
    <table class="ukp-outer" cellpadding="0" cellspacing="0" style="margin-top:6px;">
        <tbody>
            <tr>
                <td style="padding:10px 12px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            {{-- Payment Details --}}
                            @if($_showPaymentDetails)
                            <td style="width:50%; vertical-align:top; padding-right:10px;">
                                <span class="sec-title" style="font-size:11px; text-align:left; margin-bottom:4px;">{{ __('Payment Details') }}</span>
                                <table class="kv-tbl" style="font-size:10.5px;">
                                    @if($_showBankName)
                                    <tr><td class="k">{{ __('Bank Name') }}</td><td class="v">{{ $employee->bank_name ?? '—' }}</td></tr>
                                    @endif
                                    @if($_showAccountNo)
                                    <tr><td class="k">{{ __('Account No.') }}</td><td class="v">{{ $employee->account_number ?? '—' }}</td></tr>
                                    @endif
                                    @if($_showBankCode)
                                    <tr><td class="k">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td><td class="v">{{ $employee->bank_identifier_code ?? '—' }}</td></tr>
                                    @endif
                                    @if($_showAccountHolder)
                                    <tr><td class="k">{{ __('Account Holder') }}</td><td class="v">{{ $employee->account_holder_name ?? '—' }}</td></tr>
                                    @endif
                                    @if($_showTransactionMode)
                                    <tr><td class="k">{{ __('Transaction Mode') }}</td><td class="v">NEFT</td></tr>
                                    @endif
                                </table>
                            </td>
                            @else
                            <td style="width:50%; vertical-align:top; padding-right:10px;"></td>
                            @endif
                            {{-- Attendance Summary --}}
                            <td style="width:50%; vertical-align:top; padding-left:10px;">
                                <span class="sec-title" style="font-size:11px; text-align:left; margin-bottom:4px;">{{ __('Attendance Summary') }}</span>
                                <table class="kv-tbl" style="font-size:10.5px;">
                                    <tr><td class="k">{{ __('Working Days') }}</td><td class="v">{{ $officeDays }}</td></tr>
                                    <tr><td class="k">{{ __('Present Days') }}</td><td class="v" style="color:#198754;">{{ $presentDaysFmt }}</td></tr>
                                    <tr><td class="k">{{ __('Paid Leave') }}</td><td class="v" style="color:#0d6efd;">{{ $paidLeaveDays }}</td></tr>
                                    <tr><td class="k">{{ __('Unpaid Leave') }}</td><td class="v" style="color:#e07900;">{{ $unpaidLeaveDays }}</td></tr>
                                    <tr><td class="k">{{ __('Absent Days') }}</td><td class="v" style="color:#dc3545;">{{ $absentDaysFmt }}</td></tr>
                                    @if ($extraDays > 0)
                                    <tr><td class="k" style="color:#856404;">{{ __('Extra / OT Days') }}</td><td class="v" style="color:#856404;">+{{ $extraDaysFmt }}</td></tr>
                                    @endif
                                    <tr><td class="k">{{ __('Days Paid') }}</td><td class="v" style="font-weight:700;">{{ $daysPaid }}</td></tr>
                                    <tr><td class="k">{{ __('Total Hours') }}</td><td class="v">{{ $totalWorkHours }} hrs</td></tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <span class="ukp-hr" style="margin:10px 0;"></span>

                    {{-- Salary Details --}}
                    <span class="sec-title" style="font-size:11px; text-align:left; margin-bottom:4px;">{{ __('Salary Details') }}</span>
                    <table class="kv-tbl" style="font-size:10.5px;">
                        <tr><td class="k">{{ __('Gross Salary') }}</td><td class="v" style="font-weight:700; color:{{ $_themeColor }};">{{ $_fmtMoney($_storedSalary) }}</td></tr>
                        @if (!$isHourly)
                        <tr><td class="k">{{ __('Per Day Rate') }}</td><td class="v">{{ $_fmtMoney($perDaySalary) }}</td></tr>
                        <tr><td class="k">{{ __('Per Hour Rate') }}</td><td class="v">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        @else
                        <tr><td class="k">{{ __('Hourly Rate') }}</td><td class="v">{{ $_fmtMoney($perHourSalary) }}</td></tr>
                        @endif
                    </table>

                    {{-- Leave Details (monthly vs overall) --}}
                    @if (!$isHourly)
                    <span class="ukp-hr" style="margin:8px 0;"></span>
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="width:50%; vertical-align:top; padding-right:10px;">
                                <span class="sec-title" style="font-size:10px; text-align:left; margin-bottom:3px;">{{ __('This Month') }}</span>
                                <table class="kv-tbl" style="font-size:9.5px;">
                                    <tr><td class="k">{{ __('Total Leave') }}</td><td class="v"><strong style="color:#198754;">{{ $approvedLeaves }}</strong></td></tr>
                                    <tr><td class="k">{{ __('Paid Leave') }}</td><td class="v" style="color:#0d6efd;">{{ $paidLeaveDays }}</td></tr>
                                    <tr><td class="k">{{ __('Unpaid Leave') }}</td><td class="v" style="color:#dc3545;">{{ $unpaidLeaveDays }}</td></tr>
                                </table>
                            </td>
                            <td style="width:50%; vertical-align:top; padding-left:10px;">
                                <span class="sec-title" style="font-size:10px; text-align:left; margin-bottom:3px;">{{ __('Overall') }}</span>
                                <table class="kv-tbl" style="font-size:9.5px;">
                                    <tr><td class="k">{{ __('Allocated') }}</td><td class="v">{{ $totalLeaveAlloc }}</td></tr>
                                    <tr><td class="k">{{ __('Used') }}</td><td class="v"><strong>{{ $usedLeaves }}</strong></td></tr>
                                    <tr><td class="k">{{ __('Remaining') }}</td><td class="v" style="color:#0056b3; font-weight:700;">{{ $remainingLeaves }}</td></tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    {{-- ═══ Signatures ═══ --}}
    @if($_showSignatures)
    <div style="display:flex; justify-content:space-between; padding:14px 30px 6px; margin-top:6px;">
        <div style="text-align:center; min-width:140px;">
            <div style="border-top:1.5px solid #999; width:120px; margin:12px auto 4px;"></div>
            <div style="font-size:10px; color:#888;">{{ __('Employee Signature') }}</div>
        </div>
        <div style="text-align:center; min-width:140px;">
            <div style="border-top:1.5px solid #999; width:120px; margin:12px auto 4px;"></div>
            <div style="font-size:10px; color:#888;">{{ __('Authorized Signatory') }}</div>
        </div>
    </div>
    @endif

    {{-- ═══ Footer ═══ --}}
    @if($_showFooter)
    <div class="ukp-footer">
        <table class="ukp-footer-row">
            <tr>
                <td style="width:33%;">@if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }}@endif</td>
                <td style="width:34%; text-align:center;">@if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }}@endif</td>
                <td style="width:33%; text-align:right;">@if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }}@endif</td>
            </tr>
        </table>
    </div>
    @endif

</div>{{-- /.ukp-wrap --}}

<script>
/**
 * Listen for stamp data URL from the parent settings page (postMessage).
 * Updates the stamp image src dynamically for the live preview.
 */
window.addEventListener('message', function(e) {
    if (e.data && e.data.type === 'payslip-stamp' && e.data.dataUrl) {
        var img = document.querySelector('.stamp-img');
        if (img) { img.src = e.data.dataUrl; img.style.display = ''; }
        var ring = document.querySelector('.stamp-ring');
        if (ring) ring.style.display = 'none';
    }
});

window.payslipEmailSendUk = function (eid, month) {
    $.ajax({
        url:  '{{ url("payslip/send") }}/' + eid + '/' + month,
        type: 'GET',
        data: { _token: '{{ csrf_token() }}' },
        success: function (r) { show_toastr('Success', r.message, 'success'); },
        error:   function (r) { show_toastr('Error',   r.message, 'error');   }
    });
};
</script>
