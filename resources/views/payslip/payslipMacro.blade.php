@php
use Carbon\Carbon;
use Carbon\CarbonPeriod;

// ── Month helpers ─────────────────────────────────────────────────────────
$carbonMonth  = Carbon::createFromDate((int)$slipYear, (int)$slipMonth, 1);
$slipLabel    = $carbonMonth->format('F Y');

// ── All display values come directly from Utility::employeePayslipDetail ──
// No duplicate calculation here — single source of truth.
$_storedSalary   = (float) $payslipDetail['basic_salary'];  // gross salary snapshot
$_basicSalary    = (float) $employee->basic_salary;          // base for % allowances
$perDaySalary    = round((float) $payslipDetail['per_day_amount'], 2);
$perHourSalary   = round((float) $payslipDetail['per_hour_amount'], 2);
$officeDays      = (int) $payslipDetail['office_days'];
$presentDays     = $payslipDetail['present_days'];
$approvedLeaves  = $payslipDetail['approved_leaves_month'];
$disapprovedLeaves = (int) $payslipDetail['rejected_leaves_month'];
$absentDays      = $payslipDetail['absent_days'];
$extraDays       = $payslipDetail['extra_days'];
$totalWorkHours  = $payslipDetail['total_work_hours'];
$avgHrsPerDay    = $payslipDetail['avg_hrs_per_day'];
$totalLeaveAlloc = (int) $payslipDetail['total_leave_alloc'];
$remainingLeaves = (int) $payslipDetail['remaining_leaves'];
$usedLeaves      = $approvedLeaves;

// Format for display
$presentDisplay  = min($presentDays, $officeDays);
$presentDaysFmt  = ($presentDisplay == floor($presentDisplay)) ? (int)$presentDisplay : $presentDisplay;
$absentDaysFmt   = ($absentDays == floor($absentDays))  ? (int)$absentDays  : $absentDays;
$extraDaysFmt    = ($extraDays  == floor($extraDays))   ? (int)$extraDays   : $extraDays;

// ── Earnings rows ─────────────────────────────────────────────────────────
$earRows = [['label' => 'Basic Salary', 'amount' => $_storedSalary]];

foreach ($payslipDetail['earning']['allowance'] as $_ar) {
    foreach (json_decode($_ar->allowance) as $_a) {
        $earRows[] = ['label' => $_a->title, 'amount' => $_a->type === 'percentage'
            ? round($_a->amount * $_basicSalary / 100, 2) : (float)$_a->amount];
    }
}
foreach ($payslipDetail['earning']['commission'] as $_cr) {
    foreach (json_decode($_cr->commission) as $_c) {
        $earRows[] = ['label' => $_c->title, 'amount' => $_c->type === 'percentage'
            ? round($_c->amount * $_basicSalary / 100, 2) : (float)$_c->amount];
    }
}
foreach ($payslipDetail['earning']['bonous'] as $_b) {
    $earRows[] = ['label' => $_b->title ?: 'Bonus', 'amount' => $_b->type === 'percentage'
        ? round($_b->amount * $_basicSalary / 100, 2) : (float)$_b->amount];
}
foreach ($payslipDetail['earning']['otherPayment'] as $_or) {
    foreach (json_decode($_or->other_payment) as $_op) {
        $earRows[] = ['label' => $_op->title, 'amount' => $_op->type === 'percentage'
            ? round($_op->amount * $_basicSalary / 100, 2) : (float)$_op->amount];
    }
}
foreach ($payslipDetail['earning']['overTime'] as $_otr) {
    foreach (json_decode($_otr->overtime) as $_ot) {
        $earRows[] = ['label' => $_ot->title ?: 'Overtime',
            'amount' => (float)($_ot->number_of_days * $_ot->hours * $_ot->rate)];
    }
}
if ($extraDays > 0) {
    $earRows[] = ['label' => 'Extra Day(s) (' . $extraDays . 'd)',
        'amount' => round($perDaySalary * $extraDays, 2)];
}

// ── Deduction rows ────────────────────────────────────────────────────────
$dedRows = [];
foreach ($payslipDetail['deduction']['loan'] as $_lr) {
    foreach (json_decode($_lr->loan) as $_ln) {
        $_amt = $_ln->type === 'percentage'
            ? round($_ln->amount * $_basicSalary / 100, 2) : (float)$_ln->amount;
        if ($_amt > 0) $dedRows[] = ['label' => $_ln->title ?: 'Loan', 'amount' => $_amt];
    }
}
foreach ($payslipDetail['deduction']['saturation_deduction'] as $_dr) {
    foreach (json_decode($_dr->saturation_deduction) as $_d) {
        $_amt = $_d->type === 'percentage'
            ? round($_d->amount * $_basicSalary / 100, 2) : (float)$_d->amount;
        if ($_amt > 0) $dedRows[] = ['label' => $_d->title, 'amount' => $_amt];
    }
}
foreach ($payslipDetail['deduction']['pansion'] as $_p) {
    $_amt = $_p->type === 'percentage'
        ? round($_p->amount * $_basicSalary / 100, 2) : (float)$_p->amount;
    if ($_amt > 0) $dedRows[] = ['label' => $_p->title ?: 'Provident Fund', 'amount' => $_amt];
}
foreach ($payslipDetail['deduction']['leave'] as $_lea) {
    if ($_lea->empleave > 0) $dedRows[] = ['label' => 'Loss Of Pay', 'amount' => (float)$_lea->empleave];
}

$_extraEarning   = $extraDays > 0 ? round($perDaySalary * $extraDays, 2) : 0;
$totalEarnings   = $payslipDetail['totalEarning'] + $_storedSalary + $_extraEarning;
$totalDeductions = $payslipDetail['totalDeduction'];
$netSalary       = $payslipDetail['net_salary'];

$_maxED = max(count($earRows), count($dedRows));
while (count($earRows) < $_maxED) $earRows[] = ['label' => '', 'amount' => null];
while (count($dedRows) < $_maxED) $dedRows[] = ['label' => '', 'amount' => null];

// ── Company settings ──────────────────────────────────────────────────────
$companyName  = \App\Models\Utility::getValByName('company_name')      ?? '';
$companyAddr  = \App\Models\Utility::getValByName('company_address')   ?? '';
$companyCity  = \App\Models\Utility::getValByName('company_city')      ?? '';
$companyState = \App\Models\Utility::getValByName('company_state')     ?? '';
$companyZip   = \App\Models\Utility::getValByName('company_zipcode')   ?? '';
$companyPhone = \App\Models\Utility::getValByName('company_telephone') ?? '';
$companyEmail = \App\Models\Utility::getValByName('company_email')     ?? '';
$companyWeb   = \App\Models\Utility::getValByName('company_website')   ?? '';

$isPaid = $payslip->status == 1;
@endphp
