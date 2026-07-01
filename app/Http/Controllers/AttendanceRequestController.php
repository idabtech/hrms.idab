<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        if (!\Auth::user()->can('Manage Attendance Request')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $companySettings = Utility::settings();
        $type = $request->input('type', 'monthly');

        $query = AttendanceRequest::with([
            'employee.user:id,name',
            'approver:id,name'
        ])
            ->where('created_by', Auth::user()->creatorId())
            ->latest();

        if ($type == 'daily' && $request->filled('date')) {
            $query->whereDate('requested_at', $request->date);
        } elseif ($type == 'monthly' && $request->filled('month')) {
            $month = date('m', strtotime($request->month));
            $year  = date('Y', strtotime($request->month));
            $query->whereYear('requested_at', $year)->whereMonth('requested_at', $month);
        }

        $requests = $query->get();

        $statsQuery = clone $query;
        $stats = [
            'total'       => $statsQuery->count(),
            'this_month'  => (clone $statsQuery)->whereMonth('requested_at', now()->month)->count(),
            'this_week'   => (clone $statsQuery)->whereBetween('requested_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'last_30days' => (clone $statsQuery)->where('requested_at', '>=', now()->subDays(30))->count(),
        ];

        return view('attendance_requests.index', [
            'requests' => $requests,
            'stats'    => $stats,
            'type'     => $type,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'   => 'required|in:clock_in,clock_out',
            'reason' => 'nullable|string|max:255',
        ]);

        $employee = Employee::where('user_id', Auth::user()->id)->firstOrFail();

        $date = date("Y-m-d");
        $time = date("H:i:s");

        $attendanceRequest = AttendanceRequest::create([
            'employee_id'  => $employee->id,
            'type'         => $request->type,
            'reason'       => $request->reason ?? null,
            'status'       => 'pending',
            'requested_at' => $date . ' ' . $time,
            'clock_out'    => $request->type == 'clock_out' ? $time : null,
            'created_by'   => Auth::user()->creatorId(),
        ]);
        $settings = Utility::settings();

        if (isset($settings['attendance_request']) && $settings['attendance_request'] == 1) {
            $companyOwner = User::find(Auth::user()->creatorId());

            $hrUsers = $companyOwner->companyHrs();
            $hrEmails = $hrUsers->pluck('email')->toArray();

            // Map clock_in / clock_out → readable text
            $typeReadable = $request->type === 'clock_in' ? 'Clock In' : 'Clock Out';

            // Format date/time for email only
            $emailDate = date($settings['site_date_format'] ?? 'Y-m-d');
            $emailTime = date($settings['site_time_format'] ?? 'H:i');

            // Mail variables
            $uArr = [
                'company_name'   => $companyOwner->name ?? 'Company',
                'employee_name'  => $employee->name,
                'employee_email' => $employee->email ?? '',
                'type'           => $typeReadable,
                'date'           => $emailDate,
                'time'           => $emailTime,
                'reason'         => $request->reason ?? 'N/A',
                'url'            => route('attendance-requests.index'),
            ];

            // Combine recipients: company owner + HRs
            $recipients = array_merge([$companyOwner->email], $hrEmails);

            // Send to each recipient
            foreach ($recipients as $recipient) {
                Utility::sendEmailTemplate('attendance_request', $recipient, $uArr);
            }
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => __('Your request has been sent and is pending approval.'),
                'data' => $attendanceRequest,
            ], 201);
        }

        return redirect()->back()->with('success', __('Your request has been sent and is pending approval.'));
    }

    public function approve(AttendanceRequest $attendanceRequest)
    {
        // Permission check
        if (!\Auth::user()->can('Approve Attendance Request')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Prevent approving your own request
        $ownEmployee = Employee::where('user_id', Auth::id())->first();
        if ($ownEmployee && $attendanceRequest->employee_id === $ownEmployee->id) {
            return redirect()->back()->with('error', __('You cannot approve your own attendance request.'));
        }
        if ($attendanceRequest->type == "clock_out") {
                $pastAttendanceRequest = AttendanceRequest::whereDate('requested_at',$attendanceRequest->requested_at)
                ->where('employee_id', $attendanceRequest->employee_id)
                ->where('type','clock_in')
                ->where('status', 'pending')
                ->first();
                if($pastAttendanceRequest)
                {
                    return redirect()->back()->with('error', __('Please change status first clock in request.'));
                }
        }
        // Approve the request
        $attendanceRequest->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // Re-fetch to ensure all columns (including clock_out) are fresh after the update
        $attendanceRequest->refresh();

        $requestedAt = \Carbon\Carbon::parse($attendanceRequest->requested_at);

        $employeeId = $attendanceRequest->employee_id;
        $date = $requestedAt->toDateString();
        $time = $requestedAt->toTimeString();

        $employee = Employee::find($employeeId);

        if ($employee && $employee->company_start_time && $employee->company_end_time) {
            $startTime = $employee->company_start_time;
            $endTime   = $employee->company_end_time;
        } else {
            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
        }

        if ($attendanceRequest->type === 'clock_in') {
            // ---- CLOCK IN REQUEST ----
            // Clock-in time is derived from requested_at (when the employee submitted the request)
            $clockInTime = $time;

            $checkDb = AttendanceEmployee::where('employee_id', $employeeId)
                ->where('date', $date)
                ->where(function ($q) {
                    $q->where('clock_out', '00:00:00')
                      ->orWhereNull('clock_out');
                })
                ->first();

            if (empty($checkDb)) {
                $expectedStartTime = $date . ' ' . $startTime;

                $totalLateSeconds = strtotime($date . ' ' . $clockInTime) - strtotime($expectedStartTime);
                $totalLateSeconds = max($totalLateSeconds, 0);

                $hours = abs(floor($totalLateSeconds / 3600));
                $mins  = abs(floor($totalLateSeconds / 60 % 60));
                $secs  = abs(floor($totalLateSeconds % 60));
                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                $employeeAttendance                = new AttendanceEmployee();
                $employeeAttendance->employee_id   = $employeeId;
                $employeeAttendance->date          = $date;
                $employeeAttendance->status        = 'Present';
                $employeeAttendance->clock_in      = $clockInTime;
                $employeeAttendance->clock_out     = '00:00:00';
                $employeeAttendance->late          = $late;
                $employeeAttendance->early_leaving = '00:00:00';
                $employeeAttendance->overtime      = '00:00:00';
                $employeeAttendance->breaks        = $attendanceRequest->breaks;
                $employeeAttendance->total_break   = $attendanceRequest->total_break ?? '00:00:00';
                $employeeAttendance->total_rest    = $attendanceRequest->total_rest ?? '00:00:00';

                // Calculate lunch and tea totals from breaks
                $breakTotals = $this->calculateBreakTotals($attendanceRequest->breaks, $date);
                $employeeAttendance->total_lunch_time = $breakTotals['total_lunch_time'];
                $employeeAttendance->total_tea_time   = $breakTotals['total_tea_time'];

                $employeeAttendance->created_by = Auth::id();
                $employeeAttendance->save();
            }
        } elseif ($attendanceRequest->type === 'clock_out') {
            // ---- CLOCK OUT REQUEST ----
            // Use the stored clock_out time from the request record, falling back to requested_at time
            $clockOutTime = $attendanceRequest->clock_out ?? $time;

            \Log::info("AttendanceRequest approve [clock_out]: employee={$employeeId}, date={$date}, clockOutTime={$clockOutTime}, request->clock_out=" . $attendanceRequest->clock_out);

            // Find the open attendance row for this employee.
            // Also check yesterday in case the employee clocked in before midnight.
            // Handle both '00:00:00' and NULL as "no clock-out yet" states.
            $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                ->where(function ($q) {
                    $q->where('clock_out', '00:00:00')
                      ->orWhereNull('clock_out');
                })
                ->where(function ($q) use ($date) {
                    $q->where('date', $date)
                      ->orWhere('date', \Carbon\Carbon::parse($date)->subDay()->toDateString());
                })
                ->orderByDesc('date')
                ->first();

            if ($attendance) {
                \Log::info("AttendanceRequest approve [clock_out]: found attendance row id={$attendance->id}, date={$attendance->date}, current clock_out={$attendance->clock_out}");
                $attendanceDate = $attendance->date;

                $attendance->clock_out = $clockOutTime;

                // Update breaks from attendance request if available
                if ($attendanceRequest->breaks) {
                    $attendance->breaks = $attendanceRequest->breaks;
                    $breakTotals = $this->calculateBreakTotals($attendanceRequest->breaks, $attendanceDate);
                    $attendance->total_break      = $breakTotals['total_break'];
                    $attendance->total_lunch_time = $breakTotals['total_lunch_time'];
                    $attendance->total_tea_time   = $breakTotals['total_tea_time'];
                }

                // Calculate early leaving and overtime using the stored clock_out time
                $expectedEndTime = strtotime($attendanceDate . ' ' . $endTime);
                $actualEndTime   = strtotime($attendanceDate . ' ' . $clockOutTime);

                if ($actualEndTime < $expectedEndTime) {
                    $attendance->early_leaving = gmdate('H:i:s', max(0, $expectedEndTime - $actualEndTime));
                    $attendance->overtime      = '00:00:00';
                } else {
                    $attendance->overtime      = gmdate('H:i:s', max(0, $actualEndTime - $expectedEndTime));
                    $attendance->early_leaving = '00:00:00';
                }

                $attendance->save();
            } else {
                \Log::warning("AttendanceRequest approve: no open AttendanceEmployee row found for employee {$employeeId} on {$date}");
            }
        }

        return redirect()->back()->with('success', __('Request approved successfully and attendance updated.'));
    }

    public function decline(AttendanceRequest $attendanceRequest)
    {
        // Permission check
        if (!\Auth::user()->can('Decline Attendance Request')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Prevent declining your own request
        $ownEmployee = Employee::where('user_id', Auth::id())->first();
        if ($ownEmployee && $attendanceRequest->employee_id === $ownEmployee->id) {
            return redirect()->back()->with('error', __('You cannot decline your own attendance request.'));
        }

        $attendanceRequest->update([
            'status' => 'declined',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return back()->with('error', __('Request declined.'));
    }

    public function bulkApprove(Request $request)
    {
        if (!\Auth::user()->can('Approve Attendance Request')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $ownEmployee = Employee::where('user_id', Auth::id())->first();

        // Re-run the same filter query as index(), but only pending rows
        $query = AttendanceRequest::with(['employee', 'approver'])
            ->where('created_by', Auth::user()->creatorId())
            ->where('status', 'pending');

        $type = $request->input('type', 'monthly');

        if ($type === 'daily' && $request->filled('date')) {
            $query->whereDate('requested_at', $request->date);
        } elseif ($type === 'monthly' && $request->filled('month')) {
            $month = date('m', strtotime($request->month));
            $year  = date('Y', strtotime($request->month));
            $query->whereYear('requested_at', $year)->whereMonth('requested_at', $month);
        }

        // Process clock_in requests first so clock_out dependency is satisfied within the same bulk
        $pendingRequests = $query->orderByRaw("FIELD(type, 'clock_in', 'clock_out')")->get();

        if ($pendingRequests->isEmpty()) {
            return redirect()->back()->with('error', __('No pending requests found for the current filter.'));
        }

        $approvedCount = 0;
        $skippedCount  = 0;
        $skipErrors    = [];

        foreach ($pendingRequests as $attendanceRequest) {
            // Skip own requests
            if ($ownEmployee && $attendanceRequest->employee_id === $ownEmployee->id) {
                $skippedCount++;
                continue;
            }

            // For clock_out: ensure clock_in for that day is already approved (including ones just approved above)
            if ($attendanceRequest->type === 'clock_out') {
                $hasPendingClockIn = AttendanceRequest::whereDate('requested_at', $attendanceRequest->requested_at)
                    ->where('employee_id', $attendanceRequest->employee_id)
                    ->where('type', 'clock_in')
                    ->where('status', 'pending')
                    ->exists();

                if ($hasPendingClockIn) {
                    $skipErrors[] = __(':name — clock-out skipped, clock-in still pending.', [
                        'name' => optional($attendanceRequest->employee)->name,
                    ]);
                    $skippedCount++;
                    continue;
                }
            }

            $attendanceRequest->update([
                'status'      => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            // Re-fetch to ensure all columns (including clock_out) are fresh after the update
            $attendanceRequest->refresh();

            $requestedAt = Carbon::parse($attendanceRequest->requested_at);
            $employeeId  = $attendanceRequest->employee_id;
            $date        = $requestedAt->toDateString();
            $time        = $requestedAt->toTimeString();

            $employee = Employee::find($employeeId);
            if ($employee && $employee->company_start_time && $employee->company_end_time) {
                $startTime = $employee->company_start_time;
                $endTime   = $employee->company_end_time;
            } else {
                $startTime = Utility::getValByName('company_start_time');
                $endTime   = Utility::getValByName('company_end_time');
            }

            if ($attendanceRequest->type === 'clock_in') {
                // Clock-in time is derived from requested_at (when the employee submitted the request)
                $clockInTime = $time;

                $checkDb = AttendanceEmployee::where('employee_id', $employeeId)
                    ->where('date', $date)
                    ->where(function ($q) {
                        $q->where('clock_out', '00:00:00')
                          ->orWhereNull('clock_out');
                    })
                    ->first();

                if (empty($checkDb)) {
                    $expectedStartTime = $date . ' ' . $startTime;
                    $totalLateSeconds  = max(0, strtotime($date . ' ' . $clockInTime) - strtotime($expectedStartTime));
                    $late = sprintf('%02d:%02d:%02d',
                        floor($totalLateSeconds / 3600),
                        floor($totalLateSeconds / 60 % 60),
                        floor($totalLateSeconds % 60)
                    );

                    $breakTotals = $this->calculateBreakTotals($attendanceRequest->breaks, $date);

                    $empAttendance                   = new AttendanceEmployee();
                    $empAttendance->employee_id      = $employeeId;
                    $empAttendance->date             = $date;
                    $empAttendance->status           = 'Present';
                    $empAttendance->clock_in         = $clockInTime;
                    $empAttendance->clock_out        = '00:00:00';
                    $empAttendance->late             = $late;
                    $empAttendance->early_leaving    = '00:00:00';
                    $empAttendance->overtime         = '00:00:00';
                    $empAttendance->breaks           = $attendanceRequest->breaks;
                    $empAttendance->total_break      = $attendanceRequest->total_break ?? '00:00:00';
                    $empAttendance->total_rest       = $attendanceRequest->total_rest ?? '00:00:00';
                    $empAttendance->total_lunch_time = $breakTotals['total_lunch_time'];
                    $empAttendance->total_tea_time   = $breakTotals['total_tea_time'];
                    $empAttendance->created_by       = Auth::id();
                    $empAttendance->save();
                }
            } elseif ($attendanceRequest->type === 'clock_out') {
                $clockOutTime = $attendanceRequest->clock_out ?? $time;

                $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                    ->where(function ($q) {
                        $q->where('clock_out', '00:00:00')
                          ->orWhereNull('clock_out');
                    })
                    ->where(function ($q) use ($date) {
                        $q->where('date', $date)
                          ->orWhere('date', Carbon::parse($date)->subDay()->toDateString());
                    })
                    ->orderByDesc('date')
                    ->first();

                if ($attendance) {
                    $attendanceDate  = $attendance->date;
                    $attendance->clock_out = $clockOutTime;

                    if ($attendanceRequest->breaks) {
                        $attendance->breaks = $attendanceRequest->breaks;
                        $breakTotals = $this->calculateBreakTotals($attendanceRequest->breaks, $attendanceDate);
                        $attendance->total_break      = $breakTotals['total_break'];
                        $attendance->total_lunch_time = $breakTotals['total_lunch_time'];
                        $attendance->total_tea_time   = $breakTotals['total_tea_time'];
                    }

                    $expectedEndTime = strtotime($attendanceDate . ' ' . $endTime);
                    $actualEndTime   = strtotime($attendanceDate . ' ' . $clockOutTime);

                    if ($actualEndTime < $expectedEndTime) {
                        $attendance->early_leaving = gmdate('H:i:s', max(0, $expectedEndTime - $actualEndTime));
                        $attendance->overtime      = '00:00:00';
                    } else {
                        $attendance->overtime      = gmdate('H:i:s', max(0, $actualEndTime - $expectedEndTime));
                        $attendance->early_leaving = '00:00:00';
                    }

                    $attendance->save();
                }
            }

            $approvedCount++;
        }

        $message = __(':count request(s) approved successfully.', ['count' => $approvedCount]);
        if ($skippedCount) {
            $message .= ' ' . __(':count skipped (own requests or dependency issues).', ['count' => $skippedCount]);
        }
        if (!empty($skipErrors)) {
            $message .= ' ' . implode(' ', $skipErrors);
        }

        return redirect()->back()->with('success', $message);
    }

    public function bulkDecline(Request $request)
    {
        if (!\Auth::user()->can('Decline Attendance Request')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $ownEmployee = Employee::where('user_id', Auth::id())->first();

        // Re-run the same filter query as index(), but only pending rows
        $query = AttendanceRequest::where('created_by', Auth::user()->creatorId())
            ->where('status', 'pending');

        $type = $request->input('type', 'monthly');

        if ($type === 'daily' && $request->filled('date')) {
            $query->whereDate('requested_at', $request->date);
        } elseif ($type === 'monthly' && $request->filled('month')) {
            $month = date('m', strtotime($request->month));
            $year  = date('Y', strtotime($request->month));
            $query->whereYear('requested_at', $year)->whereMonth('requested_at', $month);
        }

        $pendingRequests = $query->get();

        if ($pendingRequests->isEmpty()) {
            return redirect()->back()->with('error', __('No pending requests found for the current filter.'));
        }

        $declinedCount = 0;
        $skippedCount  = 0;

        foreach ($pendingRequests as $attendanceRequest) {
            // Skip own requests
            if ($ownEmployee && $attendanceRequest->employee_id === $ownEmployee->id) {
                $skippedCount++;
                continue;
            }

            $attendanceRequest->update([
                'status'      => 'declined',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            $declinedCount++;
        }

        $message = __(':count request(s) declined.', ['count' => $declinedCount]);
        if ($skippedCount) {
            $message .= ' ' . __(':count skipped (own requests).', ['count' => $skippedCount]);
        }

        return redirect()->back()->with('error', $message);
    }

    /**
     * Calculate lunch, tea, and total break times from breaks array
     */
    private function calculateBreakTotals($breaksJson, $date = null)
    {
        $breaks = is_string($breaksJson) ? json_decode($breaksJson, true) : (array)$breaksJson;

        $totalLunchSeconds = 0;
        $totalTeaSeconds = 0;
        $totalActualBreakSeconds = 0;

        if (!empty($breaks) && is_array($breaks)) {
            foreach ($breaks as $break) {
                if (isset($break['start']) && isset($break['end'])) {
                    $breakSeconds = strtotime($date . ' ' . $break['end']) - strtotime($date . ' ' . $break['start']);

                    if ($breakSeconds > 0) {
                        $totalActualBreakSeconds += $breakSeconds;

                        if (isset($break['type'])) {
                            if ($break['type'] === 'lunch') {
                                $totalLunchSeconds += $breakSeconds;
                            } elseif ($break['type'] === 'tea') {
                                $totalTeaSeconds += $breakSeconds;
                            }
                        }
                    }
                }
            }
        }

        return [
            'total_break' => gmdate('H:i:s', $totalActualBreakSeconds),
            'total_lunch_time' => gmdate('H:i:s', $totalLunchSeconds),
            'total_tea_time' => gmdate('H:i:s', $totalTeaSeconds),
        ];
    }

    public function destroy(AttendanceRequest $attendanceRequest)
    {
        // Permission check
        if (!\Auth::user()->can('Delete Attendance Request')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $attendanceRequest->delete();
        return redirect()->route('attendance-requests.index')->with('success', __('Request deleted successfully.'));
    }

    public function clockOut(Request $request, $id)
    {
        $attendanceRequestData = AttendanceRequest::where('id', $id)->first();
        if ($attendanceRequestData) {
            AttendanceRequest::where('id', $attendanceRequestData->id)->update(['clock_out' => date("H:i:s")]);
            return redirect()->route('dashboard')->with('success', __('Employee successfully clock Out.'));
        }
        return redirect()->route('dashboard')->with('success', __('Request Not Found.'));
    }

    // public function storeFromAnotherPlatform(Request $request)
    // {
    //     $request->validate([
    //         'type'   => 'required|in:clock_in,clock_out',
    //         'reason' => 'nullable|string|max:255',
    //     ]);

    //     $user = Auth::user();
    //     $employee = Employee::where('user_id', $user->id)->firstOrFail();
    //     $date = date("Y-m-d");
    //     $time = date("H:i:s");

    //     $attendanceRequest = AttendanceRequest::create([
    //         'employee_id'  => $employee->id,
    //         'type'         => $request->type,
    //         'reason'       => $request->reason ?? null,
    //         'status'       => 'pending',
    //         'requested_at' => $date . ' ' . $time,
    //         'created_by'   => Auth::user()->creatorId(),
    //     ]);


    //     $settings = Utility::settings();
    //     if (isset($settings['attendance_request']) && $settings['attendance_request'] == 1) {
    //         $companyOwner = User::find(Auth::user()->creatorId());

    //         $hrUsers = $companyOwner->companyHrs();
    //         $hrEmails = $hrUsers->pluck('email')->toArray();

    //         // Map clock_in / clock_out → readable text
    //         $typeReadable = $request->type === 'clock_in' ? 'Clock In' : 'Clock Out';

    //         // Format date/time for email only
    //         $emailDate = date($settings['site_date_format'] ?? 'Y-m-d');
    //         $emailTime = date($settings['site_time_format'] ?? 'H:i');

    //         // Mail variables
    //         $uArr = [
    //             'company_name'   => $companyOwner->name ?? 'Company',
    //             'employee_name'  => $employee->name,
    //             'employee_email' => $employee->email ?? '',
    //             'type'           => $typeReadable,
    //             'date'           => $emailDate,
    //             'time'           => $emailTime,
    //             'reason'         => $request->reason ?? 'N/A',
    //             'url'            => route('attendance-requests.index'),
    //         ];

    //         // Combine recipients: company owner + HRs
    //         $recipients = array_merge([$companyOwner->email], $hrEmails);

    //         // Send to each recipient
    //         foreach ($recipients as $recipient) {
    //             Utility::sendEmailTemplate('attendance_request', $recipient, $uArr);
    //         }
    //     }

    //     return redirect()->back()->with('success', __('Your request has been sent and is pending approval.'));
    // }
}
