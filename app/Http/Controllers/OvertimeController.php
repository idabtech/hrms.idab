<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceRequest;
use App\Models\Employee;
use App\Models\OvertimeLog;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    /**
     * Check if current employee has an Overtime shift scheduled in Rota for today,
     * and check their active overtime clock status.
     */
    public function checkStatus(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => __('Unauthenticated.')], 401);
        }

        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return response()->json(['success' => false, 'has_overtime_shift' => false, 'is_active' => false]);
        }

        $todayStr = date('Y-m-d');

        // Check Rota for an active overtime shift for today
        $overtimeShift = Shift::where('employee_id', $employee->id)
            ->whereDate('date', $todayStr)
            ->where(function ($q) {
                $q->where('type', 'overtime')
                  ->orWhere('type', 'Overtime')
                  ->orWhere('name', 'like', '%overtime%');
            })
            ->where(function ($q) {
                $q->where('is_deleted', false)
                  ->orWhere('is_deleted', 0)
                  ->orWhereNull('is_deleted');
            })
            ->first();

        // Fallback: check if employee default shift is overtime
        if (!$overtimeShift) {
            $defaultShift = Shift::where('employee_id', $employee->id)->whereNull('date')->first() ?? $employee->shift;
            if ($defaultShift && (strtolower($defaultShift->type ?? '') === 'overtime' || str_contains(strtolower($defaultShift->name ?? ''), 'overtime'))) {
                $overtimeShift = $defaultShift;
            }
        }

        $hasOvertimeShift = !is_null($overtimeShift);
        $shiftStart = '';
        $shiftEnd   = '';

        if ($overtimeShift) {
            if (!empty($overtimeShift->company_start_time)) {
                $shiftStart = Carbon::parse($overtimeShift->company_start_time)->format('g:i A');
            }
            if (!empty($overtimeShift->company_end_time) && $overtimeShift->company_end_time !== '00:00:00') {
                $shiftEnd = Carbon::parse($overtimeShift->company_end_time)->format('g:i A');
            }
        }

        // Check active overtime log
        $activeLog = OvertimeLog::where('employee_id', $employee->id)
            ->whereDate('date', $todayStr)
            ->whereNull('end_time')
            ->first();

        // Check completed overtime logs for today and sum exact seconds
        $completedLogs = OvertimeLog::where('employee_id', $employee->id)
            ->whereDate('date', $todayStr)
            ->whereNotNull('end_time')
            ->get();

        $totalSeconds = 0;
        foreach ($completedLogs as $log) {
            if ($log->start_time && $log->end_time) {
                $totalSeconds += max(0, strtotime($log->end_time) - strtotime($log->start_time));
            }
        }

        $totalMinutes = floor($totalSeconds / 60);
        $totalOvertimeFormatted = gmdate('H:i:s', $totalSeconds);

        // An employee is actively clocked in ONLY if they have clock_in set AND clock_out is missing/00:00:00 AND no pending request
        $attendance = AttendanceEmployee::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->latest()
            ->first();

        $pendingReq = AttendanceRequest::where('employee_id', $employee->id)
            ->whereDate('requested_at', Carbon::today())
            ->where('status', 'pending')
            ->first();

        $isActivelyClockedIn = $attendance && !empty($attendance->clock_in) && ($attendance->clock_out === '00:00:00' || is_null($attendance->clock_out)) && !$pendingReq;
        $isRegularClockedOut = !$isActivelyClockedIn;

        $isOtTimeReached = true;
        if ($overtimeShift && !empty($overtimeShift->company_start_time)) {
            $otStartCarbon = Carbon::parse(Carbon::today()->toDateString() . ' ' . $overtimeShift->company_start_time);
            $isOtTimeReached = Carbon::now()->gte($otStartCarbon);
        }

        return response()->json([
            'success'               => true,
            'has_overtime_shift'    => $hasOvertimeShift,
            'shift_start'           => $shiftStart,
            'shift_end'             => $shiftEnd,
            'is_active'             => !is_null($activeLog),
            'active_log_start'      => $activeLog ? $activeLog->start_time->format('H:i') : null,
            'is_finished'           => $completedLogs->count() > 0 && is_null($activeLog),
            'total_overtime_minutes'=> $totalMinutes,
            'total_overtime_time'   => $totalOvertimeFormatted,
            'is_regular_clocked_out'=> $isRegularClockedOut,
            'is_ot_time_reached'    => $isOtTimeReached,
        ]);
    }

    /**
     * Start Overtime Clock In.
     */
    public function startOvertime(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => __('Unauthenticated.')], 401);
        }

        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return response()->json(['success' => false, 'message' => __('Employee profile not found.')], 404);
        }

        $todayStr = date('Y-m-d');

        // Check if overtime is already active
        $activeLog = OvertimeLog::where('employee_id', $employee->id)
            ->whereDate('date', $todayStr)
            ->whereNull('end_time')
            ->first();

        if ($activeLog) {
            return response()->json([
                'success' => true,
                'message' => __('Overtime is already active.'),
                'log'     => $activeLog,
            ]);
        }

        // Get today's attendance record
        $attendance = AttendanceEmployee::where('employee_id', $employee->id)
            ->whereDate('date', $todayStr)
            ->first();

        // Prevent starting overtime if regular shift is currently clocked in (and not clocked out)
        if ($attendance && $attendance->clock_in && ($attendance->clock_out === '00:00:00' || is_null($attendance->clock_out))) {
            return response()->json([
                'success' => false,
                'message' => __('You are currently clocked in to your regular shift. Please clock out of your regular shift before starting overtime.'),
            ]);
        }

        $log = OvertimeLog::create([
            'employee_id'   => $employee->id,
            'attendance_id' => $attendance ? $attendance->id : null,
            'date'          => $todayStr,
            'start_time'    => now(),
            'created_by'    => $user->creatorId(),
        ]);

        return response()->json([
            'success'    => true,
            'message'    => __('Overtime started successfully.'),
            'start_time' => $log->start_time->format('g:i A'),
        ]);
    }

    /**
     * Stop / Clock Out Overtime.
     */
    public function stopOvertime(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => __('Unauthenticated.')], 401);
        }

        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return response()->json(['success' => false, 'message' => __('Employee profile not found.')], 404);
        }

        $todayStr = date('Y-m-d');

        $activeLog = OvertimeLog::where('employee_id', $employee->id)
            ->whereDate('date', $todayStr)
            ->whereNull('end_time')
            ->first();

        if (!$activeLog) {
            return response()->json(['success' => false, 'message' => __('No active overtime session found.')], 422);
        }

        $now = now();
        $sessionSeconds = $activeLog->start_time->diffInSeconds($now);
        $minutes = max(1, round($sessionSeconds / 60));

        $activeLog->end_time         = $now;
        $activeLog->duration_minutes = $minutes;
        $activeLog->save();

        // Calculate exact cumulative overtime for today from all completed OvertimeLogs
        $completedLogs = OvertimeLog::where('employee_id', $employee->id)
            ->whereDate('date', $todayStr)
            ->whereNotNull('end_time')
            ->get();

        $totalLogSeconds = 0;
        foreach ($completedLogs as $log) {
            if ($log->start_time && $log->end_time) {
                $totalLogSeconds += max(0, strtotime($log->end_time) - strtotime($log->start_time));
            }
        }

        // Update attendance record with exact total overtime
        $attendance = AttendanceEmployee::where('employee_id', $employee->id)
            ->whereDate('date', $todayStr)
            ->first();

        // Calculate any regular shift late clock-out overtime
        $regularShiftOvertimeSec = 0;
        if ($attendance && $attendance->clock_in && $attendance->clock_out && $attendance->clock_out !== '00:00:00') {
            if ($employee->company_end_time) {
                $shiftEndTime = strtotime($todayStr . ' ' . $employee->company_end_time);
                $clockOutTime = strtotime($todayStr . ' ' . $attendance->clock_out);
                if ($clockOutTime > $shiftEndTime) {
                    $regularShiftOvertimeSec = $clockOutTime - $shiftEndTime;
                }
            }
        }

        $finalOvertimeSec = $regularShiftOvertimeSec + $totalLogSeconds;
        $formattedOvertime = gmdate('H:i:s', $finalOvertimeSec);

        if ($attendance) {
            $attendance->overtime = $formattedOvertime;
            $attendance->save();
        }

        return response()->json([
            'success'               => true,
            'message'               => __('Overtime stopped successfully.'),
            'duration_minutes'      => $minutes,
            'total_overtime_time'   => $formattedOvertime,
        ]);
    }
}
