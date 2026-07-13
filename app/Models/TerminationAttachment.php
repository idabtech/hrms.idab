<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerminationAttachment extends Model
{
    protected $fillable = [
        'termination_id',
        'user_id',
        'file_name',
        'file_path',
        'sort_order',
    ];

    public function termination()
    {
        return $this->belongsTo(Termination::class);
    }
}
