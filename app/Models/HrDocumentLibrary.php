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
        'description',
        'file_name',
        'file_path',
        'content',
        'created_by',
    ];

    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
}
