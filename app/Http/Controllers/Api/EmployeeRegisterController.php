<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeeRegisterController extends Controller
{
    /**
     * POST /api/employee/simple-register
     *
     * Minimal employee creation for external product integration.
     *
     * Expected payload:
     * {
     *   "name"     : "John Doe",         // required
     *   "email"    : "john@example.com", // required, unique in users
     *   "password" : null,               // nullable — auto-generated if omitted
     *   "role"     : "23",              // required — maps to designation_id
     *   "nickname" : "Johnny",           // accepted but ignored (no column)
     *   "color"    : "#000000"           // accepted but ignored (no column)
     * }
     *
     * Auth: Sanctum Bearer token — authenticated user is the company/creator.
     */
    public function store(Request $request): JsonResponse
    {
        // ── 1. Validate ───────────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // ── 2. Plan limit check ───────────────────────────────────────────────
        $creator        = User::find(Auth::user()->creatorId());
        $plan           = Plan::find($creator->plan);
        $totalEmployees = $creator->countEmployees();

        if ($plan && $plan->max_employees !== -1 && $totalEmployees >= $plan->max_employees) {
            return response()->json([
                'status'  => 'error',
                'message' => __('Your employee limit is over, Please upgrade plan.'),
            ], 403);
        }

        // ── 3. Resolve default language ───────────────────────────────────────
        $defaultLanguage = DB::table('settings')
            ->where('name', 'default_language')
            ->where('created_by', Auth::user()->creatorId())
            ->value('value')
            ?? DB::table('settings')
                ->where('name', 'default_language')
                ->value('value')
            ?? 'en';

        // ── 4. Create user + employee in a transaction ────────────────────────
        DB::beginTransaction();

        try {
            $plainPassword = $request->password ?? Str::random(10);
            $passcode      = random_int(100000, 999999);

            // 4a. User account
            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'password'          => Hash::make($plainPassword),
                'type'              => 'employee',
                'lang'              => $defaultLanguage,
                'created_by'        => Auth::user()->creatorId(),
                'email_verified_at' => now(),
                'passcode'          => $passcode,
            ]);

            $user->assignRole('employee');

            // 4b. Auto-increment employee number
            $latest     = Employee::where('created_by', Auth::user()->creatorId())
                                  ->latest('id')
                                  ->first();
            $employeeNo = $latest ? ($latest->employee_id + 1) : 1;

            // 4c. Employee record — only the fields we have; rest are nullable
            $employee = Employee::create([
                'user_id'        => $user->id,
                'name'           => $request->name,
                'email'          => $request->email,
                'password'       => Hash::make($plainPassword),
                'employee_id'    => $employeeNo,
                'designation_id' => $request->role,
                'created_by'     => Auth::user()->creatorId(),
            ]);

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => __('Failed to create employee. Please try again.'),
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        // ── 5. Optional welcome email (non-fatal) ─────────────────────────────
        try {
            $settings = Utility::settings(Auth::user()->creatorId());
            if (!empty($settings['new_employee']) && $settings['new_employee'] == 1) {
                Utility::sendEmailTemplate('new_employee', [$user->id => $user->email], [
                    'employee_email'       => $user->email,
                    'employee_password'    => $plainPassword,
                    'employee_name'        => $user->name,
                    'employee_passcode'    => $user->passcode,
                    'employee_branch'      => '',
                    'employee_department'  => '',
                    'employee_designation' => '',
                ]);
            }
        } catch (\Throwable) {
            // never block the response on email failure
        }

        // ── 6. Response ───────────────────────────────────────────────────────
        return response()->json([
            'status'  => 'success',
            'message' => __('Employee registered successfully.'),
            'data'    => [
                'user_id'     => $user->id,
                'employee_id' => $employee->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $request->role,
                'passcode'    => $user->passcode,
            ],
        ], 201);
    }
}
