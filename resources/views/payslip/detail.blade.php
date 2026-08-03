@php
    $netSalary            = (float) ($payslipDetail['net_salary'] ?? 0);
    $basicSalary          = (float) ($payslipDetail['salary'] ?? 0);       // basic salary (earnings base)
    $grossSalary          = (float) ($employee->salary ?? 0);              // gross / CTC
    $_basicSalary         = (float) ($employee->basic_salary ?? 0);        // for % calculations
    $totalEarning         = (float) ($payslipDetail['totalEarning'] ?? 0);
    $totalDeductions      = (float) ($payslipDetail['totalDeduction'] ?? 0);
    $totalLoanRepayment   = (float) ($payslipDetail['totalLoanRepayment'] ?? 0);
    $totalPension         = (float) ($payslipDetail['totalPansion'] ?? 0);
    $totalSatDeduction    = (float) ($payslipDetail['total_saturation_deduction'] ?? 0);
    $isHourly             = (bool)  ($payslipDetail['is_hourly'] ?? false);
    $unpaidLeaveDeduction = (float) ($payslipDetail['unpaid_leave_deduction'] ?? 0);
    $unpaidLeaveDays      = (float) ($payslipDetail['unpaid_leave_days'] ?? 0);
    $lopDays              = (float) ($payslipDetail['lop_days'] ?? 0);

    // ── Build individual earning rows (same logic as pdf.blade.php) ──────────
    $earRows = [];

    // Allowances
    foreach ($payslipDetail['earning']['allowance'] as $_ar) {
        foreach (json_decode($_ar->allowance) as $_a) {
            $earRows[] = [
                'label'  => $_a->title,
                'amount' => $_a->type === 'percentage'
                    ? round(($_a->amount * $_basicSalary) / 100, 2)
                    : (float) $_a->amount,
                'pct'    => $_a->type === 'percentage' ? $_a->amount : null,
            ];
        }
    }
    // Commissions
    foreach ($payslipDetail['earning']['commission'] as $_cr) {
        foreach (json_decode($_cr->commission) as $_c) {
            $earRows[] = [
                'label'  => $_c->title,
                'amount' => $_c->type === 'percentage'
                    ? round(($_c->amount * $_basicSalary) / 100, 2)
                    : (float) $_c->amount,
                'pct'    => $_c->type === 'percentage' ? $_c->amount : null,
            ];
        }
    }
    // Bonuses
    foreach ($payslipDetail['earning']['bonous'] as $_b) {
        $_bAmt = $_b->type === 'percentage'
            ? round(($_b->amount * $_basicSalary) / 100, 2)
            : (float) $_b->amount;
        if ($_bAmt > 0) {
            $earRows[] = [
                'label'  => $_b->title ?: __('Bonus'),
                'amount' => $_bAmt,
                'pct'    => $_b->type === 'percentage' ? $_b->amount : null,
            ];
        }
    }
    // Other payments
    foreach ($payslipDetail['earning']['otherPayment'] as $_op2) {
        foreach (json_decode($_op2->other_payment) as $_op) {
            $earRows[] = [
                'label'  => $_op->title,
                'amount' => $_op->type === 'percentage'
                    ? round(($_op->amount * $_basicSalary) / 100, 2)
                    : (float) $_op->amount,
                'pct'    => $_op->type === 'percentage' ? $_op->amount : null,
            ];
        }
    }
    // Loan/Advance (earning)
    foreach ($payslipDetail['earning']['loan'] as $_lr) {
        foreach (json_decode($_lr->loan) as $_ln) {
            $_lnAmt = $_ln->type === 'percentage'
                ? round(($_ln->amount * $_basicSalary) / 100, 2)
                : (float) $_ln->amount;
            if ($_lnAmt > 0) {
                $earRows[] = [
                    'label'  => $_ln->title ?: __('Loan/Advance'),
                    'amount' => $_lnAmt,
                    'pct'    => $_ln->type === 'percentage' ? $_ln->amount : null,
                ];
            }
        }
    }

    // ── Build individual deduction rows ──────────────────────────────────────
    $dedRows = [];

    // Loan repayment
    if ($totalLoanRepayment > 0) {
        $dedRows[] = ['label' => __('Loan Repayment'), 'amount' => $totalLoanRepayment, 'pct' => null];
    }
    // Statutory deductions (individual)
    foreach ($payslipDetail['deduction']['saturation_deduction'] as $_dr2) {
        foreach (json_decode($_dr2->saturation_deduction) as $_dd) {
            if (strtolower($_dd->title) == 'epf') {
                $_dAmt = ($_basicSalary * 12) / 100;
            } elseif (strtolower($_dd->title) == 'gpf') {
                $_dAmt = ($_basicSalary * 6) / 100;
            } else {
                $_dAmt = $_dd->type === 'percentage'
                    ? round(($_dd->amount * $_basicSalary) / 100, 2)
                    : (float) $_dd->amount;
            }
            if ($_dAmt > 0) {
                $dedRows[] = [
                    'label'  => $_dd->title,
                    'amount' => $_dAmt,
                    'pct'    => $_dd->type === 'percentage' ? $_dd->amount : null,
                ];
            }
        }
    }
    // Pension (individual)
    foreach ($payslipDetail['deduction']['pansion'] as $_p) {
        $_pAmt = $_p->type === 'percentage'
            ? round(($_p->amount * $_basicSalary) / 100, 2)
            : (float) $_p->amount;
        if ($_pAmt > 0) {
            $dedRows[] = [
                'label'  => $_p->title ?: __('Pension'),
                'amount' => $_pAmt,
                'pct'    => $_p->type === 'percentage' ? $_p->amount : null,
            ];
        }
    }
    // Unpaid leave / Loss of Pay (prevent duplicate rows)
    if ($unpaidLeaveDeduction > 0) {
        $dedRows[] = [
            'label'  => __('Unpaid Leave') . ' (' . ($unpaidLeaveDays == floor($unpaidLeaveDays) ? (int)$unpaidLeaveDays : $unpaidLeaveDays) . ' ' . __('days') . ')',
            'amount' => $unpaidLeaveDeduction,
            'pct'    => null,
        ];
    } else {
        foreach ($payslipDetail['deduction']['leave'] as $_lea) {
            if ($_lea->empleave > 0) {
                $dedRows[] = [
                    'label'  => __('Loss of Pay') . ($lopDays > 0 ? ' (' . ($lopDays == floor($lopDays) ? (int)$lopDays : $lopDays) . ' ' . __('days') . ')' : ''),
                    'amount' => (float) $_lea->empleave,
                    'pct'    => null,
                ];
            }
        }
    }

    $totalEarningsDisplay = $basicSalary;
    foreach ($earRows as $_er) {
        if (!empty($_er['amount']) && $_er['amount'] > 0) {
            $totalEarningsDisplay += (float) $_er['amount'];
        }
    }

    $totalDeductions = 0;
    foreach ($dedRows as $_dr) {
        if (!empty($_dr['amount']) && $_dr['amount'] > 0) {
            $totalDeductions += (float) $_dr['amount'];
        }
    }

    $netSalary = max(0, $totalEarningsDisplay - $totalDeductions);
