<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\TimeSheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class TimesheetReportExport implements FromCollection, WithHeadings
{

    public $start_month = '';
    public $end_month = '';
    public $branch = '';
    public $department = '';

    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function __construct($start_month, $end_month, $branch, $department){
        $this->start_month = $start_month;
        $this->end_month = $end_month;
        $this->branch = $branch;
        $this->department = $department;
    }
    public function collection()
    {
        // echo"<pre>"; print_r($this->start_month); exit;
        $data = TimeSheet::select('time_sheets.*','employees.name')->leftJoin('employees','employees.user_id','time_sheets.employee_id')->whereBetween('time_sheets.date',[$this->start_month,$this->end_month]);
        if($this->branch > 0){
            $data->where('employees.branch_id', $this->branch);
        }

        if($this->department > 0){
            $data->where('employees.department_id', $this->department);
        }

        $data = $data->get();
        // echo"<pre>"; print_r($data); exit;
        foreach($data as $k=>$item)
        {
            $data[$k]["employee_id"]=!empty($item->name) ? $item->name : '';
            $data[$k]["created_by"]=Employee::login_user($item->created_by);
            unset($item->created_at,$item->updated_at,$item->created_by,$item->branch_id,$item->department_id,$item->user_id,$item->name);

        }
        // echo"<pre>"; print_r($data); exit;
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
        ];
    }
}
