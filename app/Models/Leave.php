<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'sandwich_leave_rule_id',
        'sandwich_deduction_rate',
        'applied_on',
        'start_date',
        'end_date',
        'total_leave_days',
        'leave_duration',    // full_day | half_day  (leave-level — used for single-day leaves)
        'half_day_period',   // morning | afternoon  (leave-level — used for single-day half-day)
        'leave_reason',
        'remark',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'applied_on' => 'date',
        'sandwich_deduction_rate' => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function leaveType()
    {
        return $this->hasOne('App\Models\LeaveType', 'id', 'leave_type_id');
    }

    public function sandwichLeaveRule()
    {
        return $this->belongsTo('App\Models\SandwichLeaveRule', 'sandwich_leave_rule_id');
    }

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    /**
     * Per-day breakdown rows (populated for multi-day leaves).
     */
    public function dayDetails()
    {
        return $this->hasMany(LeaveDayDetail::class)->orderBy('date');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * True when this is a single-day half-day leave (no day_details rows).
     */
    public function isHalfDay(): bool
    {
        return $this->leave_duration === 'half_day';
    }

    /**
     * Total days consumed from the quota.
     *
     * - Single half-day     : 0.5
     * - Multi-day with breakdown : sum of daysConsumed() for paid rows only
     * - Legacy / no breakdown   : total_leave_days as stored
     */
    public function paidDaysCount(): float
    {
        if ($this->dayDetails->isEmpty()) {
            return (float) $this->total_leave_days;
        }

        return $this->dayDetails
            ->where('day_status', 'paid')
            ->sum(fn($d) => $d->daysConsumed());
    }

    /**
     * Unpaid days (informational, does not affect quota).
     */
    public function unpaidDaysCount(): float
    {
        return $this->dayDetails
            ->where('day_status', 'unpaid')
            ->sum(fn($d) => $d->daysConsumed());
    }

    /**
     * Total days consumed (paid + unpaid), respecting half-day fractions.
     */
    public function totalDaysConsumed(): float
    {
        if ($this->dayDetails->isEmpty()) {
            return (float) $this->total_leave_days;
        }

        return $this->dayDetails->sum(fn($d) => $d->daysConsumed());
    }
}
