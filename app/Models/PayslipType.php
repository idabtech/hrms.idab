<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayslipType extends Model
{
    protected $fillable = [
        'name',
        'salary_basis',  // 'monthly' | 'hourly'
        'created_by',
    ];

    /**
     * Whether this type represents an hourly-paid employee.
     */
    public function isHourly(): bool
    {
        return $this->salary_basis === 'hourly';
    }

    /**
     * Human-readable basis label.
     */
    public function basisLabel(): string
    {
        return $this->salary_basis === 'hourly' ? 'Hourly' : 'Monthly';
    }
}
