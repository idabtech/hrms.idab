<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_id',
        'requested_by',
        'request_reason',
        'required_docs',
        'status',
        'file_paths',
        'file_names',
    ];

    protected $casts = [
        'file_paths' => 'array',
        'file_names' => 'array',
    ];

    public function leave()
    {
        return $this->belongsTo(Leave::class, 'leave_id', 'id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by', 'id');
    }
}
