<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\SalaryRevision;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ApplySalaryRevisions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Run daily via cron:
     *   0 0 * * * php /path/to/artisan salary:apply-revisions
     *
     * Or run manually for a specific date:
     *   php artisan salary:apply-revisions --date=2026-06-01
     *
     * @var string
     */
    protected $signature = 'salary:apply-revisions
                            {--date= : Process revisions effective on this date (Y-m-d). Defaults to today.}
                            {--dry-run : Preview which revisions would be applied without saving.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply approved salary revisions whose effective_from date has been reached.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDate = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : Carbon::today();

        $isDryRun = (bool) $this->option('dry-run');

        $this->info(sprintf(
            '[salary:apply-revisions] Processing revisions effective on or before %s%s',
            $targetDate->toDateString(),
            $isDryRun ? ' (DRY RUN — no changes will be saved)' : ''
        ));

        // Fetch all APPROVED revisions whose effective_from is today or earlier
        $revisions = SalaryRevision::where('status', 'approved')
            ->whereDate('effective_from', '<=', $targetDate)
            ->orderBy('employee_id')
            ->orderBy('effective_from')
            ->get();

        if ($revisions->isEmpty()) {
            $this->info('No approved revisions due for application.');
            return self::SUCCESS;
        }

        $applied  = 0;
        $skipped  = 0;
        $errors   = 0;

        $settings = Utility::settings();

        foreach ($revisions as $revision) {
            $employee = Employee::find($revision->employee_id);

            if (!$employee) {
                $this->warn("  [SKIP] Revision #{$revision->id} — employee #{$revision->employee_id} not found.");
                $skipped++;
                continue;
            }

            $this->line(sprintf(
                '  [%s] Revision #%d | Employee: %s | %s → %s (effective %s)',
                $isDryRun ? 'DRY' : 'APPLY',
                $revision->id,
                $employee->name,
                number_format($revision->current_salary, 2),
                number_format($revision->revised_salary, 2),
                $revision->effective_from->toDateString()
            ));

            if (!$isDryRun) {
                try {
                    $employee->salary = $revision->revised_salary;
                    $employee->save();

                    $revision->status = 'applied';
                    $revision->save();

                    // ── Email: notify that the salary has been applied ──
                    if (!empty($settings['salary_revision_applied']) && $settings['salary_revision_applied'] == 1) {

                        $creatorUser = User::find($revision->created_by);

                        if ($creatorUser && !empty($creatorUser->email)) {

                            $uArr = [
                                'revision_employee_name'  => $employee->name,
                                'revision_current_salary' => number_format($revision->current_salary, 2),
                                'revision_revised_salary' => number_format($revision->revised_salary, 2),
                                'revision_type'           => ucfirst($revision->revision_type),
                                'revision_value'          => $revision->revision_type === 'percentage'
                                                                ? $revision->revision_value . '%'
                                                                : number_format($revision->revision_value, 2),
                                'revision_effective_from' => $revision->effective_from
                                                                ? $revision->effective_from->format('d M Y')
                                                                : '-',
                                'revision_cycle'          => $revision->cycle_months . ' Month(s)',
                                'revision_note'           => $revision->note ?? '-',
                            ];

                            try {
                                Utility::sendEmailTemplate(
                                    'salary_revision_applied',
                                    [$creatorUser->id => $creatorUser->email],
                                    $uArr
                                );
                            } catch (\Throwable $mailEx) {
                                $this->warn("  [MAIL] Revision #{$revision->id}: mail failed — " . $mailEx->getMessage());
                            }
                        }
                    }

                    $applied++;
                } catch (\Throwable $e) {
                    $this->error("  [ERROR] Revision #{$revision->id}: " . $e->getMessage());
                    $errors++;
                }
            } else {
                $applied++;
            }
        }

        $this->info(sprintf(
            'Done. Applied: %d | Skipped: %d | Errors: %d',
            $applied,
            $skipped,
            $errors
        ));

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
