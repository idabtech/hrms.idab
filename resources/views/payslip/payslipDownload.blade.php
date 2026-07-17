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
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            background: #fff;
        }

        .page {
            padding: 14px 16px;
        }

        /* ── header ── */
        .hdr {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .hdr td {
            border: none;
            padding: 0 4px;
            vertical-align: middle;
        }

        .hdr img {
            height: 38px;
        }

        .hdr-right {
            text-align: right;
            font-size: 8.5px;
            color: #555;
            line-height: 1.7;
        }

        /* ── slip card ── */
        .slip {
            border: 2px solid {{ $_themeColor }};
        }

        /* ── slip header ── */
        .slip-hdr {
            background: {{ $_themeColor }}12;
            border-bottom: 2px solid {{ $_themeColor }};
        }

        .slip-hdr-tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .slip-hdr-tbl td {
            border: none;
            padding: 8px 12px;
            vertical-align: middle;
        }

        .slip-hdr-right {
            text-align: right;
            font-size: 8.5px;
            color: #444;
            line-height: 1.7;
        }

        .slip-hdr-tbl img {
            height: 36px;
        }

        /* ── title ── */
        .slip-title {
            background: {{ $_themeColor }};
            color: #fff;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
            padding: 7px 0;
        }

        /* ── band ── */
        .band {
            background: {{ $_themeColor }};
            color: #fff;
            font-weight: bold;
            font-size: 9px;
            text-align: center;
            padding: 4px 8px;
        }

        /* ── base table ── */
        .bt {
            width: 100%;
            border-collapse: collapse;
        }

        .bt td,
        .bt th {
            border: 1px solid {{ $_themeColor }}55;
            padding: 4px 7px;
            vertical-align: middle;
            font-size: 9px;
        }

        .bt th {
            background: {{ $_themeColor }}22;
            color: {{ $_themeColor }};
            font-weight: bold;
            text-align: center;
            font-size: 8.5px;
        }

        .bt tbody tr:nth-child(even) td {
            background: {{ $_themeColor }}08;
        }

        .lbl {
            font-weight: bold;
            color: #222;
            white-space: nowrap;
        }

        .val {
            color: #444;
        }

        .ra {
            text-align: right;
            padding-right: 7px;
        }

        /* colour helpers */
        .cg {
            color: #198754;
            font-weight: bold;
        }

        .co {
            color: #e07900;
            font-weight: bold;
        }

        .cr {
            color: #cc0000;
            font-weight: bold;
        }

        .cb {
            color: #0056b3;
            font-weight: bold;
        }

        .ct {
            color: {{ $_themeColor }};
            font-weight: bold;
        }

        /* totals */
        .tot td {
            background: {{ $_themeColor }}25;
            font-weight: bold;
            color: {{ $_themeColor }};
            border-top: 1.5px solid {{ $_themeColor }};
        }

        /* net pay */
        .net-lbl {
            background: {{ $_themeColor }}25;
            font-weight: bold;
            font-size: 11px;
            color: {{ $_themeColor }};
            text-align: center;
            border: 1px solid {{ $_themeColor }}55;
            padding: 10px 8px;
        }

        .net-val {
            font-weight: bold;
            font-size: 14px;
            color: {{ $_themeColor }};
            text-align: center;
            border: 1px solid {{ $_themeColor }}55;
            padding: 10px 8px;
        }

        /* stamp */
        .stamp {
            width: 82px;
            height: 82px;
            margin: 0 auto;
            border: 3px solid {{ $_themeColor }};
            border-radius: 50%;
            box-sizing: border-box;
            text-align: center;
            overflow: hidden;
            padding-top: 22px;
            font-size: 7px;
            font-weight: bold;
            line-height: 1.4;
            color: {{ $_themeColor }};
        }

        .stamp .label {
            font-size: 6px;
            margin-top: 3px;
            letter-spacing: 1px;
        }

        .stamp-img {
            display: block;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: contain;
            border: 3px solid {{ $_themeColor }}44;
            margin: 0 auto;
        }

        /* paid/unpaid text */
        .paid {
            color: #198754;
            font-weight: bold;
        }

        .unpaid {
            color: #cc0000;
            font-weight: bold;
        }

        /* signature */
        .sig {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .sig td {
            border: none;
            padding: 4px 10px;
            vertical-align: bottom;
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #888;
            width: 110px;
            margin: 16px auto 3px;
        }

        /* footer */
        .foot {
            background: {{ $_themeColor }};
        }

        .foot-tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .foot-tbl td {
            border: none;
            padding: 5px 10px;
            color: #fff;
            font-size: 8.5px;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="slip">

            {{-- ── Slip header ── --}}
            <div class="slip-hdr">
                <table class="slip-hdr-tbl">
                    <tr>
                        <td style="width:40%;"><img src="{{ $logoUrl }}" alt="logo"></td>
                        <td class="slip-hdr-right" style="width:60%;">
                            <strong
                                style="font-size:10.5px; color:{{ $_themeColor }};">{{ $companyName }}</strong><br>
                            {{ $companyAddr }}{{ $companyCity ? ', ' . $companyCity : '' }}<br>
                            {{ $companyState }}{{ $companyZip ? ' - ' . $companyZip : '' }}
                            @if ($companyPhone)
                                <br>{{ $companyPhone }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class="slip-title">PAY SLIP &mdash; {{ strtoupper($slipLabel) }}</div>

            {{-- ══ A: Employee | Payment ══ --}}
            @if($_showEmployeeDetails || $_showPaymentDetails)
            <table class="bt" style="border:none;">
                <tr>
                    @if($_showEmployeeDetails)
                    <td style="width:{{ $_showPaymentDetails ? '50%' : '100%' }}; padding:0; border:none; vertical-align:top;">
                        <div class="band">Employee Details</div>
                        <table class="bt">
                            @if($_showName)
                            <tr>
                                <td class="lbl" style="width:45%;">Name</td>
                                <td class="val">{{ $employee->name }}</td>
                            </tr>
                            @endif
                            @if($_showDesignation)
                            <tr>
                                <td class="lbl">Designation</td>
                                <td class="val">{{ optional($employee->designation)->name ?? '—' }}</td>
                            </tr>
                            @endif
                            @if($_showEmployeeId)
                            <tr>
                                <td class="lbl">Employee ID</td>
                                <td class="val">{{ $employee->employee_id ?? '—' }}</td>
                            </tr>
                            @endif
                            @if($_showDepartment)
                            <tr>
                                <td class="lbl">Department</td>
                                <td class="val">{{ optional($employee->department)->name ?? '—' }}</td>
                            </tr>
                            @endif
                            @if($_showPanNo)
                            <tr>
                                <td class="lbl">PAN No.</td>
                                <td class="val">{{ $employee->tax_payer_id ?? '—' }}</td>
                            </tr>
                            @endif
                            @if($_showDateOfJoining)
                            <tr>
                                <td class="lbl">Date of Joining</td>
                                <td class="val">{{ $employee->company_doj ?? '—' }}</td>
                            </tr>
                            @endif
                        </table>
                    </td>
                    @endif
                    @if($_showPaymentDetails)
                    <td style="width:{{ $_showEmployeeDetails ? '50%' : '100%' }}; padding:0; border-left:1px solid {{ $_themeColor }}55; vertical-align:top;">
                        <div class="band">Payment Details</div>
                        <table class="bt">
                            @if($_showBankName)
                            <tr>
                                <td class="lbl" style="width:45%;">Bank Name</td>
                                <td class="val">{{ $employee->bank_name ?? '—' }}</td>
                            </tr>
                            @endif
                            @if($_showAccountNo)
                            <tr>
                                <td class="lbl">Account No.</td>
                                <td class="val">{{ $employee->account_number ?? '—' }}</td>
                            </tr>
                            @endif
                            @if($_showBankCode)
                            <tr>
                                <td class="lbl">{{ \App\Models\Utility::bankCodeLabel() }}</td>
                                <td class="val">{{ $employee->bank_identifier_code ?? '—' }}</td>
                            </tr>
                            @endif
                            @if($_showAccountHolder)
                            <tr>
                                <td class="lbl">Account Holder</td>
                                <td class="val">{{ $employee->account_holder_name ?? '—' }}</td>
                            </tr>
                            @endif
                            @if($_showTransactionMode)
                            <tr>
                                <td class="lbl">Transaction</td>
                                <td class="val">NEFT</td>
                            </tr>
                            @endif
                            @if($_showPayPeriod)
                            <tr>
                                <td class="lbl">Pay Period</td>
                                <td class="val">{{ $slipLabel }}</td>
                            </tr>
                            @endif
                        </table>
                    </td>
                    @endif
                </tr>
            </table>
            @endif

            {{-- ══ B: Leave | Days & Work | Salary ══ --}}
            <table class="bt" style="border:none; border-top:1px solid {{ $_themeColor }}55;">
                <tr>
                    @if (!$isHourly)
                        {{-- ── MONTHLY: show Leave Details + Days & Work ── --}}
                        <td style="width:34%; padding:0; border:none; vertical-align:top;">
                            <div class="band">Leave Details</div>
                            <table class="bt">
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
                            <div class="band">Days &amp; Work Details</div>
                            <table class="bt">
                                <tr>
                                    <td class="lbl" style="width:58%;">Working Days</td>
                                    <td class="ra ct">{{ $officeDays }}</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Present Days</td>
                                    <td class="ra cg">{{ $presentDaysFmt }}</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Leave Days</td>
                                    <td class="ra co">{{ $approvedLeaves }}</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Absent Days</td>
                                    <td class="ra cr">{{ $absentDaysFmt }}</td>
                                </tr>
                                @if ($extraDays > 0)
                                    <tr>
                                        <td class="lbl" style="color:#856404;">Extra / OT Days</td>
                                        <td class="ra" style="color:#856404; font-weight:bold;">
                                            +{{ $extraDaysFmt }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="lbl">Total Hrs Worked</td>
                                    <td class="ra val">{{ $totalWorkHours }} h</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Avg Hrs / Day</td>
                                    <td class="ra val">{{ $avgHrsPerDay }} h</td>
                                </tr>
                            </table>
                        </td>
                    @endif

                    {{-- ── Salary Details (full width for hourly, 38% for monthly) ── --}}
                    <td style="width:{{ $isHourly ? '100%' : '38%' }}; padding:0; vertical-align:top; border:none;">
                        <div class="band">Salary Details</div>
                        <table class="bt">
                            <tr>
                                <td class="lbl" style="width:50%;">Payslip Type</td>
                                <td class="ra val" style="width:50%;">{{ $payslipTypeName }}</td>
                            </tr>

                            @if ($isHourly)
                                {{-- ── HOURLY: clean rate × hours calculation ── --}}
                                <tr>
                                    <td class="lbl" style="width:50%;">Hourly Rate ( <b class="ct">A</b> )</td>
                                    <td class="ra ct" style="width:50%;">{{ $_fmtMoney($perHourSalary) }}</td>
                                </tr>
                                <tr>
                                    <td class="lbl" style="width:50%;">Hours Worked ( <b class="cg">B</b> )</td>
                                    <td class="ra cg" style="width:50%;">{{ $totalWorkHours }} hrs</td>
                                </tr>
                                <tr style="background:{{ $_themeColor }}10;">
                                    <td class="lbl" style="width:50%;"><strong>Gross Earned</strong> ( <b
                                            class="ct">A</b> &times; <b class="ct">B</b> )</td>
                                    <td class="ra ct" style="width:50%;">
                                        <strong>{{ $_fmtMoney($_storedSalary) }}</strong>
                                    </td>
                                </tr>
                            @else
                                {{-- ── MONTHLY: standard rows ── --}}
                                <tr>
                                    <td class="lbl">Basic Salary</td>
                                    <td class="ra ct">{{ $_fmtMoney($_storedSalary) }}</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Per Day Rate</td>
                                    <td class="ra val">{{ $_fmtMoney($perDaySalary) }}</td>
                                </tr>
                                <tr>
                                    <td class="lbl">Per Hour Rate</td>
                                    <td class="ra val">{{ $_fmtMoney($perHourSalary) }}</td>
                                </tr>
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
                                <td class="lbl">Pay Period</td>
                                <td class="ra val" style="font-size:8.5px;">{{ $slipLabel }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Pay Status</td>
                                <td class="ra val {{ $isPaid ? 'paid' : 'unpaid' }}">
                                    {{ $isPaid ? 'Paid' : 'Unpaid' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            {{-- ══ C: Earnings | Deductions ══ --}}
            <table class="bt" style="border:none; border-top:1px solid {{ $_themeColor }}55;">
                <tr>
                    <td style="width:50%; padding:0; border:none; vertical-align:top;">
                        <div class="band">Earnings</div>
                        <table class="bt">
                            <tr>
                                <th style="text-align:left; padding-left:7px; width:65%;">Description</th>
                                <th class="ra">Amount (INR)</th>
                            </tr>
                            @foreach ($earRows as $er)
                                <tr>
                                    <td style="padding-left:7px;">{{ $er['label'] }}</td>
                                    <td class="ra">
                                        @if ($er['amount'] !== null)
                                            {{ $_fmtMoney($er['amount']) }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="tot">
                                <td style="padding-left:7px;"><strong>Total Earnings</strong></td>
                                <td class="ra"><strong>{{ $_fmtMoney($totalEarnings) }}</strong></td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:50%; padding:0; border-left:1px solid #b2dee2; vertical-align:top;">
                        <div class="band">Deductions</div>
                        <table class="bt">
                            <tr>
                                <th style="text-align:left; padding-left:7px; width:65%;">Description</th>
                                <th class="ra">Amount (INR)</th>
                            </tr>
                            @foreach ($dedRows as $dr2)
                                <tr>
                                    <td style="padding-left:7px;">{{ $dr2['label'] }}</td>
                                    <td class="ra">
                                        @if ($dr2['amount'] !== null)
                                            {{ $_fmtMoney($dr2['amount']) }}
                                        @elseif(($dr2['label'] ?? '') !== '')
                                            &mdash;
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="tot">
                                <td style="padding-left:7px;"><strong>Total Deductions</strong></td>
                                <td class="ra"><strong>{{ $_fmtMoney($totalDeductions) }}</strong></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- ══ D: Stamp | Net Pay ══ --}}
            <table class="bt" style="border:none; border-top:1px solid #b2dee2;">
                <tr>
                    <td
                        style="width:50%; text-align:center; padding:14px 8px; border:1px solid #b2dee2; vertical-align:middle;">
                        @if ($_stampUrl)
                            <img src="{{ $_stampUrl }}" alt="Stamp" class="stamp-img">
                        @else
                            <div class="stamp">
                                {{ $companyName }}<br>
                                <span
                                    style="font-size:6px; display:block; margin-top:3px; letter-spacing:0.8px;">{{ strtoupper($slipLabel) }}</span>
                            </div>
                        @endif
                        <div style="font-size:7.5px; color:#888; margin-top:4px;">Digital Authorization</div>
                    </td>
                    <td style="width:50%; padding:0; border:none; vertical-align:middle;">
                        <table class="bt" style="border:none;">
                            <tr>
                                <td class="net-lbl" style="width:50%;">Net Pay</td>
                                <td class="net-val" style="width:50%;">{{ $_fmtMoney($netSalary) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- ══ E: Signatures ══ --}}
            @if($_showSignatures)
            <table class="sig">
                <tr>
                    <td style="width:50%;">
                        <div class="sig-line"></div>
                        <div style="font-size:8px; color:#666;">Employee Signature</div>
                    </td>
                    <td style="width:50%;">
                        <div class="sig-line"></div>
                        <div style="font-size:8px; color:#666;">Authorized Signatory</div>
                    </td>
                </tr>
            </table>
            @endif

            {{-- ══ Footer ══ --}}
            @if($_showFooter)
            <div class="foot" style="margin-top:10px;">
                <table class="foot-tbl">
                    <tr>
                        <td style="width:33%;">
                            @if ($companyPhone)
                                <strong>Tel:</strong> {{ $companyPhone }}
                            @endif
                        </td>
                        <td style="width:34%; text-align:center;">
                            @if ($companyWeb)
                                <strong>Web:</strong> {{ $companyWeb }}
                            @endif
                        </td>
                        <td style="width:33%; text-align:right;">
                            @if ($companyEmail)
                                <strong>Email:</strong> {{ $companyEmail }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            @endif

        </div>{{-- /.slip --}}
    </div>{{-- /.page --}}
</body>

</html>
