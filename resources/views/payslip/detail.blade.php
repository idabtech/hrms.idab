@php
    $netSalary         = $payslipDetail['net_salary'] ?? 0;
    // payslipDetail['salary']       = basic_salary (used as earnings base)
    // payslipDetail['basic_salary'] = gross salary snapshot (stored in pay_slips.basic_salary)
    $basicSalary       = (float) ($payslipDetail['salary'] ?? 0);       // basic salary component
    $grossSalary       = (float) ($employee->salary ?? 0);              // gross / CTC salary
    $totalAllowance    = $payslipDetail['totalAllowance'] ?? 0;
    $totalCommission   = $payslipDetail['totalCommission'] ?? 0;
    $totalOtherPayment = $payslipDetail['totalOtherPayment'] ?? 0;
    $totalLoan         = $payslipDetail['totalLoan'] ?? 0;
    $totalLoanRepayment= $payslipDetail['totalLoanRepayment'] ?? 0;
    $totalPension      = $payslipDetail['totalPansion'] ?? 0;
    $totalDeduction    = $payslipDetail['total_saturation_deduction'] ?? 0;
    $totalBonus        = $payslipDetail['total_bonus'] ?? 0;
    $totalEarning      = $payslipDetail['totalEarning'] ?? 0;
    $totalDeductions   = $payslipDetail['totalDeduction'] ?? 0;
    $isHourly          = (bool) ($payslipDetail['is_hourly'] ?? false);
    $unpaidLeaveDeduction = $payslipDetail['unpaid_leave_deduction'] ?? 0;
    $unpaidLeaveDays   = $payslipDetail['unpaid_leave_days'] ?? 0;
    $lopDays           = $payslipDetail['lop_days'] ?? 0;
@endphp

<div class="modal-body">
    <div class="row mb-3">
        <div class="col-md-6">
            <p class="mb-1 text-muted">{{ __('Employee') }}</p>
            <h6>{{ $employee->name }}</h6>
        </div>
        <div class="col-md-6 text-end">
            <p class="mb-1 text-muted">{{ __('Pay Month') }}</p>
            <h6>{{ \Carbon\Carbon::parse($payslip->salary_month . '-01')->format('F Y') }}</h6>
        </div>
    </div>

    <hr>

    {{-- Salary Summary --}}
    @if (!$isHourly)
    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="rounded p-2 text-center" style="background:#f8f9fa; border:1px solid #dee2e6;">
                <div class="text-muted small">{{ __('Gross Salary') }}</div>
                <div class="fw-bold">{{ \Auth::user()->priceFormat($grossSalary) }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="rounded p-2 text-center" style="background:#f8f9fa; border:1px solid #dee2e6;">
                <div class="text-muted small">{{ __('Basic Salary') }}</div>
                <div class="fw-bold">{{ \Auth::user()->priceFormat($basicSalary) }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="rounded p-2 text-center" style="background:#eaf7ef; border:1px solid #b7eacb;">
                <div class="text-muted small">{{ __('Net Pay') }}</div>
                <div class="fw-bold text-success">{{ \Auth::user()->priceFormat($netSalary) }}</div>
            </div>
        </div>
    </div>
    <hr>
    @endif

    {{-- Earnings --}}
    <h6 class="text-success mb-2"><i class="ti ti-plus me-1"></i>{{ __('Earnings') }}</h6>
    <table class="table table-sm table-striped mb-4">
        <tbody>
            <tr>
                <td>{{ $isHourly ? __('Gross Earned') : __('Basic Salary') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($basicSalary) }}</td>
            </tr>
            {{-- HRA and DA removed — now stored as allowances --}}
            @if ($totalAllowance > 0)
            <tr>
                <td>{{ __('Allowances') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($totalAllowance) }}</td>
            </tr>
            @endif
            @if ($totalCommission > 0)
            <tr>
                <td>{{ __('Commission') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($totalCommission) }}</td>
            </tr>
            @endif
            @if ($totalBonus > 0)
            <tr>
                <td>{{ __('Bonus') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($totalBonus) }}</td>
            </tr>
            @endif
            @if ($totalOtherPayment > 0)
            <tr>
                <td>{{ __('Other Payment') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($totalOtherPayment) }}</td>
            </tr>
            @endif
            @if ($totalLoan > 0)
            <tr>
                <td>{{ __('Loan/Advance') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($totalLoan) }}</td>
            </tr>
            @endif
            <tr class="table-success">
                <td><strong>{{ __('Total Earnings') }}</strong></td>
                <td class="text-end"><strong>{{ \Auth::user()->priceFormat($totalEarning + $basicSalary) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Deductions --}}
    <h6 class="text-danger mb-2"><i class="ti ti-minus me-1"></i>{{ __('Deductions') }}</h6>
    <table class="table table-sm table-striped mb-4">
        <tbody>
            @if ($totalLoanRepayment > 0)
            <tr>
                <td>{{ __('Loan Repayment') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($totalLoanRepayment) }}</td>
            </tr>
            @endif
            @if ($totalPension > 0)
            <tr>
                <td>{{ __('Pension') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($totalPension) }}</td>
            </tr>
            @endif
            @if ($totalDeduction > 0)
            <tr>
                <td>{{ __('Statutory Deductions') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($totalDeduction) }}</td>
            </tr>
            @endif
            @if ($lopDays > 0)
            <tr>
                <td>{{ __('Loss of Pay') }} <small class="text-muted">({{ $lopDays }} {{ __('days') }})</small></td>
                <td class="text-end text-danger">{{ \Auth::user()->priceFormat($payslipDetail['totalDeduction'] - $totalLoanRepayment - $totalPension - $totalDeduction - $unpaidLeaveDeduction) }}</td>
            </tr>
            @endif
            @if ($unpaidLeaveDeduction > 0)
            <tr>
                <td>{{ __('Unpaid Leave') }} <small class="text-muted">({{ $unpaidLeaveDays }} {{ __('days') }})</small></td>
                <td class="text-end text-danger">{{ \Auth::user()->priceFormat($unpaidLeaveDeduction) }}</td>
            </tr>
            @endif
            <tr class="table-danger">
                <td><strong>{{ __('Total Deductions') }}</strong></td>
                <td class="text-end"><strong>{{ \Auth::user()->priceFormat($totalDeductions) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Net Salary --}}
    <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background-color: #f0f4ff;">
        <h5 class="mb-0">{{ __('Net Pay') }}</h5>
        <h5 class="mb-0 text-primary">{{ \Auth::user()->priceFormat($netSalary) }}</h5>
    </div>
</div>
