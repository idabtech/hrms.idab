<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEmployee extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'late',
        'early_leaving',
        'overtime',
        'total_rest',
        'breaks',
        'total_break',
        'total_lunch_time',
        'total_tea_time',
        'company_shift_time',
        'passcode',
        'created_by',
        'is_manual_by'
    ];

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'user_id', 'employee_id');
    }

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    public function isManualBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'is_manual_by');
    }
}
