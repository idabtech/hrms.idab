<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    protected $fillable = [
        'employee_id',
        'loan_id',
        'title',
        'type',
        'amount',
        'created_by',
    ];

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id')->first();
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public static $types = [
        'fixed'      => 'Fixed',
        'percentage' => 'Percentage',
    ];
}