@endphp

<div class="modal-body px-4">

    {{-- Header: employee + month --}}
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <div class="text-muted small">{{ __('Employee') }}</div>
            <div class="fw-semibold">{{ $employee->name }}</div>
        </div>
        <div class="text-end">
            <div class="text-muted small">{{ __('Pay Month') }}</div>
            <div class="fw-semibold">{{ \Carbon\Carbon::parse($payslip->salary_month . '-01')->format('F Y') }}</div>
        </div>
    </div>

    {{-- Salary summary cards --}}
    @if (!$isHourly)
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="rounded p-2 text-center" style="background:#f8f9fa; border:1px solid #dee2e6;">
                <div class="text-muted" style="font-size:11px;">{{ __('Gross Salary') }}</div>
                <div class="fw-bold small">{{ \Auth::user()->priceFormat($grossSalary) }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="rounded p-2 text-center" style="background:#f8f9fa; border:1px solid #dee2e6;">
                <div class="text-muted" style="font-size:11px;">{{ __('Basic Salary') }}</div>
                <div class="fw-bold small">{{ \Auth::user()->priceFormat($basicSalary) }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="rounded p-2 text-center" style="background:#eaf7ef; border:1px solid #b7eacb;">
                <div class="text-muted" style="font-size:11px;">{{ __('Net Pay') }}</div>
                <div class="fw-bold small text-success">{{ \Auth::user()->priceFormat($netSalary) }}</div>
            </div>
        </div>
    </div>
    @endif

    <hr class="my-2">

    {{-- ── Earnings ── --}}
    <div class="fw-semibold text-success small mb-1">
        <i class="ti ti-plus me-1"></i>{{ __('Earnings') }}
    </div>
    <table class="table table-sm mb-3" style="font-size:13px;">
        <tbody>
            {{-- Basic Salary row --}}
            <tr>
                <td class="text-muted ps-0">{{ $isHourly ? __('Gross Earned') : __('Basic Salary') }}</td>
                <td class="text-end pe-0 fw-semibold">{{ \Auth::user()->priceFormat($basicSalary) }}</td>
            </tr>
            {{-- Individual allowance/commission/bonus/other rows --}}
            @foreach ($earRows as $_er)
                @if ($_er['amount'] > 0)
                <tr>
                    <td class="ps-0">
                        {{ $_er['label'] }}
                        @if ($_er['pct'])
                            <small class="text-muted">({{ $_er['pct'] }}%)</small>
                        @endif
                    </td>
                    <td class="text-end pe-0">{{ \Auth::user()->priceFormat($_er['amount']) }}</td>
                </tr>
                @endif
            @endforeach
            {{-- Total --}}
            <tr style="border-top:2px solid #b7eacb;">
                <td class="ps-0 fw-bold text-success">{{ __('Total Earnings') }}</td>
                <td class="text-end pe-0 fw-bold text-success">{{ \Auth::user()->priceFormat($totalEarningsDisplay) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── Deductions ── --}}
    <div class="fw-semibold text-danger small mb-1">
        <i class="ti ti-minus me-1"></i>{{ __('Deductions') }}
    </div>
    <table class="table table-sm mb-3" style="font-size:13px;">
        <tbody>
            @if (count($dedRows) === 0)
            <tr>
                <td class="ps-0 text-muted" colspan="2">{{ __('No deductions') }}</td>
            </tr>
            @else
            @foreach ($dedRows as $_dr)
            <tr>
                <td class="ps-0">
                    {{ $_dr['label'] }}
                    @if ($_dr['pct'])
                        <small class="text-muted">({{ $_dr['pct'] }}%)</small>
                    @endif
                </td>
                <td class="text-end pe-0 text-danger">{{ \Auth::user()->priceFormat($_dr['amount']) }}</td>
            </tr>
            @endforeach
            @endif
            {{-- Total --}}
            <tr style="border-top:2px solid #f5c6cb;">
                <td class="ps-0 fw-bold text-danger">{{ __('Total Deductions') }}</td>
                <td class="text-end pe-0 fw-bold text-danger">{{ \Auth::user()->priceFormat($totalDeductions) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── Net Pay ── --}}
    <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background:#f0f4ff;">
        <span class="fw-bold">{{ __('Net Pay') }}</span>
        <span class="fw-bold fs-6 text-primary">{{ \Auth::user()->priceFormat($netSalary) }}</span>
    </div>

</div>
