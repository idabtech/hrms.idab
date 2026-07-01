<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToolkitPlan extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(ToolkitPlan::class, 'plan_id');
    }
}
