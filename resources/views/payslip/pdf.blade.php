@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
    // Employee and Payslip objects are also passed to the view.
    // Variables available: slipYear, slipMonth, slipLabel, logoUrl, downloadUrl,
    // _themeColor, _bgRgba, _bgLight, _bgMed, _currSymbol, _currPos, _fmtMoney,
    // _storedSalary, _basicSalary, _salary, perDaySalary, perHourSalary,
    // officeDays, presentDays, presentDaysFmt, approvedLeaves, absentDays,
    // absentDaysFmt, extraDays, extraDaysFmt, totalWorkHours, avgHrsPerDay,
    // totalLeaveAlloc, remainingLeaves, usedLeaves, paidLeaveDays,
    // unpaidLeaveDays, unpaidLeaveDeduction, daysPaid, isHourly, hoursPerDay,
    // payslipTypeName, isPaid, earRows, earRowsPadded, dedRows, dedRowsPadded,
    // totalEarnings, totalDeductions, netSalary, companyName, companyAddr,
    // companyCity, companyState, companyZip, companyPhone, companyEmail,
    // companyWeb, _stampUrl, _isUkRequest, taxCode, niNumber, niTableLetter,
    // paymentMethod, worksNo, sortCode, previewMode
    // _showEmployeeDetails, _showPaymentDetails, _showSignatures, _showFooter
    // _showName, _showDesignation, _showEmployeeId, _showDepartment,
    // _showPanNo, _showDateOfJoining, _showNiNumber, _showTaxCode,
    // _showBankName, _showAccountNo, _showBankCode, _showAccountHolder,
    // _showTransactionMode, _showPayPeriod
    // and all YTD variables.
@endphp

