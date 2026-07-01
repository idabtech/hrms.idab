@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;

[$slipYear, $slipMonth] = explode('-', $payslip->salary_month);

// ── Theme color ───────────────────────────────────────────────────────────
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

// rgba page background tint (PHP-computed — works in dompdf)
$_hex    = ltrim($_themeColor, '#');
$_r      = hexdec(substr($_hex, 0, 2));
$_g      = hexdec(substr($_hex, 2, 2));
$_b      = hexdec(substr($_hex, 4, 2));
$_bgRgba = "rgba({$_r},{$_g},{$_b},0.08)";

// ── Currency ─────────────────────────────────────────────────────────────
$_appSettings = \App\Models\Utility::settings();
$_currSymbol  = $_appSettings['site_currency_symbol'] ?? '£';
$_currPos     = $_appSettings['site_currency_symbol_position'] ?? 'pre';
$_fmtMoney    = function($amount) use ($_currSymbol, $_currPos) {
    $n = number_format((float)$amount, 2);
    return $_currPos === 'pre' ? $_currSymbol . $n : $n . $_currSymbol;
};

// ── Month labels ─────────────────────────────────────────────────────────
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
$totalShiftHours      = (float) ($payslipDetail['total_shift_hours'] ?? ($officeDays * $hoursPerDay));
$payslipTypeName      = \App\Models\PayslipType::find($employee->salary_type)?->name ?? 'Monthly';
$isPaid               = $payslip->status == 1;

$presentDisplay  = min($presentDays, $officeDays);
$presentDaysFmt  = ($presentDisplay == floor($presentDisplay)) ? (int)$presentDisplay : $presentDisplay;
$absentDaysFmt   = ($absentDays == floor($absentDays)) ? (int)$absentDays : $absentDays;
$extraDaysFmt    = ($extraDays  == floor($extraDays))  ? (int)$extraDays  : $extraDays;

// ── UK flag (IP-based, same as bankCodeLabel logic) ───────────────────────
$_isUk = \App\Models\Utility::isUkRequest() || request()->boolean('uk_preview');

// ── UK employee fields ────────────────────────────────────────────────────
$taxCode       = !empty($employee->tax_payer_id)         ? $employee->tax_payer_id         : '—';
$niNumber      = !empty($employee->ni_number)            ? $employee->ni_number            : '—';
$niTableLetter = !empty($employee->ni_table_letter)      ? $employee->ni_table_letter      : 'A';
$paymentMethod = !empty($employee->payment_method)       ? $employee->payment_method       : 'BACS';
$worksNo       = !empty($employee->employee_id)          ? $employee->employee_id          : '—';
$sortCode      = !empty($employee->bank_identifier_code) ? $employee->bank_identifier_code : '—';

// ── Stamp ─────────────────────────────────────────────────────────────────
$_stampFile = \App\Models\Utility::getValByName('company_stamp');
$_stampUrl  = ($_stampFile && !empty($_stampFile))
    ? \App\Models\Utility::get_file('uploads/logo/') . '/' . $_stampFile
    : null;
@endphp
@php
// ── Earnings rows ──────────────────────────────────────────────────────────
$_basicLabel = $isHourly
    ? 'REGULAR (' . $totalWorkHours . ' hrs @ ' . number_format($perHourSalary, 2) . ')'
    : 'Basic Salary';
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
    $earRows[] = ['label' => $_b->title ?: 'Bonus', 'amount' => $_b->type === 'percentage' ? round($_b->amount * $employee->salary / 100, 2) : (float)$_b->amount];
}
foreach ($payslipDetail['earning']['otherPayment'] as $_op2) {
    foreach (json_decode($_op2->other_payment) as $_op) {
        $earRows[] = ['label' => $_op->title, 'amount' => $_op->type === 'percentage' ? round($_op->amount * $_storedSalary / 100, 2) : (float)$_op->amount];
    }
}
foreach ($payslipDetail['earning']['overTime'] as $_ot2) {
    foreach (json_decode($_ot2->overtime) as $_ot) {
        $earRows[] = ['label' => $_ot->title ?: 'Overtime', 'amount' => (float)($_ot->number_of_days * $_ot->hours * $_ot->rate)];
    }
}

