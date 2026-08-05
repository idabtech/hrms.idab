<?php

namespace App\Http\Controllers;

use App\Exports\PayslipExport;
use App\Models\Allowance;
use App\Models\AttendanceEmployee;
use App\Models\Commission;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Loan;
use App\Models\AccountList;
use App\Models\Expense;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\PaySlip;
use App\Models\Resignation;
use App\Models\SaturationDeduction;
use App\Models\SalaryRevision;
use App\Models\Termination;
use App\Models\Utility;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class PaySlipController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('Manage Pay Slip') || \Auth::user()->type == 'employee') {
            $employees = Employee::where(
                [
                    'created_by' => \Auth::user()->creatorId(),
                ]
            )->first();

            $month = [
                '01' => 'JAN',
                '02' => 'FEB',
                '03' => 'MAR',
                '04' => 'APR',
                '05' => 'MAY',
                '06' => 'JUN',
                '07' => 'JUL',
                '08' => 'AUG',
                '09' => 'SEP',
                '10' => 'OCT',
                '11' => 'NOV',
                '12' => 'DEC',
            ];
            $currentyear = date("Y");
            $tempyear = intval($currentyear) - 2;
            $year = [];
            for ($i = 0; $i < 10; $i++) {
                $year[$tempyear + $i] = $tempyear + $i;
            }

            return view('payslip.index', compact('employees', 'month', 'year'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'month' => 'required',
                'year' => 'required',

            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $month = $request->month;
        $year = $request->year;
        $formate_month_year = $year . '-' . $month;
        $validatePaysilp = PaySlip::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->pluck('employee_id');
        $payslip_employee = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();
        if ($payslip_employee > count($validatePaysilp)) {

            $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('id', $validatePaysilp)->get();

            foreach ($employees as $employee) {

                $chek = PaySlip::where(['employee_id' => $employee->id, 'salary_month' => $formate_month_year])->first();
                $terminationDate = Termination::where('employee_id', $employee->id)
                    ->whereDate('termination_date', '<=', Carbon::create($year, $month)->endOfMonth())
                    ->where(function ($q) use ($employee) {
                        if (!empty($employee->company_doj)) {
                            $q->whereDate('termination_date', '>=', $employee->company_doj);
                        }
                    })
                    ->exists();

                $resignationDate = Resignation::where('employee_id', $employee->id)
                    ->whereDate('resignation_date', '<=', Carbon::create($year, $month)->endOfMonth())
                    ->where(function ($q) use ($employee) {
                        if (!empty($employee->company_doj)) {
                            $q->whereDate('resignation_date', '>=', $employee->company_doj);
                        }
                    })
                    ->exists();

                // payslip generate employee salary is less then 0 code
                $checkSalary = Employee::where('id', $employee->id)->where('created_by', \Auth::user()->creatorId())
                    ->where('salary', '<=', 0)
                    ->exists();

                if ($terminationDate || $resignationDate || $checkSalary) {
                    continue;
                }

                if (!$chek && $chek == null) {
                    // Apply any salary revision due in this payslip month FIRST,
                    // so the payslip snapshot uses the revised salary.
                    SalaryRevision::applyDueRevisions($employee->id, $formate_month_year);

                    // Re-fetch employee to get the updated salary if a revision was applied
                    $employee = Employee::find($employee->id);

                    $payslipEmployee = new PaySlip();
                    $payslipEmployee->employee_id = $employee->id;
                    $payslipEmployee->salary_month = $formate_month_year;
                    $payslipEmployee->status = 0;
                    $payslipEmployee->basic_salary = !empty($employee->salary) ? $employee->salary : 0;
                    $payslipEmployee->net_salary = !empty($employee->salary) ? $employee->salary / 2 : 0;
                    $payslipEmployee->allowance = Employee::allowance($employee->id);
                    $payslipEmployee->commission = Employee::commission($employee->id);
                    $payslipEmployee->loan = Employee::loan($employee->id);
                    $payslipEmployee->saturation_deduction = Employee::saturation_deduction($employee->id);
                    $payslipEmployee->other_payment = Employee::other_payment($employee->id);
                    $payslipEmployee->overtime = Employee::overtime($employee->id);
                    $payslipEmployee->created_by = \Auth::user()->creatorId();
                    // Set a temporary net_payble so the record can be saved first
                    $payslipEmployee->net_payble = 0;

                    $payslipEmployee->save();

                    // Now calculate the accurate net salary using employeePayslipDetail
                    // which reads attendance, leave, and LOP for the correct payslip month
                    $detail = Utility::employeePayslipDetail($employee->id, $formate_month_year);
                    $payslipEmployee->net_payble = round($detail['net_salary'], 2);
                    $payslipEmployee->save();

                    //Slack Notification
                    $setting = Utility::settings(\Auth::user()->creatorId());
                    if (isset($setting['monthly_payslip_notification']) && $setting['monthly_payslip_notification'] == 1) {
                        $uArr = [
                            'year' => $formate_month_year,
                        ];
                        Utility::send_slack_msg('new_monthly_payslip', $uArr);
                    }
                    //Telegram Notification
                    $setting = Utility::settings(\Auth::user()->creatorId());
                    if (isset($setting['telegram_monthly_payslip_notification']) && $setting['telegram_monthly_payslip_notification'] == 1) {

                        $uArr = [
                            'year' => $formate_month_year,
                        ];

                        Utility::send_telegram_msg('new_monthly_payslip', $uArr);
                    }

                    //twilio
                    $setting = Utility::settings(\Auth::user()->creatorId());
                    $emp = Employee::where('id', $payslipEmployee->employee_id)->first();
                    if (isset($setting['twilio_monthly_payslip_notification']) && $setting['twilio_monthly_payslip_notification'] == 1) {
                        $uArr = [
                            'year' => $formate_month_year,
                        ];
                        Utility::send_twilio_msg($emp->phone, 'new_monthly_payslip', $uArr);
                    }

                    //webhook
                    $module = 'New Monthly Payslip';
                    $webhook = Utility::webhookSetting($module);
                    if ($webhook) {
                        $parameter = json_encode($payslipEmployee);
                        // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                        $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                        if ($status == true) {
                            return response()->json(['success' => true, 'message' => __('Payslip successfully created.')]);
                        } else {
                            return response()->json(['success' => false, 'message' => __('Webhook call failed.')]);
                        }
                    }
                }
            }
            return response()->json(['success' => true, 'message' => __('Payslip successfully created.')]);
            // return redirect()->route('payslip.index')->with('success', __('Payslip successfully created.'));
        } else {
            // dd('Payslip Already created.');
            return response()->json(['success' => false, 'message' => __('Payslip Already created.')]);
        }
    }

    public function destroy($id)
    {
        $payslip = PaySlip::find($id);

        $payslip->delete();

        return true;
    }

    public function showemployee($paySlip)
    {

        $payslip = PaySlip::find($paySlip);


        return view('payslip.show', compact('payslip'));
    }

    /**
     * Payslip detail modal — shows salary breakdown for a specific employee/month.
     */
    public function detail($id, $month)
    {
        $payslip = PaySlip::where('employee_id', $id)
            ->where('salary_month', $month)
            ->where('created_by', \Auth::user()->creatorId())
            ->first();

        $employee = Employee::find($id);
        $payslipDetail = Utility::employeePayslipDetail($id, $month);
        $isUk = Utility::isUkRequest();

        return view('payslip.detail', compact('payslip', 'employee', 'payslipDetail', 'isUk'));
    }

    public function search_json(Request $request)
    {
        $formate_month_year = $request->datePicker;
        $validatePaysilp = PaySlip::where('salary_month', '=', $formate_month_year)
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->toArray();

        // Return empty array if no payslip is found
        if (empty($validatePaysilp)) {
            return response()->json([]);
        }

        // Query employee and payslip data
        $paylip_employee = PaySlip::select(
            [
                'employees.id',
                'employees.employee_id',
                'employees.name',
                'employees.salary',
                'payslip_types.name as payroll_type',
                'pay_slips.basic_salary',
                'pay_slips.net_salary',
                'pay_slips.id as pay_slip_id',
                'pay_slips.status',
                'employees.user_id',
            ]
        )->leftJoin('employees', function ($join) use ($formate_month_year) {
            $join->on('employees.id', '=', 'pay_slips.employee_id');
            $join->on('pay_slips.salary_month', '=', \DB::raw("'" . $formate_month_year . "'"));
            $join->leftJoin('payslip_types', 'payslip_types.id', '=', 'employees.salary_type');
        })->where('employees.created_by', \Auth::user()->creatorId())->get();

        $data = [];

        // Process employee and payslip data
        foreach ($paylip_employee as $employee) {
            $salary = 0;
            $basic_salary = 0;
            $total_loan = 0;
            $total_hra = 0;
            $total_da = 0;
            $total_allowance = 0;
            $total_bonus = 0;
            $total_commission = 0;
            $total_pension = 0;
            $total_saturation_deduction = 0;
            $liveNet = $employee->net_salary ?? 0;

            if (!empty($employee->pay_slip_id)) {
                try {
                    $detail  = Utility::employeePayslipDetail($employee->id, $formate_month_year);
                    if (!empty($detail)) {
                        $liveNet = round($detail['net_salary'] ?? 0, 2);
                        $salary = round($detail['salary'] ?? 0, 2);
                        $basic_salary = round($detail['basic_salary'] ?? 0, 2);
                        $total_loan = round($detail['totalLoan'] ?? 0, 2);
                        $total_hra = round($detail['hra'] ?? 0, 2);
                        $total_da = round($detail['da'] ?? 0, 2);
                        $total_allowance = round($detail['totalAllowance'] ?? 0, 2);
                        $total_bonus = round($detail['total_bonus'] ?? 0, 2);
                        $total_commission = round($detail['totalCommission'] ?? 0, 2);
                        $total_pension = round($detail['totalPansion'] ?? 0, 2);
                        $total_saturation_deduction = round($detail['total_saturation_deduction'] ?? 0, 2);
                    }
                } catch (\Throwable $_e) {
                    // fall back to stored value if detail fails
                }
            }

            if (\Auth::user()->type == 'employee') {
                if (\Auth::user()->id == $employee->user_id) {
                    $tmp = [];
                    $tmp[] = $employee->id;
                    $tmp[] = $employee->name;
                    $tmp[] = $employee->payroll_type;
                    $tmp[] = $employee->pay_slip_id;
                    $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->basic_salary) : '-';
                    $tmp[] = $salary > 0 ? \Auth::user()->priceFormat($salary) : '-';
                    $tmp[] = $liveNet > 0 ? \Auth::user()->priceFormat($liveNet) : '-';
                    $tmp[] = $total_hra > 0 ? \Auth::user()->priceFormat($total_hra) : '-';
                    $tmp[] = $total_da > 0 ? \Auth::user()->priceFormat($total_da) : '-';
                    $tmp[] = $total_allowance > 0 ? \Auth::user()->priceFormat($total_allowance) : '-';
                    $tmp[] = $total_pension > 0 ? \Auth::user()->priceFormat($total_pension) : '-';
                    $tmp[] = $total_loan > 0 ? \Auth::user()->priceFormat($total_loan) : '-';
                    $tmp[] = $total_commission > 0 ? \Auth::user()->priceFormat($total_commission) : '-';
                    $tmp[] = $total_saturation_deduction > 0 ? \Auth::user()->priceFormat($total_saturation_deduction) : '-';
                    $tmp[] = $total_bonus > 0 ? \Auth::user()->priceFormat($total_bonus) : '-';
                    $tmp[] = $employee->status == 1 ? 'paid' : 'unpaid';
                    $tmp[] = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                    $tmp['url'] = route('employee.show', Crypt::encrypt($employee->id));
                    $data[] = $tmp;
                }
            } else {
                $tmp = [];
                $tmp[] = $employee->id;
                $tmp[] = \Auth::user()->employeeIdFormat($employee->employee_id);
                $tmp[] = $employee->name;
                $tmp[] = $employee->payroll_type;
                $tmp[] = !empty($employee->salary) ? \Auth::user()->priceFormat($employee->salary) : '-';
                $tmp[] = $salary > 0 ? \Auth::user()->priceFormat($salary) : '-';
                $tmp[] = $liveNet > 0 ? \Auth::user()->priceFormat($liveNet) : '-';
                $tmp[] = $total_hra > 0 ? \Auth::user()->priceFormat($total_hra) : '-';
                $tmp[] = $total_da > 0 ? \Auth::user()->priceFormat($total_da) : '-';
                $tmp[] = $total_allowance > 0 ? \Auth::user()->priceFormat($total_allowance) : '-';
                $tmp[] = $total_pension > 0 ? \Auth::user()->priceFormat($total_pension) : '-';
                $tmp[] = $total_loan > 0 ? \Auth::user()->priceFormat($total_loan) : '-';
                $tmp[] = $total_commission > 0 ? \Auth::user()->priceFormat($total_commission) : '-';
                $tmp[] = $total_saturation_deduction > 0 ? \Auth::user()->priceFormat($total_saturation_deduction) : '-';
                $tmp[] = $total_bonus > 0 ? \Auth::user()->priceFormat($total_bonus) : '-';
                $tmp[] = $employee->status == 1 ? 'Paid' : 'UnPaid';
                $tmp[] = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                $tmp['url'] = route('employee.show', Crypt::encrypt($employee->id));
                $data[] = $tmp;
            }
        }

        // Return the data as a JSON response with status 200
        return response()->json($data, 200);
    }


    public function paysalary($id, $date)
    {
        $employeePayslip = PaySlip::where('employee_id', '=', $id)->where('created_by', \Auth::user()->creatorId())->where('salary_month', '=', $date)->first();
        $get_employee = Employee::where('id', $id)->where('created_by', \Auth::user()->creatorId())->first();
        $get_account = AccountList::where('id', $get_employee->account_type)->where('created_by', \Auth::user()->creatorId())->first();
        $initial_balance = !empty($get_account->initial_balance) ? $get_account->initial_balance : 0;
        $net_salary = !empty($employeePayslip->net_payble) ? $employeePayslip->net_payble : 0;
        if (!empty($employeePayslip)) {
            $employeePayslip->status = 1;
            $employeePayslip->save();

            if ($get_account) {
                $total_balance = $initial_balance - $net_salary;
                $get_account->initial_balance = $total_balance;
                $get_account->save();
            }

            $set_expense = new Expense();
            $set_expense->account_id = $get_account?->id ?? null;
            $set_expense->amount = $employeePayslip->net_payble;
            $set_expense->date = date('Y-m-d');
            $set_expense->expense_category_id = '';
            $set_expense->payee_id = $get_employee->id;
            $set_expense->payment_type_id = '';
            $set_expense->referal_id = '';
            $set_expense->description = '';
            $set_expense->created_by = $get_employee->created_by;
            $set_expense->save();

            // ── HMRC RTI: Submit FPS after payment (UK employees with NI number) ──
            if (!empty($get_employee->ni_number) && \App\Services\HmrcRtiService::isConfigured()) {
                try {
                    $rtiService = new \App\Services\HmrcRtiService();
                    $fpsResult  = $rtiService->submitFps($get_employee->id, $date);

                    if (!$fpsResult['success']) {
                        \Log::warning('HMRC FPS submission failed', [
                            'employee_id'  => $get_employee->id,
                            'salary_month' => $date,
                            'message'      => $fpsResult['message'],
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Log::error('HMRC FPS submission exception', [
                        'employee_id'  => $get_employee->id,
                        'salary_month' => $date,
                        'error'        => $e->getMessage(),
                    ]);
                }
            }

            return redirect()->route('payslip.index')->with('success', __('Payslip Payment successfully.'));
        } else {
            return redirect()->route('payslip.index')->with('error', __('Payslip Payment failed.'));
        }
    }

    public function bulk_pay_create($date)
    {
        $Employees = PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->get();
        $unpaidEmployees = PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->where('status', '=', 0)->get();

        return view('payslip.bulkcreate', compact('Employees', 'unpaidEmployees', 'date'));
    }

    public function bulkpayment(Request $request, $date)
    {
        $unpaidEmployees = PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->where('status', '=', 0)->get();

        foreach ($unpaidEmployees as $employee) {
            $employee->status = 1;
            $employee->save();
        }

        return redirect()->route('payslip.index')->with('success', __('Payslip Bulk Payment successfully.'));
    }

    public function employeepayslip()
    {
        $employees = Employee::where(
            [
                'user_id' => \Auth::user()->id,
            ]
        )->first();

        $payslip = PaySlip::where('employee_id', '=', $employees->id)->get();

        return view('payslip.employeepayslip', compact('payslip'));
    }

    /**
     * ---------------------------------------------------------------
     * SHARED: Prepare all display data for payslip templates.
     *
     * Returns an array of ALL variables that any payslip template
     * (pdf, ukpdf, compact, professional) needs — earnings rows,
     * deduction rows, theme colors, currency, company info, UK
     * fields, stamp, etc.
     *
     * Call this from pdf(), downloadPdf(), settingsPreview() and
     * extract the result with compact() or pass it directly.
     * ---------------------------------------------------------------
     */
    public static function preparePayslipTemplateData(
        $employee,
        $payslip,
        array $payslipDetail,
        ?string $previewColor = null,
        bool   $previewMode = false,
        bool   $forDownload = false
    ): array {
        // Coerce null → empty string so downstream code always has a string
        $previewColor = $previewColor ?? '';

        [$slipYear, $slipMonth] = explode('-', $payslip->salary_month);

        // ── Logo / Download URL ──────────────────────────────────────────────
        $logo       = \App\Models\Utility::get_file('uploads/logo/');
        $companyLogo = \App\Models\Utility::get_company_logo();
        $logoUrl    = $logo . '/' . (!empty($companyLogo) ? $companyLogo : 'logo-dark.png');
        $downloadUrl = $forDownload
            ? ''
            : route('payslip.download', [$employee->id, $payslip->salary_month]);

        // ── Theme color ──────────────────────────────────────────────────────
        $_previewColor = !empty($previewColor) ? $previewColor : null;
        $_colorSetting = \App\Models\Utility::colorset();
        $_appSettings  = \App\Models\Utility::settings();
        $_savedPayslipColor = !empty($_appSettings['payslip_primary_color'])
            ? $_appSettings['payslip_primary_color']
            : null;
        $_themeName = $_previewColor
            ? $_previewColor
            : ($_savedPayslipColor ?: ($_colorSetting['theme_color'] ?? 'theme-2'));
        $_themeHexMap = [
            'theme-1' => '#0CAF60', 'theme-2' => '#584ED2', 'theme-3' => '#6FD943',
            'theme-4' => '#145388', 'theme-5' => '#B9406B', 'theme-6' => '#008ECC',
            'theme-7' => '#922C88', 'theme-8' => '#C0A145', 'theme-9' => '#48494B',
            'theme-10' => '#0C7785',
        ];
        $_themeColor = str_starts_with($_themeName, '#')
            ? $_themeName
            : ($_themeHexMap[$_themeName] ?? '#584ED2');

        // RGBA helper
        $_hex = ltrim($_themeColor, '#');
        $_r   = hexdec(substr($_hex, 0, 2));
        $_g   = hexdec(substr($_hex, 2, 2));
        $_b   = hexdec(substr($_hex, 4, 2));
        $_bgRgba  = "rgba({$_r},{$_g},{$_b},0.10)";
        $_bgLight = "rgba({$_r},{$_g},{$_b},0.06)";
        $_bgMed   = "rgba({$_r},{$_g},{$_b},0.12)";

        // ── Currency ─────────────────────────────────────────────────────────
        $_currSymbol = $_appSettings['site_currency_symbol'] ?? '';
        $_currPos    = $_appSettings['site_currency_symbol_position'] ?? 'pre';
        $_fmtMoney   = function ($amount) use ($_currSymbol, $_currPos) {
            $n = number_format((float) $amount, 2);
            return $_currPos === 'pre' ? $_currSymbol . $n : $n . $_currSymbol;
        };

        // ── Month ────────────────────────────────────────────────────────────
        $carbonMonth  = \Illuminate\Support\Carbon::createFromDate((int) $slipYear, (int) $slipMonth, 1);
        $slipLabel    = $carbonMonth->format('F Y');
        $slipShort    = $carbonMonth->format('M Y');
        $payDate      = $carbonMonth->copy()->endOfMonth()->format('d M Y');

        // ── Values from payslipDetail ────────────────────────────────────────
        $_storedSalary        = (float) ($payslipDetail['basic_salary'] ?? 0);
        $_basicSalary         = (float) ($employee->basic_salary ?? 0);
        $_salary              = (float) ($payslipDetail['salary'] ?? 0);
        $_hra                 = (float) ($payslipDetail['hra'] ?? 0);
        $_da                  = (float) ($payslipDetail['da'] ?? 0);
        $perDaySalary         = round((float) ($payslipDetail['per_day_amount'] ?? 0), 2);
        $perHourSalary        = round((float) ($payslipDetail['per_hour_amount'] ?? 0), 2);
        $officeDays           = (int) ($payslipDetail['office_days'] ?? 0);
        $presentDays          = (float) ($payslipDetail['present_days'] ?? 0);
        $approvedLeaves       = (float) ($payslipDetail['approved_leaves_month'] ?? 0);
        $disapprovedLeaves    = (int) ($payslipDetail['rejected_leaves_month'] ?? 0);
        $absentDays           = (float) ($payslipDetail['absent_days'] ?? 0);
        $extraDays            = (float) ($payslipDetail['extra_days'] ?? 0);
        $totalWorkHours       = (float) ($payslipDetail['total_work_hours'] ?? 0);
        $avgHrsPerDay         = (float) ($payslipDetail['avg_hrs_per_day'] ?? 0);
        $totalLeaveAlloc      = (int) ($payslipDetail['total_leave_alloc'] ?? 0);
        $remainingLeaves      = (int) ($payslipDetail['remaining_leaves'] ?? 0);
        $paidLeaveDays        = (float) ($payslipDetail['paid_leave_days'] ?? 0);
        $unpaidLeaveDays      = (float) ($payslipDetail['unpaid_leave_days'] ?? 0);
        $unpaidLeaveDeduction = (float) ($payslipDetail['unpaid_leave_deduction'] ?? 0);
        $sandwichLeaveDeduction = (float) ($payslipDetail['sandwich_leave_deduction'] ?? 0);
        $sandwichLeaveDays    = (float) ($payslipDetail['sandwich_leave_days'] ?? 0);
        $preJoiningDeduction  = (float) ($payslipDetail['pre_joining_deduction'] ?? 0);
        $preJoiningDays       = (float) ($payslipDetail['pre_joining_days'] ?? 0);
        $daysPaid             = (float) ($payslipDetail['days_paid'] ?? ($presentDays + $paidLeaveDays));
        $isHourly             = (bool) ($payslipDetail['is_hourly'] ?? false);
        $hoursPerDay          = (float) ($payslipDetail['hours_per_day'] ?? 8);
        $totalShiftHours      = (float) ($payslipDetail['total_shift_hours'] ?? ($officeDays * $hoursPerDay));
        $salaryRate           = (float) ($payslipDetail['salary_rate'] ?? $_storedSalary);
        $isPaid               = ($payslip->status ?? 0) == 1;
        $payslipTypeName      = optional(\App\Models\PayslipType::find($employee->salary_type ?? null))->name ?? 'Monthly';
        $usedLeaves           = $approvedLeaves;

        $salaryDayCalc        = $payslipDetail['salary_day_calculation'] ?? 'working_days';
        $isMonthWise          = in_array($salaryDayCalc, ['month_wise', 'calendar_month']);
        $workingDaysLabel     = $isMonthWise ? __('Month Days') : __('Working Days');
        $dayOffDays           = (float) ($payslipDetail['day_off_days'] ?? 0);
        $dayOffDaysFmt        = ($dayOffDays == floor($dayOffDays)) ? (int) $dayOffDays : $dayOffDays;

        // ── Format helpers ───────────────────────────────────────────────────
        $presentDisplay  = min($presentDays, $officeDays);
        $presentDaysFmt  = ($presentDisplay == floor($presentDisplay)) ? (int) $presentDisplay : $presentDisplay;
        $absentDaysFmt   = ($absentDays == floor($absentDays)) ? (int) $absentDays : $absentDays;
        $extraDaysFmt    = ($extraDays == floor($extraDays)) ? (int) $extraDays : $extraDays;

        // ── Earnings rows ────────────────────────────────────────────────────
        $_basicLabel = $isHourly ? __('Gross Earned') : __('Basic Salary');
        $earRows = [['label' => $_basicLabel, 'amount' => $_salary]];

        foreach ($payslipDetail['earning']['allowance'] ?? [] as $_ar) {
            foreach (json_decode($_ar->allowance ?? '[]') as $_a) {
                $earRows[] = [
                    'label'  => $_a->title ?? '',
                    'amount' => ($_a->type ?? 'fixed') === 'percentage'
                        ? round(($_a->amount ?? 0) * $_basicSalary / 100, 2)
                        : (float) ($_a->amount ?? 0),
                ];
            }
        }
        foreach ($payslipDetail['earning']['commission'] ?? [] as $_cr) {
            foreach (json_decode($_cr->commission ?? '[]') as $_c) {
                $earRows[] = [
                    'label'  => $_c->title ?? '',
                    'amount' => ($_c->type ?? 'fixed') === 'percentage'
                        ? round(($_c->amount ?? 0) * $_basicSalary / 100, 2)
                        : (float) ($_c->amount ?? 0),
                ];
            }
        }
        foreach ($payslipDetail['earning']['bonous'] ?? [] as $_b) {
            $earRows[] = [
                'label'  => $_b->title ?: __('Bonus'),
                'amount' => ($_b->type ?? 'fixed') === 'percentage'
                    ? round(($_b->amount ?? 0) * $_basicSalary / 100, 2)
                    : (float) ($_b->amount ?? 0),
            ];
        }
        foreach ($payslipDetail['earning']['otherPayment'] ?? [] as $_op2) {
            foreach (json_decode($_op2->other_payment ?? '[]') as $_op) {
                $earRows[] = [
                    'label'  => $_op->title ?? '',
                    'amount' => ($_op->type ?? 'fixed') === 'percentage'
                        ? round(($_op->amount ?? 0) * $_basicSalary / 100, 2)
                        : (float) ($_op->amount ?? 0),
                ];
            }
        }
        // Overtime
        foreach ($payslipDetail['earning']['overTime'] ?? [] as $_ot2) {
            foreach (json_decode($_ot2->overtime ?? '[]') as $_ot) {
                $earRows[] = [
                    'label'  => $_ot->title ?: __('Overtime'),
                    'amount' => (float) (($_ot->number_of_days ?? 0) * ($_ot->hours ?? 0) * ($_ot->rate ?? 0)),
                ];
            }
        }
        // Loan/Advance
        foreach ($payslipDetail['earning']['loan'] ?? [] as $_lr) {
            foreach (json_decode($_lr->loan ?? '[]') as $_ln) {
                $_amt = ($_ln->type ?? 'fixed') === 'percentage'
                    ? round(($_ln->amount ?? 0) * $_basicSalary / 100, 2)
                    : (float) ($_ln->amount ?? 0);
                if ($_amt > 0) {
                    $earRows[] = ['label' => $_ln->title ?: __('Loan/Advance'), 'amount' => $_amt];
                }
            }
        }

        // ── Deductions rows ──────────────────────────────────────────────────
        $dedRows = [];
        $totalLoanRepayment = (float) ($payslipDetail['totalLoanRepayment'] ?? 0);
        if ($totalLoanRepayment > 0) {
            $dedRows[] = ['label' => __('Loan Repayment'), 'amount' => $totalLoanRepayment];
        }
        foreach ($payslipDetail['deduction']['saturation_deduction'] ?? [] as $_dr2) {
            foreach (json_decode($_dr2->saturation_deduction ?? '[]') as $_dd) {
                $titleLower = strtolower($_dd->title ?? '');
                if ($titleLower === 'epf') {
                    $_amt = ($_basicSalary * 12) / 100;
                } elseif ($titleLower === 'gpf') {
                    $_amt = ($_basicSalary * 6) / 100;
                } else {
                    $_amt = ($_dd->type ?? 'fixed') === 'percentage'
                        ? round(($_dd->amount ?? 0) * $_basicSalary / 100, 2)
                        : (float) ($_dd->amount ?? 0);
                }
                if ($_amt > 0) {
                    $dedRows[] = ['label' => $_dd->title ?? '', 'amount' => $_amt];
                }
            }
        }
        foreach ($payslipDetail['deduction']['pansion'] ?? [] as $_p) {
            $_amt = ($_p->type ?? 'fixed') === 'percentage'
                ? round(($_p->amount ?? 0) * $_basicSalary / 100, 2)
                : (float) ($_p->amount ?? 0);
            if ($_amt > 0) {
                $dedRows[] = ['label' => $_p->title ?: __('Provident Fund'), 'amount' => $_amt];
            }
        }
        if ($unpaidLeaveDeduction > 0) {
            $dedRows[] = [
                'label'  => __('Unpaid Leave') . ' (' . ($unpaidLeaveDays == floor($unpaidLeaveDays) ? (int)$unpaidLeaveDays : $unpaidLeaveDays) . ' ' . __('days') . ')',
                'amount' => $unpaidLeaveDeduction,
            ];
        } else {
            foreach ($payslipDetail['deduction']['leave'] ?? [] as $_lea) {
                if (($_lea->empleave ?? 0) > 0) {
                    $dedRows[] = ['label' => __('Loss Of Pay'), 'amount' => (float) $_lea->empleave];
                }
            }
        }

        // Sandwich Leave deduction row
        if ($sandwichLeaveDeduction > 0) {
            $_sdDaysFmt = ($sandwichLeaveDays == floor($sandwichLeaveDays)) ? (int)$sandwichLeaveDays : $sandwichLeaveDays;
            $dedRows[] = [
                'label'  => __('Sandwich Leave') . ' (' . $_sdDaysFmt . ' ' . __('days') . ')',
                'amount' => $sandwichLeaveDeduction,
            ];
        }

        // Joining Date Adjustment deduction row
        if ($preJoiningDeduction > 0) {
            $_pjDaysFmt = ($preJoiningDays == floor($preJoiningDays)) ? (int)$preJoiningDays : $preJoiningDays;
            $dedRows[] = [
                'label'  => __('Joining Date Adjustment') . ' (' . $_pjDaysFmt . ' ' . __('days') . ')',
                'amount' => $preJoiningDeduction,
            ];
        }

        // Pad rows to equal count (for side-by-side tables)
        // (only needed for standard/pdf template — others can ignore)
        $_maxED = max(count($earRows), count($dedRows));
        $earRowsPadded = $earRows;
        $dedRowsPadded = $dedRows;
        while (count($earRowsPadded) < $_maxED) {
            $earRowsPadded[] = ['label' => '', 'amount' => null];
        }
        while (count($dedRowsPadded) < $_maxED) {
            $dedRowsPadded[] = ['label' => '', 'amount' => null];
        }

        $totalEarnings = 0;
        foreach ($earRows as $_er) {
            if (!empty($_er['amount']) && $_er['amount'] > 0) {
                $totalEarnings += (float) $_er['amount'];
            }
        }
        $totalDeductions = 0;
        foreach ($dedRows as $_dr) {
            if (!empty($_dr['amount']) && $_dr['amount'] > 0) {
                $totalDeductions += (float) $_dr['amount'];
            }
        }
        $netSalary = max(0, $totalEarnings - $totalDeductions);

        // ── Company ──────────────────────────────────────────────────────────
        $companyName  = \App\Models\Utility::getValByName('company_name') ?? '';
        $companyAddr  = \App\Models\Utility::getValByName('company_address') ?? '';
        $companyCity  = \App\Models\Utility::getValByName('company_city') ?? '';
        $companyState = \App\Models\Utility::getValByName('company_state') ?? '';
        $companyZip   = \App\Models\Utility::getValByName('company_zipcode') ?? '';
        $companyPhone = \App\Models\Utility::getValByName('company_telephone') ?? '';
        $companyEmail = \App\Models\Utility::getValByName('company_email') ?? '';
        $companyWeb   = \App\Models\Utility::getValByName('company_website') ?? '';

        // ── Stamp ────────────────────────────────────────────────────────────
        // Stamp is rendered server-side from the DB value.
        // For live preview before save, the stamp data URL is sent via
        // postMessage to the iframe (see updateSalarySlipPreview JS).
        // Cache busting (time) ensures the browser shows the updated stamp
        // immediately after a save + page refresh.
        $_stampFile = \App\Models\Utility::getValByName('company_stamp');
        $_stampUrl  = !empty($_stampFile)
            ? \App\Models\Utility::get_file('uploads/logo/') . '/' . $_stampFile . '?' . time()
            : null;

        // ── Payslip Section Visibility Flags ───────────────────────────────────
        $_showEmployeeDetails = ($_appSettings['payslip_show_employee_details'] ?? 'on') === 'on';
        $_showPaymentDetails  = ($_appSettings['payslip_show_payment_details']  ?? 'on') === 'on';
        $_showSignatures      = ($_appSettings['payslip_show_signatures']       ?? 'on') === 'on';
        $_showFooter          = ($_appSettings['payslip_show_footer']           ?? 'on') === 'on';

        // ── Per-field visibility (Employee Details) ───────────────────────────
        $_showName            = ($_appSettings['payslip_show_name']             ?? 'on') === 'on';
        $_showDesignation     = ($_appSettings['payslip_show_designation']      ?? 'on') === 'on';
        $_showEmployeeId      = ($_appSettings['payslip_show_employee_id']      ?? 'on') === 'on';
        $_showDepartment      = ($_appSettings['payslip_show_department']       ?? 'on') === 'on';
        $_showPanNo           = ($_appSettings['payslip_show_pan_no']           ?? 'on') === 'on';
        $_showDateOfJoining   = ($_appSettings['payslip_show_date_of_joining']  ?? 'on') === 'on';
        $_showNiNumber        = ($_appSettings['payslip_show_ni_number']        ?? 'on') === 'on';
        $_showTaxCode         = ($_appSettings['payslip_show_tax_code']         ?? 'on') === 'on';

        // ── Per-field visibility (Payment Details) ────────────────────────────
        $_showBankName        = ($_appSettings['payslip_show_bank_name']        ?? 'on') === 'on';
        $_showAccountNo       = ($_appSettings['payslip_show_account_no']       ?? 'on') === 'on';
        $_showBankCode        = ($_appSettings['payslip_show_bank_code']        ?? 'on') === 'on';
        $_showAccountHolder   = ($_appSettings['payslip_show_account_holder']   ?? 'on') === 'on';
        $_showTransactionMode = ($_appSettings['payslip_show_transaction_mode'] ?? 'on') === 'on';
        $_showPayPeriod       = ($_appSettings['payslip_show_pay_period']       ?? 'on') === 'on';

        // ── UK fields ────────────────────────────────────────────────────────
        $_isUkRequest = \App\Models\Utility::isUkRequest() || request()->boolean('uk_preview');
        $taxCode        = !empty($employee->tax_payer_id)      ? $employee->tax_payer_id      : '—';
        $niNumber       = !empty($employee->ni_number)         ? $employee->ni_number         : '—';
        $niTableLetter  = !empty($employee->ni_table_letter)   ? $employee->ni_table_letter   : 'A';
        $paymentMethod  = !empty($employee->payment_method)    ? $employee->payment_method    : 'BACS';
        $worksNo        = !empty($employee->employee_id)       ? $employee->employee_id       : '—';
        $sortCode       = !empty($employee->bank_identifier_code) ? $employee->bank_identifier_code : '—';

        // ── YTD (UK) ─────────────────────────────────────────────────────────
        $ytdTaxableGross         = $payslipDetail['ytd_taxable_gross']          ?? $totalEarnings;
        $ytdIncomeTax            = $payslipDetail['ytd_income_tax']             ?? 0;
        $ytdEmployeeNI           = $payslipDetail['ytd_employee_ni']            ?? 0;
        $ytdEmployerNI           = $payslipDetail['ytd_employer_ni']            ?? 0;
        $ytdStatutoryPay         = $payslipDetail['ytd_statutory_pay']          ?? 0;
        $ytdEmployeePension      = $payslipDetail['ytd_employee_pension']       ?? 0;
        $ytdEmployerPension      = $payslipDetail['ytd_employer_pension']       ?? 0;
        $ytdLabelIncomeTax       = $payslipDetail['ytd_label_income_tax']       ?? __('Income Tax');
        $ytdLabelEmployeeNI      = $payslipDetail['ytd_label_employee_ni']      ?? __('National Insurance');
        $ytdLabelEmployerNI      = $payslipDetail['ytd_label_employer_ni']      ?? __('Employer NI');
        $ytdLabelStatutoryPay    = $payslipDetail['ytd_label_statutory_pay']    ?? __('Statutory Pay');
        $ytdLabelEmployeePension = $payslipDetail['ytd_label_employee_pension'] ?? __('Employee Pension');
        $ytdLabelEmployerPension = $payslipDetail['ytd_label_employer_pension'] ?? __('Employer Pension');
        $ytdPensionRows          = $payslipDetail['ytd_pension_rows']           ?? [];

        return compact(
            'slipYear', 'slipMonth', 'slipLabel', 'slipShort', 'payDate',
            'logoUrl', 'downloadUrl',
            '_themeColor', '_bgRgba', '_bgLight', '_bgMed',
            '_currSymbol', '_currPos', '_fmtMoney',
            '_storedSalary', '_basicSalary', '_salary', '_hra', '_da',
            'perDaySalary', 'perHourSalary',
            'officeDays', 'presentDays', 'presentDaysFmt',
            'approvedLeaves', 'disapprovedLeaves',
            'absentDays', 'absentDaysFmt',
            'extraDays', 'extraDaysFmt',
            'totalWorkHours', 'avgHrsPerDay',
            'totalLeaveAlloc', 'remainingLeaves', 'usedLeaves',
            'paidLeaveDays', 'unpaidLeaveDays', 'unpaidLeaveDeduction',
            'salaryDayCalc', 'isMonthWise', 'workingDaysLabel', 'dayOffDays', 'dayOffDaysFmt',
            'preJoiningDeduction', 'preJoiningDays',
            'daysPaid', 'isHourly', 'hoursPerDay', 'totalShiftHours', 'salaryRate',
            'payslipTypeName', 'isPaid',
            'earRows', 'earRowsPadded', 'dedRows', 'dedRowsPadded',
            'totalEarnings', 'totalDeductions', 'netSalary',
            'totalLoanRepayment',
            'companyName', 'companyAddr', 'companyCity', 'companyState', 'companyZip',
            'companyPhone', 'companyEmail', 'companyWeb',
            '_stampUrl',
            '_showEmployeeDetails', '_showPaymentDetails', '_showSignatures', '_showFooter',
            '_showName', '_showDesignation', '_showEmployeeId', '_showDepartment',
            '_showPanNo', '_showDateOfJoining', '_showNiNumber', '_showTaxCode',
            '_showBankName', '_showAccountNo', '_showBankCode', '_showAccountHolder',
            '_showTransactionMode', '_showPayPeriod',
            '_isUkRequest',
            'taxCode', 'niNumber', 'niTableLetter', 'paymentMethod', 'worksNo', 'sortCode',
            'ytdTaxableGross', 'ytdIncomeTax', 'ytdEmployeeNI', 'ytdEmployerNI',
            'ytdStatutoryPay', 'ytdEmployeePension', 'ytdEmployerPension',
            'ytdLabelIncomeTax', 'ytdLabelEmployeeNI', 'ytdLabelEmployerNI',
            'ytdLabelStatutoryPay', 'ytdLabelEmployeePension', 'ytdLabelEmployerPension',
            'ytdPensionRows',
            'previewMode',
        );
    }

    /**
     * Returns the mapping of saved template keys to view names.
     * Shared between pdf(), downloadPdf(), and settingsPreview().
     */
    public static function payslipViewMap(string $type = 'view'): array
    {
        if ($type === 'download') {
            return [
                'standard'       => 'payslip.payslipDownload',
                'uk'             => 'payslip.ukPayslipDownload',
                'compact'        => 'payslip.compactDownload',
                'professional'   => 'payslip.professionalDownload',
                'modern'         => 'payslip.modernDownload',
                'classic'        => 'payslip.classicDownload',
                'minimal'        => 'payslip.minimalDownload',
                'executive'      => 'payslip.executiveDownload',
                'bold'           => 'payslip.boldDownload',
                'elegant'        => 'payslip.elegantDownload',
                'contemporary'   => 'payslip.contemporaryDownload',
            ];
        }

        return [
            'standard'       => 'payslip.pdf',
            'uk'             => 'payslip.ukpdf',
            'compact'        => 'payslip.compact',
            'professional'   => 'payslip.professional',
            'modern'         => 'payslip.modern',
            'classic'        => 'payslip.classic',
            'minimal'        => 'payslip.minimal',
            'executive'      => 'payslip.executive',
            'bold'           => 'payslip.bold',
            'elegant'        => 'payslip.elegant',
            'contemporary'   => 'payslip.contemporary',
        ];
    }

    /**
     * Resolve the payslip template view based on the saved payslip_template setting.
     */
    protected function resolvePayslipTemplate(): string
    {
        $settings = Utility::settings();
        $template = $settings['payslip_template'] ?? 'standard';
        $map      = self::payslipViewMap('view');

        return $map[$template] ?? 'payslip.pdf';
    }

    /**
     * Resolve the download PDF template based on the saved payslip_template setting.
     */
    protected function resolveDownloadTemplate(): string
    {
        $settings = Utility::settings();
        $template = $settings['payslip_template'] ?? 'standard';
        $map      = self::payslipViewMap('download');

        return $map[$template] ?? 'payslip.payslipDownload';
    }

    public function pdf($id, $month)
    {
        $payslip = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();

        $employee = Employee::find($payslip->employee_id);

        $payslipDetail = Utility::employeePayslipDetail($id, $month);

        // Prepare all template data via the shared method
        $templateData = self::preparePayslipTemplateData(
            $employee, $payslip, $payslipDetail, '', false, false
        );

        // Use the saved payslip_template setting instead of IP detection
        $view = $this->resolvePayslipTemplate();

        $previewMode = false;
        return view($view, $templateData + compact('employee', 'payslip', 'previewMode'));
    }



    /**
     * Server-side PDF download via dompdf.
     * Route: GET payslip/download/{id}/{month}  (name: payslip.download)
     */
    public function downloadPdf($id, $month)
    {
        $payslip = PaySlip::where('employee_id', $id)
            ->where('salary_month', $month)
            ->where('created_by', \Auth::user()->creatorId())
            ->firstOrFail();

        $employee      = Employee::findOrFail($payslip->employee_id);
        $payslipDetail = Utility::employeePayslipDetail($id, $month);

        [$slipYear, $slipMonth] = explode('-', $month);

        // Prepare all template data via the shared method (download mode)
        $templateData = self::preparePayslipTemplateData(
            $employee, $payslip, $payslipDetail, '', false, true
        );

        // Use the saved payslip_template setting instead of IP detection
        $template = $this->resolveDownloadTemplate();

        $pdf = Pdf::loadView($template, $templateData + compact('employee', 'payslip'))
            ->setPaper('a4', 'portrait')->setOption('isRemoteEnabled', true);

        return $pdf->download($employee->name . '_payslip_' . $month . '.pdf');
    }

    /**
     * UK-style PDF download via dompdf — uses ukPayslipDownload.blade.php layout.
     * Route: GET payslip/uk-download/{id}/{month}  (name: payslip.uk.download)
     */
    public function downloadUkPdf($id, $month)
    {
        $payslip = PaySlip::where('employee_id', $id)
            ->where('salary_month', $month)
            ->where('created_by', \Auth::user()->creatorId())
            ->firstOrFail();

        $employee      = Employee::findOrFail($payslip->employee_id);
        $payslipDetail = Utility::employeePayslipDetail($id, $month);

        // Prepare all template data via the shared method (download mode)
        $templateData = self::preparePayslipTemplateData(
            $employee, $payslip, $payslipDetail, '', false, true
        );

        $pdf = Pdf::loadView('payslip.ukPayslipDownload', $templateData + compact('employee', 'payslip'))
            ->setPaper('a4', 'portrait')->setOption('isRemoteEnabled', true);

        return $pdf->download($employee->name . '_uk_payslip_' . $month . '.pdf');
    }

    public function send($id, $month)
    {
        $payslip = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();
        $employee = Employee::find($payslip->employee_id);

        $payslip->name = $employee->name;
        $payslip->email = $employee->email;

        $payslipId = Crypt::encrypt($payslip->id);
        $payslip->url = route('payslip.payslipPdf', $payslipId);
        $setings = Utility::settings();

        if ($setings['new_payroll'] == 1) {
            $uArr = [
                'payslip_email' => $payslip->email,
                'name' => $payslip->name,
                'url' => $payslip->url,
                'salary_month' => $payslip->salary_month,
            ];

            Utility::sendEmailTemplate('new_payroll', [$payslip->email], $uArr);

            return response()->json(['success' => true, 'message' => __('Payslip successfully sent.')]);
            // return redirect()->back()->with('success', __('Payslip successfully sent.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return response()->json(['success' => false, 'message' => __('Payslip sending failed.')]);
        // return redirect()->back()->with('success', __('Payslip successfully sent.'));
    }

    public function payslipPdf($id)
    {
        $payslipId = Crypt::decrypt($id);

        $payslip = PaySlip::where('id', $payslipId)->orWhere('employee_id', $payslipId)->first();
        $month = $payslip->salary_month;
        $employee = Employee::find($payslip->employee_id);

        $payslipDetail = Utility::employeePayslipDetail($payslip->employee_id, $month);

        return view('payslip.payslipPdf', compact('payslip', 'employee', 'payslipDetail'));
    }

    /**
     * Live preview for the Salary Slip Settings page.
     * Renders a demo payslip with sample data so the preview is always available.
     * Route: GET payslip/settings-preview
     */
    public function settingsPreview(Request $request)
    {
        $template = $request->get('template', 'standard');
        $color    = $request->get('color', '');
        // Stamp preview is sent via postMessage to avoid URL length limits.

        // ── Build demo payslip data ───────────────────────────────────────────
        $demoMonth     = date('Y-m');
        [$slipYear, $slipMonth] = explode('-', $demoMonth);

        $demoDept = new \stdClass();
        $demoDept->name = 'Engineering';

        $demoDesignation = new \stdClass();
        $demoDesignation->name = 'Senior Developer';

        $demoEmployee = new \stdClass();
        $demoEmployee->id               = 1;
        $demoEmployee->name             = 'John Doe';
        $demoEmployee->employee_id      = 'EMP001';
        $demoEmployee->basic_salary     = 50000;
        $demoEmployee->salary           = 50000;
        $demoEmployee->salary_type      = 1;
        $demoEmployee->tax_payer_id     = 'ABCPD1234K';
        $demoEmployee->company_doj      = '2020-06-15';
        $demoEmployee->bank_name        = 'State Bank';
        $demoEmployee->account_number   = '12345678901';
        $demoEmployee->bank_identifier_code = 'SBIN0001234';
        $demoEmployee->account_holder_name  = 'John Doe';
        $demoEmployee->designation      = $demoDesignation;
        $demoEmployee->department       = $demoDept;
        $demoEmployee->ni_number        = 'JS877742C';
        $demoEmployee->ni_table_letter  = 'A';
        $demoEmployee->payment_method   = 'BACS';

        $demoPayslip = new \stdClass();
        $demoPayslip->employee_id  = 1;
        $demoPayslip->salary_month = $demoMonth;
        $demoPayslip->status       = 0;
        $demoPayslip->basic_salary = 50000;
        $demoPayslip->net_salary   = 50000;
        $demoPayslip->net_payble   = 42350;

        $demoDetail = [ /* ... same as before ... */ ];
        $demoDetail = [
            'basic_salary'          => 50000,
            'net_salary'            => 42350,
            'salary'                => 50000,
            'salary_rate'           => 50000,
            'hra'                   => 0,
            'da'                    => 0,
            'per_day_amount'        => 1923.08,
            'per_hour_amount'       => 240.38,
            'office_days'           => 22,
            'present_days'          => 20,
            'approved_leaves_month' => 1,
            'rejected_leaves_month' => 0,
            'absent_days'           => 1,
            'extra_days'            => 0,
            'total_work_hours'      => 160,
            'avg_hrs_per_day'       => 8,
            'total_leave_alloc'     => 18,
            'remaining_leaves'      => 12,
            'paid_leave_days'       => 1,
            'unpaid_leave_days'     => 0,
            'unpaid_leave_deduction'=> 0,
            'days_paid'             => 21,
            'is_hourly'             => false,
            'hours_per_day'         => 8,
            'total_shift_hours'     => 176,
            'totalEarning'          => 5000,
            'totalDeduction'        => 7650,
            'totalLoanRepayment'    => 0,
            'earning' => [
                'allowance'  => [], 'commission' => [], 'bonous' => [],
                'otherPayment' => [], 'overTime' => [], 'loan' => [], 'pearks' => [],
            ],
            'deduction' => [
                'saturation_deduction' => [],
                'pansion'             => [],
                'leave'               => [
                    (object) [
                        'leave_reason'     => 'Loss of Pay',
                        'leave_type'       => 'Loss of Pay',
                        'total_leave_days' => '1 days',
                        'empleave'         => 1923.08,
                    ],
                ],
                'unpaid_leave' => [],
            ],
            'ytd_taxable_gross'         => 50000,
            'ytd_income_tax'            => 5500,
            'ytd_employee_ni'           => 2150,
            'ytd_employer_ni'           => 0,
            'ytd_statutory_pay'         => 0,
            'ytd_employee_pension'      => 750,
            'ytd_employer_pension'      => 0,
            'ytd_label_income_tax'      => 'Income Tax',
            'ytd_label_employee_ni'     => 'National Insurance',
            'ytd_label_employer_ni'     => 'Employer NI',
            'ytd_label_statutory_pay'   => 'Statutory Pay',
            'ytd_label_employee_pension'=> 'Employee Pension',
            'ytd_label_employer_pension'=> 'Employer Pension',
            'ytd_pension_rows'          => [
                ['label' => 'Employee Pension', 'amount' => 750],
            ],
        ];

        $templateData = self::preparePayslipTemplateData(
            $demoEmployee, $demoPayslip, $demoDetail, $color, true, false
        );

        // ── Accept visibility overrides from live preview toggles ──────
        $showEmployee  = $request->get('show_employee');
        $showPayment   = $request->get('show_payment');
        $showSignature = $request->get('show_signatures');
        $showFoot      = $request->get('show_footer');

        if ($showEmployee !== null)  $templateData['_showEmployeeDetails'] = $showEmployee === '1';
        if ($showPayment !== null)   $templateData['_showPaymentDetails']  = $showPayment === '1';
        if ($showSignature !== null) $templateData['_showSignatures']      = $showSignature === '1';
        if ($showFoot !== null)      $templateData['_showFooter']          = $showFoot === '1';

        // ── Accept per-field visibility overrides from live preview ────────
        $fieldMap = [
            'show_name'             => '_showName',
            'show_designation'      => '_showDesignation',
            'show_employee_id'      => '_showEmployeeId',
            'show_department'       => '_showDepartment',
            'show_pan_no'           => '_showPanNo',
            'show_doj'              => '_showDateOfJoining',
            'show_ni_number'        => '_showNiNumber',
            'show_tax_code'         => '_showTaxCode',
            'show_bank_name'        => '_showBankName',
            'show_account_no'       => '_showAccountNo',
            'show_bank_code'        => '_showBankCode',
            'show_account_holder'   => '_showAccountHolder',
            'show_transaction_mode' => '_showTransactionMode',
            'show_pay_period'       => '_showPayPeriod',
        ];
        foreach ($fieldMap as $param => $key) {
            $val = $request->get($param);
            if ($val !== null) {
                $templateData[$key] = $val === '1';
            }
        }

        $map  = self::payslipViewMap('view');
        $view = $map[$template] ?? 'payslip.pdf';

        return view($view, $templateData + ['employee' => $demoEmployee, 'payslip' => $demoPayslip]);
    }

    public function editEmployee($paySlip)
    {
        $payslip = PaySlip::find($paySlip);

        return view('payslip.salaryEdit', compact('payslip'));
    }

    public function updateEmployee(Request $request, $id)
    {
        if (isset($request->allowance) && !empty($request->allowance)) {
            $allowances = $request->allowance;
            $allowanceIds = $request->allowance_id;
            foreach ($allowances as $k => $allownace) {
                $allowanceData = Allowance::find($allowanceIds[$k]);
                $allowanceData->amount = $allownace;
                $allowanceData->save();
            }
        }


        if (isset($request->commission) && !empty($request->commission)) {
            $commissions = $request->commission;
            $commissionIds = $request->commission_id;
            foreach ($commissions as $k => $commission) {
                $commissionData = Commission::find($commissionIds[$k]);
                $commissionData->amount = $commission;
                $commissionData->save();
            }
        }

        if (isset($request->loan) && !empty($request->loan)) {
            $loans = $request->loan;
            $loanIds = $request->loan_id;
            foreach ($loans as $k => $loan) {
                $loanData = Loan::find($loanIds[$k]);
                $loanData->amount = $loan;
                $loanData->save();
            }
        }


        if (isset($request->saturation_deductions) && !empty($request->saturation_deductions)) {
            $saturation_deductionss = $request->saturation_deductions;
            $saturation_deductionsIds = $request->saturation_deductions_id;
            foreach ($saturation_deductionss as $k => $saturation_deductions) {

                $saturation_deductionsData = SaturationDeduction::find($saturation_deductionsIds[$k]);
                $saturation_deductionsData->amount = $saturation_deductions;
                $saturation_deductionsData->save();
            }
        }


        if (isset($request->other_payment) && !empty($request->other_payment)) {
            $other_payments = $request->other_payment;
            $other_paymentIds = $request->other_payment_id;
            foreach ($other_payments as $k => $other_payment) {
                $other_paymentData = OtherPayment::find($other_paymentIds[$k]);
                $other_paymentData->amount = $other_payment;
                $other_paymentData->save();
            }
        }


        if (isset($request->rate) && !empty($request->rate)) {
            $rates = $request->rate;
            $rateIds = $request->rate_id;
            $hourses = $request->hours;

            foreach ($rates as $k => $rate) {
                $overtime = Overtime::find($rateIds[$k]);
                $overtime->rate = $rate;
                $overtime->hours = $hourses[$k];
                $overtime->save();
            }
        }

        $payslipEmployee = PaySlip::find($request->payslip_id);
        $payslipEmployee->allowance = Employee::allowance($payslipEmployee->employee_id);
        $payslipEmployee->commission = Employee::commission($payslipEmployee->employee_id);
        $payslipEmployee->loan = Employee::loan($payslipEmployee->employee_id);
        $payslipEmployee->saturation_deduction = Employee::saturation_deduction($payslipEmployee->employee_id);
        $payslipEmployee->other_payment = Employee::other_payment($payslipEmployee->employee_id);
        $payslipEmployee->overtime = Employee::overtime($payslipEmployee->employee_id);
        $payslipEmployee->save();

        // Recalculate net_payble using the accurate employeePayslipDetail
        // so it matches what the payslip PDF shows
        $detail = Utility::employeePayslipDetail($payslipEmployee->employee_id, $payslipEmployee->salary_month);
        $payslipEmployee->net_payble = round($detail['net_salary'], 2);
        $payslipEmployee->save();

        return redirect()->route('payslip.index')->with('success', __('Employee payroll successfully updated.'));
    }

    public function PayslipExport(Request $request)
    {
        $name = 'payslip_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new PayslipExport($request), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
}
