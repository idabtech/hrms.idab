<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarningAttachment extends Model
{
    protected $fillable = [
        'warning_id',
        'user_id',
        'file_name',
        'file_path',
        'sort_order',
    ];

    public function warning()
    {
        return $this->belongsTo(Warning::class);
    }
}
