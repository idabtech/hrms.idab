<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeaveReportExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function collection()
    {
        $creatorId = Auth::user()->creatorId();
        $employees = Employee::where('created_by', $creatorId)->get();

        $rows = collect();

        foreach ($employees as $employee) {
            $approved = Leave::where('employee_id', $employee->id)
                ->where('created_by', $creatorId)
                ->where('status', 'Approved')
                ->count();

            $reject = Leave::where('employee_id', $employee->id)
                ->where('created_by', $creatorId)
                ->where('status', 'Reject')
                ->count();

            $pending = Leave::where('employee_id', $employee->id)
                ->where('created_by', $creatorId)
                ->where('status', 'Pending')
                ->count();

            $rows->push([
                'employee_id'      => User::employeeIdFormat($employee->employee_id),
                'employee'         => $employee->name,
                'approved_leaves'  => $approved,
                'rejected_leaves'  => $reject,
                'pending_leaves'   => $pending,
            ]);
        }

        return $rows;
    }


    public function headings(): array
    {
        return [
            "Employee ID",
            "Employee",
            "Approved Leaves",
            "Rejected Leaves",
            "Pending Leaves",
        ];
    }
}
