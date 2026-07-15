@php
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;

    [$slipYear, $slipMonth] = explode('-', $payslip->salary_month);

    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $company_logo = \App\Models\Utility::get_company_logo();
    $logoUrl = $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png');
    $downloadUrl = route('payslip.download', [$employee->id, $payslip->salary_month]);

    // ── Theme color ───────────────────────────────────────────────────────────
    // theme_color holds either a named theme ('theme-1' … 'theme-10') or a custom
    // hex directly (e.g. '#51459d') when the user picks a custom color.
    $_colorSetting = \App\Models\Utility::colorset();
    $_themeName = $_colorSetting['theme_color'] ?? 'theme-2';
    $_themeHexMap = [
        'theme-1' => '#0CAF60',
        'theme-2' => '#584ED2',
        'theme-3' => '#6FD943',
        'theme-4' => '#145388',
        'theme-5' => '#B9406B',
        'theme-6' => '#008ECC',
        'theme-7' => '#922C88',
        'theme-8' => '#C0A145',
        'theme-9' => '#48494B',
        'theme-10' => '#0C7785',
    ];
    // If theme_color starts with '#' it is already a hex (custom color)
    $_themeColor = str_starts_with($_themeName, '#') ? $_themeName : $_themeHexMap[$_themeName] ?? '#584ED2';
    // dd($payslipDetail);

    // ── Currency ──────────────────────────────────────────────────────────────
    $_appSettings = \App\Models\Utility::settings();
    $_currSymbol = $_appSettings['site_currency_symbol'] ?? '';
    $_currPos = $_appSettings['site_currency_symbol_position'] ?? 'pre';
    $_fmtMoney = function ($amount) use ($_currSymbol, $_currPos) {
        $n = number_format((float) $amount, 2);
        return $_currPos === 'pre' ? $_currSymbol . $n : $n . $_currSymbol;
    };

    // ── Month ──────────────────────────────────────────────────────────────────
    $carbonMonth = Carbon::createFromDate((int) $slipYear, (int) $slipMonth, 1);
    $slipLabel = $carbonMonth->format('F Y');

    // ── All display values come directly from Utility::employeePayslipDetail ──
    // No duplicate calculation here — single source of truth.
    $_storedSalary = (float) $payslipDetail['basic_salary'];
    $_netSalary = (float) $payslipDetail['net_salary'];
    $_salary = (float) $payslipDetail['salary'];
    $_hra = (float) $payslipDetail['hra'];
    $_da = (float) $payslipDetail['da'];
    $perDaySalary = round((float) $payslipDetail['per_day_amount'], 2);
    $perHourSalary = round((float) $payslipDetail['per_hour_amount'], 2);
    $officeDays = (int) $payslipDetail['office_days'];
    $presentDays = $payslipDetail['present_days'];
    $approvedLeaves = $payslipDetail['approved_leaves_month'];
    $disapprovedLeaves = (int) $payslipDetail['rejected_leaves_month'];
    $absentDays = $payslipDetail['absent_days'];
    $extraDays = $payslipDetail['extra_days'];
    $totalWorkHours = $payslipDetail['total_work_hours'];
    $avgHrsPerDay = $payslipDetail['avg_hrs_per_day'];
    $totalLeaveAlloc = (int) $payslipDetail['total_leave_alloc'];
    $remainingLeaves = (int) $payslipDetail['remaining_leaves'];
    $usedLeaves = $approvedLeaves;
    $paidLeaveDays = $payslipDetail['paid_leave_days'] ?? 0;
    $unpaidLeaveDays = $payslipDetail['unpaid_leave_days'] ?? 0;
    $unpaidLeaveDeduction = $payslipDetail['unpaid_leave_deduction'] ?? 0;
    $daysPaid = $payslipDetail['days_paid'] ?? $presentDays + $paidLeaveDays;
    $leaveBreakdown = $payslipDetail['leave_breakdown'] ?? [];

    // Hourly flag — drives conditional display in Salary Details section
    $isHourly = (bool) ($payslipDetail['is_hourly'] ?? false);
    $hoursPerDay = (float) ($payslipDetail['hours_per_day'] ?? 8);
    $totalShiftHours = (float) ($payslipDetail['total_shift_hours'] ?? $officeDays * $hoursPerDay);
    $salaryRate = (float) ($payslipDetail['salary_rate'] ?? $_storedSalary);

    // Format for display
    $presentDisplay = min($presentDays, $officeDays);
    $presentDaysFmt = $presentDisplay == floor($presentDisplay) ? (int) $presentDisplay : $presentDisplay;
    $absentDaysFmt = $absentDays == floor($absentDays) ? (int) $absentDays : $absentDays;
    $extraDaysFmt = $extraDays == floor($extraDays) ? (int) $extraDays : $extraDays;

    // Payslip type label
    $payslipTypeName = \App\Models\PayslipType::find($employee->salary_type)?->name ?? 'Monthly';

    // ── Earnings ──────────────────────────────────────────────────────────────
    $_basicLabel = $isHourly ? __('Gross Earned') : __('Basic Salary');
    $earRows = [
        ['label' => $_basicLabel, 'amount' => $_salary],
    ];
    // HRA and DA are monthly-only components — skip for hourly (they will be 0)
    if ($_hra > 0) {
        $earRows[] = ['label' => 'HRA', 'amount' => $_hra];
    }
    if ($_da > 0) {
        $earRows[] = ['label' => 'DA', 'amount' => $_da];
    }
    foreach ($payslipDetail['earning']['allowance'] as $_ar) {
        foreach (json_decode($_ar->allowance) as $_a) {
            $earRows[] = [
                'label' => $_a->title,
                'amount' =>
                    $_a->type === 'percentage' ? round(($_a->amount * $_storedSalary) / 100, 2) : (float) $_a->amount,
            ];
        }
    }
    foreach ($payslipDetail['earning']['commission'] as $_cr) {
        foreach (json_decode($_cr->commission) as $_c) {
            $earRows[] = [
                'label' => $_c->title,
                'amount' =>
                    $_c->type === 'percentage' ? round(($_c->amount * $_storedSalary) / 100, 2) : (float) $_c->amount,
            ];
        }
    }
    foreach ($payslipDetail['earning']['bonous'] as $_b) {
        $earRows[] = [
            'label' => $_b->title ?: __('Bonus'),
            'amount' =>
                $_b->type === 'percentage' ? round(($_b->amount * $employee->salary) / 100, 2) : (float) $_b->amount,
        ];
    }
    foreach ($payslipDetail['earning']['otherPayment'] as $_op2) {
        foreach (json_decode($_op2->other_payment) as $_op) {
            $earRows[] = [
                'label' => $_op->title,
                'amount' =>
                    $_op->type === 'percentage'
                        ? round(($_op->amount * $_storedSalary) / 100, 2)
                        : (float) $_op->amount,
            ];
        }
    }
    // foreach ($payslipDetail['earning']['overTime'] as $_ot2) {
    //     foreach (json_decode($_ot2->overtime) as $_ot) {
    //         $earRows[] = ['label' => $_ot->title ?: __('Overtime'), 'amount' => (float)($_ot->number_of_days * $_ot->hours * $_ot->rate)];
    //     }
    // }
    // Extra days are informational only — not added to earnings

    // Loan/Advance (earning — money given to employee)
    foreach ($payslipDetail['earning']['loan'] as $_lr) {
        foreach (json_decode($_lr->loan) as $_ln) {
            $_amt =
                $_ln->type === 'percentage'
                    ? round(($_ln->amount * $_lr->basic_salary) / 100, 2)
                    : (float) $_ln->amount;
            if ($_amt > 0) {
                $earRows[] = ['label' => $_ln->title ?: __('Loan/Advance'), 'amount' => $_amt];
            }
        }
    }

    // ── Deductions ────────────────────────────────────────────────────────────
    $dedRows = [];

    // Loan Repayment (deduction — money employee pays back)
    $totalLoanRepayment = $payslipDetail['totalLoanRepayment'] ?? 0;
    if ($totalLoanRepayment > 0) {
        $dedRows[] = ['label' => __('Loan Repayment'), 'amount' => $totalLoanRepayment];
    }

    foreach ($payslipDetail['deduction']['saturation_deduction'] as $_dr2) {
        foreach (json_decode($_dr2->saturation_deduction) as $_dd) {
            if (strtolower($_dd->title) == 'epf') {
                $_amt = (($employee->get_net_da() + $_dr2->net_salary) * 12) / 100;
            } elseif (strtolower($_dd->title) == 'gpf') {
                $_amt = (($employee->get_net_da() + $_dr2->net_salary) * 6) / 100;
            } else {
                $_amt =
                    $_dd->type === 'percentage'
                        ? round(($_dd->amount * $_dr2->basic_salary) / 100, 2)
                        : (float) $_dd->amount;
            }
            // $_amt =
            //     $_dd->type === 'percentage'
            //         ? round(($_dd->amount * $_dr2->basic_salary) / 100, 2)
            //         : (float) $_dd->amount;
            if ($_amt > 0) {
                $dedRows[] = ['label' => $_dd->title, 'amount' => $_amt];
            }
        }
    }

    foreach ($payslipDetail['deduction']['pansion'] as $_p) {
        $_amt = $_p->type === 'percentage' ? round(($_p->amount * $employee->salary) / 100, 2) : (float) $_p->amount;
        if ($_amt > 0) {
            $dedRows[] = ['label' => $_p->title ?: __('Provident Fund'), 'amount' => $_amt];
        }
    }
    // ── LOP (Loss of Pay) — absent days deduction ────────────────────────────
    // Only shown for monthly employees; hourly employees never have LOP (zeroed in Utility).
    foreach ($payslipDetail['deduction']['leave'] as $_lea) {
        if ($_lea->empleave > 0) {
            $lopDaysLabel = is_numeric($payslipDetail['lop_days'] ?? null)
                ? ' (' . $payslipDetail['lop_days'] . ' ' . __('days') . ')'
                : '';
            $dedRows[] = ['label' => __('Loss Of Pay') . $lopDaysLabel, 'amount' => (float) $_lea->empleave];
        }
    }
    // Unpaid leave deduction — shown as a separate line when > 0
    if ($unpaidLeaveDeduction > 0) {
        $dedRows[] = [
            'label'  => __('Unpaid Leave') . ' (' . $unpaidLeaveDays . ' ' . __('days') . ')',
            'amount' => $unpaidLeaveDeduction,
        ];
    }

    // No extra day payment added — extra days are informational only
    $totalDeductions = $payslipDetail['totalDeduction'];
    $amt = $_salary - $totalDeductions;
    $totalEarnings = $payslipDetail['totalEarning'] + $_salary;
    $netSalary = max(0, $payslipDetail['totalEarning'] + $amt);

    $_maxED = max(count($earRows), count($dedRows));
    while (count($earRows) < $_maxED) {
        $earRows[] = ['label' => '', 'amount' => null];
    }
    while (count($dedRows) < $_maxED) {
        $dedRows[] = ['label' => '', 'amount' => null];
    }

    // ── Company ───────────────────────────────────────────────────────────────
    $companyName = \App\Models\Utility::getValByName('company_name') ?? '';
    $companyAddr = \App\Models\Utility::getValByName('company_address') ?? '';
    $companyCity = \App\Models\Utility::getValByName('company_city') ?? '';
    $companyState = \App\Models\Utility::getValByName('company_state') ?? '';
    $companyZip = \App\Models\Utility::getValByName('company_zipcode') ?? '';
    $companyPhone = \App\Models\Utility::getValByName('company_telephone') ?? '';
    $companyEmail = \App\Models\Utility::getValByName('company_email') ?? '';
    $companyWeb = \App\Models\Utility::getValByName('company_website') ?? '';
    $isPaid = $payslip->status == 1;

    // ── Stamp ──────────────────────────────────────────────────────────────────
    $_stampFile = \App\Models\Utility::getValByName('company_stamp');
    $_stampUrl =
        $_stampFile && !empty($_stampFile) ? \App\Models\Utility::get_file('uploads/logo/') . '/' . $_stampFile : null;
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
            <table class="bt" style="border:none;">
                <tr>
                    <td style="width:50%; padding:0; border:none; vertical-align:top;">
                        <div class="band">Employee Details</div>
                        <table class="bt">
                            <tr>
                                <td class="lbl" style="width:45%;">Name</td>
                                <td class="val">{{ $employee->name }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Designation</td>
                                <td class="val">{{ optional($employee->designation)->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Employee ID</td>
                                <td class="val">{{ $employee->employee_id ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Department</td>
                                <td class="val">{{ optional($employee->department)->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">PAN No.</td>
                                <td class="val">{{ $employee->tax_payer_id ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Date of Joining</td>
                                <td class="val">{{ $employee->company_doj ?? '—' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:50%; padding:0; border-left:1px solid {{ $_themeColor }}55; vertical-align:top;">
                        <div class="band">Payment Details</div>
                        <table class="bt">
                            <tr>
                                <td class="lbl" style="width:45%;">Bank Name</td>
                                <td class="val">{{ $employee->bank_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Account No.</td>
                                <td class="val">{{ $employee->account_number ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">{{ \App\Models\Utility::bankCodeLabel() }}</td>
                                <td class="val">{{ $employee->bank_identifier_code ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Account Holder</td>
                                <td class="val">{{ $employee->account_holder_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Transaction</td>
                                <td class="val">NEFT</td>
                            </tr>
                            <tr>
                                <td class="lbl">Pay Period</td>
                                <td class="val">{{ $slipLabel }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

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

            {{-- ══ Footer ══ --}}
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

        </div>{{-- /.slip --}}
    </div>{{-- /.page --}}
</body>

</html>
