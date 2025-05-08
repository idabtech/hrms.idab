<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\TimeSheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TimesheetExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    { 
        
        if(\Auth::user()->type == 'employee'){

            $employee = Employee::where('created_by',\Auth::user()->creatorId())->first();
        //    print_r($employee->id); exit;
            $data     = TimeSheet::where(['created_by'=> \Auth::user(), 'employee_id' => $employee->user_id])->get();
        //    print_r($data); exit;
        }else{
            $data=TimeSheet::where('created_by', \Auth::user()->creatorId())->get();
        }

        foreach($data as $k=>$timesheet)
        {
            $data[$k]["employee_id"]=!empty($timesheet->employee) ? $timesheet->employee->name : '';
            $data[$k]["created_by"]=Employee::login_user($timesheet->created_by);
            unset($timesheet->created_at,$timesheet->updated_at);
        }
        return $data;
    }
    public function headings(): array
    {
        return [
            "ID",
            "Employee Name",
            "Date",
            "Hour",
            "Remark",
            "Created By"
        ];
    }
}
