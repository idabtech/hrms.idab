<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave as LocalLeave;
use App\Models\LeaveDayDetail;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Models\Utility;
use App\Services\Idab\IdabApiService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exports\LeaveExport;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class LeaveController extends Controller
{
    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request)
    {
        if (!\Auth::user()->can('Manage Leave')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $date = Utility::AnnualLeaveCycle();

        if (\Auth::user()->type == 'employee') {
            $user     = \Auth::user();
            $employee = Employee::where('user_id', '=', $user->id)->first();

            $query = LocalLeave::where('employee_id', '=', $employee->id)
                        ->with(['dayDetails', 'leaveType']);

            // Filters
            if (!empty($request->leave_type)) {
                $query->where('leave_type_id', $request->leave_type);
            }
            if (!empty($request->status)) {
                $query->where('status', $request->status);
            }
            if (!empty($request->start_date)) {
                $query->where('start_date', '>=', $request->start_date);
            }
            if (!empty($request->end_date)) {
                $query->where('end_date', '<=', $request->end_date);
            }

            $leaves = $query->orderBy('id', 'desc')->get();

            // Stat cards: leave balance per type for current employee
            $assignedLeaveTypesForCards = $employee->leaveTypes()->get();

            if ($assignedLeaveTypesForCards->isEmpty()) {
                // No leave types assigned — no balance cards
                $leaveBalances = collect();
            } else {
                $assignedIds = $assignedLeaveTypesForCards->pluck('id')->toArray();
                $pivotDaysMap = $assignedLeaveTypesForCards->pluck('pivot.total_days', 'id')->toArray();

                $leaveBalanceQuery = LeaveType::select(
                        \DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_used, leave_types.title, leave_types.days, leave_types.id')
                    )
                    ->leftjoin('leaves', function ($join) use ($employee, $date) {
                        $join->on('leaves.leave_type_id', '=', 'leave_types.id');
                        $join->where('leaves.employee_id', '=', $employee->id);
                        $join->where('leaves.status', '=', 'Approved');
                        $join->whereBetween('leaves.created_at', [$date['start_date'], $date['end_date']]);
                    })
                    ->where('leave_types.created_by', '=', \Auth::user()->creatorId())
                    ->whereIn('leave_types.id', $assignedIds);

                $leaveBalances = $leaveBalanceQuery
                    ->groupBy('leave_types.id', 'leave_types.title', 'leave_types.days')
                    ->get()
                    ->map(function ($item) use ($pivotDaysMap) {
                        // Use per-employee total_days from pivot (treat 0 as "use global default")
                        $pivotDays = $pivotDaysMap[$item->id] ?? 0;
                        $item->days = $pivotDays > 0 ? $pivotDays : $item->days;
                        $item->remaining = max(0, $item->days - $item->total_used);
                        return $item;
                    });
            }

            $employeesList = [];
            $selectedEmployee = $employee->id;

        } else {
            // HR / Admin / Company
            $employeesList = Employee::where('created_by', \Auth::user()->creatorId())
                                ->get()
                                ->pluck('name', 'id');
            $employeesList->prepend(__('All'), '');

            $query = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())
                        ->with(['employees', 'leaveType', 'dayDetails']);

            // Filters
            if (!empty($request->employee)) {
                $query->where('employee_id', $request->employee);
            }
            if (!empty($request->leave_type)) {
                $query->where('leave_type_id', $request->leave_type);
            }
            if (!empty($request->status)) {
                $query->where('status', $request->status);
            }
            if (!empty($request->start_date)) {
                $query->where('start_date', '>=', $request->start_date);
            }
            if (!empty($request->end_date)) {
                $query->where('end_date', '<=', $request->end_date);
            }

            $leaves = $query->orderBy('id', 'desc')->get();

            // Stat cards for selected employee or null
            $leaveBalances = collect();
            $selectedEmployee = $request->employee ?? '';

            if (!empty($request->employee)) {
                // Get assigned leave types for the selected employee
                $selectedEmp = Employee::find($request->employee);
                $assignedLeaveTypesAdmin = $selectedEmp ? $selectedEmp->leaveTypes()->get() : collect();

                if ($assignedLeaveTypesAdmin->isEmpty()) {
                    // No leave types assigned — no balance cards
                    $leaveBalances = collect();
                } else {
                    $assignedIds = $assignedLeaveTypesAdmin->pluck('id')->toArray();
                    $pivotDaysMap = $assignedLeaveTypesAdmin->pluck('pivot.total_days', 'id')->toArray();

                    $leaveBalances = LeaveType::select(
                            \DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_used, leave_types.title, leave_types.days, leave_types.id')
                        )
                        ->leftjoin('leaves', function ($join) use ($request, $date) {
                            $join->on('leaves.leave_type_id', '=', 'leave_types.id');
                            $join->where('leaves.employee_id', '=', $request->employee);
                            $join->where('leaves.status', '=', 'Approved');
                            $join->whereBetween('leaves.created_at', [$date['start_date'], $date['end_date']]);
                        })
                        ->where('leave_types.created_by', '=', \Auth::user()->creatorId())
                        ->whereIn('leave_types.id', $assignedIds)
                        ->groupBy('leave_types.id', 'leave_types.title', 'leave_types.days')
                        ->get()
                        ->map(function ($item) use ($pivotDaysMap) {
                            $pivotDays = $pivotDaysMap[$item->id] ?? 0;
                            $item->days = $pivotDays > 0 ? $pivotDays : $item->days;
                            $item->remaining = max(0, $item->days - $item->total_used);
                            return $item;
                        });
                }
            }
        }

        // Leave types for filter dropdown
        if (\Auth::user()->type == 'employee') {
            // Employee sees only their assigned leave types in the filter
            $assignedFilterIds = $employee->leaveTypes()->pluck('leave_type_id')->toArray();
            if (!empty($assignedFilterIds)) {
                $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())
                                ->whereIn('id', $assignedFilterIds)
                                ->get()
                                ->pluck('title', 'id');
            } else {
                $leaveTypes = collect();
            }
        } else {
            $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())
                            ->get()
                            ->pluck('title', 'id');
        }
        $leaveTypes->prepend(__('All'), '');

        return view('leave.index', compact('leaves', 'leaveBalances', 'leaveTypes', 'employeesList', 'selectedEmployee'));
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    public function create()
    {
        if (!\Auth::user()->can('Create Leave')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if (Auth::user()->type == 'employee') {
            $employees = Employee::where('user_id', '=', \Auth::user()->id)->first();
            // For employee users, only show their assigned leave types with per-employee data
            if ($employees) {
                $assignedLeaveTypes = $employees->leaveTypes()->get();
                if ($assignedLeaveTypes->isNotEmpty()) {
                    // Override global values with per-employee pivot values (treat 0 as "use global")
                    $leavetypes = $assignedLeaveTypes->map(function ($lt) {
                        $pivotDays = $lt->pivot->total_days ?? 0;
                        $lt->days = $pivotDays > 0 ? $pivotDays : $lt->days;
                        $lt->is_paid = $lt->pivot->is_paid ?? $lt->is_paid;
                        return $lt;
                    });
                } else {
                    $leavetypes = collect();
                }
            } else {
                $leavetypes = collect();
            }
        } else {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())
                            ->get()->pluck('name', 'id');
            // For admin/HR, show all initially — AJAX will filter per employee
            $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
        }

        return view('leave.create', compact('employees', 'leavetypes'));
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request)
    {
        if (!\Auth::user()->can('Create Leave')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'employee_id'   => 'required',
            'leave_type_id' => 'required',
            'start_date'    => 'required',
            'end_date'      => 'required|after_or_equal:start_date',
            'leave_reason'  => 'required',
            'remark'        => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $leave_type = LeaveType::find($request->leave_type_id);
        $date       = Utility::AnnualLeaveCycle();

        // ── Build per-day details from form submission ─────────────────────
        // Each day row in the form submits:
        //   day_duration[YYYY-MM-DD] = full_day | half_day
        //   half_day_period[YYYY-MM-DD] = morning | afternoon  (only when half_day)
        //   day_status[YYYY-MM-DD]   = paid | unpaid
        $dayDetails   = $this->buildDayDetailsFromRequest($request);
        $totalDays    = $this->sumTotalDays($dayDetails);
        $paidDays     = $this->sumPaidDays($dayDetails);

        // Leave-level duration flag (used only when no day-breakdown exists,
        // i.e. single-day leaves submitted via the simple toggle).
        $leaveDuration  = $request->input('leave_duration', 'full_day');
        $halfDayPeriod  = $leaveDuration === 'half_day' ? $request->input('half_day_period') : null;

        // For a single-day half-day with no day_duration[] array in the form
        if (empty($dayDetails) && $leaveDuration === 'half_day') {
            $totalDays = 0.5;
            $paidDays  = 0.5;
        }

        // ── Quota check ────────────────────────────────────────────────────
        $leaves_used = LocalLeave::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'Approved')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');

        $leaves_pending = LocalLeave::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'Pending')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');

        // Use per-employee total_days from pivot if assigned, otherwise fallback to global
        $empLeaveType = \App\Models\EmployeeLeaveType::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $leave_type->id)->first();
        $allowedDays = ($empLeaveType && $empLeaveType->total_days > 0) ? $empLeaveType->total_days : $leave_type->days;
        $remaining = $allowedDays - $leaves_used;

        if ($paidDays > $remaining) {
            return redirect()->back()->with('error', __('You are not eligible for leave.'));
        }

        // if (!empty($leaves_pending) && ($leaves_pending + $paidDays) > $remaining) {
        //     return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
        // }

        // ── Save leave ─────────────────────────────────────────────────────
        $leave                   = new LocalLeave();
        $leave->employee_id      = $request->employee_id;
        $leave->leave_type_id    = $request->leave_type_id;
        $leave->applied_on       = date('Y-m-d');
        $leave->start_date       = $request->start_date;
        $leave->end_date         = $request->end_date;
        $leave->total_leave_days = $totalDays;
        $leave->leave_duration   = $leaveDuration;
        $leave->half_day_period  = $halfDayPeriod;
        $leave->leave_reason     = $request->leave_reason;
        $leave->remark           = $request->remark;
        $leave->status           = 'Pending';
        $leave->created_by       = Auth::user()->creatorId();
        $leave->save();

        // ── Save per-day breakdown ─────────────────────────────────────────
        $this->persistDayDetails($leave->id, $dayDetails);

        // ── Notification email ─────────────────────────────────────────────
        $employee = Employee::find($leave->employee_id);
        $user     = User::find($employee->created_by);

        if (Auth::user()->type != 'company') {
            $settings = Utility::settings();
            if ($settings['new_leave_request'] == 1) {
                $uArr = [
                    'employee_name'        => $employee->name,
                    'leave_type'           => $leave->leaveType->title,
                    'leave_start_end_time' => $leave->start_date->format('Y-m-d') . ' to ' . $leave->end_date->format('Y-m-d'),
                    'leave_reason'         => $leave->leave_reason ?? '',
                ];
                $resp = Utility::sendEmailTemplate('new_leave_request', [$user->id => $user->email], $uArr);
            }
        }

        // ── Google Calendar ────────────────────────────────────────────────
        if ($request->get('synchronize_type') == 'google_calender') {
            $type          = 'leave';
            $request1      = new GoogleEvent();
            $request1->title      = !empty(\Auth::user()->getLeaveType($leave->leave_type_id))
                                        ? \Auth::user()->getLeaveType($leave->leave_type_id)->title : '';
            $request1->start_date = $request->start_date;
            $request1->end_date   = $request->end_date;
            Utility::addCalendarData($request1, $type);
        }

        return redirect()->route('leave.index')->with(
            'success',
            __('Leave successfully created.') .
            ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error']))
                ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : '')
        );
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(LocalLeave $leave)
    {
        return redirect()->route('leave.index');
    }

    // =========================================================================
    // EDIT
    // =========================================================================

    public function edit(LocalLeave $leave)
    {
        if (!\Auth::user()->can('Edit Leave')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if ($leave->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        if (Auth::user()->type == 'employee') {
            $employees = Employee::where('user_id', '=', \Auth::user()->id)->first();
            // For employee users, only show their assigned leave types with per-employee data
            if ($employees) {
                $assignedLeaveTypes = $employees->leaveTypes()->get();
                if ($assignedLeaveTypes->isNotEmpty()) {
                    $leavetypes = $assignedLeaveTypes->map(function ($lt) {
                        $pivotDays = $lt->pivot->total_days ?? 0;
                        $lt->days = $pivotDays > 0 ? $pivotDays : $lt->days;
                        $lt->is_paid = $lt->pivot->is_paid ?? $lt->is_paid;
                        return $lt;
                    });
                } else {
                    $leavetypes = collect();
                }
            } else {
                $leavetypes = collect();
            }
        } else {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())
                            ->get()->pluck('name', 'id');
            $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
        }

        // Eager-load day details so the view can pre-populate the breakdown table
        $leave->load('dayDetails');

        return view('leave.edit', compact('leave', 'employees', 'leavetypes'));
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, $leave)
    {
        $leave = LocalLeave::find($leave);

        if (!\Auth::user()->can('Edit Leave')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($leave->created_by != Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'employee_id'   => 'required',
            'leave_type_id' => 'required',
            'start_date'    => 'required',
            'end_date'      => 'required|after_or_equal:start_date',
            'leave_reason'  => 'required',
            'remark'        => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $leave_type    = LeaveType::find($request->leave_type_id);
        $date          = Utility::AnnualLeaveCycle();
        $dayDetails    = $this->buildDayDetailsFromRequest($request);
        $totalDays     = $this->sumTotalDays($dayDetails);
        $paidDays      = $this->sumPaidDays($dayDetails);

        $leaveDuration = $request->input('leave_duration', 'full_day');
        $halfDayPeriod = $leaveDuration === 'half_day' ? $request->input('half_day_period') : null;

        if (empty($dayDetails) && $leaveDuration === 'half_day') {
            $totalDays = 0.5;
            $paidDays  = 0.5;
        }

        // ── Quota check (exclude current leave) ───────────────────────────
        $leaves_used = LocalLeave::whereNotIn('id', [$leave->id])
            ->where('employee_id', $request->employee_id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'Approved')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');

        $leaves_pending = LocalLeave::whereNotIn('id', [$leave->id])
            ->where('employee_id', $request->employee_id)
            ->where('leave_type_id', $leave_type->id)
            ->where('status', 'Pending')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');

        // Use per-employee total_days from pivot if assigned, otherwise fallback to global
        $empLeaveType = \App\Models\EmployeeLeaveType::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $leave_type->id)->first();
        $allowedDays = ($empLeaveType && $empLeaveType->total_days > 0) ? $empLeaveType->total_days : $leave_type->days;
        $remaining = $allowedDays - $leaves_used;

        if ($paidDays > $remaining) {
            return redirect()->back()->with('error', __('You are not eligible for leave.'));
        }

        // if (!empty($leaves_pending) && ($leaves_pending + $paidDays) > $remaining) {
        //     return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
        // }

        // ── Save ───────────────────────────────────────────────────────────
        $leave->employee_id      = $request->employee_id;
        $leave->leave_type_id    = $request->leave_type_id;
        $leave->start_date       = $request->start_date;
        $leave->end_date         = $request->end_date;
        $leave->total_leave_days = $totalDays;
        $leave->leave_duration   = $leaveDuration;
        $leave->half_day_period  = $halfDayPeriod;
        $leave->leave_reason     = $request->leave_reason;
        $leave->remark           = $request->remark;
        $leave->save();

        // Re-save day breakdown
        $leave->dayDetails()->delete();
        $this->persistDayDetails($leave->id, $dayDetails);

        return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(LocalLeave $leave)
    {
        if (!\Auth::user()->can('Delete Leave')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($leave->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // dayDetails cascade-deleted by FK
        $leave->delete();

        return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
    }

    // =========================================================================
    // EXPORT
    // =========================================================================

    public function export()
    {
        $name = 'leave_' . date('Y-m-d i:h:s');
        return Excel::download(new LeaveExport(), $name . '.xlsx');
    }

    // =========================================================================
    // ACTION (view detail modal)
    // =========================================================================

    public function action($id)
    {
        $leave     = LocalLeave::with('dayDetails')->find($id);
        $employee  = Employee::find($leave->employee_id);
        $leavetype = LeaveType::find($leave->leave_type_id);

        return view('leave.action', compact('employee', 'leavetype', 'leave'));
    }

    // =========================================================================
    // CHANGE ACTION (approve / reject)
    // =========================================================================

    public function changeaction(Request $request)
    {
        $leave = LocalLeave::find($request->leave_id);

        // Prevent hr/manager from actioning their own leave
        $actorEmployee = Employee::where('user_id', \Auth::id())->first();
        if ($actorEmployee && $actorEmployee->id == $leave->employee_id) {
            return redirect()->route('leave.index')
                ->with('error', __('You cannot approve or decline your own leave request.'));
        }

        $leave->status = $request->status;

        if ($leave->status == 'Approved') {
            // Recalculate total from the stored day details (respects half-day fractions)
            $leave->load('dayDetails');
            $leave->total_leave_days = $leave->totalDaysConsumed();
        }

        $leave->save();

        $employee = Employee::find($leave->employee_id);

        // ── Rota shifts sync ───────────────────────────────────────────────
        if ($employee) {
            try {
                $creatorId = $leave->created_by;
                $leaveType = optional($leave->leaveType)->title ?? 'Leave';
                $sd        = $leave->start_date instanceof Carbon ? $leave->start_date->format('Y-m-d') : $leave->start_date;
                $ed        = $leave->end_date   instanceof Carbon ? $leave->end_date->format('Y-m-d')   : $leave->end_date;
                $period    = CarbonPeriod::create($sd, $ed);

                if ($leave->status === 'Approved') {
                    foreach ($period as $day) {
                        Shift::updateOrCreate(
                            ['employee_id' => $employee->id, 'date' => $day->format('Y-m-d')],
                            [
                                'name'               => $leaveType,
                                'company_start_time' => '00:00:00',
                                'company_end_time'   => '23:59:00',
                                'type'               => 'leave',
                                'notes'              => $leave->leave_reason ?? null,
                                'is_deleted'         => false,
                                'created_by'         => $creatorId,
                            ]
                        );
                    }
                } else {
                    foreach ($period as $day) {
                        $shiftRow = Shift::where('employee_id', $employee->id)
                            ->where('date', $day->format('Y-m-d'))
                            ->where('type', 'leave')
                            ->where('created_by', $creatorId)
                            ->first();

                        if ($shiftRow) {
                            $shiftRow->delete();
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('LeaveController: rota shift sync failed.', [
                    'leave_id' => $leave->id, 'error' => $e->getMessage(),
                ]);
            }
        }

        // ── Idab API notification ──────────────────────────────────────────
        if ($leave->status === 'Approved' && $employee) {
            try {
                $idab          = app(IdabApiService::class);
                $leaveTypeName = optional($leave->leaveType)->title ?? 'Leave';
                $idab->notifyLeaveApproved(
                    $employee->id,
                    $leave->start_date,
                    $leave->end_date,
                    $leaveTypeName,
                    $leave->leave_reason ?? ''
                );
            } catch (\Throwable $e) {
                Log::error('LeaveController: idabcard notifyLeaveApproved failed.', [
                    'leave_id' => $leave->id, 'error' => $e->getMessage(),
                ]);
            }
        }

        // ── Twilio ─────────────────────────────────────────────────────────
        $setting = Utility::settings(\Auth::user()->creatorId());
        $emp     = Employee::find($leave->employee_id);

        if (isset($setting['twilio_leave_approve_notification']) && $setting['twilio_leave_approve_notification'] == 1) {
            if (!empty($emp->phone)) {
                Utility::send_twilio_msg($emp->phone, 'leave_approve_reject', ['leave_status' => $leave->status]);
            } else {
                return redirect()->route('leave.index')->with('error', __('Employee phone number is missing.'));
            }
        }

        // ── Email ──────────────────────────────────────────────────────────
        $setings = Utility::settings();

        if ($setings['leave_status'] == 1) {
            $employee = Employee::where('id', $leave->employee_id)
                ->where('created_by', '=', \Auth::user()->creatorId())
                ->first();

            if (!empty($employee->email)) {
                $uArr = [
                    'leave_email'       => $employee->email,
                    'leave_status_name' => $employee->name,
                    'leave_status'      => $request->status,
                    'leave_reason'      => $leave->leave_reason,
                    'leave_start_date'  => $leave->start_date,
                    'leave_end_date'    => $leave->end_date,
                    'total_leave_days'  => $leave->total_leave_days,
                ];
                $resp = Utility::sendEmailTemplate('leave_status', [$employee->email], $uArr);

                return redirect()->route('leave.index')->with(
                    'success',
                    __('Leave status successfully updated.') .
                    ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error']))
                        ? '<br><span class="text-danger">' . $resp['error'] . '</span>' : '')
                );
            } else {
                return redirect()->route('leave.index')->with('error', __('Employee email is missing.'));
            }
        }

        return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.'));
    }

    // =========================================================================
    // JSONCOUNT  (leave balance for dropdown)
    // =========================================================================

    public function jsoncount(Request $request)
    {
        $date = Utility::AnnualLeaveCycle();

        // Get leave types assigned to this specific employee (with pivot total_days)
        $employee = Employee::find($request->employee_id);
        if (!$employee) {
            return collect();
        }

        $assignedLeaveTypes = $employee->leaveTypes()->get();
        if ($assignedLeaveTypes->isEmpty()) {
            return collect();
        }

        $assignedLeaveTypeIds = $assignedLeaveTypes->pluck('id')->toArray();
        // Build maps from pivot
        $pivotDaysMap = $assignedLeaveTypes->pluck('pivot.total_days', 'id')->toArray();
        $pivotIsPaidMap = $assignedLeaveTypes->pluck('pivot.is_paid', 'id')->toArray();

        $query = LeaveType::select(
                \DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days, leave_types.id, leave_types.is_paid')
            )
            ->leftjoin('leaves', function ($join) use ($request, $date) {
                $join->on('leaves.leave_type_id', '=', 'leave_types.id');
                $join->where('leaves.employee_id', '=', $request->employee_id);
                $join->where('leaves.status', '=', 'Approved');
                $join->whereBetween('leaves.created_at', [$date['start_date'], $date['end_date']]);
            })
            ->where('leave_types.created_by', '=', \Auth::user()->creatorId())
            ->whereIn('leave_types.id', $assignedLeaveTypeIds);

        $leave_counts = $query
            ->groupBy('leave_types.id', 'leave_types.title', 'leave_types.days', 'leave_types.is_paid')
            ->get()
            ->map(function ($item) use ($pivotDaysMap, $pivotIsPaidMap) {
                // Use per-employee values from pivot (treat 0 as "use global default")
                $pivotDays = $pivotDaysMap[$item->id] ?? 0;
                $item->days = $pivotDays > 0 ? $pivotDays : $item->days;
                $item->is_paid = $pivotIsPaidMap[$item->id] ?? $item->is_paid;
                $item->remaining = max(0, $item->days - $item->total_leave);
                return $item;
            });

        return $leave_counts;
    }

    // =========================================================================
    // CALENDAR
    // =========================================================================

    public function calender(Request $request)
    {
        if (\Auth::user()->type == 'employee') {
            $user     = \Auth::user();
            $employee = Employee::where('user_id', '=', $user->id)->first();
            $leaves   = LocalLeave::where('employee_id', '=', $employee->id)->get();
        } else {
            $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
        }

        return view('leave.calender', compact('leaves'));
    }

    public function get_leave_data(Request $request)
    {
        if (\Auth::user()->type == 'employee') {
            $user     = \Auth::user();
            $employee = Employee::where('user_id', '=', $user->id)->first();
            $data     = LocalLeave::where('employee_id', '=', $employee->id)->get();
        } else {
            $data = LocalLeave::where('created_by', \Auth::user()->creatorId())->get();
        }

        $arrayJson = [];

        if ($request->get('calender_type') == 'google_calender') {
            $arrayJson = Utility::getCalendarData('leave');
        } else {
            foreach ($data as $val) {
                $endRaw   = $val->end_date instanceof Carbon ? $val->end_date->format('Y-m-d') : $val->end_date;
                $end_date = date_create($endRaw);
                date_add($end_date, date_interval_create_from_date_string('1 days'));

                $arrayJson[] = [
                    'id'        => $val->id,
                    'title'     => !empty(\Auth::user()->getLeaveType($val->leave_type_id))
                                        ? \Auth::user()->getLeaveType($val->leave_type_id)->title : '',
                    'start'     => $val->start_date instanceof Carbon ? $val->start_date->format('Y-m-d') : $val->start_date,
                    'end'       => date_format($end_date, 'Y-m-d H:i:s'),
                    'className' => $val->color ?? '',
                    'textColor' => '#FFF',
                    'allDay'    => true,
                    'url'       => route('leave.action', $val['id']),
                ];
            }
        }

        return $arrayJson;
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Parse the per-day form fields into a structured array.
     *
     * Form sends arrays keyed by date string (YYYY-MM-DD):
     *   day_duration[2026-06-10]    = full_day | half_day
     *   half_day_period[2026-06-10] = morning | afternoon
     *   day_status[2026-06-10]      = paid | unpaid
     *
     * Returns array of:
     *   ['date' => ..., 'day_duration' => ..., 'half_day_period' => ..., 'day_status' => ...]
     */
    private function buildDayDetailsFromRequest(Request $request): array
    {
        $durations   = $request->input('day_duration', []);
        $periods     = $request->input('half_day_period_day', []); // keyed by date
        $statuses    = $request->input('day_status', []);

        if (empty($durations) && empty($statuses)) {
            return [];
        }

        // Merge all date keys from both arrays
        $dates = array_unique(array_merge(array_keys($durations), array_keys($statuses)));

        $result = [];
        foreach ($dates as $date) {
            $dur    = $durations[$date]  ?? 'full_day';
            $status = $statuses[$date]   ?? 'paid';
            $period = ($dur === 'half_day') ? ($periods[$date] ?? 'morning') : null;

            if (!in_array($dur,    ['full_day', 'half_day']))     $dur    = 'full_day';
            if (!in_array($status, ['paid', 'unpaid']))            $status = 'paid';
            if ($period && !in_array($period, ['morning', 'afternoon'])) $period = 'morning';

            $result[] = [
                'date'            => $date,
                'day_duration'    => $dur,
                'half_day_period' => $period,
                'day_status'      => $status,
            ];
        }

        return $result;
    }

    /**
     * Sum total days consumed across all day-detail rows.
     * Half-day = 0.5, full-day = 1.0.
     */
    private function sumTotalDays(array $dayDetails): float
    {
        if (empty($dayDetails)) {
            return 0;
        }

        $total = 0.0;
        foreach ($dayDetails as $d) {
            $total += ($d['day_duration'] === 'half_day') ? 0.5 : 1.0;
        }
        return $total;
    }

    /**
     * Sum only paid days for quota checking.
     */
    private function sumPaidDays(array $dayDetails): float
    {
        if (empty($dayDetails)) {
            return 0;
        }

        $total = 0.0;
        foreach ($dayDetails as $d) {
            if ($d['day_status'] === 'paid') {
                $total += ($d['day_duration'] === 'half_day') ? 0.5 : 1.0;
            }
        }
        return $total;
    }

    /**
     * Insert / update leave_day_details rows.
     */
    private function persistDayDetails(int $leaveId, array $dayDetails): void
    {
        foreach ($dayDetails as $d) {
            LeaveDayDetail::updateOrCreate(
                ['leave_id' => $leaveId, 'date' => $d['date']],
                [
                    'day_duration'    => $d['day_duration'],
                    'half_day_period' => $d['half_day_period'],
                    'day_status'      => $d['day_status'],
                ]
            );
        }
    }
}
