<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'title',
        'amount',
        'start_date',
        'end_date',
        'description',
        'document_requested',
        'document_requested_at',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(TravelExpenseDocument::class, 'travel_expense_id', 'id');
    }

    public function bills()
    {
        return $this->hasMany(TravelExpenseDocument::class, 'travel_expense_id', 'id')->where('category', 'bill');
    }

    public function voucher_documents()
    {
        return $this->hasMany(TravelExpenseDocument::class, 'travel_expense_id', 'id')->where('category', 'document');
    }
}
