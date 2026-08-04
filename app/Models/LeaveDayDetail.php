<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveDayDetail extends Model
{
    protected $table = 'leave_day_details';

    protected $fillable = [
        'leave_id',
        'date',
        'day_duration',    // full_day | half_day
        'half_day_period', // morning | afternoon (nullable, only when day_duration=half_day)
        'day_status',      // paid | unpaid
        'sandwich_leave_rule_id',
        'sandwich_deduction_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'sandwich_deduction_rate' => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }

    public function sandwichLeaveRule()
    {
        return $this->belongsTo(SandwichLeaveRule::class, 'sandwich_leave_rule_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * How many "leave days" this detail row consumes from the quota.
     * half_day = 0.5, full_day = 1.0
     */
    public function daysConsumed(): float
    {
        return $this->day_duration === 'half_day' ? 0.5 : 1.0;
    }
}