<style>
    .ps-modal {
        background: #fff;
        padding: 10px 14px;
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 12.5px;
        color: #1c1c1c;
    }

    .ps-modal * {
        box-sizing: border-box;
    }

    .ps-bar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
    }

    .ps-card {
        border: 2px solid {{ $_themeColor }};
        border-radius: 4px;
        overflow: hidden;
        max-width: 800px;
        margin: 0 auto;
    }

    .ps-hdr {
        background: {{ $_themeColor }}12;
        border-bottom: 2px solid {{ $_themeColor }};
    }

    .ps-hdr table {
        width: 100%;
        border-collapse: collapse;
    }

    .ps-hdr td {
        border: none;
        padding: 12px 16px;
        vertical-align: middle;
    }

    .ps-hdr-right {
        text-align: right;
        font-size: 11px;
        color: #555;
        line-height: 1.7;
    }

    .ps-hdr img {
        height: 46px;
        object-fit: contain;
    }

    .ps-title {
        background: {{ $_themeColor }};
        color: #fff;
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 2px;
        padding: 10px 0;
    }

    .ps-band {
        background: {{ $_themeColor }};
        color: #fff;
        font-weight: 700;
        font-size: 12px;
        text-align: center;
        padding: 5px 10px;
    }

    .ps-t {
        width: 100%;
        border-collapse: collapse;
    }

    .ps-t td,
    .ps-t th {
        border: 1px solid {{ $_themeColor }}55;
        padding: 6px 10px;
        vertical-align: middle;
        font-size: 12px;
    }

    .ps-t th {
        background: {{ $_themeColor }}22;
        color: {{ $_themeColor }};
        font-weight: 700;
        text-align: center;
        font-size: 11.5px;
    }

    .ps-t tbody tr:nth-child(even) td {
        background: {{ $_themeColor }}08;
    }

    .lbl {
        font-weight: 600;
        color: #333;
        white-space: nowrap;
        width: 48%;
    }

    .val {
        color: #444;
    }

    .ra {
        text-align: right;
        padding-right: 12px !important;
    }

    .v-green {
        color: #198754;
        font-weight: 700;
    }

    .v-orange {
        color: #e07900;
        font-weight: 700;
    }

    .v-red {
        color: #dc3545;
        font-weight: 700;
    }

    .v-blue {
        color: #0d6efd;
        font-weight: 700;
    }

    .v-teal {
        color: {{ $_themeColor }};
        font-weight: 700;
    }

    .ps-total td {
        background: {{ $_themeColor }}25 !important;
        font-weight: 700;
        color: {{ $_themeColor }};
        border-top: 2px solid {{ $_themeColor }} !important;
    }

    .ps-net-row {
        border-top: 1px solid {{ $_themeColor }}55;
    }

    .ps-stamp-td {
        width: 50%;
        text-align: center;
        padding: 18px 10px;
        border-right: 1px solid {{ $_themeColor }}55;
        vertical-align: middle;
    }

    .ps-stamp-ring {
        display: inline-block;
        width: 82px;
        height: 82px;
        border-radius: 50%;
        border: 3px solid {{ $_themeColor }};
        color: {{ $_themeColor }};
        font-size: 8.5px;
        font-weight: 700;
        text-align: center;
        line-height: 1.4;
        padding: 14px 6px;
    }

    .ps-stamp-img {
        display: inline-block;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: contain;
        border: 3px solid {{ $_themeColor }}44;
    }

    .ps-net-td {
        width: 50%;
        vertical-align: middle;
        padding: 0;
    }

    .ps-net-inner {
        width: 100%;
        border-collapse: collapse;
        height: 100%;
    }

    .ps-net-lbl {
        background: {{ $_themeColor }}25;
        font-weight: 700;
        font-size: 13px;
        color: {{ $_themeColor }};
        text-align: center;
        padding: 16px 14px;
        border: 1px solid {{ $_themeColor }}55;
        width: 55%;
    }

    .ps-net-val {
        font-weight: 700;
        font-size: 20px;
        color: {{ $_themeColor }};
        text-align: center;
        padding: 16px 14px;
        border: 1px solid {{ $_themeColor }}55;
    }

    .badge-paid {
        background: #198754;
        color: #fff;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-unpaid {
        background: #dc3545;
        color: #fff;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
    }

    .ps-sig {
        border-top: 1px solid {{ $_themeColor }}55;
        padding: 14px 20px 8px;
    }

    .ps-sig table {
        width: 100%;
        border-collapse: collapse;
    }

    .ps-sig td {
        border: none;
        text-align: center;
        padding: 0 10px;
        vertical-align: bottom;
    }

    .ps-sig-line {
        border-top: 1.5px solid #aaa;
        width: 140px;
        margin: 18px auto 5px;
    }

    .ps-sig-label {
        font-size: 10.5px;
        color: #666;
    }

    .ps-footer {
        background: {{ $_themeColor }};
        color: #fff;
    }

    .ps-footer table {
        width: 100%;
        border-collapse: collapse;
    }

    .ps-footer td {
        border: none;
        padding: 7px 14px;
        color: #fff;
        font-size: 11px;
    }
</style>

