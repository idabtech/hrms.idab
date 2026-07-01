<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\Employee;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveDayDetail;
use App\Models\Utility;

class ReportController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    private function timeToSeconds(string $time): int
    {
        if (empty($time) || $time === '00:00:00') return 0;
        [$h, $m, $s] = array_map('intval', explode(':', $time));
        return ($h * 3600) + ($m * 60) + $s;
    }

    private function secondsToTime(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    // ── GET /api/report/branches ──────────────────────────────────────────────
    /**
     * Returns all branches belonging to the authenticated user's company.
     *
     * Response:
     *   data: [ { id, name }, … ]
     */
    public function branches()
    {
        $branches = Branch::where('created_by', Auth::user()->creatorId())
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status'  => true,
            'message' => 'Branches fetched successfully',
            'data'    => $branches,
        ]);
    }

    // ── GET /api/report/departments?branch_id= ────────────────────────────────
    /**
     * Returns departments for the authenticated user's company.
     * Optionally filtered by branch_id query param.
     *
     * Response:
     *   data: [ { id, name, branch_id, branch_name }, … ]
     */
    public function departments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $query = Department::with('branch')
            ->where('created_by', Auth::user()->creatorId())
            ->orderBy('name');

        if (!empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        $departments = $query->get()->map(fn ($d) => [
            'id'          => $d->id,
            'name'        => $d->name,
            'branch_id'   => $d->branch_id,
            'branch_name' => optional($d->branch)->name,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Departments fetched successfully',
            'data'    => $departments,
        ]);
    }

    // ── POST /api/employee-report ─────────────────────────────────────────────
    /**
     * Returns the same data structure as the web monthlyAttendance page.
     *
     * Request params (all optional):
     *   month        string  Y-m  (e.g. "2026-05")
     *   branch_id    int
     *   department   int
     *   employee_ids int[]
     *
     * Response mirrors the Blade variables:
     *   data.branch, data.department, data.curMonth,
     *   data.totalPresent, data.totalLeave,
     *   data.totalOvertime, data.totalEarlyLeave, data.totalLate,
     *   dates[]
     *   employeesAttendance[]:
     *     employee_id, name,
     *     total_hours, total_present, total_leave, total_half_day,
     *     total_day_off, total_holiday, total_lunch_break, total_tea_break,
     *     attendance_score,
     *     status{ "01": { status, label, future, clock_in, clock_out, company_shift_time }, … }
     */
    public function employeeReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month'          => 'nullable|date_format:Y-m',
            'branch_id'      => 'nullable|integer|exists:branches,id',
            'department'     => 'nullable|integer|exists:departments,id',
            'employee_ids'   => 'nullable|array',
            'employee_ids.*' => 'integer|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $creatorId = Auth::user()->creatorId();

            // ── Month ─────────────────────────────────────────────────────────
            if (!empty($request->month)) {
                $currentdate = strtotime($request->month);
                $month       = date('m', $currentdate);
                $year        = date('Y', $currentdate);
                $curMonth    = date('M-Y', $currentdate);
            } else {
                $month    = date('m');
                $year     = date('Y');
                $curMonth = date('M-Y', strtotime($year . '-' . $month));
            }

            $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
            $dates = [];
            for ($i = 1; $i <= $num_of_days; $i++) {
                $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }

            $monthStart = $year . '-' . $month . '-01';
            $monthEnd   = $year . '-' . $month . '-' . $num_of_days;

            // ── Branch / Department labels ────────────────────────────────────
            $branchLabel     = 'All';
            $departmentLabel = 'All';

            // ── Employee query ────────────────────────────────────────────────
            $employeeQuery = Employee::select('id', 'name')
                ->where('created_by', $creatorId);

            if (!empty($request->employee_ids)) {
                $employeeQuery->whereIn('id', $request->employee_ids);
            }

            if (!empty($request->branch_id)) {
                $employeeQuery->where('branch_id', $request->branch_id);
                $branch = Branch::find($request->branch_id);
                $branchLabel = $branch ? $branch->name : 'All';
            }

            if (!empty($request->department)) {
                $employeeQuery->where('department_id', $request->department);
                $dept = Department::find($request->department);
                $departmentLabel = $dept ? $dept->name : 'All';
            }

            $employees = $employeeQuery->get()->pluck('name', 'id');

            if ($employees->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No employees found',
                ], 404);
            }

            // ── Company settings: working days ────────────────────────────────
            $company_settings = Utility::settings();
            $workingDaysRaw = $company_settings['rota_working_days'] ?? '1,2,3,4,5,6,0';
            $workingDays      = array_map(
                'intval',
                array_filter(array_map('trim', explode(',', $workingDaysRaw)), 'strlen')
            );

            $satPattern     = $company_settings['saturday_pattern'] ?? 'none';
            // ── Holidays for the month ────────────────────────────────────────
            $holidays = Holiday::where('created_by', $creatorId)
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date',   '>=', $monthStart)
                ->get();

            $holidayDates = [];
            foreach ($holidays as $h) {
                $hPeriod = \Carbon\CarbonPeriod::create($h->start_date, $h->end_date);
                foreach ($hPeriod as $hDay) {
                    $holidayDates[$hDay->format('Y-m-d')] = $h->occasion ?? 'Holiday';
                }
            }

            // ── Approved leaves for all employees in the month ────────────────
            // Build a rich map: [employee_id][date] = { type, duration, period, status }
            // so half-day and paid/unpaid are respected — same logic as the web controller.
            $employeeIds    = $employees->keys()->toArray();
            $approvedLeaves = Leave::whereIn('employee_id', $employeeIds)
                ->where('status', 'Approved')
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date',   '>=', $monthStart)
                ->with(['leaveType', 'dayDetails'])
                ->get();

            $leaveDateMap = []; // [employee_id][date] = detail array
            foreach ($approvedLeaves as $lv) {
                $leaveTypeName = optional($lv->leaveType)->title ?? 'Leave';
                $lvPeriod      = \Carbon\CarbonPeriod::create(
                    $lv->start_date instanceof Carbon ? $lv->start_date : Carbon::parse($lv->start_date),
                    $lv->end_date   instanceof Carbon ? $lv->end_date   : Carbon::parse($lv->end_date)
                );

                $detailsByDate = $lv->dayDetails->keyBy(
                    fn($d) => $d->date instanceof Carbon
                        ? $d->date->format('Y-m-d')
                        : Carbon::parse($d->date)->format('Y-m-d')
                );

                foreach ($lvPeriod as $lvDay) {
                    $dk     = $lvDay->format('Y-m-d');
                    $detail = $detailsByDate->get($dk);

                    if ($detail) {
                        $duration   = $detail->day_duration    ?? 'full_day';
                        $period     = $detail->half_day_period ?? null;
                        $payStatus  = $detail->day_status      ?? 'paid';
                    } else {
                        $duration   = $lv->leave_duration  ?? 'full_day';
                        $period     = $lv->half_day_period ?? null;
                        $payStatus  = 'paid'; // legacy leaves default to paid
                    }

                    $leaveDateMap[$lv->employee_id][$dk] = [
                        'type'     => $leaveTypeName,
                        'duration' => $duration,
                        'period'   => $period,
                        'status'   => $payStatus,
                    ];
                }
            }

            $tz = config('app.timezone') ?: 'UTC';

            // ── Grand totals (mirrors $data in web controller) ────────────────
            $grandTotalPresent    = 0;
            $grandTotalLeave      = 0;
            $grandTotalPaidLeave  = 0;
            $grandTotalUnpaidLeave= 0;
            $ovetimeHours         = $overtimeMins = 0;
            $earlyleaveHours      = $earlyleaveMins = 0;
            $lateHours            = $lateMins = 0;

            $employeesAttendance = [];

            foreach ($employees as $id => $employeeName) {

                $attendanceStatus   = [];
                $totalPresents      = 0;
                $totalLeaves        = 0;
                $totalAbsents       = 0;
                $totalDayOffs       = 0;
                $totalHolidays      = 0;
                $totalHalfDays      = 0;
                $clockHalfDays      = 0;   // clock-based half-days (counted in $totalPresents)
                $leaveHalfDays      = 0;   // leave-based half-days (not in $totalPresents)
                $totalWorkedSeconds = 0;
                $totalLunchSeconds  = 0;
                $totalTeaSeconds    = 0;
                $totalPaidLeave     = 0;
                $totalUnpaidLeave   = 0;

                foreach ($dates as $date) {
                    $dateFormat = $year . '-' . $month . '-' . $date;
                    $dayOfWeek  = (int) Carbon::parse($dateFormat)->dayOfWeek;
                    $isFuture   = $dateFormat > date('Y-m-d');

                    $isHoliday  = isset($holidayDates[$dateFormat]);
                    $isDayOff   = !empty($workingDays) && !in_array($dayOfWeek, $workingDays);
                    if (!$isDayOff && $dayOfWeek === 6 && in_array(6, $workingDays)) {
                        $isDayOff = !$this->isSaturdayWorking(Carbon::parse($dateFormat), $satPattern);
                    }

                    // ── Resolve leave detail for this date ────────────────────
                    $isOnLeave    = isset($leaveDateMap[$id][$dateFormat]);
                    $leaveDetail  = $isOnLeave ? $leaveDateMap[$id][$dateFormat] : null;
                    $leaveLabel   = $leaveDetail ? $leaveDetail['type']     : '';
                    $leaveDur     = $leaveDetail ? ($leaveDetail['duration'] ?? 'full_day') : 'full_day';
                    $leavePeriod  = $leaveDetail ? ($leaveDetail['period']   ?? null)       : null;
                    $leavePaySt   = $leaveDetail ? ($leaveDetail['status']   ?? 'paid')     : 'paid';
                    $leaveIsHalf  = ($leaveDur === 'half_day');
                    $leaveDays    = $leaveIsHalf ? 0.5 : 1.0;

                    // Build descriptive label
                    $leaveCellLabel = $leaveLabel;
                    if ($leaveIsHalf) {
                        $leaveCellLabel .= ' — Half Day' . ($leavePeriod ? (' ' . ucfirst($leavePeriod)) : '');
                    }
                    if ($leavePaySt === 'unpaid') {
                        $leaveCellLabel .= ' (Unpaid)';
                    }

                    // Closure to record leave in all counters
                    $recordLeave = function() use (
                        &$totalLeaves, &$totalHalfDays, &$leaveHalfDays, &$grandTotalLeave,
                        &$totalPaidLeave, &$totalUnpaidLeave,
                        &$grandTotalPaidLeave, &$grandTotalUnpaidLeave,
                        $leaveIsHalf, $leaveDays, $leavePaySt
                    ) {
                        $totalLeaves    += $leaveDays;
                        $grandTotalLeave+= $leaveDays;
                        if ($leaveIsHalf) {
                            $totalHalfDays++;
                            $leaveHalfDays++;
                        }
                        if ($leavePaySt === 'paid') {
                            $totalPaidLeave       += $leaveDays;
                            $grandTotalPaidLeave  += $leaveDays;
                        } else {
                            $totalUnpaidLeave      += $leaveDays;
                            $grandTotalUnpaidLeave += $leaveDays;
                        }
                    };

                    // ── Future dates ──────────────────────────────────────────
                    if ($isFuture) {
                        if ($isHoliday) {
                            $attendanceStatus[$date] = [
                                'status'             => 'PH',
                                'label'              => $holidayDates[$dateFormat],
                                'future'             => true,
                                'clock_in'           => '',
                                'clock_out'          => '',
                                'company_shift_time' => '',
                            ];
                            $totalHolidays++;
                        } elseif ($isDayOff) {
                            $attendanceStatus[$date] = [
                                'status'             => 'DO',
                                'label'              => 'Day Off',
                                'future'             => true,
                                'clock_in'           => '',
                                'clock_out'          => '',
                                'company_shift_time' => '',
                            ];
                            $totalDayOffs++;
                        } elseif ($isOnLeave) {
                            $futureStatus = $leaveIsHalf ? 'HD' : 'L';
                            $attendanceStatus[$date] = [
                                'status'             => $futureStatus,
                                'label'              => $leaveCellLabel,
                                'future'             => true,
                                'clock_in'           => '',
                                'clock_out'          => '',
                                'company_shift_time' => '',
                            ];
                            $recordLeave();
                        } else {
                            $attendanceStatus[$date] = [
                                'status'             => 'NA',
                                'label'              => 'Not Added',
                                'future'             => true,
                                'clock_in'           => '',
                                'clock_out'          => '',
                                'company_shift_time' => '',
                            ];
                        }
                        continue;
                    }

                    // ── Past / today ──────────────────────────────────────────
                    $attendance = AttendanceEmployee::where('employee_id', $id)
                        ->where('date', $dateFormat)
                        ->first();

                    if ($isHoliday && empty($attendance)) {
                        $attendanceStatus[$date] = [
                            'status'             => 'PH',
                            'label'              => $holidayDates[$dateFormat],
                            'future'             => false,
                            'clock_in'           => '',
                            'clock_out'          => '',
                            'company_shift_time' => '',
                        ];
                        $totalHolidays++;

                    } elseif ($isDayOff && empty($attendance)) {
                        $attendanceStatus[$date] = [
                            'status'             => 'DO',
                            'label'              => 'Day Off',
                            'future'             => false,
                            'clock_in'           => '',
                            'clock_out'          => '',
                            'company_shift_time' => '',
                        ];
                        $totalDayOffs++;

                    } elseif ($isOnLeave && empty($attendance)) {
                        // Pure leave day — no clock-in record
                        $pureStatus = $leaveIsHalf ? 'HD' : 'L';
                        $attendanceStatus[$date] = [
                            'status'             => $pureStatus,
                            'label'              => $leaveCellLabel,
                            'future'             => false,
                            'clock_in'           => '',
                            'clock_out'          => '',
                            'company_shift_time' => '',
                        ];
                        $recordLeave();

                    } elseif (!empty($attendance)) {

                        // ── Calculate worked seconds ──────────────────────────
                        $attDateStr = is_string($attendance->date)
                            ? $attendance->date
                            : $attendance->date->format('Y-m-d');

                        $normalise = fn($t) => $t ? ((strlen($t) === 5) ? $t . ':00' : $t) : null;
                        $ciRaw = $normalise($attendance->clock_in);
                        $coRaw = $normalise($attendance->clock_out);

                        $dayWorkedSeconds = 0;
                        if (!empty($ciRaw) && $ciRaw !== '00:00:00') {
                            try {
                                $in = Carbon::createFromFormat('Y-m-d H:i:s', $attDateStr . ' ' . $ciRaw, $tz);
                            } catch (\Exception $e) { $in = null; }

                            if ($in) {
                                if (!empty($coRaw) && $coRaw !== '00:00:00') {
                                    try {
                                        $out = Carbon::createFromFormat('Y-m-d H:i:s', $attDateStr . ' ' . $coRaw, $tz);
                                    } catch (\Exception $e) { $out = null; }
                                    if ($out && $out->lessThan($in)) $out->addDay();
                                } else {
                                    $out = ($attDateStr === Carbon::now($tz)->toDateString()) ? Carbon::now($tz) : null;
                                }

                                if ($out) {
                                    $dayWorkedSeconds = $in->diffInSeconds($out);
                                    if (!empty($attendance->total_break) && $attendance->total_break !== '00:00:00') {
                                        [$bH, $bM, $bS] = array_map('intval', explode(':', $attendance->total_break));
                                        $dayWorkedSeconds -= ($bH * 3600 + $bM * 60 + $bS);
                                    }
                                    $dayWorkedSeconds = max(0, $dayWorkedSeconds);
                                }
                            }
                        }

                        // Classification thresholds (mirrors web controller):
                        //   < 2 h  → full-day leave / absence
                        //   2–6 h  → half day
                        //   >= 6 h → full present
                        $isFullDayLeave = ($dayWorkedSeconds > 0 && $dayWorkedSeconds < (2 * 3600));
                        $isHalfDay      = ($dayWorkedSeconds >= (2 * 3600) && $dayWorkedSeconds < (6 * 3600));

                        $ciDisplay = (!empty($attendance->clock_in)  && $attendance->clock_in  !== '00:00:00') ? substr($attendance->clock_in, 0, 5)  : '';
                        $coDisplay = (!empty($attendance->clock_out) && $attendance->clock_out !== '00:00:00') ? substr($attendance->clock_out, 0, 5) : '';
                        $shiftDisp = $attendance->company_shift_time ?? '';

                        if (in_array($attendance->status, ['present', 'Present'])) {
                            if ($isOnLeave) {
                                // Leave takes priority over clock-in
                                if ($isFullDayLeave) {
                                    // Barely clocked in — treat as full leave
                                    $attendanceStatus[$date] = [
                                        'status'             => $leaveIsHalf ? 'HD' : 'L',
                                        'label'              => $leaveCellLabel,
                                        'future'             => false,
                                        'clock_in'           => $ciDisplay,
                                        'clock_out'          => $coDisplay,
                                        'company_shift_time' => $shiftDisp,
                                    ];
                                    $recordLeave();
                                } elseif ($isHalfDay) {
                                    // Half-day clocked + half-day leave → 0.5 toward score total.
                                    // Do NOT call $recordLeave() for leaveHalfDays — the clock
                                    // half already accounts for the 0.5 via $clockHalfDays.
                                    $totalWorkedSeconds += $dayWorkedSeconds;
                                    $attendanceStatus[$date] = [
                                        'status'             => 'HD',
                                        'label'              => 'Half Day + ' . $leaveCellLabel,
                                        'future'             => false,
                                        'clock_in'           => $ciDisplay,
                                        'clock_out'          => $coDisplay,
                                        'company_shift_time' => $shiftDisp,
                                    ];
                                    $totalPresents++;
                                    $grandTotalPresent++;
                                    $totalHalfDays++;
                                    $clockHalfDays++;
                                    // Record leave counts (paid/unpaid) without touching leaveHalfDays
                                    $totalLeaves     += $leaveDays;
                                    $grandTotalLeave += $leaveDays;
                                    if ($leavePaySt === 'paid') {
                                        $totalPaidLeave      += $leaveDays;
                                        $grandTotalPaidLeave += $leaveDays;
                                    } else {
                                        $totalUnpaidLeave      += $leaveDays;
                                        $grandTotalUnpaidLeave += $leaveDays;
                                    }
                                } else {
                                    // >= 6 h but approved leave exists — leave takes priority
                                    $attendanceStatus[$date] = [
                                        'status'             => $leaveIsHalf ? 'HD' : 'L',
                                        'label'              => $leaveCellLabel,
                                        'future'             => false,
                                        'clock_in'           => $ciDisplay,
                                        'clock_out'          => $coDisplay,
                                        'company_shift_time' => $shiftDisp,
                                    ];
                                    $recordLeave();
                                }
                            } else {
                                // Normal present day (no approved leave)
                                if ($isFullDayLeave) {
                                    $attendanceStatus[$date] = [
                                        'status'             => 'L',
                                        'label'              => 'Leave (short attendance)',
                                        'future'             => false,
                                        'clock_in'           => $ciDisplay,
                                        'clock_out'          => $coDisplay,
                                        'company_shift_time' => $shiftDisp,
                                    ];
                                    $totalLeaves        += 1;
                                    $grandTotalLeave    += 1;
                                    $totalUnpaidLeave   += 1;
                                    $grandTotalUnpaidLeave += 1;
                                } else {
                                    $totalWorkedSeconds += $dayWorkedSeconds;
                                    $cellStatus = $isHalfDay ? 'HD' : 'P';
                                    $attendanceStatus[$date] = [
                                        'status'             => $cellStatus,
                                        'label'              => $isHalfDay ? 'Half Day' : 'Present',
                                        'future'             => false,
                                        'clock_in'           => $ciDisplay,
                                        'clock_out'          => $coDisplay,
                                        'company_shift_time' => $shiftDisp,
                                    ];
                                    $totalPresents++;
                                    $grandTotalPresent++;
                                    if ($isHalfDay) {
                                        $totalHalfDays++;
                                        $clockHalfDays++;
                                    }

                                    $totalLunchSeconds += $this->timeToSeconds($attendance->total_lunch_time ?? '00:00:00');
                                    $totalTeaSeconds   += $this->timeToSeconds($attendance->total_tea_time   ?? '00:00:00');

                                    if ($attendance->overtime > 0) {
                                        $ovetimeHours += date('h', strtotime($attendance->overtime));
                                        $overtimeMins += date('i', strtotime($attendance->overtime));
                                    }
                                    if ($attendance->early_leaving > 0) {
                                        $earlyleaveHours += date('h', strtotime($attendance->early_leaving));
                                        $earlyleaveMins  += date('i', strtotime($attendance->early_leaving));
                                    }
                                    if ($attendance->late > 0) {
                                        $lateHours += date('h', strtotime($attendance->late));
                                        $lateMins  += date('i', strtotime($attendance->late));
                                    }
                                }
                            }
                        } else {
                            // Attendance record exists but not Present
                            $cellStatus = $isOnLeave ? ($leaveIsHalf ? 'HD' : 'L') : 'A';
                            $cellLabel  = $isOnLeave ? $leaveCellLabel : 'Absent';
                            $attendanceStatus[$date] = [
                                'status'             => $cellStatus,
                                'label'              => $cellLabel,
                                'future'             => false,
                                'clock_in'           => '',
                                'clock_out'          => '',
                                'company_shift_time' => $shiftDisp,
                            ];
                            if ($isOnLeave) {
                                $recordLeave();
                            } else {
                                $totalAbsents++;
                                $grandTotalLeave++;
                            }
                        }

                    } else {
                        // Past working day, no record → Absent
                        $attendanceStatus[$date] = [
                            'status'             => 'A',
                            'label'              => 'Absent',
                            'future'             => false,
                            'clock_in'           => '',
                            'clock_out'          => '',
                            'company_shift_time' => '',
                        ];
                        $totalAbsents++;
                        $grandTotalLeave++;
                    }
                }

                // ── Attendance score: present / working days up to today ───────
                $isCurrentMonth = ($year == date('Y') && $month == date('m'));
                $scoreCapDate   = $isCurrentMonth ? date('Y-m-d') : $monthEnd;
                $satPattern     = $company_settings['saturday_pattern'] ?? 'none';

                $workingDayCount = 0;

                foreach ($dates as $d) {
                    $df  = $year . '-' . $month . '-' . $d;
                    if ($df > $scoreCapDate) break; // stop at end-of-cap
                    $dow = (int) \Carbon\Carbon::parse($df)->dayOfWeek;
                    $isH = isset($holidayDates[$df]);
                    $isD = !empty($workingDays) && !in_array($dow, $workingDays);

                    // Alternate Saturday pattern: even when Sat(6) is in workingDays,
                    // check whether this specific Saturday is actually working.
                    if ($dow === 6 && in_array(6, $workingDays)) {
                        $carbonDay = \Carbon\Carbon::parse($df);
                        $isSaturdayWorking = $this->isSaturdayWorking($carbonDay, $satPattern);

                        $isD = !$isSaturdayWorking;
                        if ($isSaturdayWorking) {
                            $isH = false;
                        }
                    }

                    if (!$isH && !$isD) $workingDayCount++;
                }

                // ── Effective present days: full day = 1.0, half day = 0.5 ──
                // Clock-based HD rows are counted as 1 in $totalPresents, so subtract 0.5 each.
                // Leave-based HD rows are NOT in $totalPresents, so add 0.5 each.
                $effectivePresent = ($totalPresents - ($clockHalfDays * 0.5))
                                  + ($leaveHalfDays * 0.5);
                $effectivePresentFmt = ($effectivePresent == floor($effectivePresent))
                    ? (int) $effectivePresent
                    : $effectivePresent;

                // ── Extra days = days worked beyond the working-day count ────
                $extraDays = max(0, $effectivePresent - $workingDayCount);
                $extraDaysFmt = ($extraDays == floor($extraDays))
                    ? (int) $extraDays
                    : $extraDays;

                $employeesAttendance[] = [
                    'employee_id'         => $id,
                    'name'                => $employeeName,
                    'total_hours'         => $this->secondsToTime($totalWorkedSeconds),
                    'total_present'       => $totalPresents,
                    'total_leave'         => $totalLeaves,
                    'total_paid_leave'    => $totalPaidLeave,
                    'total_unpaid_leave'  => $totalUnpaidLeave,
                    'total_half_day'      => $totalHalfDays,
                    'total_day_off'       => $totalDayOffs,
                    'total_holiday'       => $totalHolidays,
                    'total_lunch_break'   => $this->secondsToTime($totalLunchSeconds),
                    'total_tea_break'     => $this->secondsToTime($totalTeaSeconds),
                    'working_days'        => $workingDayCount,
                    'extra_days'          => $extraDaysFmt,
                    'attendance_score'    => $effectivePresentFmt . '/' . $workingDayCount,
                    'status'              => $attendanceStatus,
                ];
            }

            // ── Grand summary (mirrors $data array in web controller) ─────────
            $summary = [
                'branch'              => $branchLabel,
                'department'          => $departmentLabel,
                'curMonth'            => $curMonth,
                'totalPresent'        => $grandTotalPresent,
                'totalLeave'          => $grandTotalLeave,
                'totalPaidLeave'      => $grandTotalPaidLeave,
                'totalUnpaidLeave'    => $grandTotalUnpaidLeave,
                'totalOvertime'       => round($ovetimeHours + ($overtimeMins / 60), 2),
                'totalEarlyLeave'     => round($earlyleaveHours + ($earlyleaveMins / 60), 2),
                'totalLate'           => round($lateHours + ($lateMins / 60), 2),
            ];

            return response()->json([
                'status'              => true,
                'message'             => 'Employee report fetched successfully',
                'data'                => [
                    'summary'             => $summary,
                    'dates'               => $dates,
                    'employeesAttendance' => $employeesAttendance,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Decide whether a given date (Carbon instance) is a working Saturday
     * based on the saturday_pattern setting.
     *
     * pattern values:
     *   'all'  → every Saturday is working
     *   'odd'  → 1st, 3rd, 5th Saturday of the month
     *   'even' → 2nd, 4th Saturday of the month
     *   'none' (or anything else) → Saturday is never a working day
     *
     * This is only called when dow === 6 (Saturday) and Saturday IS in the
     * configured working-day set.
     */
    private function isSaturdayWorking(\Carbon\Carbon $date, string $pattern): bool
    {
        if ($pattern === 'all') {
            return true;
        }

        if ($pattern === 'none') {
            return false;
        }

        // Find which occurrence of Saturday this is in the month (1st, 2nd, 3rd…)
        $occurrence = (int) ceil($date->day / 7);

        if ($pattern === 'odd') {
            return ($occurrence % 2) === 1; // 1st, 3rd, 5th
        }

        if ($pattern === 'even') {
            return ($occurrence % 2) === 0; // 2nd, 4th
        }

        return false;
    }
}
