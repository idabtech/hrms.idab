<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrDocumentLibrary extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'folder_id',
        'description',
        'file_name',
        'file_path',
        'content',
        'created_by',
    ];

    public function folder()
    {
        return $this->belongsTo(HrDocumentFolder::class, 'folder_id');
    }

    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
}
