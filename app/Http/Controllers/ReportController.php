<?php

namespace App\Http\Controllers;

use App\Exports\accountstatementExport;
use App\Exports\LeaveExport;
use App\Exports\LeaveReportExport;
use App\Exports\PayrollExport;
use App\Exports\TimesheetExport;
use App\Exports\TimesheetReportExport;
use App\Models\AccountList;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Deposit;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Leave;
use App\Models\LeaveDayDetail;
use App\Models\LeaveType;
use App\Models\PaySlip;
use App\Models\TimeSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Shift;
use App\Models\User;
use App\Models\Utility;

class ReportController extends Controller
{

    public function incomeVsExpense(Request $request)
    {

        if (\Auth::user()->can('Manage Report')) {
            $deposit = Deposit::where('created_by', \Auth::user()->creatorId());

            $labels       = $data = [];
            $expenseCount = $incomeCount = 0;
            $incomeData = [];
            $expenseData = [];
            if (!empty($request->start_month) && !empty($request->end_month)) {

                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);

                $currentdate = $start;
                $month       = [];
                while ($currentdate <= $end) {
                    $month = date('m', $currentdate);
                    $year  = date('Y', $currentdate);

                    $depositFilter = Deposit::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();

                    $depositsTotal = 0;
                    foreach ($depositFilter as $deposit) {
                        $depositsTotal += $deposit->amount;
                    }

                    $incomeData[] = $depositsTotal;
                    $incomeCount  += $depositsTotal;

                    $expenseFilter = Expense::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();
                    $expenseTotal  = 0;
                    foreach ($expenseFilter as $expense) {
                        $expenseTotal += $expense->amount;
                    }
                    $expenseData[] = $expenseTotal;
                    $expenseCount  += $expenseTotal;

                    $labels[]    = date('M Y', $currentdate);
                    $currentdate = strtotime('+1 month', $currentdate);
                }

                $filter['startDateRange'] = date('M-Y', strtotime($request->start_month));
                $filter['endDateRange']   = date('M-Y', strtotime($request->end_month));
            } else {
                for ($i = 0; $i < 6; $i++) {

                    $month = date('m', strtotime("-$i month"));
                    $year  = date('Y', strtotime("-$i month"));

                    $depositFilter = Deposit::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();

                    $depositTotal = 0;
                    foreach ($depositFilter as $deposit) {
                        $depositTotal += $deposit->amount;
                    }

                    $incomeData[] = $depositTotal;
                    $incomeCount  += $depositTotal;

                    $expenseFilter = Expense::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();
                    $expenseTotal  = 0;
                    foreach ($expenseFilter as $expense) {
                        $expenseTotal += $expense->amount;
                    }
                    $expenseData[] = $expenseTotal;
                    $expenseCount  += $expenseTotal;

                    $labels[] = date('M Y', strtotime("-$i month"));
                }
                $filter['startDateRange'] = date('M-Y');
                $filter['endDateRange']   = date('M-Y', strtotime("-5 month"));
            }

            $incomeArr['name'] = __('Income');
            $incomeArr['data'] = $incomeData;

            $expenseArr['name'] = __('Expense');
            $expenseArr['data'] = $expenseData;

            $data[] = $incomeArr;
            $data[] = $expenseArr;



            return view('report.income_expense', compact('labels', 'data', 'incomeCount', 'expenseCount', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function leave(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            $filterYear['branch']        = __('All');
            $filterYear['department']    = __('All');
            $filterYear['type']          = __('Monthly');
            $filterYear['dateYearRange'] = date('M-Y');

            // ── Resolve date range for valid employees filtering ──────────────────
            if ($request->type == 'yearly' && !empty($request->year)) {
                $startRange = $request->year . '-01-01';
                $endRange   = $request->year . '-12-31';
                $filterYear['dateYearRange'] = $request->year;
                $filterYear['type']          = __('Yearly');
            } elseif ($request->type == 'monthly' && !empty($request->month)) {
                $month       = date('m', strtotime($request->month));
                $year        = date('Y', strtotime($request->month));
                $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                $startRange  = $year . '-' . sprintf('%02d', $month) . '-01';
                $endRange    = $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $num_of_days);
                $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                $filterYear['type']          = __('Monthly');
            } else {
                $month       = date('m');
                $year        = date('Y');
                $monthYear   = date('Y-m');
                $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
                $startRange  = $year . '-' . sprintf('%02d', $month) . '-01';
                $endRange    = $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $num_of_days);
                $filterYear['dateYearRange'] = date('M-Y', strtotime($monthYear));
                $filterYear['type']          = __('Monthly');
            }

            $creatorId = \Auth::user()->creatorId();

            // 1) Active employees joined on or before $endRange
            $activeEmpIds = Employee::where('created_by', $creatorId)
                ->where('is_active', 1)
                ->whereHas('user', function ($q) {
                    $q->where('is_active', 1);
                })
                ->where(function ($q) use ($endRange) {
                    $q->whereNull('company_doj')
                      ->orWhere('company_doj', '<=', $endRange);
                })
                ->pluck('id')
                ->toArray();

            // 2) Inactive/terminated employees who have leaves or attendance in this date range,
            //    or whose termination/resignation date is on/after $startRange
            $leaveEmpIds = Leave::where('created_by', $creatorId)
                ->where('start_date', '<=', $endRange)
                ->where('end_date', '>=', $startRange)
                ->pluck('employee_id')
                ->toArray();

            $attendanceEmpIds = \App\Models\AttendanceEmployee::where('created_by', $creatorId)
                ->whereBetween('date', [$startRange, $endRange])
                ->pluck('employee_id')
                ->toArray();

            $terminatedEmpIds = \App\Models\Termination::where('created_by', $creatorId)
                ->where('termination_date', '>=', $startRange)
                ->pluck('employee_id')
                ->toArray();

            $resignedEmpIds = \App\Models\Resignation::where('created_by', $creatorId)
                ->where('resignation_date', '>=', $startRange)
                ->pluck('employee_id')
                ->toArray();

            $validEmpIds = array_unique(array_merge(
                $activeEmpIds,
                $leaveEmpIds,
                $attendanceEmpIds,
                $terminatedEmpIds,
                $resignedEmpIds
            ));

            $employees = Employee::whereIn('id', $validEmpIds)->where('created_by', $creatorId);
            if (!empty($request->branch)) {
                $employees->where('branch_id', $request->branch);
                $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }
            if (!empty($request->department)) {
                $employees->where('department_id', $request->department);
                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }

            $employees = $employees->get();

            $leaves        = [];
            $totalApproved = $totalReject = $totalPending = 0;
            foreach ($employees as $employee) {

                $employeeLeave['id']          = $employee->id;
                $employeeLeave['employee_id'] = $employee->employee_id;
                $employeeLeave['employee']    = $employee->name;

                $approved = Leave::where('employee_id', $employee->id)->where('status', 'Approved')->where('created_by', \Auth::user()->creatorId());
                $reject   = Leave::where('employee_id', $employee->id)->where('status', 'Reject')->where('created_by', \Auth::user()->creatorId());
                $pending  = Leave::where('employee_id', $employee->id)->where('status', 'Pending')->where('created_by', \Auth::user()->creatorId());

                if ($request->type == 'yearly' && !empty($request->year)) {
                    $approved->whereYear('start_date', $request->year);
                    $reject->whereYear('start_date', $request->year);
                    $pending->whereYear('start_date', $request->year);
                } elseif ($request->type == 'monthly' && !empty($request->month)) {
                    $mVal = date('m', strtotime($request->month));
                    $yVal = date('Y', strtotime($request->month));

                    $approved->whereMonth('start_date', $mVal)->whereYear('start_date', $yVal);
                    $reject->whereMonth('start_date', $mVal)->whereYear('start_date', $yVal);
                    $pending->whereMonth('start_date', $mVal)->whereYear('start_date', $yVal);
                } else {
                    $mVal = date('m');
                    $yVal = date('Y');

                    $approved->whereMonth('start_date', $mVal)->whereYear('start_date', $yVal);
                    $reject->whereMonth('start_date', $mVal)->whereYear('start_date', $yVal);
                    $pending->whereMonth('start_date', $mVal)->whereYear('start_date', $yVal);
                }

                $approved = $approved->count();
                $reject   = $reject->count();
                $pending  = $pending->count();

                $totalApproved += $approved;
                $totalReject   += $reject;
                $totalPending  += $pending;

                $employeeLeave['approved'] = $approved;
                $employeeLeave['reject']   = $reject;
                $employeeLeave['pending']  = $pending;


                $leaves[] = $employeeLeave;
            }

            $starting_year = date('Y', strtotime('-5 year'));
            $ending_year   = date('Y', strtotime('+5 year'));

            $filterYear['starting_year'] = $starting_year;
            $filterYear['ending_year']   = $ending_year;

            $filter['totalApproved'] = $totalApproved;
            $filter['totalReject']   = $totalReject;
            $filter['totalPending']  = $totalPending;

            return view('report.leave', compact('department', 'branch', 'leaves', 'filterYear', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function employeeLeave(Request $request, $employee_id, $status, $type, $month, $year)
    {
        if (\Auth::user()->can('Manage Report')) {
            $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())->get();
            $leaves     = [];
            foreach ($leaveTypes as $leaveType) {
                $leave        = new Leave();
                $leave->title = $leaveType->title;
                $totalLeave   = Leave::where('employee_id', $employee_id)->where('status', $status)->where('leave_type_id', $leaveType->id)->where('created_by', \Auth::user()->creatorId());
                if ($type == 'yearly') {
                    $totalLeave->whereYear('start_date', $year);
                } else {
                    $m = date('m', strtotime($month));
                    $y = date('Y', strtotime($month));

                    $totalLeave->whereMonth('start_date', $m)->whereYear('start_date', $y);
                }
                $totalLeave = $totalLeave->count();

                $leave->total = $totalLeave;
                $leaves[]     = $leave;
            }

            $leaveData = Leave::where('employee_id', $employee_id)->where('status', $status)->where('created_by', \Auth::user()->creatorId());
            if ($type == 'yearly') {
                $leaveData->whereYear('start_date', $year);
            } else {
                $m = date('m', strtotime($month));
                $y = date('Y', strtotime($month));

                $leaveData->whereMonth('start_date', $m)->whereYear('start_date', $y);
            }

            $leaveData = $leaveData->get();


            return view('report.leaveShow', compact('leaves', 'leaveData'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function accountStatement(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            $accountList = AccountList::where('created_by', \Auth::user()->creatorId())->get()->pluck('account_name', 'id');
            $accountList->prepend('All', '');

            $filterYear['account'] = __('All');
            $filterYear['type']    = __('Income');


            if ($request->type == 'expense') {
                $accountData = Expense::orderBy('id');
                $accounts    = Expense::select('account_lists.id', 'account_lists.account_name')->leftjoin('account_lists', 'expenses.account_id', '=', 'account_lists.id')->groupBy('expenses.account_id')->selectRaw('sum(amount) as total');

                if (!empty($request->start_month) && !empty($request->end_month)) {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                } else {
                    $start = strtotime(date('Y-m'));
                    $end   = strtotime(date('Y-m', strtotime("-5 month")));
                }

                $currentdate = $start;

                while ($currentdate <= $end) {
                    $data['month'] = date('m', $currentdate);
                    $data['year']  = date('Y', $currentdate);

                    $accountData->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );

                    $accounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );

                    $currentdate = strtotime('+1 month', $currentdate);
                }

                $filterYear['startDateRange'] = date('M-Y', $start);
                $filterYear['endDateRange']   = date('M-Y', $end);

                if (!empty($request->account)) {
                    $accountData->where('account_id', $request->account);
                    $accounts->where('account_lists.id', $request->account);

                    $filterYear['account'] = !empty(AccountList::find($request->account)) ? Department::find($request->account)->account_name : '';
                }

                $accounts->where('expenses.created_by', \Auth::user()->creatorId());

                $filterYear['type'] = __('Expense');
            } else {
                $accountData = Deposit::orderBy('id');
                $accounts    = Deposit::select('account_lists.id', 'account_lists.account_name')->leftjoin('account_lists', 'deposits.account_id', '=', 'account_lists.id')->groupBy('deposits.account_id')->selectRaw('sum(amount) as total');

                if (!empty($request->start_month) && !empty($request->end_month)) {

                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                } else {
                    $start = strtotime(date('Y-m'));
                    $end   = strtotime(date('Y-m', strtotime("-5 month")));
                }

                $currentdate = $start;

                while ($currentdate <= $end) {
                    $data['month'] = date('m', $currentdate);
                    $data['year']  = date('Y', $currentdate);

                    $accountData->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );
                    $currentdate = strtotime('+1 month', $currentdate);

                    $accounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                        }
                    );
                    $currentdate = strtotime('+1 month', $currentdate);
                }

                $filterYear['startDateRange'] = date('M-Y', $start);
                $filterYear['endDateRange']   = date('M-Y', $end);

                if (!empty($request->account)) {
                    $accountData->where('account_id', $request->account);
                    $accounts->where('account_lists.id', $request->account);

                    $filterYear['account'] = !empty(AccountList::find($request->account)) ? Department::find($request->account)->account_name : '';
                }
                $accounts->where('deposits.created_by', \Auth::user()->creatorId());
            }

            $accountData->where('created_by', \Auth::user()->creatorId());
            $accountData = $accountData->get();

            $accounts = $accounts->get();


            return view('report.account_statement', compact('accountData', 'accountList', 'accounts', 'filterYear'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function payroll(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            $filterYear['branch']     = __('All');
            $filterYear['department'] = __('All');
            $filterYear['type']       = __('Monthly');

            $payslips = PaySlip::select('pay_slips.*', 'employees.name')->leftjoin('employees', 'pay_slips.employee_id', '=', 'employees.id')->where('pay_slips.created_by', \Auth::user()->creatorId());


            if ($request->type == 'monthly' && !empty($request->month)) {

                $payslips->where('salary_month', $request->month);

                $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                $filterYear['type']          = __('Monthly');
            } elseif (!isset($request->type)) {
                $month = date('Y-m');

                $payslips->where('salary_month', $month);

                $filterYear['dateYearRange'] = date('M-Y', strtotime($month));
                $filterYear['type']          = __('Monthly');
            }

            if ($request->type == 'yearly' && !empty($request->year)) {
                $startMonth = $request->year . '-01';
                $endMonth   = $request->year . '-12';
                $payslips->where('salary_month', '>=', $startMonth)->where('salary_month', '<=', $endMonth);

                $filterYear['dateYearRange'] = $request->year;
                $filterYear['type']          = __('Yearly');
            }

            if (!empty($request->branch)) {
                $payslips->where('employees.branch_id', $request->branch);

                $filterYear['branch'] = !empty(Branch::find($request->branch)) ? Branch::find($request->branch)->name : '';
            }

            if (!empty($request->department)) {
                $payslips->where('employees.department_id', $request->department);

                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }

            $payslips = $payslips->get();

            $totalBasicSalary = $totalNetSalary = $totalAllowance = $totalCommision = $totalLoan = $totalSaturationDeduction = $totalOtherPayment = $totalOverTime = 0;

            foreach ($payslips as $payslip) {
                $totalBasicSalary += $payslip->basic_salary;
                $totalNetSalary   += $payslip->net_payble;

                $allowances = json_decode($payslip->allowance);
                foreach ($allowances as $allowance) {
                    $totalAllowance += $allowance->amount;
                }

                $commisions = json_decode($payslip->commission);
                foreach ($commisions as $commision) {
                    $totalCommision += $commision->amount;
                }

                $loans = json_decode($payslip->loan);
                foreach ($loans as $loan) {
                    $totalLoan += $loan->amount;
                }

                $saturationDeductions = json_decode($payslip->saturation_deduction);
                foreach ($saturationDeductions as $saturationDeduction) {
                    $totalSaturationDeduction += $saturationDeduction->amount;
                }

                $otherPayments = json_decode($payslip->other_payment);
                foreach ($otherPayments as $otherPayment) {
                    $totalOtherPayment += $otherPayment->amount;
                }

                $overtimes = json_decode($payslip->overtime);
                foreach ($overtimes as $overtime) {
                    $days  = $overtime->number_of_days;
                    $hours = $overtime->hours;
                    $rate  = $overtime->rate;

                    $totalOverTime += ($rate * $hours) * $days;
                }
            }

            $filterData['totalBasicSalary']         = $totalBasicSalary;
            $filterData['totalNetSalary']           = $totalNetSalary;
            $filterData['totalAllowance']           = $totalAllowance;
            $filterData['totalCommision']           = $totalCommision;
            $filterData['totalLoan']                = $totalLoan;
            $filterData['totalSaturationDeduction'] = $totalSaturationDeduction;
            $filterData['totalOtherPayment']        = $totalOtherPayment;
            $filterData['totalOverTime']            = $totalOverTime;


            $starting_year = date('Y', strtotime('-5 year'));
            $ending_year   = date('Y', strtotime('+5 year'));

            $filterYear['starting_year'] = $starting_year;
            $filterYear['ending_year']   = $ending_year;

            return view('report.payroll', compact('payslips', 'filterData', 'branch', 'department', 'filterYear'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function monthlyAttendance(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            $data['branch']     = __('All');
            $data['department'] = __('All');

            if (!empty($request->month)) {
                $currentdate = strtotime($request->month);
                $month       = date('m', $currentdate);
                $year        = date('Y', $currentdate);
                $curMonth    = date('M-Y', strtotime($request->month));
            } else {
                $month    = date('m');
                $year     = date('Y');
                $curMonth = date('M-Y', strtotime($year . '-' . $month));
            }

            $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
            $dates = [];
            for ($i = 1; $i <= $num_of_days; $i++) {
                $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }

            $monthStart = $year . '-' . sprintf('%02d', $month) . '-01';
            $monthEnd   = $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $num_of_days);
            $creatorId  = \Auth::user()->creatorId();
            $data['sandwich_rules'] = \App\Models\SandwichLeaveRule::where('created_by', $creatorId)->where('is_active', 1)->get();

            // ── Resolve valid employees for this month ────────────────────────────
            // 1) Active employees whose user is active and who joined on/before $monthEnd
            $activeEmpIds = Employee::where('created_by', $creatorId)
                ->where('is_active', 1)
                ->whereHas('user', function ($q) {
                    $q->where('is_active', 1);
                })
                ->where(function ($q) use ($monthEnd) {
                    $q->whereNull('company_doj')
                      ->orWhere('company_doj', '<=', $monthEnd);
                })
                ->pluck('id')
                ->toArray();

            // 2) Inactive/terminated/deleted employees who have attendance or leave in THIS report month,
            //    or whose termination/resignation date is on or after $monthStart
            $attendanceEmpIds = \App\Models\AttendanceEmployee::where('created_by', $creatorId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->pluck('employee_id')
                ->toArray();

            $leaveEmpIds = Leave::where('created_by', $creatorId)
                ->where('status', 'Approved')
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date', '>=', $monthStart)
                ->pluck('employee_id')
                ->toArray();

            $terminatedEmpIds = \App\Models\Termination::where('created_by', $creatorId)
                ->where('termination_date', '>=', $monthStart)
                ->pluck('employee_id')
                ->toArray();

            $resignedEmpIds = \App\Models\Resignation::where('created_by', $creatorId)
                ->where('resignation_date', '>=', $monthStart)
                ->pluck('employee_id')
                ->toArray();

            $validEmpIds = array_unique(array_merge(
                $activeEmpIds,
                $attendanceEmpIds,
                $leaveEmpIds,
                $terminatedEmpIds,
                $resignedEmpIds
            ));

            $employees = Employee::select('id', 'name')
                ->whereIn('id', $validEmpIds)
                ->where('created_by', $creatorId);

            if (!empty($request->employee_id) && $request->employee_id[0] != 0) {
                $employees->whereIn('id', $request->employee_id);
            }

            if (!empty($request->branch_id)) {
                $employees->where('branch_id', $request->branch_id);
                $data['branch'] = !empty(Branch::find($request->branch_id)) ? Branch::find($request->branch_id)->name : '';
            }

            if (!empty($request->department)) {
                $employees->where('department_id', $request->department);
                $data['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }

            if (!empty($request->employees)) {
                $employees->where('employee_id', $request->employees);
                $data['employees'] = !empty(Employee::find($request->employees)) ? Employee::find($request->employees)->name : '';
            }

            $employees = $employees->get()->pluck('name', 'id');

            $employeesAttendance = [];
            $totalPresent        = $totalLeave = 0;
            $ovetimeHours        = $overtimeMins = $earlyleaveHours = $earlyleaveMins = $lateHours = $lateMins = 0;

            $tz = config('app.timezone') ?: 'UTC';

            // ── Working days & holidays for day-off detection ─────────────────
            // Use fetchSettings() directly to bypass the static cache — the cache can
            // hold stale values from earlier in the request (e.g. loaded by the layout).
            $company_settings = Utility::settings();

            $workingDaysRaw = $company_settings['rota_working_days'] ?? '1,2,3,4,5,6,0'; // default to all days if not set
            $workingDays    = array_map('intval', array_filter(array_map('trim', explode(',', $workingDaysRaw)), 'strlen'));
            $satPattern     = $company_settings['saturday_pattern'] ?? 'none';

            $monthStart = $year . '-' . $month . '-01';
            $monthEnd   = $year . '-' . $month . '-' . $num_of_days;

            // Build holiday date hash-set for the month
            $holidays = \App\Models\Holiday::where('created_by', \Auth::user()->creatorId())
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date',   '>=', $monthStart)
                ->get();
            $holidayDates = [];
            foreach ($holidays as $h) {
                $hPeriod = \Carbon\CarbonPeriod::create($h->start_date, $h->end_date);
                foreach ($hPeriod as $hDay) {
                    $holidayDates[$hDay->format('Y-m-d')] = $h->occasion ?? 'Holiday';
                }
            }

            // ── Build per-day leave map, respecting half-day and paid/unpaid ──────
            // Structure: $leaveDateMap[employee_id][date] = [
            //   'type'     => leave type name (string),
            //   'duration' => 'full_day' | 'half_day',
            //   'period'   => 'morning' | 'afternoon' | null,
            //   'status'   => 'paid' | 'unpaid',
            // ]
            $employeeIds = $employees->keys()->toArray();
            $approvedLeaves = Leave::whereIn('employee_id', $employeeIds)
                ->where('status', 'Approved')
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date',   '>=', $monthStart)
                ->with(['leaveType', 'dayDetails'])
                ->get();

            $leaveDateMap = []; // [employee_id][date] = detail array

            foreach ($approvedLeaves as $lv) {
                $leaveTypeName = optional($lv->leaveType)->title ?? 'Leave';
                $lvPeriod      = \Carbon\CarbonPeriod::create(
                    $lv->start_date instanceof \Carbon\Carbon ? $lv->start_date : \Carbon\Carbon::parse($lv->start_date),
                    $lv->end_date   instanceof \Carbon\Carbon ? $lv->end_date   : \Carbon\Carbon::parse($lv->end_date)
                );

                // Index existing day_details by date string for O(1) lookup
                $detailsByDate = $lv->dayDetails->keyBy(
                    fn($d) => $d->date instanceof \Carbon\Carbon
                        ? $d->date->format('Y-m-d')
                        : \Carbon\Carbon::parse($d->date)->format('Y-m-d')
                );

                foreach ($lvPeriod as $lvDay) {
                    $dk     = $lvDay->format('Y-m-d');
                    $detail = $detailsByDate->get($dk);

                    // Per-day detail takes priority; fall back to leave-level fields
                    $leaveTypePaid = optional($lv->leaveType)->is_paid ?? true;
                    if ($detail) {
                        $duration = $detail->day_duration    ?? 'full_day';
                        $period   = $detail->half_day_period ?? null;
                        $status   = $detail->day_status      ?? ($leaveTypePaid ? 'paid' : 'unpaid');
                        $sRuleId  = $detail->sandwich_leave_rule_id  ?? $lv->sandwich_leave_rule_id;
                        $sRate    = $detail->sandwich_deduction_rate ?? $lv->sandwich_deduction_rate;
                    } else {
                        // Single-day leave or legacy leave with no day_details rows
                        $duration = $lv->leave_duration   ?? 'full_day';
                        $period   = $lv->half_day_period  ?? null;
                        $status   = $leaveTypePaid ? 'paid' : 'unpaid';
                        $sRuleId  = $lv->sandwich_leave_rule_id;
                        $sRate    = $lv->sandwich_deduction_rate;
                    }

                    $leaveDateMap[$lv->employee_id][$dk] = [
                        'type'                    => $leaveTypeName,
                        'leave_type_id'           => $lv->leave_type_id,
                        'duration'                => $duration,
                        'period'                  => $period,
                        'status'                  => $status,
                        'leave_pay_type'          => $status, // 'paid' or 'unpaid' — used by modal
                        'use_leave_balance'       => 1,       // always deduct from balance for approved leaves
                        'sandwich_leave_rule_id'  => $sRuleId,
                        'sandwich_deduction_rate' => $sRate,
                    ];
                }
            }

            foreach ($employees as $id => $employee) {
                $attendances['name'] = $employee;
                $totalPresents = 0;
                $totalLeaves   = 0;      // counts in 0.5 increments for half-day leaves
                $totalAbsents  = 0;
                $totalDayOffs  = 0;
                $totalHolidays = 0;
                $totalHalfDays = 0;      // all half-days (clock + leave) — for THD column
                $clockHalfDays = 0;      // clock-based half-days only  (affect present score)
                $leaveHalfDays = 0;      // leave-based half-days only  (affect leave score)
                $totalPaidLeave   = 0;
                $totalUnpaidLeave = 0;
                $attendanceStatus = [];
                $totalWorkedSeconds = 0;

                $totalLunchSeconds = 0;
                $totalTeaSeconds = 0;

                foreach ($dates as $date) {
                    $dateFormat = $year . '-' . $month . '-' . $date;
                    $dayOfWeek  = (int) \Carbon\Carbon::parse($dateFormat)->dayOfWeek;
                    $isFuture   = $dateFormat > date('Y-m-d');

                    // ── Determine day type (applies to past AND future) ───────
                    $isHoliday  = isset($holidayDates[$dateFormat]);
                    $isDayOff   = !empty($workingDays) && !in_array($dayOfWeek, $workingDays);

                    // Alternate Saturday: Sat is in workingDays but this specific
                    // Saturday may be off depending on the pattern.
                    if (!$isDayOff && $dayOfWeek === 6 && in_array(6, $workingDays)) {
                        $isDayOff = !$this->isSaturdayWorking(\Carbon\Carbon::parse($dateFormat), $satPattern);
                    }

                    $isOnLeave  = isset($leaveDateMap[$id][$dateFormat]);
                    // ── Resolve leave detail for this specific date ────────────
                    $leaveDetail   = $isOnLeave ? $leaveDateMap[$id][$dateFormat] : null;
                    $leaveLabel    = $leaveDetail ? $leaveDetail['type']     : '';
                    $leaveDuration = $leaveDetail ? ($leaveDetail['duration'] ?? 'full_day') : 'full_day';
                    $leavePeriod   = $leaveDetail ? ($leaveDetail['period']   ?? null)       : null;
                    $leavePayStatus = $leaveDetail ? ($leaveDetail['status']  ?? 'paid')     : 'paid';
                    $leaveIsHalf   = ($leaveDuration === 'half_day');
                    $leaveDays     = $leaveIsHalf ? 0.5 : 1.0;

                    // Helper closure: record a leave occurrence into all relevant counters
                    $recordLeave = function() use (
                        &$totalLeaves, &$totalHalfDays, &$leaveHalfDays, &$totalLeave,
                        &$totalPaidLeave, &$totalUnpaidLeave,
                        $leaveIsHalf, $leaveDays, $leavePayStatus
                    ) {
                        $totalLeaves += $leaveDays;
                        $totalLeave  += $leaveDays;
                        if ($leaveIsHalf) {
                            $totalHalfDays++;
                            $leaveHalfDays++;
                        }
                        if ($leavePayStatus === 'paid') $totalPaidLeave   += $leaveDays;
                        else                           $totalUnpaidLeave += $leaveDays;
                    };

                    // Build a descriptive label suffix for half-day leaves
                    $leaveCellLabel = $leaveLabel;
                    if ($leaveIsHalf) {
                        $leaveCellLabel .= ' — Half Day' . ($leavePeriod ? (' ' . ucfirst($leavePeriod)) : '');
                    }
                    if ($leavePayStatus === 'unpaid') {
                        $leaveCellLabel .= ' (Unpaid)';
                    }

                    if ($isFuture) {
                        // Future date — show holiday/day-off/leave if applicable, else NA
                        if ($isHoliday) {
                            $attendanceStatus[$date] = [
                                'status' => 'PH',
                                'label'  => $holidayDates[$dateFormat],
                                'future' => true,
                                'clock_in' => '', 'clock_out' => '', 'company_shift_time' => '',
                            ];
                            $totalHolidays++;
                        } elseif ($isDayOff) {
                            $attendanceStatus[$date] = [
                                'status' => 'DO',
                                'label'  => 'Day Off',
                                'future' => true,
                                'clock_in' => '', 'clock_out' => '', 'company_shift_time' => '',
                            ];
                            $totalDayOffs++;
                        } elseif ($isOnLeave) {
                            $futureStatus = $leaveIsHalf ? 'HD' : 'L';
                            $attendanceStatus[$date] = [
                                'status'                  => $futureStatus,
                                'label'                   => $leaveCellLabel,
                                'future'                  => true,
                                'clock_in'                => '',
                                'clock_out'               => '',
                                'company_shift_time'      => '',
                                'leave_pay_type'          => $leavePayStatus,
                                'use_leave_balance'       => 1,
                                'leave_type_id'           => $leaveDetail['leave_type_id'] ?? '',
                                'sandwich_leave_rule_id'  => $leaveDetail['sandwich_leave_rule_id'] ?? '',
                                'sandwich_deduction_rate' => $leaveDetail['sandwich_deduction_rate'] ?? '',
                            ];
                            $recordLeave();
                        } else {
                            $attendanceStatus[$date] = [
                                'status' => 'NA',
                                'label'  => 'Not Added',
                                'future' => true,
                                'clock_in' => '', 'clock_out' => '', 'company_shift_time' => '',
                            ];
                        }
                        continue; // skip attendance DB query for future dates
                    }

                    // ── Past / today ──────────────────────────────────────────
                    $attendance = AttendanceEmployee::where('employee_id', $id)
                        ->where('date', $dateFormat)
                        ->first();

                    if ($isHoliday && empty($attendance)) {
                        $attendanceStatus[$date] = [
                            'status' => 'PH', 'label' => $holidayDates[$dateFormat],
                            'future' => false, 'clock_in' => '', 'clock_out' => '', 'company_shift_time' => '',
                        ];
                        $totalHolidays++;

                    } elseif ($isDayOff && empty($attendance)) {
                        $attendanceStatus[$date] = [
                            'status' => 'DO', 'label' => 'Day Off',
                            'future' => false, 'clock_in' => '', 'clock_out' => '', 'company_shift_time' => '',
                        ];
                        $totalDayOffs++;

                    } elseif ($isOnLeave && empty($attendance)) {
                        // No attendance record — pure leave day (or half-day leave)
                        $pureStatus = $leaveIsHalf ? 'HD' : 'L';
                        $attendanceStatus[$date] = [
                            'status'                  => $pureStatus,
                            'label'                   => $leaveCellLabel,
                            'future'                  => false,
                            'clock_in'                => '',
                            'clock_out'               => '',
                            'company_shift_time'      => '',
                            'leave_pay_type'          => $leavePayStatus,
                            'use_leave_balance'       => 1,
                            'leave_type_id'           => $leaveDetail['leave_type_id'] ?? '',
                            'sandwich_leave_rule_id'  => $leaveDetail['sandwich_leave_rule_id'] ?? '',
                            'sandwich_deduction_rate' => $leaveDetail['sandwich_deduction_rate'] ?? '',
                        ];
                        $recordLeave();

                    } elseif (!empty($attendance)) {

                        // Normalise date to string regardless of whether Eloquent cast it
                        $attDateStr = is_string($attendance->date)
                            ? $attendance->date
                            : $attendance->date->format('Y-m-d');

                        // Normalise clock values: accept H:i or H:i:s
                        $normaliseTime = function ($t) {
                            if (!$t) return null;
                            return (strlen($t) === 5) ? $t . ':00' : $t;
                        };

                        $clockInRaw  = $normaliseTime($attendance->clock_in);
                        $clockOutRaw = $normaliseTime($attendance->clock_out);

                        $dayWorkedSeconds = 0;
                        if (!empty($clockInRaw) && $clockInRaw !== '00:00:00') {
                            try {
                                $in = Carbon::createFromFormat('Y-m-d H:i:s', $attDateStr . ' ' . $clockInRaw, $tz);
                            } catch (\Exception $e) {
                                $in = null;
                            }

                            if ($in) {
                                if (!empty($clockOutRaw) && $clockOutRaw !== '00:00:00') {
                                    try {
                                        $out = Carbon::createFromFormat('Y-m-d H:i:s', $attDateStr . ' ' . $clockOutRaw, $tz);
                                    } catch (\Exception $e) {
                                        $out = null;
                                    }
                                    if ($out && $out->lessThan($in)) $out->addDay();
                                } else {
                                    // clock_out missing — try shift end time as fallback
                                    $out = null;
                                    if (!empty($attendance->company_shift_time)) {
                                        $shiftParts = explode(' - ', $attendance->company_shift_time);
                                        if (!empty($shiftParts[1])) {
                                            $shiftEnd = $normaliseTime(trim($shiftParts[1]));
                                            if ($shiftEnd && $shiftEnd !== '00:00:00') {
                                                try {
                                                    $out = Carbon::createFromFormat('Y-m-d H:i:s', $attDateStr . ' ' . $shiftEnd, $tz);
                                                    if ($out->lessThan($in)) $out->addDay();
                                                } catch (\Exception $e) { $out = null; }
                                            }
                                        }
                                    }
                                    // If still no clock_out and it's today, use current time
                                    if (!$out) {
                                        $out = ($attDateStr === Carbon::now($tz)->toDateString()) ? Carbon::now($tz) : null;
                                    }
                                }

                                if ($out) {
                                    $dayWorkedSeconds = $in->diffInSeconds($out);
                                    if (!empty($attendance->total_break) && $attendance->total_break !== '00:00:00') {
                                        [$bH, $bM, $bS] = array_map('intval', explode(':', $attendance->total_break));
                                        $dayWorkedSeconds -= ($bH * 3600 + $bM * 60 + $bS);
                                    }
                                    $dayWorkedSeconds = max(0, $dayWorkedSeconds);
                                }
                            } // end if ($in)
                        } // end if clockInRaw

                        // Duration-based classification:
                        //   < 2 hours  → treat as full-day leave (employee barely showed up)
                        //   2–6 hours  → half day
                        //   >= 6 hours → full present
                        $isFullDayLeave = ($dayWorkedSeconds > 0 && $dayWorkedSeconds < (2 * 3600));
                        $isHalfDay      = ($dayWorkedSeconds >= (2 * 3600) && $dayWorkedSeconds < (6 * 3600));

                        $ciDisplay = (!empty($attendance->clock_in)  && $attendance->clock_in  !== '00:00:00') ? substr($attendance->clock_in, 0, 5)  : '';
                        $coDisplay = (!empty($attendance->clock_out) && $attendance->clock_out !== '00:00:00') ? substr($attendance->clock_out, 0, 5) : '';
                        $shiftDisplay = $attendance->company_shift_time ?? '';

                        if (in_array($attendance->status, ['present', 'Present'])) {
                            if ($isOnLeave) {
                                // Employee clocked in but also has an approved leave for this day.
                                // Use clock-in duration to decide which wins:
                                //   < 2h  → full leave (ignore minimal clock-in)
                                //   2–6h  → half-day present + leave
                                //   >= 6h → leave takes priority (full leave day)
                                if ($isFullDayLeave) {
                                    $attendanceStatus[$date] = [
                                        'status'                  => $leaveIsHalf ? 'HD' : 'L',
                                        'label'                   => $leaveCellLabel,
                                        'future'                  => false,
                                        'clock_in'                => $ciDisplay,
                                        'clock_out'               => $coDisplay,
                                        'company_shift_time'      => $shiftDisplay,
                                        'leave_pay_type'          => $leavePayStatus,
                                        'use_leave_balance'       => 1,
                                        'leave_type_id'           => $leaveDetail['leave_type_id'] ?? '',
                                        'sandwich_leave_rule_id'  => $leaveDetail['sandwich_leave_rule_id'] ?? '',
                                        'sandwich_deduction_rate' => $leaveDetail['sandwich_deduction_rate'] ?? '',
                                    ];
                                    $recordLeave();
                                } elseif ($isHalfDay) {
                                    // Half present + half leave → counts as 0.5 toward score
                                    // $totalPresents++ then $clockHalfDays++ gives -0.5 from present side.
                                    // $recordLeave() must NOT also add +0.5 via leaveHalfDays
                                    // (the 0.5 leave portion is already accounted for by the clock half).
                                    $totalWorkedSeconds += $dayWorkedSeconds;
                                    $attendanceStatus[$date] = [
                                        'status'                  => 'HD',
                                        'label'                   => 'Half Day + ' . $leaveCellLabel,
                                        'future'                  => false,
                                        'clock_in'                => $ciDisplay,
                                        'clock_out'               => $coDisplay,
                                        'company_shift_time'      => $shiftDisplay,
                                        'leave_pay_type'          => $leavePayStatus,
                                        'use_leave_balance'       => 1,
                                        'leave_type_id'           => $leaveDetail['leave_type_id'] ?? '',
                                        'sandwich_leave_rule_id'  => $leaveDetail['sandwich_leave_rule_id'] ?? '',
                                        'sandwich_deduction_rate' => $leaveDetail['sandwich_deduction_rate'] ?? '',
                                    ];
                                    $totalPresents++;
                                    $totalHalfDays++;
                                    $clockHalfDays++;
                                    // Record the leave portion — but do NOT count it as a leave-based
                                    // half-day for score purposes (the clock half already covers the 0.5).
                                    $totalLeaves    += $leaveDays;
                                    $totalLeave     += $leaveDays;
                                    if ($leavePayStatus === 'paid') $totalPaidLeave   += $leaveDays;
                                    else                           $totalUnpaidLeave += $leaveDays;
                                } else {
                                    // >= 6h — leave still takes priority
                                    $attendanceStatus[$date] = [
                                        'status'                  => $leaveIsHalf ? 'HD' : 'L',
                                        'label'                   => $leaveCellLabel,
                                        'future'                  => false,
                                        'clock_in'                => $ciDisplay,
                                        'clock_out'               => $coDisplay,
                                        'company_shift_time'      => $shiftDisplay,
                                        'leave_pay_type'          => $leavePayStatus,
                                        'use_leave_balance'       => 1,
                                        'leave_type_id'           => $leaveDetail['leave_type_id'] ?? '',
                                        'sandwich_leave_rule_id'  => $leaveDetail['sandwich_leave_rule_id'] ?? '',
                                        'sandwich_deduction_rate' => $leaveDetail['sandwich_deduction_rate'] ?? '',
                                    ];
                                    $recordLeave();
                                }
                            } else {
                                // Normal present day (no approved leave)
                                if ($isFullDayLeave) {
                                    $attendanceStatus[$date] = [
                                        'status'            => 'L',
                                        'label'             => 'Leave (short attendance)',
                                        'future'            => false,
                                        'clock_in'          => $ciDisplay,
                                        'clock_out'         => $coDisplay,
                                        'company_shift_time'=> $shiftDisplay,
                                    ];
                                    // Short attendance with no approved leave: counts as unpaid absence
                                    $totalLeaves += 1;
                                    $totalLeave  += 1;
                                    $totalUnpaidLeave += 1;
                                } else {
                                    $totalWorkedSeconds += $dayWorkedSeconds;
                                    $isExplicitHalf = ($isHalfDay && empty($attendance->is_manual_by));
                                    $cellStatus = $isExplicitHalf ? 'HD' : 'P';
                                    $attendanceStatus[$date] = [
                                        'status'            => $cellStatus,
                                        'label'             => $isExplicitHalf ? 'Half Day' : 'Present',
                                        'future'            => false,
                                        'clock_in'          => $ciDisplay,
                                        'clock_out'         => $coDisplay,
                                        'company_shift_time'=> $shiftDisplay,
                                    ];
                                    $totalPresent++;
                                    $totalPresents++;
                                    if ($isExplicitHalf) {
                                        $totalHalfDays++;
                                        $clockHalfDays++;
                                    }

                                    $lunch = $attendance->total_lunch_time ?? '00:00:00';
                                    $tea   = $attendance->total_tea_time ?? '00:00:00';
                                    $totalLunchSeconds += $this->timeToSeconds($lunch);
                                    $totalTeaSeconds   += $this->timeToSeconds($tea);

                                    if ($attendance->overtime > 0) {
                                        $ovetimeHours += date('h', strtotime($attendance->overtime));
                                        $overtimeMins += date('i', strtotime($attendance->overtime));
                                    }
                                    if ($attendance->early_leaving > 0) {
                                        $earlyleaveHours += date('h', strtotime($attendance->early_leaving));
                                        $earlyleaveMins  += date('i', strtotime($attendance->early_leaving));
                                    }
                                    if ($attendance->late > 0) {
                                        $lateHours += date('h', strtotime($attendance->late));
                                        $lateMins  += date('i', strtotime($attendance->late));
                                    }
                                }
                            }
                        } else {
                            // Attendance record exists but status is not Present
                            $cellStatus = match($attendance->status) {
                                'Day Off', 'DO' => 'DO',
                                'Public Holiday', 'PH' => 'PH',
                                'Half Day', 'HD' => 'HD',
                                'Leave', 'L' => 'L',
                                'Absent', 'A' => 'A',
                                default => ($isOnLeave ? ($leaveIsHalf ? 'HD' : 'L') : 'A'),
                            };
                            $cellLabel  = match($cellStatus) {
                                'DO' => 'Day Off',
                                'PH' => 'Public Holiday',
                                'HD' => 'Half Day',
                                'L'  => 'Leave',
                                'A'  => 'Absent',
                                default => ($isOnLeave ? $leaveCellLabel : 'Absent'),
                            };
                            $attendanceStatus[$date] = [
                                'status'            => $cellStatus,
                                'label'             => $cellLabel,
                                'future'            => false,
                                'clock_in'          => '',
                                'clock_out'         => '',
                                'company_shift_time'=> $shiftDisplay,
                                'leave_pay_type'          => $attendance->leave_pay_type ?? ($leavePayStatus ?? 'unpaid'),
                                'use_leave_balance'       => $attendance->use_leave_balance ?? ($isOnLeave ? 1 : 0),
                                'leave_type_id'           => $attendance->leave_type_id ?? ($leaveDetail['leave_type_id'] ?? ''),
                                'sandwich_leave_rule_id'  => $attendance->sandwich_leave_rule_id ?? ($leaveDetail['sandwich_leave_rule_id'] ?? ''),
                                'sandwich_deduction_rate' => $attendance->sandwich_deduction_rate ?? ($leaveDetail['sandwich_deduction_rate'] ?? ''),
                            ];
                            if ($cellStatus === 'DO') {
                                $totalDayOffs++;
                            } elseif ($cellStatus === 'PH') {
                                $totalHolidays++;
                            } elseif ($cellStatus === 'HD') {
                                $totalHalfDays++;
                                $clockHalfDays++;
                                $totalPresents++;
                                $totalLeaves += 0.5;
                                $totalLeave += 0.5;
                                if (($attendance->leave_pay_type ?? 'unpaid') === 'paid') {
                                    $totalPaidLeave += 0.5;
                                } else {
                                    $totalUnpaidLeave += 0.5;
                                }
                            } elseif ($cellStatus === 'L') {
                                $totalLeaves++;
                                $totalLeave++;
                                if (($attendance->leave_pay_type ?? 'unpaid') === 'paid') {
                                    $totalPaidLeave += 1.0;
                                } else {
                                    $totalUnpaidLeave += 1.0;
                                }
                            } else { // 'A'
                                $totalAbsents++;
                                $totalLeave++;
                                if (($attendance->leave_pay_type ?? 'unpaid') === 'paid') {
                                    $totalPaidLeave += 1.0;
                                } else {
                                    $totalUnpaidLeave += 1.0;
                                }
                            }
                        }

                    } else {
                        // Past working day, no record → Absent
                        $attendanceStatus[$date] = [
                            'status' => 'A', 'label' => 'Absent',
                            'future' => false, 'clock_in' => '', 'clock_out' => '', 'company_shift_time' => '',
                        ];
                        $totalAbsents++;
                        $totalLeave++;
                    }
                }

                // ── Attendance score: present / (working days excl. holidays & day-offs) 
                $isCurrentMonth = ($year == date('Y') && $month == date('m'));
                $scoreCapDate   = $isCurrentMonth ? date('Y-m-d') : $monthEnd;
                $satPattern     = $company_settings['saturday_pattern'] ?? 'none';

                $workingDayCount = 0;

                foreach ($dates as $d) {
                    $df  = $year . '-' . $month . '-' . $d;
                    if ($df > $scoreCapDate) break; // stop at end-of-cap
                    $dow = (int) \Carbon\Carbon::parse($df)->dayOfWeek;
                    $isH = isset($holidayDates[$df]);
                    $isD = !empty($workingDays) && !in_array($dow, $workingDays);

                    // Alternate Saturday pattern: even when Sat(6) is in workingDays,
                    // check whether this specific Saturday is actually working.
                    if ($dow === 6 && in_array(6, $workingDays)) {
                        $carbonDay = \Carbon\Carbon::parse($df);
                        $isSaturdayWorking = $this->isSaturdayWorking($carbonDay, $satPattern);

                        $isD = !$isSaturdayWorking;
                        if ($isSaturdayWorking) {
                            $isH = false;
                        }
                    }

                    if (!$isH && !$isD) $workingDayCount++;
                }

                $total_lunch_break = $this->secondsToTime($totalLunchSeconds);
                $total_tea_break   = $this->secondsToTime($totalTeaSeconds);

                // ── Effective present days: full day = 1.0, half day = 0.5 ──
                // $totalPresents counts clock-based HD rows as 1, so subtract 0.5 per
                // clock-based half-day to get the true fractional present count.
                // Leave-based half-days are NOT in $totalPresents — they contribute 0.5
                // each to the score via $leaveHalfDays.
                $effectivePresent = ($totalPresents - ($clockHalfDays * 0.5))
                                  + ($leaveHalfDays * 0.5);
                // Format: strip trailing .0 so whole-number employees see "23" not "23.0"
                $effectivePresentFmt = ($effectivePresent == floor($effectivePresent))
                    ? (int) $effectivePresent
                    : $effectivePresent;

                // ── Extra days = days worked beyond the working-day count ────
                $extraDays = max(0, $effectivePresent - $workingDayCount);
                $extraDaysFmt = ($extraDays == floor($extraDays))
                    ? (int) $extraDays
                    : $extraDays;

                $attendances['status']            = $attendanceStatus;
                $attendances['employee_id']       = $id;
                $attendances['total_present']     = $totalPresents;
                $attendances['total_leave']        = $totalLeaves;
                $attendances['total_paid_leave']   = $totalPaidLeave;
                $attendances['total_unpaid_leave'] = $totalUnpaidLeave;
                $attendances['total_absent']       = $totalAbsents;
                $attendances['total_day_off']      = $totalDayOffs;
                $attendances['total_holiday']      = $totalHolidays;
                $attendances['total_half_day']     = $totalHalfDays;
                $attendances['total_hours']       = $this->secondsToTime($totalWorkedSeconds);
                $attendances['total_lunch_break'] = $total_lunch_break;
                $attendances['total_tea_break']   = $total_tea_break;
                $attendances['working_days']      = $workingDayCount;
                $attendances['extra_days']        = $extraDaysFmt;
                $attendances['attendance_score']  = $effectivePresentFmt . '/' . $workingDayCount;

                $employeesAttendance[] = $attendances;
            }

            $totalOverTime   = $ovetimeHours + ($overtimeMins / 60);
            $totalEarlyleave = $earlyleaveHours + ($earlyleaveMins / 60);
            $totalLate       = $lateHours + ($lateMins / 60);

            $data['totalOvertime']   = $totalOverTime;
            $data['totalEarlyLeave'] = $totalEarlyleave;
            $data['totalLate']       = $totalLate;
            $data['totalPresent']    = $totalPresent;
            $data['totalLeave']      = $totalLeave;
            $data['curMonth']        = $curMonth;

            $data['company_shifts'] = isset($company_settings['company_shifts']) ? json_decode($company_settings['company_shifts'], true) : [];

            // dd($employeesAttendance, $branch, $department, $employees, $dates, $data);
            return view('report.monthlyAttendance', compact('employeesAttendance', 'branch', 'department', 'employees', 'dates', 'data'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updateMonthlyAttendanceStatus(Request $request)
    {
        try {
            if (!\Auth::user()->can('Manage Report')) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }

            $request->validate([
                'employee_id' => 'required|integer|exists:employees,id',
                'date' => 'required|date',
                'status' => 'required|in:P,A,HD,L,DO,PH',
                'company_shift_time' => 'nullable|string|max:255',
                'passcode' => 'required|string|max:255',
                'leave_pay_type' => 'nullable|string|in:paid,unpaid',
                'use_leave_balance' => 'nullable',
            ]);
            $employee = Employee::find($request->employee_id);

            $passcode = trim((string)$request->passcode);

            $empPasscode      = $employee?->user?->passcode;
            $authUserPasscode = Auth::user()->passcode;
            $creatorUser      = \App\Models\User::find(Auth::user()->creatorId());
            $creatorPasscode  = $creatorUser?->passcode;

            $validPasscodes = array_map('strval', array_filter([
                $empPasscode,
                $authUserPasscode,
                $creatorPasscode,
            ]));

            if (!$employee || !in_array($passcode, $validPasscodes)) {
                return response()->json([
                    'success' => false,
                    'message' => __('Passcode verification failed')
                ], 200);
            }

            $statusVal = $request->status;

            $dbStatusMap = [
                'P'  => 'Present',
                'HD' => 'Half Day',
                'A'  => 'Absent',
                'L'  => 'Leave',
                'DO' => 'Day Off',
                'PH' => 'Public Holiday',
            ];
            $status = $dbStatusMap[$statusVal] ?? 'Present';

            $attendance = AttendanceEmployee::firstOrNew([
                'employee_id' => $request->employee_id,
                'date' => Carbon::parse($request->date)->format('Y-m-d'),
            ]);

            $company_start_time = !empty($request->company_shift_time) ? (explode(' - ', $request->company_shift_time)[0] ?? null) : null;
            $company_end_time   = !empty($request->company_shift_time) ? (explode(' - ', $request->company_shift_time)[1] ?? null) : null;

            $attendance->status = $status;
            $attendance->company_shift_time = $request->company_shift_time;

            $attendance->leave_pay_type = $request->leave_pay_type ?? 'unpaid';
            $attendance->use_leave_balance = ($request->has('use_leave_balance') && ($request->use_leave_balance == 1 || $request->use_leave_balance === 'true' || $request->use_leave_balance === 'on')) ? 1 : 0;
            $attendance->leave_type_id = !empty($request->leave_type_id) ? $request->leave_type_id : null;

            $sandwichRuleId = !empty($request->sandwich_leave_rule_id) ? $request->sandwich_leave_rule_id : null;
            $sandwichRule   = $sandwichRuleId ? \App\Models\SandwichLeaveRule::find($sandwichRuleId) : null;
            $sandwichRate   = $sandwichRule ? $sandwichRule->deduction_rate : null;

            $attendance->sandwich_leave_rule_id  = $sandwichRuleId;
            $attendance->sandwich_deduction_rate = $sandwichRate;

            // Sync with LeaveDayDetail if leave detail exists for this employee and date
            $targetDate = Carbon::parse($request->date)->format('Y-m-d');
            $dayDetail  = \App\Models\LeaveDayDetail::whereHas('leave', function ($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            })->where('date', $targetDate)->first();

            if ($dayDetail) {
                $dayDetail->sandwich_leave_rule_id  = $sandwichRuleId;
                $dayDetail->sandwich_deduction_rate = $sandwichRate;
                // Only sync day_status when admin explicitly sets a leave/absent status
                if (in_array($statusVal, ['L', 'HD', 'A'])) {
                    $dayDetail->day_status = $attendance->leave_pay_type;
                }
                $dayDetail->save();
            }

            $attendance->is_manual = true;
            $attendance->is_manual_by = \Auth::user()->id;

            if ($statusVal === 'P') {
                // Use manually entered clock_in/clock_out if provided, otherwise fall back to shift times
                $clockIn  = !empty($request->clock_in)  ? $request->clock_in  : $company_start_time;
                $clockOut = !empty($request->clock_out) ? $request->clock_out : $company_end_time;
                $attendance->clock_in  = $clockIn  ? Carbon::parse($request->date . ' ' . $clockIn)->format('H:i:s')  : null;
                $attendance->clock_out = $clockOut ? Carbon::parse($request->date . ' ' . $clockOut)->format('H:i:s') : null;
            } elseif ($statusVal === 'HD') {
                // Half Day: worked duration between 2h and 6h
                $clockIn  = !empty($request->clock_in)  ? $request->clock_in  : ($company_start_time ?: '09:00');
                $clockOut = !empty($request->clock_out) ? $request->clock_out : null;

                if (!$clockOut) {
                    $clockOut = Carbon::parse($request->date . ' ' . $clockIn)->addHours(4)->format('H:i');
                } else {
                    $inTime  = Carbon::parse($request->date . ' ' . $clockIn);
                    $outTime = Carbon::parse($request->date . ' ' . $clockOut);
                    if ($outTime->lessThan($inTime)) $outTime->addDay();
                    $workedSecs = $inTime->diffInSeconds($outTime);
                    if ($workedSecs >= (6 * 3600)) {
                        $clockOut = $inTime->copy()->addHours(4)->format('H:i');
                    }
                }

                $attendance->clock_in  = Carbon::parse($request->date . ' ' . $clockIn)->format('H:i:s');
                $attendance->clock_out = Carbon::parse($request->date . ' ' . $clockOut)->format('H:i:s');
            } else {
                $attendance->clock_in  = '00:00:00';
                $attendance->clock_out = '00:00:00';
            }

            $attendance->late = '00:00:00';
            $attendance->early_leaving = '00:00:00';
            $attendance->overtime = '00:00:00';

            $refreshType = $employee->refresh_type ?? 'none';
            if ($refreshType == 'fixed') {

                $lunchSeconds = ($employee->lunch_start && $employee->lunch_end)
                    ? Carbon::parse($employee->lunch_start)->diffInSeconds(Carbon::parse($employee->lunch_end))
                    : 0;

                $teaSeconds = ($employee->tea_start && $employee->tea_end)
                    ? Carbon::parse($employee->tea_start)->diffInSeconds(Carbon::parse($employee->tea_end))
                    : 0;

                $totalSeconds = $lunchSeconds + $teaSeconds;

                $attendance->total_lunch_time = $this->secondsToTime($lunchSeconds);
                $attendance->total_tea_time   = $this->secondsToTime($teaSeconds);
                $attendance->total_break      = $this->secondsToTime($totalSeconds);
            } else if ($refreshType == 'flexible') {

                $attendance->total_lunch_time = !empty($employee->lunch_minutes) ? $this->secondsToTime($employee->lunch_minutes * 60) : '00:00:00';
                $attendance->total_tea_time   = !empty($employee->tea_minutes)   ? $this->secondsToTime($employee->tea_minutes * 60)   : '00:00:00';
                $attendance->total_break = $this->secondsToTime(
                    $this->timeToSeconds($attendance->total_lunch_time) +
                    $this->timeToSeconds($attendance->total_tea_time)
                );
            } else {
                $attendance->total_lunch_time = '00:00:00';
                $attendance->total_tea_time   = '00:00:00';
                $attendance->total_break      = '00:00:00';
            }

            $attendance->total_rest = '00:00:00';

            if (!$attendance->exists) {
                $attendance->created_by = \Auth::user()->creatorId();
            }

            $attendance->save();

            // Recalculate row summary for the employee for the saved month
            $savedMonth = Carbon::parse($request->date);
            $monthStart = $savedMonth->copy()->startOfMonth()->toDateString();
            $monthEnd   = $savedMonth->copy()->endOfMonth()->toDateString();
            $tz = config('app.timezone') ?: 'UTC';

            $monthAttendances = AttendanceEmployee::where('employee_id', $request->employee_id)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->get();

            $totalWorkedSeconds = 0;
            $totalPresents      = 0;
            $totalLunchSeconds  = 0;
            $totalTeaSeconds    = 0;
            $totalLeaves        = 0;
            $totalHalfDays      = 0;
            $clockHalfDays      = 0;   // clock-based half-days (affect $totalPresents score)
            $leaveHalfDays      = 0;   // leave-based half-days (don't affect $totalPresents)
            $totalDayOffs       = 0;
            $totalHolidays      = 0;
            $totalPaidLeave     = 0;
            $totalUnpaidLeave   = 0;
            $totalAbsents       = 0;

            // Load working days + holidays once for the month (needed for TDO, TPH, score)
            // fetchSettings() bypasses the static cache to always get the current DB value.
            $settings       = \App\Models\Utility::settings();
            $workingDaysRaw = $settings['rota_working_days'] ?? '1,2,3,4,5';
            $workingDays    = array_map('intval', array_filter(array_map('trim', explode(',', $workingDaysRaw)), 'strlen'));
            $satPattern     = $settings['saturday_pattern'] ?? 'none';

            $holidaysInMonth = \App\Models\Holiday::where('created_by', \Auth::user()->creatorId())
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date',   '>=', $monthStart)
                ->get();
            $holidayDates = [];
            foreach ($holidaysInMonth as $h) {
                $hPeriod = \Carbon\CarbonPeriod::create($h->start_date, $h->end_date);
                foreach ($hPeriod as $hDay) {
                    $holidayDates[$hDay->format('Y-m-d')] = true;
                }
            }

            // Approved leaves for this employee in the month
            $approvedLeaves = \App\Models\Leave::where('employee_id', $request->employee_id)
                ->where('status', 'Approved')
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date',   '>=', $monthStart)
                ->with('dayDetails')
                ->get();
            $leaveDates     = [];  // date => ['duration'=>, 'status'=>]
            foreach ($approvedLeaves as $lv) {
                $detailsByDate = $lv->dayDetails->keyBy(
                    fn($d) => $d->date instanceof \Carbon\Carbon
                        ? $d->date->format('Y-m-d')
                        : \Carbon\Carbon::parse($d->date)->format('Y-m-d')
                );
                $lvPeriod = \Carbon\CarbonPeriod::create($lv->start_date, $lv->end_date);
                foreach ($lvPeriod as $lvDay) {
                    $dk     = $lvDay->format('Y-m-d');
                    $detail = $detailsByDate->get($dk);
                    $leaveDates[$dk] = [
                        'duration' => $detail ? ($detail->day_duration ?? 'full_day') : ($lv->leave_duration ?? 'full_day'),
                        'status'   => $detail ? ($detail->day_status  ?? 'paid')     : 'paid',
                    ];
                }
            }

            // Count TDO and TPH across the whole month up to today
            $allDates = \Carbon\CarbonPeriod::create($monthStart, $monthEnd);
            foreach ($allDates as $d) {
                if ($d->format('Y-m-d') > date('Y-m-d')) break;
                $dow = (int) $d->dayOfWeek;
                $df  = $d->format('Y-m-d');
                if (isset($holidayDates[$df])) {
                    // Only count as holiday if no attendance record
                    $hasAtt = $monthAttendances->firstWhere('date', $df);
                    if (!$hasAtt) $totalHolidays++;
                } elseif (!empty($workingDays) && !in_array($dow, $workingDays)) {
                    $hasAtt = $monthAttendances->firstWhere('date', $df);
                    if (!$hasAtt) $totalDayOffs++;
                } elseif ($dow === 6 && in_array(6, $workingDays)) {
                    // Saturday is in working days — but check alternate pattern
                    if (!$this->isSaturdayWorking($d, $satPattern)) {
                        $hasAtt = $monthAttendances->firstWhere('date', $df);
                        if (!$hasAtt) $totalDayOffs++;
                    }
                }
            }

            $isWorkDay = function($dateStr) use ($workingDays, $satPattern, $holidayDates) {
                $cd = Carbon::parse($dateStr);
                $dow = (int)$cd->dayOfWeek;
                if (isset($holidayDates[$dateStr])) return false;
                if (!empty($workingDays) && !in_array($dow, $workingDays)) return false;
                if ($dow === 6 && in_array(6, $workingDays)) {
                    if (!$this->isSaturdayWorking($cd, $satPattern)) return false;
                }
                return true;
            };

            foreach ($monthAttendances as $rec) {
                $recDate = is_string($rec->date) ? $rec->date : $rec->date->format('Y-m-d');
                if (in_array($rec->status, ['Day Off', 'DO'])) {
                    $totalDayOffs++;
                    continue;
                }
                if (in_array($rec->status, ['Public Holiday', 'PH'])) {
                    $totalHolidays++;
                    continue;
                }
                if (!$isWorkDay($recDate) && !in_array($rec->status, ['present', 'Present', 'P'])) {
                    // Attendance record marked Absent/Leave on a Day Off (Sat/Sun) — do not count as working day absent
                    continue;
                }
                if (in_array($rec->status, ['Half Day', 'HD'])) {
                    $totalHalfDays++;
                    $clockHalfDays++;
                    $totalPresents++;
                    $totalLeaves += 0.5;
                    if (($rec->leave_pay_type ?? 'unpaid') === 'paid') {
                        $totalPaidLeave += 0.5;
                    } else {
                        $totalUnpaidLeave += 0.5;
                    }
                    $totalLunchSeconds += $this->timeToSeconds($rec->total_lunch_time ?? '00:00:00');
                    $totalTeaSeconds   += $this->timeToSeconds($rec->total_tea_time   ?? '00:00:00');
                    continue;
                }
                if (!in_array($rec->status, ['present', 'Present'])) {
                    if (in_array($rec->status, ['Leave', 'L'])) {
                        $totalLeaves++;
                    } else {
                        $totalAbsents++;
                    }
                    if (($rec->leave_pay_type ?? 'unpaid') === 'paid') {
                        $totalPaidLeave += 1.0;
                    } elseif (isset($leaveDates[$recDate])) {
                        $ld       = $leaveDates[$recDate];
                        $ldays    = ($ld['duration'] === 'half_day') ? 0.5 : 1.0;
                        if ($ld['status'] === 'paid') $totalPaidLeave += $ldays; else $totalUnpaidLeave += $ldays;
                    } else {
                        $totalUnpaidLeave += 1.0;
                    }
                    continue;
                }
                $totalPresents++;
                $totalLunchSeconds += $this->timeToSeconds($rec->total_lunch_time ?? '00:00:00');
                $totalTeaSeconds   += $this->timeToSeconds($rec->total_tea_time   ?? '00:00:00');

                // Normalise clock values to H:i:s
                $normalise = fn($t) => $t ? ((strlen($t) === 5) ? $t . ':00' : $t) : null;
                $ciRaw = $normalise($rec->clock_in);
                $coRaw = $normalise($rec->clock_out);

                // If clock_out is missing but clock_in exists, try to derive clock_out
                // from the stored company_shift_time (format: "HH:MM - HH:MM")
                if (!empty($ciRaw) && $ciRaw !== '00:00:00'
                    && (empty($coRaw) || $coRaw === '00:00:00')
                    && !empty($rec->company_shift_time)
                ) {
                    $shiftParts = explode(' - ', $rec->company_shift_time);
                    if (!empty($shiftParts[1])) {
                        $coRaw = $normalise(trim($shiftParts[1]));
                    }
                }

                if (!empty($ciRaw) && $ciRaw !== '00:00:00') {
                    try {
                        $in = Carbon::createFromFormat('Y-m-d H:i:s', $recDate . ' ' . $ciRaw, $tz);
                    } catch (\Exception $e) { $in = null; }

                    if ($in && !empty($coRaw) && $coRaw !== '00:00:00') {
                        try {
                            $out = Carbon::createFromFormat('Y-m-d H:i:s', $recDate . ' ' . $coRaw, $tz);
                        } catch (\Exception $e) { $out = null; }

                        if ($out) {
                            if ($out->lessThan($in)) $out->addDay();
                            $daySeconds = $in->diffInSeconds($out);
                            if (!empty($rec->total_break) && $rec->total_break !== '00:00:00') {
                                [$bH, $bM, $bS] = array_map('intval', explode(':', $rec->total_break));
                                $daySeconds -= ($bH * 3600 + $bM * 60 + $bS);
                            }
                            $daySeconds = max(0, $daySeconds);
                            $totalWorkedSeconds += $daySeconds;
                            // Duration-based classification:
                            // < 2h  → full-day leave (don't count as present or half-day)
                            // 2–6h  → half day
                            // >= 6h → full present
                            if ($daySeconds > 0 && $daySeconds < (2 * 3600)) {
                                // Full-day leave: don't count as present
                                $totalWorkedSeconds -= $daySeconds; // undo the addition above
                                $totalPresents--;                   // undo the totalPresents++ above
                                $totalLeaves++;
                            } elseif ($daySeconds >= (2 * 3600) && $daySeconds < (6 * 3600)) {
                                if (!empty($rec->is_manual_by) && in_array($rec->status, ['present', 'Present', 'P'])) {
                                    // Manually marked Present by HR — do not convert to half day
                                } else {
                                    $totalHalfDays++;
                                    $clockHalfDays++;
                                }
                            }
                        }
                    }
                }
            }

            // Also count approved leave days where no attendance record exists
            foreach ($leaveDates as $ld => $lvDetail) {
                if ($ld > date('Y-m-d')) continue;
                $hasAtt = $monthAttendances->firstWhere('date', $ld);
                if (!$hasAtt) {
                    $ldays = ($lvDetail['duration'] === 'half_day') ? 0.5 : 1.0;
                    $totalLeaves += $ldays;
                    if ($lvDetail['duration'] === 'half_day') { $totalHalfDays++; $leaveHalfDays++; }
                    if ($lvDetail['status'] === 'paid') $totalPaidLeave += $ldays; else $totalUnpaidLeave += $ldays;
                }
            }

            // ── Effective present (float) for score ───────────────────────────
            $effectivePresentScore = ($totalPresents - ($clockHalfDays * 0.5))
                                   + ($leaveHalfDays * 0.5);

            return response()->json([
                'success'            => true,
                'status'             => $request->status,
                'label'              => $status,
                'clock_in'           => $attendance->clock_in  ? substr($attendance->clock_in, 0, 5)  : '',
                'clock_out'          => $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '',
                'company_shift_time' => $attendance->company_shift_time ?? '',
                'leave_pay_type'     => $attendance->leave_pay_type ?? 'unpaid',
                'use_leave_balance'  => $attendance->use_leave_balance ?? 0,
                'leave_type_id'      => $attendance->leave_type_id ?? '',
                'sandwich_leave_rule_id' => $attendance->sandwich_leave_rule_id ?? '',
                'row_summary'        => [
                    'total_hours'        => $this->secondsToTime($totalWorkedSeconds),
                    'total_present'      => $totalPresents,
                    'total_leave'        => $totalLeaves,
                    'total_paid_leave'   => $totalPaidLeave,
                    'total_unpaid_leave' => $totalUnpaidLeave,
                    'total_absent'       => $totalAbsents,
                    'total_half_day'     => $totalHalfDays,
                    'total_day_off'      => $totalDayOffs,
                    'total_holiday'      => $totalHolidays,
                    'total_lunch_break'  => $this->secondsToTime($totalLunchSeconds),
                    'total_tea_break'    => $this->secondsToTime($totalTeaSeconds),
                    'attendance_score'   => $this->calcAttendanceScore(
                                                $request->employee_id,
                                                $monthStart,
                                                $monthEnd,
                                                $effectivePresentScore
                                            ),
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in updateMonthlyAttendanceStatus: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }

    private function timeToSeconds($time)
    {
        if (!$time || $time === '00:00:00' || $time === '00:00') return 0;

        $parts = explode(':', $time);
        $h = (int) ($parts[0] ?? 0);
        $m = (int) ($parts[1] ?? 0);
        $s = (int) ($parts[2] ?? 0);

        return ($h * 3600) + ($m * 60) + $s;
    }

    private function secondsToTime($seconds)
    {
        // gmdate('H:i:s') wraps at 24 h — use integer arithmetic instead
        // so monthly totals like 184:30:00 display correctly.
        $seconds = max(0, (int) $seconds);
        $h = (int) floor($seconds / 3600);
        $m = (int) floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    /**
     * Decide whether a given date (Carbon instance) is a working Saturday
     * based on the saturday_pattern setting.
     *
     * pattern values:
     *   'all'  → every Saturday is working
     *   'odd'  → 1st, 3rd, 5th Saturday of the month
     *   'even' → 2nd, 4th Saturday of the month
     *   'none' (or anything else) → Saturday is never a working day
     *
     * This is only called when dow === 6 (Saturday) and Saturday IS in the
     * configured working-day set.
     */
    private function isSaturdayWorking(\Carbon\Carbon $date, string $pattern): bool
    {
        if ($pattern === 'all') {
            return true;
        }

        if ($pattern === 'none') {
            return false;
        }

        // Find which occurrence of Saturday this is in the month (1st, 2nd, 3rd…)
        $occurrence = (int) ceil($date->day / 7);

        if ($pattern === 'odd') {
            return ($occurrence % 2) === 1; // 1st, 3rd, 5th
        }

        if ($pattern === 'even') {
            return ($occurrence % 2) === 0; // 2nd, 4th
        }

        return false;
    }

    /**
     * Calculate attendance score string "present/workingDays" for a given employee
     * and month. Working days = days up to today, excluding holidays and day-offs.
     * $effectivePresent supports fractions (0.5 per half-day).
     */
    private function calcAttendanceScore(int $employeeId, string $monthStart, string $monthEnd, float $effectivePresent): string
    {
        // fetchSettings() bypasses the static cache to always get the current DB value.
        $settings       = \App\Models\Utility::settings();
        $workingDaysRaw = $settings['rota_working_days'] ?? '1,2,3,4,5';
        $workingDays    = array_map('intval', array_filter(array_map('trim', explode(',', $workingDaysRaw)), 'strlen'));
        $satPattern     = $settings['saturday_pattern'] ?? 'none';

        // Holiday dates for the month
        $holidays = \App\Models\Holiday::where('created_by', \Auth::user()->creatorId())
            ->where('start_date', '<=', $monthEnd)
            ->where('end_date',   '>=', $monthStart)
            ->get();
        $holidayDates = [];
        foreach ($holidays as $h) {
            $period = \Carbon\CarbonPeriod::create($h->start_date, $h->end_date);
            foreach ($period as $day) {
                $holidayDates[$day->format('Y-m-d')] = true;
            }
        }

        $workingDayCount = 0;
        $period = \Carbon\CarbonPeriod::create($monthStart, $monthEnd);

        // For a completed past month count ALL working days in the month.
        // For the current month only count up to today so the score grows day-by-day.
        $scoreCapDate = (substr($monthStart, 0, 7) === date('Y-m'))
            ? date('Y-m-d')
            : $monthEnd;

        foreach ($period as $day) {
            if ($day->format('Y-m-d') > $scoreCapDate) break;
            $dow = (int) $day->dayOfWeek;
            if (!empty($workingDays) && !in_array($dow, $workingDays)) continue;
            if (isset($holidayDates[$day->format('Y-m-d')])) continue;

            // Alternate Saturday check
            if ($dow === 6 && in_array(6, $workingDays)) {
                if (!$this->isSaturdayWorking($day, $satPattern)) continue;
            }

            $workingDayCount++;
        }

        $presentFmt = ($effectivePresent == floor($effectivePresent))
            ? (int) $effectivePresent
            : $effectivePresent;

        return $presentFmt . '/' . $workingDayCount;
    }

    public function timesheet(Request $request)
    {
        if (\Auth::user()->can('Manage Report')) {
            $branch = Branch::where('created_by', Auth::user()->creatorId())->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', Auth::user()->creatorId())->pluck('name', 'id');
            $department->prepend('All', '');

            $filterYear['branch']     = __('All');
            $filterYear['department'] = __('All');

            /**
             * Base Query
             */
            $attendances = AttendanceEmployee::select(
                'attendance_employees.*',
                'employees.name as employee_name',
                'employees.shift_id'
            )
                ->leftJoin('employees', 'attendance_employees.employee_id', '=', 'employees.id')
                ->where('employees.created_by', Auth::user()->creatorId());


            /**
             * Date Filter
             */
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $attendances->whereBetween('attendance_employees.date', [
                    $request->start_date,
                    $request->end_date
                ]);

                $filterYear['start_date'] = $request->start_date;
                $filterYear['end_date']   = $request->end_date;
            } else {
                $filterYear['start_date'] = date('Y-m-01');
                $filterYear['end_date']   = date('Y-m-t');

                $attendances->whereBetween('attendance_employees.date', [
                    $filterYear['start_date'],
                    $filterYear['end_date']
                ]);
            }

            /**
             * Branch / Department Filters
             */
            if (!empty($request->branch)) {
                $attendances->where('attendance_employees.branch_id', $request->branch);
                $filterYear['branch'] = optional(Branch::find($request->branch))->name;
            }

            if (!empty($request->department)) {
                $attendances->where('attendance_employees.department_id', $request->department);
                $filterYear['department'] = optional(Department::find($request->department))->name;
            }

            $attendances = $attendances->get();

            /**
             * Result containers
             */
            $timesheets = [];
            $timesheetFilters = [];

            /**
             * Helper for formatting minutes
             */
            $formatMinutes = function (int $minutes): string {
                $h = intdiv($minutes, 60);
                $m = $minutes % 60;
                return "{$h} hours {$m} minutes";
            };

            // Pre-load all shifts to avoid N+1 queries
            $shifts = Shift::all()->keyBy('id');
            foreach ($attendances as $attendance) {

                // Skip if no valid clock in
                if (empty($attendance->clock_in) || $attendance->clock_in === '00:00:00') {
                    continue;
                }

                // CLOCK IN
                $clockIn = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);

                // CLOCK OUT
                $clockOutTime = $attendance->clock_out;

                // If clock_out is 00:00:00 or empty, get shift end time
                if (empty($clockOutTime) || $clockOutTime === '00:00:00') {
                    // Try to get shift end time
                    if (!empty($attendance->shift_id) && isset($shifts[$attendance->shift_id])) {
                        $shift = $shifts[$attendance->shift_id];
                        if (!empty($shift->company_end_time) && $shift->company_end_time !== '00:00:00') {
                            $clockOutTime = $shift->company_end_time;
                        }
                    }

                    // If still no clock out time, use 8 hours default
                    if (empty($clockOutTime) || $clockOutTime === '00:00:00') {
                        $clockOut = clone $clockIn;
                        $clockOut->addHours(8);
                    } else {
                        $clockOut = Carbon::parse($attendance->date . ' ' . $clockOutTime);

                        // Handle night shift
                        if ($clockOut->lte($clockIn)) {
                            $clockOut->addDay();
                        }
                    }
                } else {
                    $clockOut = Carbon::parse($attendance->date . ' ' . $clockOutTime);

                    // Handle night shift
                    if ($clockOut->lte($clockIn)) {
                        $clockOut->addDay();
                    }
                }

                // TOTAL DURATION (MINUTES)
                $totalMinutes = $clockIn->diffInMinutes($clockOut);

                // BREAK TIME (HH:MM:SS)
                $breakMinutes = 0;
                if (!empty($attendance->total_break) && $attendance->total_break !== '00:00:00') {
                    $breakParts = explode(':', $attendance->total_break);
                    if (count($breakParts) >= 2) {
                        $bh = (int)$breakParts[0];
                        $bm = (int)$breakParts[1];
                        $breakMinutes = ($bh * 60) + $bm;
                    }
                }

                // WORKING TIME
                $workingMinutes = max(0, $totalMinutes - $breakMinutes);
                $workingHours   = round($workingMinutes / 60, 2);

                // DESCRIPTION
                $description = "Today's timesheet "
                    . $clockIn->format('h:i A') . " "
                    . $clockOut->format('h:i A')
                    . " : Total " . $formatMinutes($totalMinutes)
                    . " - Total Break time : " . $formatMinutes($breakMinutes)
                    . " = total working hours : " . $formatMinutes($workingMinutes);

                /** -----------------------------
                 * Table data
                 * ----------------------------- */
                $timesheets[] = (object)[
                    'employee_id' => $attendance->employee_id,
                    'employee'    => (object)[
                        'name' => $attendance->employee_name,
                    ],
                    'date'        => $attendance->date,
                    'hours'       => $workingHours,
                    'remark'      => $description,
                ];

                /** -----------------------------
                 * Employee summary cards
                 * ----------------------------- */
                if (!isset($timesheetFilters[$attendance->employee_id])) {
                    $timesheetFilters[$attendance->employee_id] = (object)[
                        'employee_id' => $attendance->employee_id,
                        'employee'    => (object)[
                            'name' => $attendance->employee_name,
                        ],
                        'total'       => 0,
                    ];
                }

                $timesheetFilters[$attendance->employee_id]->total += $workingHours;
            }

            /**
             * Final totals
             */
            $timesheetFilters = collect($timesheetFilters);

            $filterYear['totalEmployee'] = $timesheetFilters->count();
            $filterYear['totalHours']    = round($timesheetFilters->sum('total'), 2);

            return view('report.timesheet', compact(
                'timesheets',
                'branch',
                'department',
                'filterYear',
                'timesheetFilters'
            ));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function LeaveReportExport()
    {
        $name = 'leave_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new LeaveReportExport(), $name . '.xlsx');

        return $data;
    }

    public function AccountStatementReportExport(Request $request)
    {
        $name = 'Account Statement_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new accountstatementExport(), $name . '.xlsx');

        return $data;
    }

    public function PayrollReportExport($month, $branch, $department)
    {
        $data = [];
        $data['branch'] = __('All');
        $data['department'] = __('All');

        if ($branch != 0) {
            $data['branch'] = !empty(Branch::find($branch)) ? Branch::find($branch)->id : '';
        }

        if ($department != 0) {
            $data['department'] = !empty(Department::find($department)) ? Department::find($department)->id : '';
        }
        $data['month'] = $month;
        $name = 'Payroll_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new PayrollExport($data), $name . '.xlsx');

        return $data;
    }

    public function exportTimeshhetReport(Request $request)
    {
        $name = 'Timesheet_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new TimesheetReportExport(), $name . '.xlsx');

        return $data;
    }

    public function exportCsv($filter_month, $branch, $department, $employee)
    {
        $data['branch'] = __('All');
        $data['department'] = __('All');

        $employees = Employee::select('id', 'name')->where('created_by', \Auth::user()->creatorId());
        if ($branch != 0) {
            $employees->where('branch_id', $branch);
            $data['branch'] = !empty(Branch::find($branch)) ? Branch::find($branch)->name : '';
        }

        if ($department != 0) {
            $employees->where('department_id', $department);
            $data['department'] = !empty(Department::find($department)) ? Department::find($department)->name : '';
        }
        if ($employee != 0) {
            $employeeIds = explode(',', $employee);
            $emp = Employee::whereIn('id', $employeeIds);
        } else {
            $emp = Employee::where('created_by', \Auth::user()->creatorId());
        }

        $employees = $emp->get()->pluck('name', 'id');

        $currentdate = strtotime($filter_month);
        $month       = date('m', $currentdate);
        $year        = date('Y', $currentdate);
        $data['curMonth']    = date('M-Y', strtotime($filter_month));


        $fileName = $data['branch'] . ' ' . __('Branch') . ' ' . $data['curMonth'] . ' ' . __('Attendance Report of') . ' ' . $data['department'] . ' ' . __('Department') . ' ' . '.csv';

        $employeesAttendance = [];
        $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
        for ($i = 1; $i <= $num_of_days; $i++) {
            $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        foreach ($employees as $id => $employee) {
            $attendances['name'] = $employee;

            foreach ($dates as $date) {
                $dateFormat = $year . '-' . $month . '-' . $date;

                if ($dateFormat <= date('Y-m-d')) {
                    $employeeAttendance = AttendanceEmployee::where('employee_id', $id)->where('date', $dateFormat)->first();

                    if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                        $attendanceStatus[$date] = 'P';
                    } elseif (!empty($employeeAttendance) && $employeeAttendance->status == 'Leave') {
                        $attendanceStatus[$date] = 'A';
                    } else {
                        $attendanceStatus[$date] = '-';
                    }
                } else {
                    $attendanceStatus[$date] = '-';
                }
                $attendances[$date] = $attendanceStatus[$date];
            }

            $employeesAttendance[] = $attendances;
        }

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );
        $emp = array(
            'employee',
        );

        $columns = array_merge($emp, $dates);

        $callback = function () use ($employeesAttendance, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($employeesAttendance as $attendance) {
                fputcsv($file, str_replace('"', '', array_values($attendance)));
            }


            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getdepartment(Request $request)
    {
        if ($request->branch_id == 0) {
            $departments = Department::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('created_by', '=', Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }
        return response()->json($departments);
    }

    public function getemployee(Request $request)
    {
        if (!$request->department_id) {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        } else {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->where('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($employees);
    }
}