// ── Deductions rows ───────────────────────────────────────────────────────
$dedRows = [];
foreach ($payslipDetail['deduction']['loan'] as $_lr) {
    foreach (json_decode($_lr->loan) as $_ln) {
        $_amt = $_ln->type === 'percentage' ? round($_ln->amount * $_lr->basic_salary / 100, 2) : (float)$_ln->amount;
        if ($_amt > 0) $dedRows[] = ['label' => $_ln->title ?: 'Loan', 'amount' => $_amt];
    }
}
foreach ($payslipDetail['deduction']['saturation_deduction'] as $_dr2) {
    foreach (json_decode($_dr2->saturation_deduction) as $_dd) {
        $_amt = $_dd->type === 'percentage' ? round($_dd->amount * $_dr2->basic_salary / 100, 2) : (float)$_dd->amount;
        if ($_amt > 0) $dedRows[] = ['label' => $_dd->title, 'amount' => $_amt];
    }
}
foreach ($payslipDetail['deduction']['pansion'] as $_p) {
    $_amt = $_p->type === 'percentage' ? round($_p->amount * $employee->salary / 100, 2) : (float)$_p->amount;
    if ($_amt > 0) $dedRows[] = ['label' => $_p->title ?: 'Employee Pension', 'amount' => $_amt];
}
foreach ($payslipDetail['deduction']['leave'] as $_lea) {
    if ($_lea->empleave > 0) $dedRows[] = ['label' => 'Loss Of Pay', 'amount' => (float)$_lea->empleave];
}
if ($unpaidLeaveDeduction > 0) {
    $dedRows[] = ['label' => 'Unpaid Leave (' . $unpaidLeaveDays . ' days)', 'amount' => $unpaidLeaveDeduction];
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
$ytdLabelIncomeTax       = $payslipDetail['ytd_label_income_tax']       ?? 'Income Tax';
$ytdLabelEmployeeNI      = $payslipDetail['ytd_label_employee_ni']      ?? 'National Insurance';
$ytdLabelEmployerNI      = $payslipDetail['ytd_label_employer_ni']      ?? 'Employer NI';
$ytdLabelStatutoryPay    = $payslipDetail['ytd_label_statutory_pay']    ?? 'Statutory Pay';
$ytdLabelEmployeePension = $payslipDetail['ytd_label_employee_pension'] ?? 'Employee Pension';
$ytdLabelEmployerPension = $payslipDetail['ytd_label_employer_pension'] ?? 'Employer Pension';

// ── Company ───────────────────────────────────────────────────────────────
$companyName  = \App\Models\Utility::getValByName('company_name')      ?? '';
$companyPhone = \App\Models\Utility::getValByName('company_telephone') ?? '';
$companyEmail = \App\Models\Utility::getValByName('company_email')     ?? '';
$companyWeb   = \App\Models\Utility::getValByName('company_website')   ?? '';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
/* ══════════════════════════════════════════════════════════════════════════
   UK Payslip Download — dompdf-safe CSS
   Mirrors the modal view (ukpdf.blade.php) exactly.
   Key dompdf constraints:
     - No flexbox/grid — use nested tables only
     - overflow:hidden on cells to prevent bleed
     - Explicit pixel widths on inner tables where possible
     - DejaVu Sans for currency symbol support
   ══════════════════════════════════════════════════════════════════════════ */
@page {
    margin: 10mm 8mm;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 10px;
    color: #111;
    line-height: 1.5;
    background: {{ $_bgRgba }};
}
.page { padding: 10px; }

/* ── Standalone employee name box ── */
.emp-box {
    background: #fff;
    border: 2px solid {{ $_themeColor }};
    padding: 8px 12px;
    margin-bottom: 4px;
}
.emp-name { font-weight: bold; font-size: 11px; margin-bottom: 1px; }
.emp-dept { font-size: 9px; color: #444; }

/* ── Outer bordered box (contains everything) ── */
.outer-box {
    background: #fff;
    border: 2px solid {{ $_themeColor }};
    width: 100%;
    border-collapse: collapse;
}

/* Company name header */
.co-header {
    text-align: center;
    font-weight: bold;
    font-size: 11px;
    padding: 8px 10px;
    border-bottom: 2px solid {{ $_themeColor }};
}

/* ── Two-column layout table ── */
.two-col {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.two-col td {
    vertical-align: top;
    overflow: hidden;
}

/* Left column: 47% with right border */
.left-col {
    width: 47%;
    border-right: 2px solid {{ $_themeColor }};
    padding: 10px 10px;
}

/* Right column: remaining 53% */
.right-col {
    width: 53%;
    padding: 10px 10px;
}

/* ── Divider line inside left column ── */
.divider {
    border-top: 1.5px solid {{ $_themeColor }};
    margin: 8px 0;
    height: 0;
}

/* ── Section heading (centered bold) ── */
.sec-heading {
    font-weight: bold;
    font-size: 10px;
    text-align: center;
    padding-bottom: 4px;
}

/* ── Key-value table (left column details & YTD) ── */
.kv-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 9px;
}
.kv-table td {
    padding: 1.5px 0;
    vertical-align: top;
    overflow: hidden;
}
.kv-table .kv-label {
    width: 55%;
    color: #333;
}
.kv-table .kv-value {
    width: 45%;
    text-align: right;
    color: #111;
}

/* ── Employee name in left column ── */
.emp-heading {
    font-weight: bold;
    font-size: 10px;
    padding-bottom: 4px;
}

/* ── Line-item list (income/deduction rows) ── */
.item-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 9px;
}
.item-table td {
    padding: 1.5px 0;
    vertical-align: top;
    overflow: hidden;
}
.item-table .item-desc {
    width: 62%;
    color: #333;
}
.item-table .item-amt {
    width: 38%;
    text-align: right;
    color: #111;
}

/* ── Total row (bold + top border) ── */
.total-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 9.5px;
    margin-top: 3px;
}
.total-table td {
    padding: 3px 0;
    font-weight: bold;
    border-top: 1.5px solid {{ $_themeColor }};
    vertical-align: top;
    overflow: hidden;
}
.total-table .tot-amt {
    width: 38%;
    text-align: right;
}

