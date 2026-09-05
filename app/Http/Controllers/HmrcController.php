<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\HmrcService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HmrcController extends Controller
{
    /**
     * Verify a single NI number (AJAX call from employee form or list).
     * Company/HR can verify any employee's NI number.
     * No redirect required — uses Client Credentials grant.
     *
     * POST /hmrc/verify-nino
     */
    public function verifyNino(Request $request)
    {
        if (!in_array(Auth::user()->type, ['company', 'hr', 'super admin'])) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $request->validate([
            'nino' => 'required|string|max:20',
        ]);

        if (!HmrcService::isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => __('HMRC integration is not enabled. Please contact super admin.'),
            ], 400);
        }

        $hmrc   = new HmrcService();
        $result = $hmrc->verifyNino($request->nino);

        return response()->json($result);
    }

    /**
     * Verify an employee's stored NI number by employee ID.
     * Company/HR can click a "Verify NI" button next to the employee.
     *
     * POST /hmrc/verify-employee-nino/{employeeId}
     */
    public function verifyEmployeeNino($employeeId)
    {
        if (!in_array(Auth::user()->type, ['company', 'hr', 'super admin'])) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $employee = Employee::where('id', $employeeId)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => __('Employee not found.')], 404);
        }

        if (empty($employee->ni_number)) {
            return response()->json([
                'success' => false,
                'message' => __('No NI number stored for this employee.'),
            ], 400);
        }

        if (!HmrcService::isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => __('HMRC integration is not enabled. Please contact super admin.'),
            ], 400);
        }

        $hmrc   = new HmrcService();
        $result = $hmrc->verifyNino($employee->ni_number);

        // Add employee info to response
        $result['employee_name'] = $employee->name;
        $result['employee_id']   = $employee->employee_id;

        return response()->json($result);
    }

    /**
     * Test HMRC API connection (super admin / company).
     * Quick check that credentials are working.
     *
     * GET /hmrc/test-connection
     */
    public function testConnection()
    {
        if (!in_array(Auth::user()->type, ['company', 'hr', 'super admin'])) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        if (!HmrcService::isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => __('HMRC integration is not enabled. Please contact super admin.'),
            ], 400);
        }

        $hmrc   = new HmrcService();
        $result = $hmrc->testConnection();

        return response()->json($result);
    }

    /**
     * Bulk verify all UK employees' NI numbers for a company.
     * Returns a summary of valid/invalid NI formats.
     *
     * GET /hmrc/bulk-verify-nino
     */
    public function bulkVerifyNino()
    {
        if (!in_array(Auth::user()->type, ['company', 'hr', 'super admin'])) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        if (!HmrcService::isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => __('HMRC integration is not enabled. Please contact super admin.'),
            ], 400);
        }

        // Get all employees with NI numbers for this company
        $employees = Employee::where('created_by', Auth::user()->creatorId())
            ->whereNotNull('ni_number')
            ->where('ni_number', '!=', '')
            ->get(['id', 'employee_id', 'name', 'ni_number']);

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('No employees with NI numbers found.'),
            ], 400);
        }

        $results = [];
        $validCount   = 0;
        $invalidCount = 0;

        foreach ($employees as $emp) {
            $validation = HmrcService::validateNinoFormat($emp->ni_number);

            $results[] = [
                'employee_id'   => $emp->employee_id,
                'name'          => $emp->name,
                'ni_number'     => $emp->ni_number,
                'format_valid'  => $validation['valid'],
                'formatted'     => $validation['formatted'] ?? null,
                'message'       => $validation['message'],
            ];

            if ($validation['valid']) {
                $validCount++;
            } else {
                $invalidCount++;
            }
        }

        // Also test HMRC connectivity once
        $hmrc = new HmrcService();
        $connectionTest = $hmrc->testConnection();

        return response()->json([
            'success'        => true,
            'total'          => count($results),
            'valid_count'    => $validCount,
            'invalid_count'  => $invalidCount,
            'hmrc_connected' => $connectionTest['success'],
            'hmrc_message'   => $connectionTest['message'],
            'employees'      => $results,
        ]);
    }

    /**
     * Manually submit FPS for a specific employee/month.
     * Company/HR can trigger this if automatic submission failed.
     *
     * POST /hmrc/submit-fps/{employeeId}/{salaryMonth}
     */
    public function submitFps($employeeId, $salaryMonth)
    {
        Log::info('===========================================================');
        Log::info('🚀 [HMRC FPS SUBMISSION STARTED]', [
            'triggered_by_user_id'   => Auth::id(),
            'triggered_by_user_name' => Auth::user()->name ?? 'N/A',
            'user_type'              => Auth::user()->type ?? 'unknown',
            'employee_id'            => $employeeId,
            'salary_month'           => $salaryMonth,
            'timestamp'              => now()->toDateTimeString(),
        ]);

        if (!in_array(Auth::user()->type, ['company', 'hr', 'super admin'])) {
            Log::warning('⚠️ [HMRC FPS SUBMISSION] Permission denied for user: ' . Auth::id());
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        if (!\App\Services\HmrcRtiService::isConfigured()) {
            $companySettings = \App\Models\Utility::settings();
            Log::error('❌ [HMRC FPS SUBMISSION FAILED] HMRC RTI is not fully configured.', [
                'hmrc_enabled'            => \App\Services\HmrcService::isEnabled(),
                'hmrc_employer_paye_ref'   => $companySettings['hmrc_employer_paye_ref'] ?? 'NOT SET',
                'hmrc_accounts_office_ref'=> $companySettings['hmrc_accounts_office_ref'] ?? 'NOT SET',
            ]);
            return response()->json([
                'success' => false,
                'message' => __('HMRC RTI is not fully configured. Ensure employer PAYE reference and Accounts Office reference are set in HMRC Settings.'),
            ], 400);
        }

        // Verify employee belongs to this company
        $employee = Employee::where('id', $employeeId)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$employee) {
            Log::error('❌ [HMRC FPS SUBMISSION FAILED] Employee not found or does not belong to creator ID: ' . Auth::user()->creatorId(), [
                'requested_employee_id' => $employeeId,
            ]);
            return response()->json(['success' => false, 'message' => __('Employee not found.')], 404);
        }

        Log::info('👤 [HMRC FPS SUBMISSION] Employee Found', [
            'employee_id'   => $employee->id,
            'employee_name' => $employee->name . ' ' . ($employee->last_name ?? ''),
            'ni_number'     => $employee->ni_number ?? 'MISSING',
            'email'         => $employee->email,
        ]);

        $rtiService = new \App\Services\HmrcRtiService();
        $result = $rtiService->submitFps((int)$employeeId, $salaryMonth);

        Log::info('🏁 [HMRC FPS SUBMISSION RESULT]', $result);
        Log::info('===========================================================');

        return response()->json($result);
    }

    /**
     * Get FPS submission status/history for an employee.
     *
     * GET /hmrc/fps-status/{employeeId}/{salaryMonth?}
     */
    public function fpsStatus($employeeId, $salaryMonth = null)
    {
        if (!in_array(Auth::user()->type, ['company', 'hr', 'super admin'])) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        // Verify employee belongs to this company
        $employee = Employee::where('id', $employeeId)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$employee) {
            return response()->json(['success' => false, 'message' => __('Employee not found.')], 404);
        }

        $submissions = \App\Services\HmrcRtiService::getSubmissions((int)$employeeId, $salaryMonth);

        return response()->json([
            'success'     => true,
            'employee'    => $employee->name,
            'submissions' => $submissions,
        ]);
    }

    /**
     * Return HMRC Submission Detail Modal View.
     *
     * GET /hmrc/submission-detail/{employeeId}/{salaryMonth}
     */
    public function submissionDetailModal($employeeId, $salaryMonth)
    {
        if (!in_array(Auth::user()->type, ['company', 'hr', 'super admin', 'HR'])) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $employee = Employee::where('id', $employeeId)
            ->where('created_by', Auth::user()->creatorId())
            ->first();

        if (!$employee) {
            return response()->json(['error' => __('Employee not found.')], 404);
        }

        $submission = DB::table('hmrc_rti_submissions')
            ->where('employee_id', $employeeId)
            ->where('salary_month', $salaryMonth)
            ->latest()
            ->first();

        return view('payslip.hmrc_submission_modal', compact('employee', 'submission', 'salaryMonth'));
    }

    /**
     * Bulk submit FPS to HMRC for selected employees or all paid employees for a salary month.
     *
     * POST /hmrc/bulk-submit-fps
     */
    public function bulkSubmitFps(Request $request)
    {
        if (!in_array(Auth::user()->type, ['company', 'hr', 'super admin', 'HR'])) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        if (!\App\Services\HmrcRtiService::isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => __('HMRC RTI is not fully configured. Ensure employer PAYE reference and Accounts Office reference are set in HMRC Settings.'),
            ], 400);
        }

        $month = $request->input('month');
        $employeeIds = $request->input('employee_ids', []);

        if (empty($month)) {
            return response()->json(['success' => false, 'message' => __('Salary month is required.')], 400);
        }

        // Get paid payslips for this month for the creator
        $query = DB::table('pay_slips')
            ->join('employees', 'pay_slips.employee_id', '=', 'employees.id')
            ->where('pay_slips.created_by', Auth::user()->creatorId())
            ->where('pay_slips.salary_month', $month)
            ->where('pay_slips.status', '1');

        if (!empty($employeeIds) && is_array($employeeIds)) {
            $query->where(function($q) use ($employeeIds) {
                $q->whereIn('pay_slips.employee_id', $employeeIds)
                  ->orWhereIn('pay_slips.id', $employeeIds);
            });
        }

        $payslips = $query->select('pay_slips.employee_id', 'employees.name')->get();

        if ($payslips->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('No eligible paid payslips found for submission in ') . $month . '.',
            ], 400);
        }

        $rtiService = new \App\Services\HmrcRtiService();
        $successCount = 0;
        $alreadySubmittedCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($payslips as $ps) {
            // Check if already submitted successfully
            $existing = DB::table('hmrc_rti_submissions')
                ->where('employee_id', $ps->employee_id)
                ->where('salary_month', $month)
                ->whereIn('status', ['submitted', 'ACCEPTED', 'ACCEPTED_SANDBOX'])
                ->first();

            if ($existing) {
                $alreadySubmittedCount++;
                continue;
            }

            try {
                $result = $rtiService->submitFps((int)$ps->employee_id, $month);
                if (!empty($result['success'])) {
                    $successCount++;
                } else {
                    $failedCount++;
                    $errors[] = $ps->name . ': ' . ($result['message'] ?? __('Submission failed'));
                }
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = $ps->name . ': ' . $e->getMessage();
                Log::error('❌ [HMRC BULK FPS EXCEPTION]', [
                    'employee_id' => $ps->employee_id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        $msg = sprintf(
            __('HMRC Bulk FPS Submission Completed: %d submitted successfully, %d already submitted, %d failed.'),
            $successCount,
            $alreadySubmittedCount,
            $failedCount
        );

        return response()->json([
            'success'                 => ($failedCount === 0 || $successCount > 0 || $alreadySubmittedCount > 0),
            'message'                 => $msg,
            'success_count'           => $successCount,
            'already_submitted_count' => $alreadySubmittedCount,
            'failed_count'            => $failedCount,
            'errors'                  => $errors,
        ]);
    }
}
