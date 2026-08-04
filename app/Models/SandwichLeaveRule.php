<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SandwichLeaveRule extends Model
{
    use HasFactory;

    protected $table = 'sandwich_leave_rules';

    protected $fillable = [
        'name',
        'deduction_rate',
        'rate_type',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'deduction_rate' => 'float',
        'is_active' => 'boolean',
    ];
}
