<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'company_start_time',
        'company_end_time',
        'employee_id',
        'date',
        'type',
        'notes',
        'is_deleted',
        'created_by',
    ];

    protected $casts = [
        'date'       => 'date:Y-m-d',
        'is_deleted' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /**
     * Named shift templates: no employee_id, no date.
     * Used in the Shift management page and as quick-fill options.
     */
    public function scopeTemplates($query)
    {
        return $query->whereNull('employee_id')->whereNull('date');
    }

    /**
     * Employee default shifts: employee_id set, date NULL.
     * Shown on every day in the rota unless overridden or deleted for that date.
     */
    public function scopeEmployeeDefaults($query)
    {
        return $query->whereNotNull('employee_id')->whereNull('date');
    }

    /**
     * Rota date overrides: employee_id + specific date.
     * Includes both active overrides (is_deleted=false) and
     * deletion markers (is_deleted=true).
     */
    public function scopeRotaEntries($query)
    {
        return $query->whereNotNull('employee_id')->whereNotNull('date');
    }

    /**
     * Active rota overrides only (not deleted).
     */
    public function scopeActiveRotaEntries($query)
    {
        return $query->whereNotNull('employee_id')
                     ->whereNotNull('date')
                     ->where('is_deleted', false);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeBetweenDates($query, string $start, string $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }
}
