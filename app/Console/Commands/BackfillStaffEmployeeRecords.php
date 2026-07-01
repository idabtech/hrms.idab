<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;

class BackfillStaffEmployeeRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Run for all companies:
     *   php artisan staff:backfill-employee-records
     *
     * Preview without saving:
     *   php artisan staff:backfill-employee-records --dry-run
     *
     * Limit to a specific company user ID:
     *   php artisan staff:backfill-employee-records --company=5
     *
     * @var string
     */
    protected $signature = 'staff:backfill-employee-records
                            {--dry-run : Preview which users would be processed without saving anything.}
                            {--company= : Only process staff belonging to this company user ID.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing Employee records for existing admin staff users (hr, manager, etc.) so they can clock in/out.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun  = (bool) $this->option('dry-run');
        $companyId = $this->option('company');

        $this->info(
            '[staff:backfill-employee-records] Finding admin staff users without an Employee record'
            . ($isDryRun ? ' (DRY RUN — nothing will be saved)' : '') . '...'
        );

        // Find all non-employee, non-super-admin users that have no employee record
        $query = User::where('type', '!=', 'employee')
                     ->where('type', '!=', 'super admin')
                     ->where('type', '!=', 'company')
                     ->whereDoesntHave('employee');

        if ($companyId) {
            $query->where('created_by', (int) $companyId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('No admin staff users found without an Employee record. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$users->count()} user(s) to process.");
        $this->newLine();

        $created = 0;
        $skipped = 0;
        $errors  = 0;

        foreach ($users as $user) {
            // Double-check (race condition safety)
            if (Employee::where('user_id', $user->id)->exists()) {
                $this->line("  [SKIP] {$user->name} (ID: {$user->id}, type: {$user->type}) — already has an employee record.");
                $skipped++;
                continue;
            }

            // Generate next employee_id number within this company
            $latest           = Employee::where('created_by', $user->created_by)->latest('id')->first();
            $employeeIdNumber = $latest ? ($latest->employee_id + 1) : 1;

            // Pick first available branch/department/designation for this company
            $branch      = Branch::where('created_by', $user->created_by)->first();
            $department  = Department::where('created_by', $user->created_by)->first();
            $designation = Designation::where('created_by', $user->created_by)->first();

            $this->line(sprintf(
                '  [%s] %s (ID: %d, type: %s) → employee_id #%d | branch: %s | dept: %s | desig: %s',
                $isDryRun ? 'DRY' : 'CREATE',
                $user->name,
                $user->id,
                $user->type,
                $employeeIdNumber,
                $branch      ? $branch->name      : 'none',
                $department  ? $department->name  : 'none',
                $designation ? $designation->name : 'none'
            ));

            if (!$isDryRun) {
                try {
                    Employee::create([
                        'user_id'        => $user->id,
                        'name'           => $user->name,
                        'last_name'      => '',
                        'email'          => $user->email,
                        'employee_id'    => $employeeIdNumber,
                        'branch_id'      => $branch      ? $branch->id      : null,
                        'department_id'  => $department  ? $department->id  : null,
                        'designation_id' => $designation ? $designation->id : null,
                        'company_doj'    => date('Y-m-d'),
                        'created_by'     => $user->created_by,
                        'is_admin_staff' => 1,
                    ]);

                    $created++;
                } catch (\Throwable $e) {
                    $this->error("  [ERROR] {$user->name} (ID: {$user->id}): " . $e->getMessage());
                    $errors++;
                }
            } else {
                $created++;
            }
        }

        $this->newLine();
        $this->info(sprintf(
            'Done. %s: %d | Skipped: %d | Errors: %d',
            $isDryRun ? 'Would create' : 'Created',
            $created,
            $skipped,
            $errors
        ));

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
