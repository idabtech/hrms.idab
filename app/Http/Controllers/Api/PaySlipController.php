<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\Bonous;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\PaySlip;
use App\Models\Pension;
use App\Models\Peark;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaySlipController extends Controller
{
    // ── GET /api/payslips?month=Y-m ──────────────────────────────────────────
    /**
     * List payslips for the authenticated user's company.
     *
     * For company / HR users  → all employees' payslips for the given month.
     * For employee users      → only their own payslips.
     *
     * Query params:
     *   month  string  Y-m  (e.g. "2026-05")  — defaults to last month
     *
     * Response:
     *   data: [
     *     {
     *       payslip_id, employee_id, employee_code, employee_name,
     *       payroll_type, basic_salary, net_payble, status,
     *       salary_month
     *     }, …
     *   ]
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'nullable|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();

            $month = !empty($request->month)
                ? $request->month
                : date('Y-m', strtotime('last month'));

            $query = PaySlip::with(['employees.salaryType'])
                ->where('salary_month', $month)
                ->where('created_by', $user->creatorId());

            // Employees only see their own payslips
            if ($user->type === 'employee') {
                $employee = Employee::where('user_id', $user->id)->first();
                if (!$employee) {
                    return response()->json(['status' => false, 'message' => 'Employee not found'], 404);
                }
                $query->where('employee_id', $employee->id);
            }

            $payslips = $query->get()->map(fn ($p) => [
                'payslip_id'    => $p->id,
                'employee_id'   => $p->employee_id,
                'employee_code' => optional($p->employees)->employee_id,
                'employee_name' => optional($p->employees)->name,
                'payroll_type'  => optional(optional($p->employees)->salaryType)->name,
                'basic_salary'  => (float) $p->basic_salary,
                'net_payble'    => (float) $p->net_payble,
                'status'        => $p->status == 1 ? 'Paid' : 'UnPaid',
                'salary_month'  => $p->salary_month,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Payslips fetched successfully',
                'month'   => $month,
                'data'    => $payslips,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ── GET /api/payslips/{id} ────────────────────────────────────────────────
    /**
     * Full payslip detail — mirrors the pdf.blade.php / payslipPdf.blade.php view.
     *
     * Returns:
     *   payslip   — header fields (basic_salary, net_payble, salary_month, status)
     *   employee  — name, designation, email, etc.
     *   company   — name, address, city, state, zipcode
     *   earning   — basic_salary + allowances, commissions, bonuses, perks,
     *               other_payments, overtime  (each with resolved amounts)
     *   deduction — loans, saturation_deductions, pensions, leave_deduction
     *   totals    — total_earning, total_deduction, net_salary
     */
    public function show($id)
    {
        try {
            $user    = Auth::user();
            $payslip = PaySlip::with('employees')->find($id);

            if (!$payslip) {
                return response()->json(['status' => false, 'message' => 'Payslip not found'], 404);
            }

            // Scope check — employees can only see their own
            if ($user->type === 'employee') {
                $employee = Employee::where('user_id', $user->id)->first();
                if (!$employee || $payslip->employee_id !== $employee->id) {
                    return response()->json(['status' => false, 'message' => 'Permission denied'], 403);
                }
            } elseif ($payslip->created_by !== $user->creatorId()) {
                return response()->json(['status' => false, 'message' => 'Permission denied'], 403);
            }

            $employee = $payslip->employees;
            $month    = $payslip->salary_month;

            // ── Company info ──────────────────────────────────────────────────
            $company = [
                'name'    => Utility::getValByName('company_name'),
                'address' => Utility::getValByName('company_address'),
                'city'    => Utility::getValByName('company_city'),
                'state'   => Utility::getValByName('company_state'),
                'zipcode' => Utility::getValByName('company_zipcode'),
            ];

            // ── Earnings ──────────────────────────────────────────────────────
            $basicSalary    = (float) $payslip->basic_salary;
            $totalEarning   = $basicSalary;
            $earningLines   = [];

            // Basic salary line
            $earningLines[] = [
                'category' => 'Basic Salary',
                'title'    => '-',
                'type'     => '-',
                'amount'   => $basicSalary,
            ];

            // Allowances (stored as JSON snapshot on payslip)
            $allowances = json_decode($payslip->allowance, true) ?? [];
            foreach ($allowances as $a) {
                $resolved = $a['type'] === 'percentage'
                    ? round(($a['amount'] * $basicSalary) / 100, 2)
                    : (float) $a['amount'];
                $earningLines[] = [
                    'category'   => 'Allowance',
                    'title'      => $a['title'],
                    'type'       => ucfirst($a['type']),
                    'raw_amount' => (float) $a['amount'],
                    'amount'     => $resolved,
                ];
                $totalEarning += $resolved;
            }

            // Commissions
            $commissions = json_decode($payslip->commission, true) ?? [];
            foreach ($commissions as $c) {
                $resolved = $c['type'] === 'percentage'
                    ? round(($c['amount'] * $basicSalary) / 100, 2)
                    : (float) $c['amount'];
                $earningLines[] = [
                    'category'   => 'Commission',
                    'title'      => $c['title'],
                    'type'       => ucfirst($c['type']),
                    'raw_amount' => (float) $c['amount'],
                    'amount'     => $resolved,
                ];
                $totalEarning += $resolved;
            }

            // Bonuses (live from DB — not snapshotted on payslip)
            $bonuses = Bonous::where('employee_id', $employee->id)->get();
            foreach ($bonuses as $b) {
                $resolved = $b->type === 'percentage'
                    ? round(($b->amount * $employee->salary) / 100, 2)
                    : (float) $b->amount;
                $earningLines[] = [
                    'category' => 'Bonus',
                    'title'    => $b->title,
                    'type'     => '-',
                    'amount'   => $resolved,
                ];
                $totalEarning += $resolved;
            }

            // Perks (live from DB)
            $perks = Peark::where('employee_id', $employee->id)->get();
            foreach ($perks as $p) {
                $earningLines[] = [
                    'category'     => 'Perk',
                    'title'        => $p->title,
                    'type'         => 'Coupon (' . $p->peark_coupon . ')',
                    'amount'       => (float) $p->amount,
                ];
                $totalEarning += (float) $p->amount;
            }

            // Other payments
            $otherPayments = json_decode($payslip->other_payment, true) ?? [];
            foreach ($otherPayments as $op) {
                $resolved = $op['type'] === 'percentage'
                    ? round(($op['amount'] * $basicSalary) / 100, 2)
                    : (float) $op['amount'];
                $earningLines[] = [
                    'category'   => 'Other Payment',
                    'title'      => $op['title'],
                    'type'       => ucfirst($op['type']),
                    'raw_amount' => (float) $op['amount'],
                    'amount'     => $resolved,
                ];
                $totalEarning += $resolved;
            }

            // Overtime
            $overtimes = json_decode($payslip->overtime, true) ?? [];
            foreach ($overtimes as $ot) {
                $otAmount = (float) $ot['number_of_days'] * (float) $ot['hours'] * (float) $ot['rate'];
                $earningLines[] = [
                    'category'       => 'Overtime',
                    'title'          => $ot['title'],
                    'number_of_days' => $ot['number_of_days'],
                    'hours'          => $ot['hours'],
                    'rate'           => (float) $ot['rate'],
                    'amount'         => $otAmount,
                ];
                $totalEarning += $otAmount;
            }

            // ── Deductions ────────────────────────────────────────────────────
            $totalDeduction  = 0;
            $deductionLines  = [];

            // Loans
            $loans = json_decode($payslip->loan, true) ?? [];
            foreach ($loans as $l) {
                $resolved = $l['type'] === 'percentage'
                    ? round(($l['amount'] * $basicSalary) / 100, 2)
                    : (float) $l['amount'];
                $deductionLines[] = [
                    'category'   => 'Loan',
                    'title'      => $l['title'],
                    'type'       => ucfirst($l['type']),
                    'raw_amount' => (float) $l['amount'],
                    'amount'     => $resolved,
                ];
                $totalDeduction += $resolved;
            }

            // Statutory Deductions
            $satDeductions = json_decode($payslip->saturation_deduction, true) ?? [];
            foreach ($satDeductions as $sd) {
                $resolved = $sd['type'] === 'percentage'
                    ? round(($sd['amount'] * $basicSalary) / 100, 2)
                    : (float) $sd['amount'];
                $deductionLines[] = [
                    'category'   => 'Statutory Deduction',
                    'title'      => $sd['title'],
                    'type'       => ucfirst($sd['type']),
                    'raw_amount' => (float) $sd['amount'],
                    'amount'     => $resolved,
                ];
                $totalDeduction += $resolved;
            }

            // Pensions (live from DB)
            $pensions = Pension::where('employee_id', $employee->id)->get();
            foreach ($pensions as $pen) {
                $resolved = $pen->type === 'percentage'
                    ? round(($pen->amount * $employee->salary) / 100, 2)
                    : (float) $pen->amount;
                $deductionLines[] = [
                    'category' => 'Pension',
                    'title'    => $pen->title,
                    'type'     => '-',
                    'amount'   => $resolved,
                ];
                $totalDeduction += $resolved;
            }

            // Leave deduction (same logic as Utility::employeePayslipDetail)
            $salary       = (float) $employee->salary;
            $perDayAmount = $salary / Carbon::now()->daysInMonth;
            $today        = Carbon::now();
            $daysSoFar    = $today->day;

            $attendanceCount = AttendanceEmployee::where('employee_id', $employee->id)
                ->whereMonth('date', $today->month)
                ->whereYear('date', $today->year)
                ->whereDate('date', '<=', $today)
                ->count(DB::raw('DISTINCT DATE(date)'));

            $leaveDays = Leave::where('employee_id', $employee->id)
                ->whereMonth('start_date', $today->month)
                ->whereYear('start_date', $today->year)
                ->sum('total_leave_days');

            $absentDays     = max($daysSoFar - ($attendanceCount + $leaveDays), 0);
            $leaveDeduction = round($absentDays * $perDayAmount, 2);

            $deductionLines[] = [
                'category'        => 'Leave Deduction',
                'title'           => 'Day off',
                'type'            => '-',
                'total_leave_days'=> $absentDays,
                'amount'          => $leaveDeduction,
            ];
            $totalDeduction += $leaveDeduction;

            // ── Net salary ────────────────────────────────────────────────────
            $netSalary = $salary + $totalEarning - $basicSalary - $totalDeduction;
            // (basic_salary is already in totalEarning, so subtract once to avoid double-count)
            // Actually mirror Utility: net = basic + totalEarning(excl basic) - totalDeduction
            // Utility does: net_salary = salary + totalEarning - totalDeduction
            // where totalEarning = allowance+commission+bonus+perk+otherPayment+overtime (no basic)
            // We added basic to earningLines for display but NOT to totalEarning calc below:
            $totalEarningExclBasic = $totalEarning - $basicSalary;
            $netSalary = $salary + $totalEarningExclBasic - $totalDeduction;

            return response()->json([
                'status'  => true,
                'message' => 'Payslip detail fetched successfully',
                'data'    => [
                    'payslip' => [
                        'id'           => $payslip->id,
                        'salary_month' => $payslip->salary_month,
                        'basic_salary' => $basicSalary,
                        'net_payble'   => (float) $payslip->net_payble,
                        'status'       => $payslip->status == 1 ? 'Paid' : 'UnPaid',
                        'created_at'   => $payslip->created_at?->toDateString(),
                    ],
                    'employee' => [
                        'id'          => $employee->id,
                        'employee_id' => $employee->employee_id,
                        'name'        => $employee->name,
                        'email'       => $employee->email,
                        'designation' => optional($employee->designation)->name,
                        'department'  => optional($employee->department)->name,
                        'branch'      => optional($employee->branch)->name,
                    ],
                    'company'         => $company,
                    'earning_lines'   => $earningLines,
                    'deduction_lines' => $deductionLines,
                    'totals'          => [
                        'total_earning'   => round($totalEarningExclBasic, 2),
                        'total_deduction' => round($totalDeduction, 2),
                        'net_salary'      => round($netSalary, 2),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ── GET /api/payslips/employee/{employee_id}?month=Y-m ───────────────────
    /**
     * All payslips for a specific employee (company/HR use).
     * Employees can call this for themselves only.
     *
     * Query params:
     *   month  string  Y-m  (optional — omit to get all months)
     */
    public function byEmployee(Request $request, $employeeId)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'nullable|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user     = Auth::user();
            $employee = Employee::where('id', $employeeId)
                ->where('created_by', $user->creatorId())
                ->first();

            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee not found'], 404);
            }

            // Employees can only fetch their own
            if ($user->type === 'employee') {
                $self = Employee::where('user_id', $user->id)->first();
                if (!$self || $self->id !== $employee->id) {
                    return response()->json(['status' => false, 'message' => 'Permission denied'], 403);
                }
            }

            $query = PaySlip::where('employee_id', $employeeId)
                ->where('created_by', $user->creatorId())
                ->orderByDesc('salary_month');

            if (!empty($request->month)) {
                $query->where('salary_month', $request->month);
            }

            $payslips = $query->get()->map(fn ($p) => [
                'payslip_id'   => $p->id,
                'salary_month' => $p->salary_month,
                'basic_salary' => (float) $p->basic_salary,
                'net_payble'   => (float) $p->net_payble,
                'status'       => $p->status == 1 ? 'Paid' : 'UnPaid',
            ]);

            return response()->json([
                'status'   => true,
                'message'  => 'Employee payslips fetched successfully',
                'employee' => [
                    'id'   => $employee->id,
                    'name' => $employee->name,
                ],
                'data'     => $payslips,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
