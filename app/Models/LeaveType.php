<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'title',
        'days',
        'is_paid',
        'created_by',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];
}
