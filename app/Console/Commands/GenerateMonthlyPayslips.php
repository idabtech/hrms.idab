<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\PaySlip;
use App\Models\Resignation;
use App\Models\SalaryRevision;
use App\Models\Termination;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyPayslips extends Command
{
    /**
     * Generate payslips for the previous month automatically.
     *
     * Intended to run on the 6th or 7th of each month via scheduler.
     * Example: Running on 06-Aug-2026 generates payslips for July 2026.
     *
     * Manual usage:
     *   php artisan payslip:generate-monthly
     *   php artisan payslip:generate-monthly --month=07 --year=2026
     *   php artisan payslip:generate-monthly --dry-run
     */
    protected $signature = 'payslip:generate-monthly
                            {--month= : Month to generate payslips for (01-12). Defaults to previous month.}
                            {--year= : Year to generate payslips for. Defaults to previous month year.}
                            {--dry-run : Preview which payslips would be generated without saving.}';

    protected $description = 'Auto-generate monthly payslips for all employees (previous month by default).';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        // Determine target month
        if ($this->option('month') && $this->option('year')) {
            $month = str_pad($this->option('month'), 2, '0', STR_PAD_LEFT);
            $year  = $this->option('year');
        } else {
            // Default: previous month
            $previousMonth = Carbon::now()->subMonth();
            $month = $previousMonth->format('m');
            $year  = $previousMonth->format('Y');
        }

        $formateMonthYear = $year . '-' . $month;
        $monthEndDate = date($year . '-' . $month . '-t');

        $this->info(sprintf(
            '[payslip:generate-monthly] Generating payslips for %s%s',
            $formateMonthYear,
            $isDryRun ? ' (DRY RUN)' : ''
        ));

        Log::info("[payslip:generate-monthly] Started for {$formateMonthYear}");

        // Get all company users (each company has its own set of employees)
        $companies = User::where('type', 'company')->get();

        $totalGenerated = 0;
        $totalSkipped   = 0;
        $totalErrors    = 0;

        foreach ($companies as $company) {
            $this->line("  Processing company: {$company->name} (ID: {$company->id})");

            // Find employees who already have payslips for this month
            $existingPayslipEmployeeIds = PaySlip::where('salary_month', $formateMonthYear)
                ->where('created_by', $company->id)
                ->pluck('employee_id')
                ->toArray();

            // Get eligible employees
            $employees = Employee::where('created_by', $company->id)
                ->where('company_doj', '<=', $monthEndDate)
                ->whereNotIn('id', $existingPayslipEmployeeIds)
                ->get();

            if ($employees->isEmpty()) {
                $this->line("    No new employees to process.");
                continue;
            }

            foreach ($employees as $employee) {
                // Skip terminated employees
                $isTerminated = Termination::where('employee_id', $employee->id)
                    ->whereDate('termination_date', '<=', Carbon::create($year, $month)->endOfMonth())
                    ->exists();

                // Skip resigned employees
                $isResigned = Resignation::where('employee_id', $employee->id)
                    ->whereDate('resignation_date', '<=', Carbon::create($year, $month)->endOfMonth())
                    ->exists();

                // Skip employees with zero salary
                $hasNoSalary = $employee->salary <= 0;

                if ($isTerminated || $isResigned || $hasNoSalary) {
                    $totalSkipped++;
                    continue;
                }

                if ($isDryRun) {
                    $this->line("    [DRY] Would generate for: {$employee->name}");
                    $totalGenerated++;
                    continue;
                }

                try {
                    // Apply any pending salary revisions first
                    SalaryRevision::applyDueRevisions($employee->id, $formateMonthYear);
                    $employee = Employee::find($employee->id); // refresh

                    // Create payslip record
                    $payslip = new PaySlip();
                    $payslip->employee_id          = $employee->id;
                    $payslip->salary_month         = $formateMonthYear;
                    $payslip->status               = 0;
                    $payslip->basic_salary         = $employee->salary ?? 0;
                    $payslip->net_salary           = ($employee->salary ?? 0) / 2;
                    $payslip->allowance            = Employee::allowance($employee->id);
                    $payslip->commission           = Employee::commission($employee->id);
                    $payslip->loan                 = Employee::loan($employee->id);
                    $payslip->saturation_deduction = Employee::saturation_deduction($employee->id);
                    $payslip->other_payment        = Employee::other_payment($employee->id);
                    $payslip->overtime             = Employee::overtime($employee->id);
                    $payslip->created_by           = $company->id;
                    $payslip->net_payble           = 0;
                    $payslip->save();

                    // Calculate accurate net salary
                    $detail = Utility::employeePayslipDetail($employee->id, $formateMonthYear);
                    $payslip->net_payble = round($detail['net_salary'], 2);
                    $payslip->save();

                    $totalGenerated++;

                    // Send notifications
                    $setting = Utility::settings($company->id);
                    if (!empty($setting['monthly_payslip_notification']) && $setting['monthly_payslip_notification'] == 1) {
                        $uArr = ['year' => $formateMonthYear];
                        Utility::send_slack_msg('new_monthly_payslip', $uArr);
                    }
                    if (!empty($setting['telegram_monthly_payslip_notification']) && $setting['telegram_monthly_payslip_notification'] == 1) {
                        $uArr = ['year' => $formateMonthYear];
                        Utility::send_telegram_msg('new_monthly_payslip', $uArr);
                    }

                } catch (\Throwable $e) {
                    $totalErrors++;
                    $this->error("    [ERROR] {$employee->name}: " . $e->getMessage());
                    Log::error("[payslip:generate-monthly] Error for employee {$employee->id}: " . $e->getMessage());
                }
            }
        }

        $this->info(sprintf(
            'Done. Generated: %d | Skipped: %d | Errors: %d',
            $totalGenerated, $totalSkipped, $totalErrors
        ));

        Log::info("[payslip:generate-monthly] Completed. Generated: {$totalGenerated}, Skipped: {$totalSkipped}, Errors: {$totalErrors}");

        return $totalErrors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
