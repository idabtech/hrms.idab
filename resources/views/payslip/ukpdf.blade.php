@php
use Carbon\Carbon;

[$slipYear, $slipMonth] = explode('-', $payslip->salary_month);

$downloadUrl  = route('payslip.uk.download', [$employee->id, $payslip->salary_month]);

// ── Dynamic theme color ───────────────────────────────────────────────────
$_colorSetting = \App\Models\Utility::colorset();
$_themeName    = $_colorSetting['theme_color'] ?? 'theme-2';
$_themeHexMap  = [
    'theme-1'  => '#0CAF60',
    'theme-2'  => '#584ED2',
    'theme-3'  => '#6FD943',
    'theme-4'  => '#145388',
    'theme-5'  => '#B9406B',
    'theme-6'  => '#008ECC',
    'theme-7'  => '#922C88',
    'theme-8'  => '#C0A145',
    'theme-9'  => '#48494B',
    'theme-10' => '#0C7785',
];
$_themeColor = str_starts_with($_themeName, '#')
    ? $_themeName
    : ($_themeHexMap[$_themeName] ?? '#584ED2');

// Proper rgba tint for page background
$_hex = ltrim($_themeColor, '#');
$_r   = hexdec(substr($_hex, 0, 2));
$_g   = hexdec(substr($_hex, 2, 2));
$_b   = hexdec(substr($_hex, 4, 2));
$_bgRgba  = "rgba({$_r},{$_g},{$_b},0.10)";

// ── Currency ──────────────────────────────────────────────────────────────
$_appSettings = \App\Models\Utility::settings();
$_currSymbol  = $_appSettings['site_currency_symbol'] ?? '£';
$_currPos     = $_appSettings['site_currency_symbol_position'] ?? 'pre';
$_fmtMoney    = function($amount) use ($_currSymbol, $_currPos) {
    $n = number_format((float)$amount, 2);
    return $_currPos === 'pre' ? $_currSymbol . $n : $n . $_currSymbol;
};

// ── Month labels ──────────────────────────────────────────────────────────
$carbonMonth = Carbon::createFromDate((int)$slipYear, (int)$slipMonth, 1);
$slipLabel   = $carbonMonth->format('F Y');
$slipShort   = $carbonMonth->format('M Y');
$payDate     = $carbonMonth->copy()->endOfMonth()->format('d M Y');

// ── All display values ────────────────────────────────────────────────────
$_storedSalary        = (float) $payslipDetail['basic_salary'];
$perDaySalary         = round((float) $payslipDetail['per_day_amount'], 2);
$perHourSalary        = round((float) $payslipDetail['per_hour_amount'], 2);
$officeDays           = (int)   $payslipDetail['office_days'];
$presentDays          = $payslipDetail['present_days'];
$approvedLeaves       = $payslipDetail['approved_leaves_month'];
$disapprovedLeaves    = (int)   $payslipDetail['rejected_leaves_month'];
$absentDays           = $payslipDetail['absent_days'];
$extraDays            = $payslipDetail['extra_days'];
$totalWorkHours       = $payslipDetail['total_work_hours'];
$avgHrsPerDay         = $payslipDetail['avg_hrs_per_day'];
$totalLeaveAlloc      = (int)   $payslipDetail['total_leave_alloc'];
$remainingLeaves      = (int)   $payslipDetail['remaining_leaves'];
$paidLeaveDays        = $payslipDetail['paid_leave_days']          ?? 0;
$unpaidLeaveDays      = $payslipDetail['unpaid_leave_days']        ?? 0;
$unpaidLeaveDeduction = $payslipDetail['unpaid_leave_deduction']   ?? 0;
$daysPaid             = $payslipDetail['days_paid']                ?? ($presentDays + $paidLeaveDays);
$isHourly             = (bool)  ($payslipDetail['is_hourly']       ?? false);
$hoursPerDay          = (float) ($payslipDetail['hours_per_day']   ?? 8);
$payslipTypeName      = \App\Models\PayslipType::find($employee->salary_type)?->name ?? 'Monthly';
$isPaid               = $payslip->status == 1;

$presentDisplay  = min($presentDays, $officeDays);
$presentDaysFmt  = ($presentDisplay == floor($presentDisplay)) ? (int)$presentDisplay : $presentDisplay;
$absentDaysFmt   = ($absentDays == floor($absentDays)) ? (int)$absentDays : $absentDays;
$extraDaysFmt    = ($extraDays  == floor($extraDays))  ? (int)$extraDays  : $extraDays;

// ── UK-specific flag — drives conditional display in the payslip ─────────
// Uses same IP detection as Utility::bankCodeLabel(). Cached per IP 24h.
// On localhost/private IPs returns false — use ?uk_preview=1 in URL to override
// during local development.
$_isUkRequest = \App\Models\Utility::isUkRequest()
    || request()->boolean('uk_preview');
