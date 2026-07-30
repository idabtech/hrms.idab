<?php

namespace App\Console\Commands;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceRequest;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoClockOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:clock-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clock out employees after shift end time plus configured auto_clock_out_time minutes for today only';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Get all company creators
        $creators = User::whereIn('type', ['company', 'super admin'])->pluck('id');

        $clockedOutCount = 0;

        foreach ($creators as $creatorId) {
            // Get company settings for this creator using new getCompanySettings method
            $settings = Utility::getCompanySettings($creatorId);

            // Check if auto_clock_out is enabled
            if (isset($settings['auto_clock_out']) && $settings['auto_clock_out'] === 'on') {
                $autoClockOutMinutes = isset($settings['auto_clock_out_time']) && is_numeric($settings['auto_clock_out_time'])
                    ? (int) $settings['auto_clock_out_time']
                    : 30;

                // Company timezone fallback to Asia/Kolkata if empty or UTC
                $tz = (!empty($settings['timezone']) && $settings['timezone'] !== 'UTC')
                    ? $settings['timezone']
                    : 'Asia/Kolkata';

                date_default_timezone_set($tz);
                $now = Carbon::now($tz);
                $todayDate = $now->toDateString(); // Today's date only

                // Get all employee IDs belonging to this company
                $companyEmployees = Employee::where('created_by', $creatorId)->get();
                $companyEmpIds = $companyEmployees->pluck('id')->toArray();

                if (empty($companyEmpIds)) {
                    continue;
                }

                // ── 1. Handle attendance_employees table records (Today's date only) ──────
                $openAttendances = AttendanceEmployee::whereIn('employee_id', $companyEmpIds)
                    ->where('date', $todayDate) // STRICT: Today's date only
                    ->whereNotNull('clock_in')
                    ->where('clock_in', '!=', '')
                    ->where('clock_in', '!=', '00:00:00')
                    ->where(function ($q) {
                        $q->whereNull('clock_out')
                          ->orWhere('clock_out', '')
                          ->orWhere('clock_out', '00:00:00');
                    })
                    ->where(function ($q) {
                        $q->whereNull('status')
                          ->orWhere('status', 'Present')
                          ->orWhere('status', 'present');
                    })
                    ->get();

                foreach ($openAttendances as $attendance) {
                    $employee = $companyEmployees->firstWhere('id', $attendance->employee_id);
                    if (!$employee) {
                        continue;
                    }

                    $attendanceDate = $attendance->date;

                    // ── Skip if employee ALREADY created a manual clock_out request ──
                    $hasClockOutRequest = AttendanceRequest::where('employee_id', $employee->id)
                        ->where('type', 'clock_out')
                        ->whereDate('requested_at', $attendanceDate)
                        ->exists();

                    if ($hasClockOutRequest) {
                        continue;
                    }

                    // Resolve shift end time (Employee specific first, then shift, then settings default)
                    $shiftEndTimeStr = null;
                    if (!empty($employee->company_end_time)) {
                        $shiftEndTimeStr = $employee->company_end_time;
                    } elseif ($employee->shift_id) {
                        $shift = Shift::find($employee->shift_id);
                        if ($shift && !empty($shift->company_end_time)) {
                            $shiftEndTimeStr = $shift->company_end_time;
                        }
                    }
                    if (empty($shiftEndTimeStr)) {
                        $shiftEndTimeStr = $settings['company_end_time'] ?? '18:00';
                    }
                    if (empty($shiftEndTimeStr)) {
                        continue;
                    }

                    if (strlen($shiftEndTimeStr) == 5) {
                        $shiftEndTimeStr .= ':00';
                    }

                    $attendanceDate = $attendance->date;

                    try {
                        $shiftEndCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate . ' ' . $shiftEndTimeStr, $tz);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $targetAutoClockOut = $shiftEndCarbon->copy()->addMinutes($autoClockOutMinutes);

                    if ($now->greaterThanOrEqualTo($targetAutoClockOut)) {
                        $realClockOutTime = $now->format('H:i:s');

                        $shiftEndTimestamp = strtotime($attendanceDate . ' ' . $shiftEndTimeStr);
                        $clockOutTimestamp = strtotime($attendanceDate . ' ' . $realClockOutTime);

                        $earlyLeaving = '00:00:00';
                        $overtime     = '00:00:00';

                        if ($clockOutTimestamp < $shiftEndTimestamp) {
                            $diff  = $shiftEndTimestamp - $clockOutTimestamp;
                            $hours = floor($diff / 3600);
                            $mins  = floor(($diff / 60) % 60);
                            $secs  = floor($diff % 60);
                            $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } elseif ($clockOutTimestamp > $shiftEndTimestamp) {
                            $diff  = $clockOutTimestamp - $shiftEndTimestamp;
                            $hours = floor($diff / 3600);
                            $mins  = floor(($diff / 60) % 60);
                            $secs  = floor($diff % 60);
                            $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        }

                        $attendance->clock_out         = $realClockOutTime;
                        $attendance->early_leaving     = $earlyLeaving;
                        $attendance->overtime          = $overtime;
                        $attendance->status            = 'Present';
                        $attendance->is_auto_clock_out = 1;
                        $attendance->save();

                        $clockedOutCount++;

                        Log::info(sprintf(
                            '[auto:clock-out] Auto clocked out employee #%d (%s) for date %s at real time %s (shift end %s)',
                            $employee->id,
                            $employee->name,
                            $attendanceDate,
                            $realClockOutTime,
                            $shiftEndTimeStr
                        ));
                    }
                }

                // ── 2. Handle unapproved/pending clock_in requests in attendance_requests (Today's date only)
                $pendingRequests = AttendanceRequest::whereIn('employee_id', $companyEmpIds)
                    ->whereDate('requested_at', $todayDate) // STRICT: Today's date only
                    ->where('type', 'clock_in')
                    ->whereIn('status', ['pending', 'approved'])
                    ->get();

                foreach ($pendingRequests as $pReq) {
                    $employee = $companyEmployees->firstWhere('id', $pReq->employee_id);
                    if (!$employee) {
                        continue;
                    }

                    $reqDate = Carbon::parse($pReq->requested_at)->format('Y-m-d');

                    // Check if a clock_out request already exists for this employee on this date
                    $existingClockOutReq = AttendanceRequest::where('employee_id', $employee->id)
                        ->where('type', 'clock_out')
                        ->whereDate('requested_at', $reqDate)
                        ->exists();

                    if ($existingClockOutReq) {
                        continue;
                    }

                    // Check if employee already has a clock_out in attendance_employees table
                    $attEmp = AttendanceEmployee::where('employee_id', $employee->id)
                        ->where('date', $reqDate)
                        ->first();

                    if ($attEmp && !empty($attEmp->clock_out) && $attEmp->clock_out !== '00:00:00') {
                        continue;
                    }

                    // Resolve shift end time for employee (Employee specific first, then shift, then settings default)
                    $shiftEndTimeStr = null;
                    if (!empty($employee->company_end_time)) {
                        $shiftEndTimeStr = $employee->company_end_time;
                    } elseif ($employee->shift_id) {
                        $shift = Shift::find($employee->shift_id);
                        if ($shift && !empty($shift->company_end_time)) {
                            $shiftEndTimeStr = $shift->company_end_time;
                        }
                    }
                    if (empty($shiftEndTimeStr)) {
                        $shiftEndTimeStr = $settings['company_end_time'] ?? '18:00';
                    }
                    if (empty($shiftEndTimeStr)) {
                        continue;
                    }

                    if (strlen($shiftEndTimeStr) == 5) {
                        $shiftEndTimeStr .= ':00';
                    }

                    try {
                        $shiftEndCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $reqDate . ' ' . $shiftEndTimeStr, $tz);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $targetAutoClockOut = $shiftEndCarbon->copy()->addMinutes($autoClockOutMinutes);

                    if ($now->greaterThanOrEqualTo($targetAutoClockOut)) {
                        $realClockOutTime = $now->format('H:i:s');

                        // Create an automatic clock_out AttendanceRequest
                        AttendanceRequest::create([
                            'employee_id'  => $employee->id,
                            'type'         => 'clock_out',
                            'clock_out'    => $realClockOutTime,
                            'reason'       => 'automatic clock out',
                            'status'       => 'pending',
                            'requested_at' => $reqDate . ' ' . $realClockOutTime,
                            'created_by'   => $creatorId,
                        ]);

                        // If attendance_employees record exists for this date, also update its clock_out
                        if ($attEmp) {
                            $attEmp->clock_out         = $realClockOutTime;
                            $attEmp->is_auto_clock_out = 1;
                            $attEmp->save();
                        }

                        $clockedOutCount++;

                        Log::info(sprintf(
                            '[auto:clock-out] Created auto clock out AttendanceRequest for employee #%d (%s) on date %s at %s',
                            $employee->id,
                            $employee->name,
                            $reqDate,
                            $realClockOutTime
                        ));
                    }
                }
            }
        }

        $this->info("[auto:clock-out] Completed. Total employees auto clocked out: {$clockedOutCount}");

        return 0;
    }
}