/* ── Gap between sections ── */
.sec-gap { height: 10px; }

/* ── Net Pay row ── */
.net-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 11px;
    margin-top: 8px;
}
.net-table td {
    padding: 4px 0;
    font-weight: bold;
    border-top: 2px solid {{ $_themeColor }};
    vertical-align: middle;
    overflow: hidden;
}
.net-table .net-label { font-size: 11.5px; }
.net-table .net-value { width: 40%; text-align: right; font-size: 12px; }

/* ── Stamp ── */
.stamp-area { text-align: center; padding: 10px 0 4px; }
.stamp-circle {
    width: 70px;
    height: 70px;
    border: 2.5px solid {{ $_themeColor }};
    border-radius: 50%;
    margin: 0 auto;
    padding-top: 16px;
    font-size: 7px;
    font-weight: bold;
    text-align: center;
    color: {{ $_themeColor }};
    line-height: 1.3;
}
.stamp-img {
    display: block;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: contain;
    border: 2.5px solid {{ $_themeColor }};
    margin: 0 auto;
}
.stamp-caption { font-size: 7.5px; color: #888; margin-top: 3px; }

/* ── Pay status ── */
.status-paid { color: #198754; font-weight: bold; }
.status-unpaid { color: #cc0000; font-weight: bold; }

/* ── Footer ── */
.footer-bar {
    background: {{ $_themeColor }};
    margin-top: 6px;
    width: 100%;
}
.footer-bar td {
    padding: 5px 10px;
    color: #fff;
    font-size: 8px;
    border: none;
}
</style>
</head>
<body>
<div class="page">

    {{-- ═══ Employee Name + Department (standalone box) ═══ --}}
    <div class="emp-box">
        <div class="emp-name">{{ $employee->name }}</div>
        <div class="emp-dept">Department: {{ optional($employee->department)->name ?? '' }}</div>
    </div>

    {{-- ═══ Main outer bordered box ═══ --}}
    <table class="outer-box" cellpadding="0" cellspacing="0">
        <tr>
            <td class="co-header">{{ strtoupper($companyName) }}</td>
        </tr>
        <tr>
            <td style="padding: 0;">

                {{-- Two-column layout --}}
                <table class="two-col" cellpadding="0" cellspacing="0">
                    <tr>

                        {{-- ══ LEFT COLUMN: Employee Details + Year To Date ══ --}}
                        <td class="left-col">

                            <div class="emp-heading">{{ $employee->name }}</div>

                            <table class="kv-table">
                                <tr><td class="kv-label">Pay Period</td><td class="kv-value">{{ $slipShort }}</td></tr>
                                <tr><td class="kv-label">Pay Date</td><td class="kv-value">{{ $payDate }}</td></tr>
                                <tr><td class="kv-label">Pay Type</td><td class="kv-value">{{ $payslipTypeName }}</td></tr>
                                @if($_isUk)
                                <tr><td class="kv-label">Payment Method</td><td class="kv-value">{{ $paymentMethod }}</td></tr>
                                @endif
                                <tr><td class="kv-label">Works No</td><td class="kv-value">{{ $worksNo }}</td></tr>
                                @if($_isUk)
                                <tr><td class="kv-label">Tax Code</td><td class="kv-value">{{ $taxCode }}</td></tr>
                                <tr><td class="kv-label">NI Number</td><td class="kv-value">{{ $niNumber }}</td></tr>
                                <tr><td class="kv-label">NI Table Letter</td><td class="kv-value">{{ $niTableLetter }}</td></tr>
                                @endif
                                <tr>
                                    <td class="kv-label">Pay Status</td>
                                    <td class="kv-value {{ $isPaid ? 'status-paid' : 'status-unpaid' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</td>
                                </tr>
                            </table>

                            @if($_isUk)
                            <div class="divider"></div>

                            <div class="sec-heading">Year To Date</div>
                            <table class="kv-table">
                                <tr><td class="kv-label">Taxable Gross</td><td class="kv-value">{{ $_fmtMoney($totalEarnings) }}</td></tr>
                                @if($ytdIncomeTax > 0)
                                <tr><td class="kv-label">{{ $ytdLabelIncomeTax }}</td><td class="kv-value">{{ $_fmtMoney($ytdIncomeTax) }}</td></tr>
                                @endif
                                @if($ytdEmployeeNI > 0)
                                <tr><td class="kv-label">{{ $ytdLabelEmployeeNI }}</td><td class="kv-value">{{ $_fmtMoney($ytdEmployeeNI) }}</td></tr>
                                @endif
                                @if($ytdEmployerNI > 0)
                                <tr><td class="kv-label">{{ $ytdLabelEmployerNI }}</td><td class="kv-value">{{ $_fmtMoney($ytdEmployerNI) }}</td></tr>
                                @endif
                                @if($ytdStatutoryPay > 0)
                                <tr><td class="kv-label">{{ $ytdLabelStatutoryPay }}</td><td class="kv-value">{{ $_fmtMoney($ytdStatutoryPay) }}</td></tr>
                                @endif
                                @php $ytdPensionRows = $payslipDetail['ytd_pension_rows'] ?? []; @endphp
                                @foreach($ytdPensionRows as $_ytdPen)
                                @if(($_ytdPen['amount'] ?? 0) > 0)
                                <tr><td class="kv-label">{{ $_ytdPen['label'] ?? 'Pension' }}</td><td class="kv-value">{{ $_fmtMoney($_ytdPen['amount']) }}</td></tr>
                                @endif
                                @endforeach
                            </table>
                            @endif

                        </td>{{-- /left-col --}}

                        {{-- ══ RIGHT COLUMN: Income | Deductions | Net Pay | Stamp ══ --}}
                        <td class="right-col">

                            {{-- Income --}}
                            <div class="sec-heading">Income</div>
                            <table class="item-table">
                                @foreach($earRows as $er)
                                @if(($er['label'] ?? '') !== '')
                                <tr>
                                    <td class="item-desc">{{ $er['label'] }}</td>
                                    <td class="item-amt">{{ $er['amount'] !== null ? $_fmtMoney($er['amount']) : '' }}</td>
                                </tr>
                                @endif
                                @endforeach
                            </table>
                            <table class="total-table">
                                <tr>
                                    <td>Total Income</td>
                                    <td class="tot-amt">{{ $_fmtMoney($totalEarnings) }}</td>
                                </tr>
                            </table>

                            <div class="sec-gap"></div>

                            {{-- Deductions --}}
                            <div class="sec-heading">Deductions</div>
                            <table class="item-table">
                                @php $hasDed = count(array_filter($dedRows, fn($r) => ($r['label'] ?? '') !== '')) > 0; @endphp
                                @if($hasDed)
                                    @foreach($dedRows as $dr)
                                    @if(($dr['label'] ?? '') !== '')
                                    <tr>
                                        <td class="item-desc">{{ $dr['label'] }}</td>
                                        <td class="item-amt">{{ $dr['amount'] !== null ? $_fmtMoney($dr['amount']) : '—' }}</td>
                                    </tr>
                                    @endif
                                    @endforeach
                                @else
                                    <tr><td colspan="2" style="color:#999; font-style:italic;">No deductions</td></tr>
                                @endif
                            </table>
                            <table class="total-table">
                                <tr>
                                    <td>Total Deductions</td>
                                    <td class="tot-amt">{{ $_fmtMoney($totalDeductions) }}</td>
                                </tr>
                            </table>

                            <div class="sec-gap"></div>

                            {{-- Net Pay --}}
                            <table class="net-table">
                                <tr>
                                    <td class="net-label">Net Pay</td>
                                    <td class="net-value">{{ $_fmtMoney($netSalary) }}</td>
                                </tr>
                            </table>

                            {{-- Stamp --}}
                            <div class="stamp-area">
                                @if($_stampUrl)
                                    <img src="{{ $_stampUrl }}" alt="Stamp" class="stamp-img">
                                @else
                                    <div class="stamp-circle">
                                        {{ $companyName }}<br>
                                        <span style="font-size:6px; letter-spacing:0.5px;">{{ strtoupper($slipShort) }}</span>
                                    </div>
                                @endif
                                <div class="stamp-caption">Digital Authorization</div>
                            </div>

                        </td>{{-- /right-col --}}

                    </tr>
                </table>{{-- /.two-col --}}

            </td>
        </tr>
    </table>{{-- /.outer-box --}}

    {{-- ═══ Footer ═══ --}}
    <table class="footer-bar" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:33%;">@if($companyPhone)<strong>Tel:</strong> {{ $companyPhone }}@endif</td>
            <td style="width:34%; text-align:center;">@if($companyWeb)<strong>Web:</strong> {{ $companyWeb }}@endif</td>
            <td style="width:33%; text-align:right;">@if($companyEmail)<strong>Email:</strong> {{ $companyEmail }}@endif</td>
        </tr>
    </table>

</div>{{-- /.page --}}
</body>
</html>
