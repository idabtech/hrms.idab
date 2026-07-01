<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolkitPlan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'monthly_document_limit',
        'can_edit_documents',
        'can_download_documents'
    ];

    public function userPlans()
    {
        return $this->hasMany(UserToolkitPlan::class);
    }
}
