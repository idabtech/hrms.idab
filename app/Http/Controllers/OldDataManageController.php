<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class OldDataManageController extends Controller
{
    //------------------- Old Data Manage (Flexible break set) ------------------//
    public function OldDataManage()
    {
        $employees = Employee::where('shift_break_type', 'fixed')->whereNull('lunch_start')->get();

        if ($employees->isEmpty()) {
            return redirect()->back()->with('error', __('No employees found with fixed shift break and missing lunch start.'));
        }

        foreach ($employees as $employee) {
            $employee->shift_break_type = 'flexible';
            $employee->lunch_minutes = 45;
            $employee->tea_minutes = 15;
            $employee->save();
        }

        return redirect()->back()->with('success', __('Old employee break data was updated successfully.'));
    }
}
