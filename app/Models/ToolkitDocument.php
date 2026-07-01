<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolkitDocument extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'file_path',
        'file_type',
        'is_editable'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function edits()
    {
        return $this->hasMany(ToolkitDocumentEdit::class);
    }
}
