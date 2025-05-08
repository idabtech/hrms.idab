<?php

namespace App\Exports;

use App\Models\AttendanceEmployee;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

// class MonthlyAttendanceExport implements FromCollection
// {
//     /**
//     * @return \Illuminate\Support\Collection
//     */
//     public function collection()
//     {
//         //
//     }
// }


class MonthlyAttendanceExport implements FromView, WithStyles
{
    use Exportable;
    
    public function __construct($month, $year, $branch, $department){
        $this->month = $month;
        $this->year = $year;
        $this->branch = $branch;
        $this->department = $department;
    }

    public function view(): View
    {
        $total_date = cal_days_in_month(CAL_GREGORIAN,$this->month,$this->year);

        $data = Employee::where('employees.created_by', \Auth::user()->creatorId());
        if($this->branch > 0){
            $data->where('employees.branch_id', $this->branch);
        }

        if($this->department > 0){
            $data->where('employees.department_id', $this->department);
        }

        $data = $data->get();

        $company_address = DB::table('settings')->where(['name' => 'company_address','created_by' => \Auth::user()->creatorId()])->first();
        
        $attendance_details = array();

        foreach($data as $item){
            $attendance = AttendanceEmployee::whereMonth('attendance_employees.date', '=', $this->month)->where('employee_id',$item->id)->get();
            
            if(count($attendance) > 0){
                foreach($attendance as $item1){
                    $attendance_details[$item1->employee_id]['name'] = $item->name;   
                    $attendance_details[$item1->employee_id][] = $item1->date;
                }
            }else{
                $attendance_details[$item->id]['name'] = $item->name;   
                $attendance_details[$item->id][] = '';
            }
        }
        foreach($attendance_details as $key1 => $value){
            for($i = 1; $i <= $total_date; $i++){
                $day = ($i < 9) ? "0".$i : $i;
                if(!in_array($this->year.'-'.$this->month.'-'.$day, $value)){
                    $attendance_details[$key1]['attendence'][$this->year.'-'.$this->month.'-'.$day] = "Absent";
                }else{
                    $attendance_details[$key1]['attendence'][$this->year.'-'.$this->month.'-'.$day] = "Present";   
                }
            }
            
        }
        $month = $this->month;
        $year = $this->year;

        return view('report.export', compact('data','company_address','total_date','attendance_details','month','year'));
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:U1');
        $sheet->mergeCells('A2:U2');
        $sheet->mergeCells('A3:U3');
        $sheet->mergeCells('A4:U4');
        $sheet->mergeCells('A5:U5');

        $sheet->getStyle('A1:U1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:U2')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        $sheet->getStyle('A3:U3')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ]);
        $sheet->getStyle('A4:U4')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $sheet->getStyle('A5:U5')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(30);
        $sheet->getRowDimension(3)->setRowHeight(30);
        $sheet->getRowDimension(4)->setRowHeight(30);
        $sheet->getRowDimension(5)->setRowHeight(30);
    }
}