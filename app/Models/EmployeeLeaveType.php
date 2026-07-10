<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveType extends Model
{
    protected $table = 'employee_leave_types';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'total_days',
        'is_paid',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
