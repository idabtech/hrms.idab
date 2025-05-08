<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\ShiftTurner;
use App\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftTurnerController extends Controller
{
    //

    public function index(Request $request)
    {
        $employees = Employee::where('created_by', \Auth::user()->creatorId())->get();

        $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $department->prepend('All', '');

        $sub_department = SubDepartment::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $sub_department->prepend('All', '');

        $turner_employee = ShiftTurner::select('employee')->where('created_by', \Auth::user()->creatorId())->get();
        $emp_array_ser = $turner_employee->toArray();
        $check_emp_list = unserialize($emp_array_ser[0]['employee']);
        return view('shiftturner.index', compact('employees', 'department', 'sub_department', 'check_emp_list'));
    }

    public function getSubDepartmentandEmployee(Request $request)
    {
        $subdepartment = SubDepartment::where(['department' => $request->department_id, 'created_by' => \Auth::user()->creatorId()])->get();
        $employee = Employee::where(['department_id' => $request->department_id, 'created_by' => \Auth::user()->creatorId()])->get();
        $subdepartment_output = '';
        foreach ($subdepartment as $sub) {
            $subdepartment_output .= '<option value="' . $sub->id . '">' . $sub->name . '</option>';
        }

        $turner_employee = ShiftTurner::select('employee')->where('created_by', \Auth::user()->creatorId())->first();
        $turner_employee_arr = unserialize($turner_employee->employee); 

        $employee_output = '';
        foreach ($employee as $emp) {
            if(in_array($emp->id, $turner_employee_arr)){
                $check = 'checked';
            }else{
                $check = '';
            }
            $employee_output .= '<tr>
                                    <td>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="employee_id[]" '.$check.' id="checkbox' . $emp->id . '" value="' . $emp->id . '">
                                        </div>
                                    </td>
                                    <td>' . $emp->name . '</td>
                                </tr>';
        }

        $final_output = array('subdepartment_output' => $subdepartment_output, 'employee_output' => $employee_output);
        print_r(json_encode($final_output));
    }

    public function getEmployeeList(Request $request)
    {

        $employee = Employee::where(['subdepartment_id' => $request->subdepartment_id, 'created_by' => \Auth::user()->creatorId()])->get();

        $turner_employee = ShiftTurner::select('employee')->where('created_by', \Auth::user()->creatorId())->first();
        $turner_employee_arr = unserialize($turner_employee->employee); 
        
        $employee_list = '';
        foreach ($employee as $emp) {
            if(in_array($emp->id, $turner_employee_arr)){
                $check = 'checked';
            }else{
                $check = '';
            }
            $employee_list .= '<tr>
                                    <td>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="employee_id[]" '.$check.' id="checkbox' . $emp->id . '" value="' . $emp->id . '">
                                        </div>
                                    </td>
                                    <td>' . $emp->name . '</td>
                                </tr>';
        }
        print_r($employee_list);
    }

    public function store(Request $request)
    {

        ShiftTurner::where('created_by', Auth::user()->creatorId())->delete();
        $employee = serialize($request->employee_id);

        $shift_turner = new ShiftTurner();
        $shift_turner->employee = $employee;
        $shift_turner->created_by = Auth::user()->creatorId();
        $shift_turner->save();


        return redirect()->back()->with('success', 'Shift turner on for selected employee');

    }
}