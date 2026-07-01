<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SalaryRevision extends Model
{
    protected $fillable = [
        'employee_id',
        'current_salary',
        'revised_salary',
        'revision_type',
        'revision_value',
        'cycle_months',
        'effective_from',
        'status',
        'note',
        'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
    ];

    // Cycle options shown in the UI
    public static array $cycleOptions = [
        1  => '1 Month',
        2  => '2 Months',
        3  => '3 Months',
        4  => '4 Months',
        5  => '5 Months',
        6  => '6 Months',
        7  => '7 Months',
        8  => '8 Months',
        9  => '9 Months',
        10 => '10 Months',
        11 => '11 Months',
        12 => '12 Months'
    ];

    public static array $revisionTypes = [
        'fixed'      => 'Fixed Amount',
        'percentage' => 'Percentage (%)',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Calculate the revised salary from a base salary.
     */
    public static function calculateRevised(float $baseSalary, string $type, float $value): float
    {
        if ($type === 'percentage') {
            return round($baseSalary + ($baseSalary * $value / 100), 2);
        }

        return round($baseSalary + $value, 2);
    }

    /**
     * Find all approved revisions whose effective_from falls within the given
     * payslip month (YYYY-MM) and apply them.
     *
     * Called from PaySlipController::store() before saving each payslip.
     */
    public static function applyDueRevisions(int $employeeId, string $salaryMonth): void
    {
        // Parse the payslip month to get start/end of that month
        $start = Carbon::createFromFormat('Y-m', $salaryMonth)->startOfMonth();
        $end   = Carbon::createFromFormat('Y-m', $salaryMonth)->endOfMonth();

        $revisions = self::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('effective_from', [$start, $end])
            ->orderBy('effective_from')
            ->get();

        foreach ($revisions as $revision) {
            $employee = Employee::find($employeeId);
            if (!$employee) {
                continue;
            }

            // Apply the new salary
            $employee->salary = $revision->revised_salary;
            $employee->save();

            // Mark this revision as applied
            $revision->status = 'applied';
            $revision->save();
        }
    }
}
