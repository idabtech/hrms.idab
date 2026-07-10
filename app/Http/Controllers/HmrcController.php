<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\HmrcService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
