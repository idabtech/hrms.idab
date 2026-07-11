@php
    $netSalary = $payslipDetail['net_salary'] ?? 0;
    $salary = $payslipDetail['salary'] ?? 0;
    $basicSalary = $payslipDetail['basic_salary'] ?? 0;
    $hra = $payslipDetail['hra'] ?? 0;
    $da = $payslipDetail['da'] ?? 0;
    $totalAllowance = $payslipDetail['totalAllowance'] ?? 0;
    $totalCommission = $payslipDetail['totalCommission'] ?? 0;
    $totalLoan = $payslipDetail['totalLoan'] ?? 0;
    $totalLoanRepayment = $payslipDetail['totalLoanRepayment'] ?? 0;
    $totalPension = $payslipDetail['totalPansion'] ?? 0;
    $totalDeduction = $payslipDetail['total_saturation_deduction'] ?? 0;
    $totalBonus = $payslipDetail['total_bonus'] ?? 0;
    $totalEarning = $payslipDetail['totalEarning'] ?? 0;
    $totalDeductions = $payslipDetail['totalDeduction'] ?? 0;
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

    {{-- Earnings --}}
    <h6 class="text-success mb-3"><i class="ti ti-plus me-1"></i>{{ __('Earnings') }}</h6>
    <table class="table table-sm table-striped mb-4">
        <tbody>
            <tr>
                <td>{{ __('Salary') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($salary) }}</td>
            </tr>
            @if (!$isUk && $hra > 0)
            <tr>
                <td>{{ __('HRA') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($hra) }}</td>
            </tr>
            @endif
            @if (!$isUk && $da > 0)
            <tr>
                <td>{{ __('DA') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($da) }}</td>
            </tr>
            @endif
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
            @if ($totalLoan > 0)
            <tr>
                <td>{{ __('Loan/Advance') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($totalLoan) }}</td>
            </tr>
            @endif
            @if ($totalBonus > 0)
            <tr>
                <td>{{ __('Bonus') }}</td>
                <td class="text-end">{{ \Auth::user()->priceFormat($totalBonus) }}</td>
            </tr>
            @endif
            <tr class="table-success">
                <td><strong>{{ __('Total Earnings') }}</strong></td>
                <td class="text-end"><strong>{{ \Auth::user()->priceFormat($totalEarning + $salary) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Deductions --}}
    <h6 class="text-danger mb-3"><i class="ti ti-minus me-1"></i>{{ __('Deductions') }}</h6>
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
            <tr class="table-danger">
                <td><strong>{{ __('Total Deductions') }}</strong></td>
                <td class="text-end"><strong>{{ \Auth::user()->priceFormat($totalDeductions) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Net Salary --}}
    <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background-color: #f0f4ff;">
        <h5 class="mb-0">{{ __('Net Salary') }}</h5>
        <h5 class="mb-0 text-primary">{{ \Auth::user()->priceFormat($netSalary) }}</h5>
    </div>
</div>
