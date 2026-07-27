@php
    // All template data is pre-computed from
    // App\Http\Controllers\PaySlipController::preparePayslipTemplateData().
    // Employee and Payslip objects are also passed to the view.
    // Variables match those listed in ukpdf.blade.php.
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
    @if($_showEmployeeDetails)
    <div class="emp-box">
        @if($_showName)
        <div class="emp-name">{{ $employee->name }}</div>
        @endif
        @if($_showDepartment)
        <div class="emp-dept">Department: {{ optional($employee->department)->name ?? '' }}</div>
        @endif
    </div>
    @endif

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
                        @if($_showEmployeeDetails)
                        <td class="left-col">

                            @if($_showName)
                            <div class="emp-heading">{{ $employee->name }}</div>
                            @endif

                            <table class="kv-table">
                                @if($_showPayPeriod)
                                <tr><td class="kv-label">Pay Period</td><td class="kv-value">{{ $slipShort }}</td></tr>
                                @endif
                                <tr><td class="kv-label">Pay Date</td><td class="kv-value">{{ $payDate }}</td></tr>
                                <tr><td class="kv-label">Pay Type</td><td class="kv-value">{{ $payslipTypeName }}</td></tr>
                                @if($_isUkRequest)
                                <tr><td class="kv-label">Payment Method</td><td class="kv-value">{{ $paymentMethod }}</td></tr>
                                @endif
                                <tr><td class="kv-label">Works No</td><td class="kv-value">{{ $worksNo }}</td></tr>
                                @if($_isUkRequest && $_showTaxCode)
                                <tr><td class="kv-label">Tax Code</td><td class="kv-value">{{ $taxCode }}</td></tr>
                                @endif
                                @if($_isUkRequest && $_showNiNumber)
                                <tr><td class="kv-label">NI Number</td><td class="kv-value">{{ $niNumber }}</td></tr>
                                @endif
                                @if($_isUkRequest)
                                <tr><td class="kv-label">NI Table Letter</td><td class="kv-value">{{ $niTableLetter }}</td></tr>
                                @endif
                                <tr>
                                    <td class="kv-label">Pay Status</td>
                                    <td class="kv-value {{ $isPaid ? 'status-paid' : 'status-unpaid' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</td>
                                </tr>
                            </table>

                            @if($_isUkRequest)
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
                        @else
                        <td style="display:none;"></td>
                        @endif

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
    @if($_showFooter)
    <table class="footer-bar" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:33%;">@if($companyPhone)<strong>Tel:</strong> {{ $companyPhone }}@endif</td>
            <td style="width:34%; text-align:center;">@if($companyWeb)<strong>Web:</strong> {{ $companyWeb }}@endif</td>
            <td style="width:33%; text-align:right;">@if($companyEmail)<strong>Email:</strong> {{ $companyEmail }}@endif</td>
        </tr>
    </table>
    @endif

</div>{{-- /.page --}}
</body>
</html>