// tax_payer_id  → Tax Code (e.g. 1257L)
// ni_number     → NI Number (e.g. JS877742C) — new dedicated column
// ni_table_letter → NI Table Letter (e.g. A) — new column, default A
// payment_method  → BACS/CHAPS/Cash/Cheque — new column, default BACS
// employee_id   → Works No
// bank_identifier_code → Sort Code (correct use of this field)
$taxCode        = !empty($employee->tax_payer_id)      ? $employee->tax_payer_id      : '—';
$niNumber       = !empty($employee->ni_number)         ? $employee->ni_number         : '—';
$niTableLetter  = !empty($employee->ni_table_letter)   ? $employee->ni_table_letter   : 'A';
$paymentMethod  = !empty($employee->payment_method)    ? $employee->payment_method    : 'BACS';
$worksNo        = !empty($employee->employee_id)       ? $employee->employee_id       : '—';
$sortCode       = !empty($employee->bank_identifier_code) ? $employee->bank_identifier_code : '—';
@endphp
@php
// ── Earnings rows ─────────────────────────────────────────────────────────
$_basicLabel = $isHourly
    ? 'REGULAR (' . $totalWorkHours . ' hrs @ ' . number_format($perHourSalary, 2) . ')'
    : __('Basic Salary');
$earRows = [['label' => $_basicLabel, 'amount' => $_storedSalary]];
foreach ($payslipDetail['earning']['allowance'] as $_ar) {
    foreach (json_decode($_ar->allowance) as $_a) {
        $earRows[] = ['label' => $_a->title, 'amount' => $_a->type === 'percentage' ? round($_a->amount * $_storedSalary / 100, 2) : (float)$_a->amount];
    }
}
foreach ($payslipDetail['earning']['commission'] as $_cr) {
    foreach (json_decode($_cr->commission) as $_c) {
        $earRows[] = ['label' => $_c->title, 'amount' => $_c->type === 'percentage' ? round($_c->amount * $_storedSalary / 100, 2) : (float)$_c->amount];
    }
}
foreach ($payslipDetail['earning']['bonous'] as $_b) {
    $earRows[] = ['label' => $_b->title ?: __('Bonus'), 'amount' => $_b->type === 'percentage' ? round($_b->amount * $employee->salary / 100, 2) : (float)$_b->amount];
}
foreach ($payslipDetail['earning']['otherPayment'] as $_op2) {
    foreach (json_decode($_op2->other_payment) as $_op) {
        $earRows[] = ['label' => $_op->title, 'amount' => $_op->type === 'percentage' ? round($_op->amount * $_storedSalary / 100, 2) : (float)$_op->amount];
    }
}
foreach ($payslipDetail['earning']['overTime'] as $_ot2) {
    foreach (json_decode($_ot2->overtime) as $_ot) {
        $earRows[] = ['label' => $_ot->title ?: __('Overtime'), 'amount' => (float)($_ot->number_of_days * $_ot->hours * $_ot->rate)];
    }
}

// ── Deductions rows ───────────────────────────────────────────────────────
$dedRows = [];
// Loan Repayment (deduction)
$totalLoanRepayment = $payslipDetail['totalLoanRepayment'] ?? 0;
if ($totalLoanRepayment > 0) {
    $dedRows[] = ['label' => __('Loan Repayment'), 'amount' => $totalLoanRepayment];
}
foreach ($payslipDetail['deduction']['saturation_deduction'] as $_dr2) {
    foreach (json_decode($_dr2->saturation_deduction) as $_dd) {
        $_amt = $_dd->type === 'percentage' ? round($_dd->amount * $_dr2->basic_salary / 100, 2) : (float)$_dd->amount;
        if ($_amt > 0) $dedRows[] = ['label' => $_dd->title, 'amount' => $_amt];
    }
}
foreach ($payslipDetail['deduction']['pansion'] as $_p) {
    $_amt = $_p->type === 'percentage' ? round($_p->amount * $employee->salary / 100, 2) : (float)$_p->amount;
    if ($_amt > 0) $dedRows[] = ['label' => $_p->title ?: __('Employee Pension'), 'amount' => $_amt];
}
foreach ($payslipDetail['deduction']['leave'] as $_lea) {
    if ($_lea->empleave > 0) $dedRows[] = ['label' => __('Loss Of Pay'), 'amount' => (float)$_lea->empleave];
}
if ($unpaidLeaveDeduction > 0) {
    $dedRows[] = ['label' => __('Unpaid Leave') . ' (' . $unpaidLeaveDays . ' ' . __('days') . ')', 'amount' => $unpaidLeaveDeduction];
}

$totalEarnings   = $payslipDetail['totalEarning'] + $_storedSalary;
$totalDeductions = $payslipDetail['totalDeduction'];
$netSalary       = $payslipDetail['net_salary'];

