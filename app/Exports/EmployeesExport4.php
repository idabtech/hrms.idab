<?php

namespace App\Exports;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use App\Models\Utility;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;

use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// class EmployeesExport implements FromCollection, WithHeadings
// {
//     /**
//     * @return \Illuminate\Support\Collection
//     */
//     public function collection()
//     {
//         $data = Employee::where('created_by', \Auth::user()->creatorId())->get();
//         foreach($data as $k => $employees)
//         {
//             unset($employees->id,$employees->user_id,$employees->subdepartment_id,$employees->shift_id,$employees->shift_date,$employees->documents,$employees->tax_payer_id,$employees->pf_id,$employees->esic_id,$employees->is_active,$employees->created_at,$employees->updated_at);
//             // unset($employees->id,$employees->dob,$employees->documents,$employees->tax_payer_id,$employees->is_active,$employees->created_at,$employees->updated_at);

//             $data[$k]["branch_id"]=!empty($employees->branch) ? $employees->branch->name : '-';
//             $data[$k]["department_id"]=!empty($employees->department) ? $employees->department->name : '-';
//             $data[$k]["designation_id"]= !empty($employees->designation) ? $employees->designation->name : '-';
//             $data[$k]["salary_type"]=!empty($employees->salary_type) ? $employees->salaryType->name :'-';
//             $data[$k]["salary"]=Employee::employee_salary($employees->salary);
//             $data[$k]["created_by"]=Employee::login_user($employees->created_by);

//         }

//         return $data;
//     }

//     public function headings(): array
//     {
//         return [
//             "Name",
//             "lastName",
//             "Date of Birth",
//             "Gender",
//             "Phone Number",
//             "Address",
//             "Email ID",
//             "Password",
//             "Employee ID",
//             "Branch",
//             "Department",
//             "Designation",
//             "Date of Join",
//             "Account Holder Name",
//             "Account Number",
//             "Bank Name",
//             "Bank Identifier Code",
//             "Branch Location",
//             "Salary Type",
//             "Salary",
//             "Created By"
//         ];
//     }
// }


class EmployeesExport4 implements FromView, WithStyles
{
    use Exportable;

    public function view(): View
    {
        $data = Employee::where('created_by', \Auth::user()->creatorId())->get();

        $company_address = DB::table('settings')->where(['name' => 'company_address','created_by' => \Auth::user()->creatorId()])->first();
        
        foreach($data as $k => $employees)
        {
            unset($employees->id,$employees->user_id,$employees->subdepartment_id,$employees->shift_id,$employees->shift_date,$employees->documents,$employees->tax_payer_id,$employees->pf_id,$employees->esic_id,$employees->is_active,$employees->created_at,$employees->updated_at);

            $data[$k]["branch_id"]=!empty($employees->branch) ? $employees->branch->name : '-';
            $data[$k]["department_id"]=!empty($employees->department) ? $employees->department->name : '-';
            $data[$k]["designation_id"]= !empty($employees->designation) ? $employees->designation->name : '-';
            $data[$k]["salary_type"]=!empty($employees->salary_type) ? $employees->salaryType->name :'-';
            $data[$k]["salary"]=Employee::employee_salary($employees->salary);
            $data[$k]["created_by"]=Employee::login_user($employees->created_by);

        }

        return view('employee.export15', compact('data','company_address'));
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        // Apply styles to cells or columns
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
            // 'fill' => [
            //     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            //     'startColor' => [
            //         'rgb' => 'FF0000',
            //     ],
            // ],
        ]);

        $sheet->getStyle('A2:M2')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(60);
    }
}
