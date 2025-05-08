<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\CodeCoverage\Percentage;
use App\Models\Leave;
use Carbon\Carbon;

class Employee extends Model
{
    protected $table = 'employees';
    protected $fillable = [
        'user_id',
        'name',
        'last_name',
        'dob',
        'gender',
        'phone',
        'address',
        'email',
        'password',
        'employee_id',
        'branch_id',
        'department_id',
        'subdepartment_id',
        'designation_id',
        'shift_id',
        'company_doj',
        'documents',
        'account_holder_name',
        'account_number',
        'bank_name',
        'bank_identifier_code',
        'branch_location',
        'tax_payer_id',
        'pf_id',
        'esic_id',
        'salary_type',
        'salary',
        'created_by',
    ];

    public function documents()
    {
        return $this->hasMany('App\Models\EmployeeDocument', 'employee_id', 'employee_id')->get();
    }

    public function salary_type()
    {
        return $this->hasOne('App\Models\PayslipType', 'id', 'salary_type')->pluck('name')->first();
    }
 
    public function get_net_salary()
    {

      
        // $this->id = 4;
        $earning['allowance']         = Allowance::where('employee_id', $this->id)->get();
        $employess = Employee::find($this->id);

        $totalAllowance = 0;

        foreach ($earning['allowance'] as $earn) {
            if ($earn->type == 'percentage') {
                $empall  = $earn->amount * $employess->salary / 100;
            } else {
                $empall = $earn->amount;
            }
            $totalAllowance += $empall;
        }

        
        $earning['commission']        = Commission::where('employee_id', $this->id)->get();
        $employess = Employee::find($this->id);
        $totalCommission = 0;

        foreach ($earning['commission'] as $earn) {
            if ($earn->type == 'percentage') {
                $empcom  = $earn->amount * $employess->salary / 100;
            } else {
                $empcom = $earn->amount;
            }
            $totalCommission += $empcom;
        }

        $earning['bonous']        = Bonous::where('employee_id', $this->id)->get();
        $employess = Employee::find($this->id);
        $totalBonous = 0;

        foreach ($earning['bonous'] as $earn) {
            if ($earn->type == 'percentage') {
                $empbon  = $earn->amount * $employess->salary / 100;
            } else {
                $empbon = $earn->amount;
            }
            $totalBonous += $empbon;
        }

        // $earning['otherPayment']      = OtherPayment::where('employee_id', $this->id)->get();
        // $employess = Employee::find($this->id);
        // $totalotherpayment = 0;

        // foreach ($earning['otherPayment'] as $earn) {
        //     if ($earn->type == 'percentage') {
        //         $empotherpay  = $earn->amount * $employess->salary / 100;
        //     } else {
        //         $empotherpay = $earn->amount;
        //     }
        //     $totalotherpayment += $empotherpay;
        // }

        $earning['overTime']          = Overtime::select('id', 'title')->selectRaw('number_of_days * hours* rate as amount')->where('employee_id', $this->id)->get();
        $earning['totalOverTime']     = Overtime::selectRaw('number_of_days * hours* rate as total')->where('employee_id', $this->id)->get()->sum('total');

        $deduction['loan']           = Loan::where('employee_id', $this->id)->get();
        $employess = Employee::find($this->id);
        $totalloan = 0;

        foreach ($deduction['loan'] as $earn) {
            if ($earn->type == 'percentage') {
                $emploan  = $earn->amount * $employess->salary / 100;
            } else {
                $emploan = $earn->amount;
            }
            $totalloan += $emploan;
        }

        $deduction['deduction']      = SaturationDeduction::where('employee_id', $this->id)->get();
        $employess = Employee::find($this->id);
        $totaldeduction = 0;

        foreach ($deduction['deduction'] as $earn) {
            if ($earn->type == 'percentage') {
                $empdeduction  = $earn->amount * $employess->salary / 100;
            } else {
                $empdeduction = $earn->amount;
            }
            $totaldeduction += $empdeduction;
        }

        $deduction['pansion']           = Pension::where('employee_id', $this->id)->get();
        $employess = Employee::find($this->id);
        $totalPansion = 0;

        foreach ($deduction['pansion'] as $earn) {
            if ($earn->type == 'percentage') {
                $emppansion  = $earn->amount * $employess->salary / 100;
            } else {
                $emppansion = $earn->amount;
            }
            $totalPansion += $emppansion;
        }

        // $deduction['leave']      = Leave::where('employee_id', $this->id)->get();
        // $employess = Employee::find($this->id);

        // $per_day_amount = $employess->salary / 30;
        // $totalleave = 0;

        // foreach($deduction['leave'] as $earn){
        //     $empleave = $per_day_amount * $earn->total_leave_days; 

        //     $totalleave += $empleave;
        // }
        $deduction['leave']    = Leave::select('leaves.*','leave_types.title as leave_type')->leftjoin('leave_types','leave_types.id','=','leaves.leave_type_id')->where('leaves.employee_id', $this->id)->where(function ($query) {
            $query->whereMonth('leaves.start_date',Carbon::now()->month - 1)
                 ->orWhereMonth('leaves.end_date',Carbon::now()->month - 1);
        })->get();
       
        $employess = Employee::find($this->id);

        $per_day_amount = $employess->salary / 30;
        $totalLeaveDays = 0;
        $leaveAmount = 0;
        foreach($deduction['leave'] as $earn){
            $empleave = $per_day_amount * $earn->total_leave_days;
            $earn->empleave = $empleave;
            $leaveAmount += $empleave;
            $totalLeaveDays += $earn->total_leave_days;
        }

        $total_days = Carbon::now()->daysInMonth;
        $numberOfWeeks = floor($total_days / Carbon::DAYS_PER_WEEK);
        $numberOfWeeks = $numberOfWeeks * 2;
        $total_working_days = $total_days - $numberOfWeeks;

        $attendence = AttendanceEmployee::where('employee_id', $this->id)->whereMonth('date',Carbon::now()->subMonth()->month)->count(); 
        // dd($attendence);       
        $total_attendace = $attendence + $totalLeaveDays;

        if($total_working_days > $total_attendace){
            $notClockinDays = $total_working_days - $total_attendace;
            $empleave = $per_day_amount * $notClockinDays; 
            $leaveAmount += $empleave;
            $deduction['leave'][] = (object)array(
                'leave_reason' => 'None clock In',
                'leave_type' => 'None clock In',
                'total_leave_days' => $notClockinDays .' days',
                'empleave' => $empleave
            ); 
        }

        $net_salary['earning']        = $earning;
        $net_salary['totalEarning']   = $totalAllowance + $totalCommission + $totalBonous + $earning['totalOverTime'] + $employess->salary;

        $net_salary['deduction']      = $deduction;
        $net_salary['totalDeduction'] = $totalloan + $totaldeduction + $totalPansion + $leaveAmount;

        $net_salary = $net_salary['totalEarning'] - $net_salary['totalDeduction'];

        return $net_salary;
    }

