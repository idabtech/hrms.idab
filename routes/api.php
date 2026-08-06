<?php

use App\Http\Controllers\AttendanceEmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\EmployeeRegisterController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\PaySlipController as ApiPaySlipController;
use App\Http\Controllers\Api\CareerApiController;
use App\Http\Controllers\EmployeeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('sso-login', [SsoController::class, 'ssoLogin'])->name('sso.login');
Route::post('attendance-requests', [AttendanceRequestController::class, 'store'])->middleware(['auth:sanctum'])->name('attendance-requests.store');
Route::post('attendanceemployee/attendance', [AttendanceEmployeeController::class, 'attendance'])->name('attendanceemployee.attendance')->middleware(['auth', 'XSS', 'throttle:1,1']);
Route::post('company/register', [RegisteredUserController::class, 'companyStore'])->name('company.register');
Route::post('employee/register', [RegisteredUserController::class, 'employeeStore'])->name('employee.register');

// Public Career API (No Auth Token required)
Route::get('career/job', [CareerApiController::class, 'getAllJobs'])->name('api.career.alljobs');
Route::get('career/{id}/{lang?}', [CareerApiController::class, 'getCareerJobs'])->name('api.career');

Route::post('employee/simple-register', [EmployeeRegisterController::class, 'store'])
    ->middleware(['auth:sanctum'])
    ->name('employee.simple-register');

// ── Shift routes for external product integration (Sanctum Bearer token) ──────
Route::middleware(['auth:sanctum'])->prefix('shifts')->name('shifts.')->group(function () {
    Route::post('assign',      [ShiftController::class, 'assign'])->name('assign');
    Route::post('bulk-assign', [ShiftController::class, 'bulkAssign'])->name('bulk-assign');
    Route::post('bulk-remove', [ShiftController::class, 'bulkRemove'])->name('bulk-remove');
    Route::post('copy-week',   [ShiftController::class, 'copyWeek'])->name('copy-week');
});
Route::post('getemployee', [EmployeeController::class, 'getEmployeesApi'])->middleware(['auth:sanctum'])->name('getemployee');
Route::post('/break-requests', [AttendanceEmployeeController::class, 'handleBreak'])->middleware(['auth:sanctum']);
Route::post('passcode-verification', [AttendanceEmployeeController::class, 'passcodeVerify'])->middleware(['auth:sanctum']);
// Get current break status
Route::get('/break-status', [AttendanceEmployeeController::class, 'getBreakStatus'])->middleware(['auth:sanctum']);

Route::post('/employee-report', [ReportController::class, 'employeeReport'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/report/branches',    [ReportController::class, 'branches']);
    Route::get('/report/departments', [ReportController::class, 'departments']);

    // ── Payslip APIs ──────────────────────────────────────────────────────────
    // List payslips for a month (company/HR = all employees, employee = own only)
    Route::get('/payslips',                          [ApiPaySlipController::class, 'index']);
    // Full payslip detail (earnings + deductions breakdown)
    Route::get('/payslips/{id}',                     [ApiPaySlipController::class, 'show']);
    // All payslips for a specific employee
    Route::get('/payslips/employee/{employee_id}',   [ApiPaySlipController::class, 'byEmployee']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
