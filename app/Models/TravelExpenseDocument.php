<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelExpenseDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_expense_id',
        'category',
        'file_name',
        'file_path',
    ];

    public function travelExpense()
    {
        return $this->belongsTo(TravelExpense::class, 'travel_expense_id', 'id');
    }
}
