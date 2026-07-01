<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // SHARED HELPER
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve an Employee from an email address, scoped to the authenticated
     * company. Returns the Employee model or a JSON error response.
     *
     * @return Employee|JsonResponse
     */
    private function resolveEmployeeByEmail(string $email): Employee|JsonResponse
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => "No user found with email: {$email}",
            ], 404);
        }

        $employee = Employee::where('user_id', $user->id)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (! $employee) {
            return response()->json([
                'status'  => 'error',
                'message' => "Employee with email {$email} not found under your company account.",
            ], 404);
        }

        return $employee;
    }

    /**
     * Normalise a time value to H:i:s.
     * Accepts H:i (e.g. "09:00") or H:i:s (e.g. "09:00:00").
     * Returns null if the input is empty.
     */
    private function normaliseTime(?string $time): ?string
    {
        if (empty($time)) return null;
        // Already H:i:s
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) return $time;
        // H:i → append :00
        if (preg_match('/^\d{2}:\d{2}$/', $time)) return $time . ':00';
        return $time;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/shifts/assign
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create or update a single shift for one employee (looked up by email).
     *
     * {
     *   "email"      : "john@example.com",  // required
     *   "start_time" : "09:00:00",          // required  H:i:s
     *   "end_time"   : "17:00:00",          // required  H:i:s
     *   "date"       : "2026-05-21",        // required unless is_default=true
     *   "name"       : "Morning Shift",     // optional
     *   "type"       : "regular",           // optional
     *   "notes"      : "...",               // optional
     *   "is_default" : false                // optional — true = employee default (no date)
     * }
     */
    public function assign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'      => 'required|email',
            'start_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time'   => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'date'       => 'nullable|date_format:Y-m-d',
            'name'       => 'nullable|string|max:100',
            'type'       => 'nullable|in:regular,overtime,holiday,night,weekend,emergency,training,leave',
            'notes'      => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $result = $this->resolveEmployeeByEmail($request->email);
        if ($result instanceof JsonResponse) return $result;
        $employee   = $result;
        $targetUser = User::find($employee->user_id);

        $isDefault = $request->boolean('is_default', false);

        $payload = [
            'name'               => $request->name ?? null,
            'company_start_time' => $this->normaliseTime($request->start_time),
            'company_end_time'   => $this->normaliseTime($request->end_time),
            'type'               => $request->type ?? 'regular',
            'notes'              => $request->notes ?? null,
            'is_deleted'         => false,
            'created_by'         => Auth::user()->creatorId(),
        ];

        if ($isDefault) {
            $shift = Shift::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => null],
                $payload
            );
        } else {
            if (! $request->filled('date')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'The date field is required when is_default is false.',
                ], 422);
            }
            $shift = Shift::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $request->date],
                $payload
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Shift saved successfully.',
            'data'    => [
                'shift_id'    => $shift->id,
                'employee_id' => $employee->id,
                'user_id'     => $targetUser->id,
                'email'       => $targetUser->email,
                'name'        => $shift->name,
                'start_time'  => $shift->company_start_time,
                'end_time'    => $shift->company_end_time,
                'type'        => $shift->type,
                'date'        => $shift->date ? $shift->date->format('Y-m-d') : null,
                'is_default'  => is_null($shift->date),
                'notes'       => $shift->notes,
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/shifts/bulk-assign
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Assign a shift template to multiple employees across a date range.
     * Employees are identified by email instead of internal employee_id.
     *
     * {
     *   "emails"       : ["a@x.com", "b@x.com"],  // required
     *   "start_time"   : "09:00:00",               // required  — shift start
     *   "end_time"     : "17:00:00",               // required  — shift end
     *   "shift_name"   : "Morning",                // optional
     *   "start_date"   : "2026-06-01",             // required  — range start
     *   "end_date"     : "2026-06-07",             // required  — range end
     *   "weekdays"     : [1,2,3,4,5],              // optional  0=Sun…6=Sat, default all
     *   "type"         : "regular",                // optional
     *   "overwrite"    : false                     // optional  — overwrite existing shifts
     * }
     */
    public function bulkAssign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'emails'       => 'required|array|min:1',
            'emails.*'     => 'required|email',
            'start_time'   => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time'     => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'shift_name'   => 'nullable|string|max:100',
            'start_date'   => 'required|date_format:Y-m-d',
            'end_date'     => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'weekdays'     => 'nullable|array',
            'weekdays.*'   => 'integer|between:0,6',
            'type'         => 'nullable|in:regular,overtime,holiday,night,weekend,emergency,training,leave',
            'overwrite'    => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $creatorId = Auth::user()->creatorId();

        // ── Resolve all employees from emails ─────────────────────────────────
        $employees   = collect();
        $notFound    = [];

        foreach ($request->emails as $email) {
            $user = User::where('email', $email)->first();
            if (! $user) { $notFound[] = $email; continue; }

            $emp = Employee::where('user_id', $user->id)
                ->where('created_by', $creatorId)
                ->first();

            if (! $emp) { $notFound[] = $email; continue; }

            $employees->push($emp);
        }

        if ($employees->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No valid employees found for the provided emails.',
                'not_found' => $notFound,
            ], 422);
        }

        // ── Assign across date range ──────────────────────────────────────────
        $weekdays  = $request->weekdays ?? [0, 1, 2, 3, 4, 5, 6];
        $overwrite = $request->boolean('overwrite', false);
        $period    = CarbonPeriod::create($request->start_date, $request->end_date);
        $created   = 0;
        $skipped   = 0;

        $shiftPayload = [
            'name'               => $request->shift_name ?? null,
            'company_start_time' => $this->normaliseTime($request->start_time),
            'company_end_time'   => $this->normaliseTime($request->end_time),
            'type'               => $request->type ?? 'regular',
            'is_deleted'         => false,
            'created_by'         => $creatorId,
        ];

        foreach ($period as $day) {
            if (! in_array($day->dayOfWeek, $weekdays)) continue;
            $dateStr = $day->format('Y-m-d');

            foreach ($employees as $emp) {
                $existing = Shift::rotaEntries()
                    ->where('employee_id', $emp->id)
                    ->where('date', $dateStr)
                    ->first();

                // Active override exists and overwrite is off → skip
                if ($existing && ! $existing->is_deleted && ! $overwrite) {
                    $skipped++;
                    continue;
                }

                Shift::updateOrCreate(
                    ['employee_id' => $emp->id, 'date' => $dateStr],
                    $shiftPayload
                );
                $created++;
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "{$created} shifts assigned" . ($skipped ? ", {$skipped} skipped (already assigned)" : '') . '.',
            'data'    => [
                'assigned'  => $created,
                'skipped'   => $skipped,
                'not_found' => $notFound,   // emails that couldn't be resolved
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/shifts/bulk-remove
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Remove shifts for multiple employees across a date range.
     * Employees are identified by email.
     *
     * {
     *   "emails"     : ["a@x.com", "b@x.com"],  // required
     *   "start_date" : "2026-06-01",             // required
     *   "end_date"   : "2026-06-07",             // required
     *   "weekdays"   : [1,2,3,4,5]               // optional  0=Sun…6=Sat, default all
     * }
     *
     * Smart logic (mirrors the HRMS rota UI):
     *   - Active date override → mark is_deleted = true
     *   - No override but employee has a default shift → insert deletion marker
     *   - No shift at all → skip
     */
    public function bulkRemove(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'emails'     => 'required|array|min:1',
            'emails.*'   => 'required|email',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'weekdays'   => 'nullable|array',
            'weekdays.*' => 'integer|between:0,6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $creatorId = Auth::user()->creatorId();

        // ── Resolve employees ─────────────────────────────────────────────────
        $employees = collect();
        $notFound  = [];

        foreach ($request->emails as $email) {
            $user = User::where('email', $email)->first();
            if (! $user) { $notFound[] = $email; continue; }

            $emp = Employee::with('shift')
                ->where('user_id', $user->id)
                ->where('created_by', $creatorId)
                ->first();

            if (! $emp) { $notFound[] = $email; continue; }

            $employees->push($emp);
        }

        if ($employees->isEmpty()) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'No valid employees found for the provided emails.',
                'not_found' => $notFound,
            ], 422);
        }

        $employeeIds = $employees->pluck('id')->toArray();
        $weekdays    = $request->weekdays ?? [0, 1, 2, 3, 4, 5, 6];
        $period      = CarbonPeriod::create($request->start_date, $request->end_date);

        // Pre-load default shifts and existing overrides for the range
        $defaultShifts = Shift::employeeDefaults()
            ->whereIn('employee_id', $employeeIds)
            ->where('created_by', $creatorId)
            ->get()
            ->keyBy('employee_id');

        $existingOverrides = Shift::rotaEntries()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->where('created_by', $creatorId)
            ->get();

        $removed = 0;
        $skipped = 0;

        foreach ($period as $day) {
            if (! in_array($day->dayOfWeek, $weekdays)) continue;
            $dateStr = $day->format('Y-m-d');

            foreach ($employees as $emp) {
                $override = $existingOverrides->first(fn($r) =>
                    $r->employee_id === $emp->id &&
                    $r->date->format('Y-m-d') === $dateStr
                );

                if ($override) {
                    if ($override->is_deleted) { $skipped++; continue; }
                    $override->update(['is_deleted' => true]);
                    $removed++;
                    continue;
                }

                // No override — check for a default shift to suppress
                $hasDefault = isset($defaultShifts[$emp->id]) || $emp->shift;
                if (! $hasDefault) { $skipped++; continue; }

                $defaultShift = $defaultShifts[$emp->id] ?? $emp->shift;

                Shift::create([
                    'employee_id'        => $emp->id,
                    'date'               => $dateStr,
                    'name'               => null,
                    'company_start_time' => $defaultShift->company_start_time,
                    'company_end_time'   => $defaultShift->company_end_time,
                    'type'               => 'regular',
                    'is_deleted'         => true,
                    'created_by'         => $creatorId,
                ]);
                $removed++;
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "{$removed} shifts removed" . ($skipped ? ", {$skipped} skipped" : '') . '.',
            'data'    => [
                'removed'   => $removed,
                'skipped'   => $skipped,
                'not_found' => $notFound,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/shifts/copy-week
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Copy shifts from a source period to a target period for one employee.
     * Employee is identified by email.
     *
     * {
     *   "email"     : "john@example.com",  // required
     *   "src_start" : "2026-05-19",        // required — source range start
     *   "src_end"   : "2026-05-25",        // required — source range end
     *   "tgt_start" : "2026-05-26",        // required — target range start
     *   "tgt_end"   : "2026-06-01"         // required — target range end
     * }
     *
     * Logic (mirrors the HRMS rota UI):
     *   - If explicit date overrides exist in source → copy them with date offset
     *   - If no overrides → fall back to employee default shift and stamp each
     *     day in the target range
     *   - Existing active shifts in target are skipped (not overwritten)
     */
    public function copyWeek(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'src_start' => 'required|date_format:Y-m-d',
            'src_end'   => 'required|date_format:Y-m-d|after_or_equal:src_start',
            'tgt_start' => 'required|date_format:Y-m-d',
            'tgt_end'   => 'required|date_format:Y-m-d|after_or_equal:tgt_start',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $result = $this->resolveEmployeeByEmail($request->email);
        if ($result instanceof JsonResponse) return $result;
        $employee  = $result;
        $creatorId = Auth::user()->creatorId();

        // ── Source: try explicit overrides first ──────────────────────────────
        $sourceEntries = Shift::activeRotaEntries()
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$request->src_start, $request->src_end])
            ->get();

        // ── Fallback: employee default or company-assigned shift ──────────────
        $defaultShift = null;
        if ($sourceEntries->isEmpty()) {
            $defaultShift = Shift::employeeDefaults()
                ->where('employee_id', $employee->id)
                ->where('created_by', $creatorId)
                ->first()
                ?? $employee->shift;

            if (! $defaultShift) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No shifts found in the source period to copy.',
                ], 422);
            }
        }

        $offset  = Carbon::parse($request->src_start)->diffInDays(Carbon::parse($request->tgt_start), false);
        $tgtEnd  = Carbon::parse($request->tgt_end);
        $created = 0;
        $skipped = 0;

        if ($defaultShift) {
            // Stamp the default shift onto every day in the target range
            foreach (CarbonPeriod::create($request->tgt_start, $request->tgt_end) as $day) {
                $dateStr = $day->format('Y-m-d');

                if (Shift::activeRotaEntries()->where('employee_id', $employee->id)->where('date', $dateStr)->exists()) {
                    $skipped++;
                    continue;
                }

                Shift::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $dateStr],
                    [
                        'name'               => $defaultShift->name,
                        'company_start_time' => $defaultShift->company_start_time,
                        'company_end_time'   => $defaultShift->company_end_time,
                        'type'               => $defaultShift->type ?? 'regular',
                        'notes'              => $defaultShift->notes,
                        'is_deleted'         => false,
                        'created_by'         => $creatorId,
                    ]
                );
                $created++;
            }
        } else {
            // Copy explicit overrides with date offset
            foreach ($sourceEntries as $src) {
                $newDate = Carbon::parse($src->date->format('Y-m-d'))->addDays($offset);
                if ($newDate->gt($tgtEnd)) continue;

                if (Shift::activeRotaEntries()->where('employee_id', $employee->id)->where('date', $newDate->format('Y-m-d'))->exists()) {
                    $skipped++;
                    continue;
                }

                Shift::create([
                    'employee_id'        => $employee->id,
                    'date'               => $newDate->format('Y-m-d'),
                    'name'               => $src->name,
                    'company_start_time' => $src->company_start_time,
                    'company_end_time'   => $src->company_end_time,
                    'type'               => $src->type,
                    'notes'              => $src->notes,
                    'is_deleted'         => false,
                    'created_by'         => $creatorId,
                ]);
                $created++;
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "{$created} shifts copied" . ($skipped ? ", {$skipped} skipped (already exist)" : '') . '.',
            'data'    => [
                'copied'  => $created,
                'skipped' => $skipped,
                'email'   => $request->email,
            ],
        ]);
    }
}
