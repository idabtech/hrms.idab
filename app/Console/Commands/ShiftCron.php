<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shift;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ShiftTurner;
class ShiftCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shift:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $turner_employee = ShiftTurner::get();
        foreach($turner_employee as $turner){
            $check_emp_list = unserialize($turner->employee);

            $emp_list = Employee::whereIn('id', $check_emp_list)->get();
            foreach ($emp_list as $employee) {
                $check_auto_switch = \DB::table('settings')->where(['name'=>'shift_change','created_by'=>$employee->created_by])->first();
                if(isset($check_auto_switch->name) && $check_auto_switch->value == 'on'){
                    $shifts = Shift::where('created_by', $employee->created_by)->get();
                    $get_shift_turner = \DB::table('settings')->where(['name'=>'shift_turner', 'created_by'=>$employee->created_by])->first();
                    $sift_turner = isset($get_shift_turner->value) ? $get_shift_turner->value : 7;
                    $next_key = 0;
                    foreach ($shifts as $key => $shift) {
                        if($shift->id == $employee->shift_id){
                            $next_key = $key+1;
                            if(date('Y-m-d',strtotime('+'.$sift_turner.' day',strtotime($employee->shift_date))) == date("Y-m-d")){
                                $employee = Employee::where('id', '=', $employee->id)->first();
                                $employee->shift_id = isset($shifts[$next_key]['id']) ? $shifts[$next_key]['id'] : $shifts[0]['id'];
                                $employee->updated_at = date("Y-m-d H:i:s");
                                $employee->shift_date = date("Y-m-d H:i:s");
                                $employee->save();
                            }else{
                                \Log::info("No update");
                            }
                        }else{
                            \Log::info("shift not matched");
                        }
                    }
                    
                }else{
                    \Log::info("company auto shifts off");
                }
            }
        }
       
    }
}