<div class="ps-modal">

    @if(!($previewMode ?? false))
    <div class="ps-bar">
        <a href="{{ $downloadUrl }}" class="btn btn-sm btn-primary">
            <i class="fa fa-download me-1"></i>{{ __('Download PDF') }}
        </a>
        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr' || \Auth::user()->type == 'HR')
            <button type="button" class="btn btn-sm btn-warning"
                onclick="payslipEmailSend({{ $employee->id }},'{{ $payslip->salary_month }}')">
                <i class="fa fa-paper-plane me-1"></i>{{ __('Send Email') }}
            </button>
        @endif
    </div>
    @endif

    <div class="ps-card">

        {{-- Header --}}
        <div class="ps-hdr">
            <table>
                <tr>
                    <td style="width:40%;"><img src="{{ $logoUrl }}" alt="logo"></td>
                    <td class="ps-hdr-right" style="width:60%;">
                        <strong style="font-size:13px; color:{{ $_themeColor }};">{{ $companyName }}</strong><br>
                        {{ $companyAddr }}{{ $companyCity ? ', ' . $companyCity : '' }}<br>
                        {{ $companyState }}{{ $companyZip ? ' - ' . $companyZip : '' }}
                        @if ($companyPhone)
                            <br>{{ $companyPhone }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="ps-title">PAY SLIP &mdash; {{ strtoupper($slipLabel) }}</div>

        {{-- A: Employee | Payment --}}
        @if($_showEmployeeDetails || $_showPaymentDetails)
        <table class="ps-t" style="border:none;">
            <tr>
                @if($_showEmployeeDetails)
                <td style="width:{{ $_showPaymentDetails ? '50%' : '100%' }}; padding:0; border-left:1px solid {{ $_themeColor }}55; vertical-align:top;">
                    <div class="ps-band">{{ __('Employee Details') }}</div>
                    <table class="ps-t">
                        @if($_showName)
                        <tr>
                            <td class="lbl">{{ __('Name') }}</td>
                            <td class="val">{{ $employee->name }}</td>
                        </tr>
                        @endif
                        @if($_showDesignation)
                        <tr>
                            <td class="lbl">{{ __('Designation') }}</td>
                            <td class="val">{{ optional($employee->designation)->name ?? '—' }}</td>
                        </tr>
                        @endif
                        @if($_showEmployeeId)
                        <tr>
                            <td class="lbl">{{ __('Employee ID') }}</td>
                            <td class="val">{{ $employee->employee_id ?? '—' }}</td>
                        </tr>
                        @endif
                        @if($_showDepartment)
                        <tr>
                            <td class="lbl">{{ __('Department') }}</td>
                            <td class="val">{{ optional($employee->department)->name ?? '—' }}</td>
                        </tr>
                        @endif
                        @if($_showPanNo)
                        <tr>
                            <td class="lbl">{{ __('PAN No.') }}</td>
                            <td class="val">{{ $employee->tax_payer_id ?? '—' }}</td>
                        </tr>
                        @endif
                        @if($_isUkRequest && $_showNiNumber)
                        <tr>
                            <td class="lbl">{{ __('NI Number') }}</td>
                            <td class="val">{{ $niNumber }}</td>
                        </tr>
                        @endif
                        @if($_isUkRequest && $_showTaxCode)
                        <tr>
                            <td class="lbl">{{ __('Tax Code') }}</td>
                            <td class="val">{{ $taxCode }}</td>
                        </tr>
                        @endif
                        @if($_showDateOfJoining)
                        <tr>
                            <td class="lbl">{{ __('Date of Joining') }}
                            <td class="val">{{ $employee->company_doj ?? '—' }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
                @endif
                @if($_showPaymentDetails)
                <td style="width:{{ $_showEmployeeDetails ? '50%' : '100%' }}; padding:0; border-left:1px solid {{ $_themeColor }}55; vertical-align:top;">
                    <div class="ps-band">{{ __('Payment Details') }}</div>
                    <table class="ps-t">
                        @if($_showBankName)
                        <tr>
                            <td class="lbl">{{ __('Bank Name') }}</td>
                            <td class="val">{{ $employee->bank_name ?? '—' }}</td>
                        </tr>
                        @endif
                        @if($_showAccountNo)
                        <tr>
                            <td class="lbl">{{ __('Account No.') }}</td>
                            <td class="val">{{ $employee->account_number ?? '—' }}</td>
                        </tr>
                        @endif
                        @if($_showBankCode)
                        <tr>
                            <td class="lbl">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td>
                            <td class="val">{{ $employee->bank_identifier_code ?? '—' }}</td>
                        </tr>
                        @endif
                        @if($_showAccountHolder)
                        <tr>
                            <td class="lbl">{{ __('Account Holder') }}</td>
                            <td class="val">{{ $employee->account_holder_name ?? '—' }}</td>
                        </tr>
                        @endif
                        @if($_showTransactionMode)
                        <tr>
                            <td class="lbl">{{ __('Transaction Mode') }}</td>
                            <td class="val">NEFT</td>
                        </tr>
                        @endif
                        @if($_showPayPeriod)
                        <tr>
                            <td class="lbl">{{ __('Pay Period') }}</td>
                            <td class="val">{{ $slipLabel }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
                @endif
            </tr>
        </table>
        @endif

        {{-- B: Leave | Days & Work | Salary --}}
        <table class="ps-t" style="border:none; border-top:1px solid {{ $_themeColor }}55;">
            <tr>
                @if (!$isHourly)
                    {{-- ── MONTHLY: show Leave Details + Days & Work ── --}}
                    <td style="width:34%; padding:0; border:none; vertical-align:top;">
                        <div class="ps-band">{{ __('Leave Details') }}</div>
                        <table class="ps-t">
                            <tr>
                                <th style="width:50%;">This Month</th>
                                <th>Overall</th>
                            </tr>
                            <tr>
                                <td>Total: <strong class="cg">{{ $approvedLeaves }}</strong></td>
                                <td>Allocated: <strong>{{ $totalLeaveAlloc }}</strong></td>
                            </tr>
                            <tr>
                                <td><span class="paid">Paid: {{ $paidLeaveDays }}</span></td>
                                <td>Used: <strong>{{ $usedLeaves }}</strong></td>
                            </tr>
                            <tr>
                                <td><span class="unpaid">Unpaid: {{ $unpaidLeaveDays }}</span></td>
                                <td>Remaining: <strong class="cb">{{ $remainingLeaves }}</strong></td>
                            </tr>
                        </table>
                    </td>
                    <td
                        style="width:28%; padding:0; border-left:1px solid {{ $_themeColor }}55; border-right:1px solid {{ $_themeColor }}55; vertical-align:top;">
                        <div class="ps-band">{{ __('Days & Work Details') }}</div>
                        <table class="ps-t">
                            <tr>
                                <td class="lbl" style="width:60%;">{{ $workingDaysLabel ?? __('Working Days') }}</td>
                                <td class="ra val v-teal">{{ $officeDays }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Present Days') }}</td>
                                <td class="ra val v-green">{{ $presentDaysFmt }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Leave Days') }}</td>
                                <td class="ra val v-orange">{{ $approvedLeaves }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Absent Days') }}</td>
                                <td class="ra val v-red">{{ $absentDaysFmt }}</td>
                            </tr>
                            @if (!empty($isMonthWise) || !empty($dayOffDays))
                            <tr>
                                <td class="lbl">{{ __('Day Off') }}</td>
                                <td class="ra val" style="color:#6c757d;">{{ $dayOffDaysFmt ?? 0 }}</td>
                            </tr>
                            @endif
                            @if ($extraDays > 0)
                                <tr style="background:#fff8e1;">
                                    <td class="lbl" style="color:#856404;">{{ __('Extra / OT Days') }}</td>
                                    <td class="ra" style="color:#856404; font-weight:700;">+{{ $extraDaysFmt }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="lbl">{{ __('Total Hrs Worked') }}</td>
                                <td class="ra val">{{ $totalWorkHours }} hrs</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Avg Hrs / Day') }}</td>
                                <td class="ra val">{{ $avgHrsPerDay }} hrs</td>
                            </tr>
                        </table>
                    </td>
                @endif

                {{-- ── Salary Details (full width for hourly, 38% for monthly) ── --}}
                <td style="width:{{ $isHourly ? '100%' : '38%' }}; padding:0; vertical-align:top; border:none;">
                    <div class="ps-band">{{ __('Salary Details') }}</div>
                    <table class="ps-t">
                        <tr>
                            <td class="lbl" style="width:50%;">{{ __('Payslip Type') }}</td>
                            <td class="ra val" style="width:50%;">{{ $payslipTypeName }}</td>
                        </tr>

                        @if ($isHourly)
                            {{-- ── HOURLY: clean rate × hours calculation ── --}}
                            <tr>
                                <td style="width:50%;" class="lbl">{{ __('Hourly Rate') }} ( <b class="v-teal">A</b>
                                    )</td>
                                <td style="width:50%;" class="ra val v-teal">{{ $_fmtMoney($perHourSalary) }}</td>
                            </tr>
                            <tr>
                                <td style="width:50%;" class="lbl">{{ __('Hours Worked') }} ( <b
                                        class="v-green">B</b> )</td>
                                <td style="width:50%;" class="ra val v-green">{{ $totalWorkHours }} hrs</td>
                            </tr>
                            <tr style="background:{{ $_themeColor }}10;">
                                <td style="width:50%;" class="lbl"><strong>{{ __('Gross Earned') }} ( <b
                                            class="v-teal">A</b> &times; <b class="v-green">B</b> )</strong></td>
                                <td style="width:50%;" class="ra val v-teal">
                                    <strong>{{ $_fmtMoney($_storedSalary) }}</strong>
                                </td>
                            </tr>
                        @else
                            {{-- ── MONTHLY: standard rows ── --}}
                            <tr>
                                <td class="lbl">{{ __('Gross Salary') }}</td>
                                <td class="ra val v-teal">{{ $_fmtMoney($_storedSalary) }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Per Day Rate') }}</td>
                                <td class="ra val">{{ $_fmtMoney($perDaySalary) }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ __('Per Hour Rate') }}</td>
                                <td class="ra val">{{ $_fmtMoney($perHourSalary) }}</td>
                            </tr>
                            @if (!empty($isMonthWise) || !empty($dayOffDays))
                            <tr>
                                <td class="lbl">{{ __('Day Off') }}</td>
                                <td class="ra val" style="color:#6c757d;">{{ $dayOffDaysFmt ?? 0 }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="lbl">Days Paid</td>
                                <td class="ra cb">
                                    @php $daysPaidFmt = ($daysPaid == floor($daysPaid)) ? (int)$daysPaid : $daysPaid; @endphp
                                    {{ $daysPaidFmt }}
                                    @if ($paidLeaveDays > 0)
                                        <span style="font-size:7.5px; color:#198754;">(+{{ $paidLeaveDays }}
                                            leave)</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($unpaidLeaveDays > 0)
                                <tr>
                                    <td class="lbl unpaid">Unpaid Leave</td>
                                    <td class="ra unpaid">{{ $unpaidLeaveDays }} days</td>
                                </tr>
                            @endif
                        @endif

                        <tr>
                            <td class="lbl">{{ __('Pay Period') }}</td>
                            <td class="ra val" style="font-size:11px;">{{ $slipLabel }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __('Pay Status') }}</td>
                            <td class="ra val">
                                @if ($isPaid)
                                    <span class="badge-paid">{{ __('Paid') }}</span>
                                @else
                                    <span class="badge-unpaid">{{ __('Unpaid') }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- C: Earnings | Deductions --}}
        <table class="ps-t" style="border:none; border-top:1px solid {{ $_themeColor }}55;">
            <tr>
                <td style="width:50%; padding:0; border:none; vertical-align:top;">
                    <div class="ps-band">{{ __('Earnings') }}</div>
                    <table class="ps-t">
                        <tr>
                            <th style="text-align:left; padding-left:10px; width:65%;">{{ __('Description') }}</th>
                            <th class="ra">{{ __('Amount') }}</th>
                        </tr>
                        @foreach ($earRowsPadded as $er)
                            <tr>
                                <td style="padding-left:10px;">{{ $er['label'] }}</td>
                                <td class="ra">
                                    @if ($er['amount'] !== null)
                                        {{ $_fmtMoney($er['amount']) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr class="ps-total">
                            <td style="padding-left:10px;"><strong>{{ __('Total Earnings') }}</strong></td>
                            <td class="ra"><strong>{{ $_fmtMoney($totalEarnings) }}</strong></td>
                        </tr>
                    </table>
                </td>
                <td style="width:50%; padding:0; border-left:1px solid {{ $_themeColor }}55; vertical-align:top;">
                    <div class="ps-band">{{ __('Deductions') }}</div>
                    <table class="ps-t">
                        <tr>
                            <th style="text-align:left; padding-left:10px; width:65%;">{{ __('Description') }}</th>
                            <th class="ra">{{ __('Amount') }}</th>
                        </tr>
                        @foreach ($dedRowsPadded as $dr2)
                            <tr>
                                <td style="padding-left:10px;">{{ $dr2['label'] }}</td>
                                <td class="ra">
                                    @if ($dr2['amount'] !== null)
                                        {{ $_fmtMoney($dr2['amount']) }}
                                    @elseif(($dr2['label'] ?? '') !== '')
                                        &mdash;
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr class="ps-total">
                            <td style="padding-left:10px;"><strong>{{ __('Total Deductions') }}</strong></td>
                            <td class="ra"><strong>{{ $_fmtMoney($totalDeductions) }}</strong></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        {{-- D: Stamp | Net Pay --}}
        <table class="ps-t ps-net-row" style="border:none;">
            <tr>
                <td class="ps-stamp-td">
                    {{-- Always render the img so postMessage can update it dynamically --}}
                    <img src="{{ $_stampUrl ?? '' }}" alt="Stamp" class="ps-stamp-img"
                         style="{{ $_stampUrl ? '' : 'display:none;' }}">
                    @if (!$_stampUrl)
                        <div class="ps-stamp-ring">
                            {{ $companyName }}<br>
                            <span
                                style="font-size:7.5px; display:block; margin-top:4px; letter-spacing:1px;">{{ strtoupper($slipLabel) }}</span>
                        </div>
                    @endif
                    <div style="font-size:10px; color:#888; margin-top:6px;">{{ __('Digital Authorization') }}</div>
                </td>
                <td class="ps-net-td">
                    <table class="ps-net-inner">
                        <tr>
                            <td class="ps-net-lbl">{{ __('Net Pay') }}</td>
                            @if ($netSalary > 0)
                                <td class="ps-net-val">{{ $_fmtMoney($netSalary) }}</td>
                            @else
                                <td class="ps-net-val">{{ $_fmtMoney(0) }}</td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- E: Signatures --}}
        @if($_showSignatures)
        <div class="ps-sig">
            <table>
                <tr>
                    <td>
                        <div class="ps-sig-line"></div>
                        <div class="ps-sig-label">{{ __('Employee Signature') }}</div>
                    </td>
                    <td>
                        <div class="ps-sig-line"></div>
                        <div class="ps-sig-label">{{ __('Authorized Signatory') }}</div>
                    </td>
                </tr>
            </table>
        </div>
        @endif

        {{-- Footer --}}
        @if($_showFooter)
        <div class="ps-footer">
            <table>
                <tr>
                    <td style="width:33%;">
                        @if ($companyPhone)
                            <strong>{{ __('Tel') }}:</strong> {{ $companyPhone }}
                        @endif
                    </td>
                    <td style="width:34%; text-align:center;">
                        @if ($companyWeb)
                            <strong>{{ __('Web') }}:</strong> {{ $companyWeb }}
                        @endif
                    </td>
                    <td style="width:33%; text-align:right;">
                        @if ($companyEmail)
                            <strong>{{ __('Email') }}:</strong> {{ $companyEmail }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        @endif

    </div>{{-- /.ps-card --}}
</div>{{-- /.ps-modal --}}

<script>
/**
 * Listen for stamp data URL from the parent settings page (postMessage).
 * Updates the stamp image src dynamically for the live preview.
 */
window.addEventListener('message', function(e) {
    if (e.data && e.data.type === 'payslip-stamp' && e.data.dataUrl) {
        var img = document.querySelector('.ps-stamp-img');
        if (img) { img.src = e.data.dataUrl; img.style.display = ''; }
        var ring = document.querySelector('.ps-stamp-ring');
        if (ring) ring.style.display = 'none';
    }
});

    window.payslipEmailSend = function(eid, month) {
        $.ajax({
            url: '{{ url('payslip/send') }}/' + eid + '/' + month,
            type: 'GET',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(r) {
                show_toastr('Success', r.message, 'success');
            },
            error: function(r) {
                show_toastr('Error', r.message, 'error');
            }
        });
    };
</script>