// ── Year To Date ──────────────────────────────────────────────────────────
$ytdTaxableGross         = $payslipDetail['ytd_taxable_gross']          ?? $totalEarnings;
$ytdIncomeTax            = $payslipDetail['ytd_income_tax']             ?? 0;
$ytdEmployeeNI           = $payslipDetail['ytd_employee_ni']            ?? 0;
$ytdEmployerNI           = $payslipDetail['ytd_employer_ni']            ?? 0;
$ytdStatutoryPay         = $payslipDetail['ytd_statutory_pay']          ?? 0;
$ytdEmployeePension      = $payslipDetail['ytd_employee_pension']       ?? 0;
$ytdEmployerPension      = $payslipDetail['ytd_employer_pension']       ?? 0;
// Dynamic labels — match exactly what appears in the deductions list
$ytdLabelIncomeTax       = $payslipDetail['ytd_label_income_tax']       ?? __('Income Tax');
$ytdLabelEmployeeNI      = $payslipDetail['ytd_label_employee_ni']      ?? __('National Insurance');
$ytdLabelEmployerNI      = $payslipDetail['ytd_label_employer_ni']      ?? __('Employer NI');
$ytdLabelStatutoryPay    = $payslipDetail['ytd_label_statutory_pay']    ?? __('Statutory Pay');
$ytdLabelEmployeePension = $payslipDetail['ytd_label_employee_pension'] ?? __('Employee Pension');
$ytdLabelEmployerPension = $payslipDetail['ytd_label_employer_pension'] ?? __('Employer Pension');

// ── Company ───────────────────────────────────────────────────────────────
$companyName  = \App\Models\Utility::getValByName('company_name')      ?? '';
$companyPhone = \App\Models\Utility::getValByName('company_telephone') ?? '';
$companyEmail = \App\Models\Utility::getValByName('company_email')     ?? '';
$companyWeb   = \App\Models\Utility::getValByName('company_website')   ?? '';

// ── Stamp ─────────────────────────────────────────────────────────────────
$_stampFile = \App\Models\Utility::getValByName('company_stamp');
$_stampUrl  = ($_stampFile && !empty($_stampFile))
    ? \App\Models\Utility::get_file('uploads/logo/') . '/' . $_stampFile
    : null;
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

    {{-- ═══ Standalone: Employee name + Dept ═══ --}}
    <div class="ukp-emp-box">
        <div class="ukp-emp-name">{{ $employee->name }}</div>
        <div class="ukp-emp-dept">{{ __('Department') }}: {{ optional($employee->department)->name ?? '' }}</div>
    </div>

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
                                <td class="ukp-left">

                                    <span class="kv-name">{{ $employee->name }}</span>
                                    <table class="kv-tbl">
                                        <tr><td class="k">{{ __('Pay Period') }}</td><td class="v">{{ $slipShort }}</td></tr>
                                        <tr><td class="k">{{ __('Pay Date') }}</td><td class="v">{{ $payDate }}</td></tr>
                                        <tr><td class="k">{{ __('Pay Type') }}</td><td class="v">{{ $payslipTypeName }}</td></tr>
                                        @if($_isUkRequest)
                                        <tr><td class="k">{{ __('Payment Method') }}</td><td class="v">{{ $paymentMethod }}</td></tr>
                                        @endif
                                        <tr><td class="k">{{ __('Works No') }}</td><td class="v">{{ $worksNo }}</td></tr>
                                        @if($_isUkRequest)
                                        <tr><td class="k">{{ __('Tax Code') }}</td><td class="v">{{ $taxCode }}</td></tr>
                                        <tr><td class="k">{{ __('NI Number') }}</td><td class="v">{{ $niNumber }}</td></tr>
                                        <tr><td class="k">{{ __('NI Table Letter') }}</td><td class="v">{{ $niTableLetter }}</td></tr>
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
                                        @if($_stampUrl)
                                            <img src="{{ $_stampUrl }}" alt="Stamp" class="stamp-img">
                                        @else
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

    {{-- ═══ Footer ═══ --}}
    <div class="ukp-footer">
        <table class="ukp-footer-row">
            <tr>
                <td style="width:33%;">@if($companyPhone)<strong>{{ __('Tel') }}:</strong> {{ $companyPhone }}@endif</td>
                <td style="width:34%; text-align:center;">@if($companyWeb)<strong>{{ __('Web') }}:</strong> {{ $companyWeb }}@endif</td>
                <td style="width:33%; text-align:right;">@if($companyEmail)<strong>{{ __('Email') }}:</strong> {{ $companyEmail }}@endif</td>
            </tr>
        </table>
    </div>

</div>{{-- /.ukp-wrap --}}

<script>
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