    public static function allowance($id)
    {

        // dd('hey');
        //allowance
        $allowances      = Allowance::where('employee_id', '=', $id)->get();
        $total_allowance = 0;
        foreach ($allowances as $allowance) {
            $total_allowance = $allowance->amount + $total_allowance;
        }

        $allowance_json = json_encode($allowances);

        return $allowance_json;
    }

    public static function commission($id)
    {
        //commission
        $commissions      = Commission::where('employee_id', '=', $id)->get();
        // dd($commissions);
        $total_commission = 0;

        foreach ($commissions as $commission) {
            $total_commission = $commission->amount + $total_commission;
        }
        $commission_json = json_encode($commissions);

        return $commission_json;
    }

    public static function loan($id)
    {
        //Loan
        $loans      = Loan::where('employee_id', '=', $id)->get();
        $total_loan = 0;
        foreach ($loans as $loan) {
            $total_loan = $loan->amount + $total_loan;
        }
        $loan_json = json_encode($loans);

        return $loan_json;
    }


    // public static function leave($id)
    // {
    //     //Loan
    //     $leaves  = Leave::where('employee_id', '=', $this->id)->get();
    //     $total_leave = 0;
    //     foreach ($leaves as $leave) {
    //         $employee       = Employee::find($leave->employee_id);
    //         $per_day_amount = $employee->salary / 30;
    //         $empleave = $per_day_amount * $leave->total_leave_days; 

    //         $total_leave = $empleave + $total_leave;
    //     }
    //     $leave_json = json_encode($leaves);

    //     return $leave_json;
    // }

    public static function saturation_deduction($id)
    {
        //Saturation Deduction
        $saturation_deductions      = SaturationDeduction::where('employee_id', '=', $id)->get();
        $total_saturation_deduction = 0;
        foreach ($saturation_deductions as $saturation_deduction) {
            $total_saturation_deduction = $saturation_deduction->amount + $total_saturation_deduction;
        }
        $saturation_deduction_json = json_encode($saturation_deductions);

        return $saturation_deduction_json;
    }

    public static function other_payment($id)
    {
        //OtherPayment
        $other_payments      = OtherPayment::where('employee_id', '=', $id)->get();
        $total_other_payment = 0;
        foreach ($other_payments as $other_payment) {
            $total_other_payment = $other_payment->amount + $total_other_payment;
        }
        $other_payment_json = json_encode($other_payments);

        return $other_payment_json;
    }

    public static function overtime($id)
    {
        //Overtime
        $over_times      = Overtime::where('employee_id', '=', $id)->get();
        $total_over_time = 0;
        foreach ($over_times as $over_time) {
            $total_work      = $over_time->number_of_days * $over_time->hours;
            $amount          = $total_work * $over_time->rate;
            $total_over_time = $amount + $total_over_time;
        }
        $over_time_json = json_encode($over_times);

        return $over_time_json;
    }

    public static function employee_id()
    {
        $employee = Employee::latest()->first();

        return !empty($employee) ? $employee->id + 1 : 1;
    }

    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'branch_id');
    }

    public function phone()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'phone');
    }

    public function department()
    {
        return $this->hasOne('App\Models\Department', 'id', 'department_id');
    }

    public function subdepartment()
    {
        return $this->hasOne('App\Models\SubDepartment', 'id', 'subdepartment_id');
    }

    public function designation()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation_id');
    }

    public function shift()
    {
        return $this->hasOne('App\Models\Shift', 'id', 'shift_id');
    }
    public function salaryType()
    {
        return $this->hasOne('App\Models\PayslipType', 'id', 'salary_type');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function paySlip()
    {
        return $this->hasOne('App\Models\PaySlip', 'id', 'employee_id');
    }


    public function present_status($employee_id, $data)
    {
        return AttendanceEmployee::where('employee_id', $employee_id)->where('date', $data)->first();
    }
    public static function employee_name($name)
    {

        $employee = Employee::where('id', $name)->first();
        if (!empty($employee)) {
            return $employee->name;
        }
    }


    public static function login_user($name)
    {
        $user = User::where('id', $name)->first();
        return $user->name;
    }

    public static function employee_salary($salary)
    {

        $employee = Employee::where("salary", $salary)->first();
        // dd($employee);
        if ($employee->salary == '0' || $employee->salary == '0.0') {
            return "-";
        } else {
            return $employee->salary;
        }
    }
}
