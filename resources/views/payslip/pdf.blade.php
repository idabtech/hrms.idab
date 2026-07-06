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

    // Hourly flag — drives conditional display in Salary Details section
    $isHourly = (bool) ($payslipDetail['is_hourly'] ?? false);
    $hoursPerDay = (float) ($payslipDetail['hours_per_day'] ?? 8);
    $totalShiftHours = (float) ($payslipDetail['total_shift_hours'] ?? $officeDays * $hoursPerDay);
    $salaryRate = (float) ($payslipDetail['salary_rate'] ?? $_storedSalary); // raw stored amount

    // Format for display
    $presentDisplay = min($presentDays, $officeDays);
    $presentDaysFmt = $presentDisplay == floor($presentDisplay) ? (int) $presentDisplay : $presentDisplay;
    $absentDaysFmt = $absentDays == floor($absentDays) ? (int) $absentDays : $absentDays;
    $extraDaysFmt = $extraDays == floor($extraDays) ? (int) $extraDays : $extraDays;

    // Payslip type label
    $payslipTypeName = \App\Models\PayslipType::find($employee->salary_type)?->name ?? 'Monthly';

    // ── Earnings ──────────────────────────────────────────────────────────────
    $_basicLabel = $isHourly ? __('Gross Earned') : __('Basic Salary');
    // $earRows = [['label' => $_basicLabel, 'amount' => $_storedSalary]];
    $earRows = [
        ['label' => $_basicLabel, 'amount' => $_salary],
        ['label' => 'HRA', 'amount' => $_hra],
        ['label' => 'DA', 'amount' => $_da],
    ];

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
    //         $earRows[] = [
    //             'label' => $_ot->title ?: __('Overtime'),
    //             'amount' => (float) ($_ot->number_of_days * $_ot->hours * $_ot->rate),
    //         ];
    //     }
    // }
    // Extra days are informational only — not added to earnings
    // ── Deductions ────────────────────────────────────────────────────────────
    $dedRows = [];
    foreach ($payslipDetail['deduction']['loan'] as $_lr) {
        foreach (json_decode($_lr->loan) as $_ln) {
            $_amt =
                $_ln->type === 'percentage'
                    ? round(($_ln->amount * $_lr->basic_salary) / 100, 2)
                    : (float) $_ln->amount;
            if ($_amt > 0) {
                $dedRows[] = ['label' => $_ln->title ?: __('Loan'), 'amount' => $_amt];
            }
        }
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
    // foreach ($payslipDetail['deduction']['leave'] as $_lea) {
    //     if ($_lea->empleave > 0) {
    //         $dedRows[] = ['label' => __('Loss Of Pay'), 'amount' => (float) $_lea->empleave];
    //     }
    // }
    // // Unpaid leave deduction — shown as a separate line
    // if ($unpaidLeaveDeduction > 0) {
    //     $dedRows[] = [
    //         'label' => __('Unpaid Leave') . ' (' . $unpaidLeaveDays . ' ' . __('days') . ')',
    //         'amount' => $unpaidLeaveDeduction,
    //     ];
    // }

    // No extra day payment added — extra days are informational only
    $totalDeductions = $payslipDetail['totalDeduction'];
    $amt = $_salary - $totalDeductions;
    $totalEarnings = $payslipDetail['totalEarning'] + $_salary;
    $netSalary = $payslipDetail['totalEarning'] + $amt;

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

    <div class="ps-bar">
        <a href="{{ $downloadUrl }}" class="btn btn-sm btn-primary">
            <i class="fa fa-download me-1"></i>{{ __('Download PDF') }}
        </a>
        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
            <button type="button" class="btn btn-sm btn-warning"
                onclick="payslipEmailSend({{ $employee->id }},'{{ $payslip->salary_month }}')">
                <i class="fa fa-paper-plane me-1"></i>{{ __('Send Email') }}
            </button>
        @endif
    </div>

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
        <table class="ps-t" style="border:none;">
            <tr>
                <td style="width:50%; padding:0; border-left:1px solid {{ $_themeColor }}55; vertical-align:top;">
                    <div class="ps-band">{{ __('Employee Details') }}</div>
                    <table class="ps-t">
                        <tr>
                            <td class="lbl">{{ __('Name') }}</td>
                            <td class="val">{{ $employee->name }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __('Designation') }}</td>
                            <td class="val">{{ optional($employee->designation)->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __('Employee ID') }}</td>
                            <td class="val">{{ $employee->employee_id ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __('Department') }}</td>
                            <td class="val">{{ optional($employee->department)->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __('PAN No.') }}</td>
                            <td class="val">{{ $employee->tax_payer_id ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __('Date of Joining') }}</td>
                            <td class="val">{{ $employee->company_doj ?? '—' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width:50%; padding:0; border-left:1px solid {{ $_themeColor }}55; vertical-align:top;">
                    <div class="ps-band">{{ __('Payment Details') }}</div>
                    <table class="ps-t">
                        <tr>
                            <td class="lbl">{{ __('Bank Name') }}</td>
                            <td class="val">{{ $employee->bank_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __('Account No.') }}</td>
                            <td class="val">{{ $employee->account_number ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __(\App\Models\Utility::bankCodeLabel()) }}</td>
                            <td class="val">{{ $employee->bank_identifier_code ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __('Account Holder') }}</td>
                            <td class="val">{{ $employee->account_holder_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __('Transaction Mode') }}</td>
                            <td class="val">NEFT</td>
                        </tr>
                        <tr>
                            <td class="lbl">{{ __('Pay Period') }}</td>
                            <td class="val">{{ $slipLabel }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

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
                                <td>Remaining: <strong class="cb">{{ $remainingLeaves }}</strong></td>
                            </tr>
                            <tr>
                                <td><span class="unpaid">Unpaid: {{ $unpaidLeaveDays }}</span></td>
                                <td>Rejected: <strong class="cr">{{ $disapprovedLeaves }}</strong></td>
                            </tr>
                        </table>
                    </td>
                    <td
                        style="width:28%; padding:0; border-left:1px solid {{ $_themeColor }}55; border-right:1px solid {{ $_themeColor }}55; vertical-align:top;">
                        <div class="ps-band">{{ __('Days & Work Details') }}</div>
                        <table class="ps-t">
                            <tr>
                                <td class="lbl" style="width:60%;">{{ __('Working Days') }}</td>
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
                        @foreach ($earRows as $er)
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
                        @foreach ($dedRows as $dr2)
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
                    @if ($_stampUrl)
                        <img src="{{ $_stampUrl }}" alt="Stamp" class="ps-stamp-img">
                    @else
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

        {{-- Footer --}}
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

    </div>{{-- /.ps-card --}}
</div>{{-- /.ps-modal --}}

<script>
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
