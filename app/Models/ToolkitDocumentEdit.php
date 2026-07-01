<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolkitDocumentEdit extends Model
{
    protected $fillable = [
        'toolkit_document_id',
        'user_id',
        'edited_file_path',
        'file_type',
        'content',
    ];

    public function document()
    {
        return $this->belongsTo(ToolkitDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
