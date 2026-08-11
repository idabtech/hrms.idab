<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrDocumentFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'created_by',
    ];

    public function parent()
    {
        return $this->belongsTo(HrDocumentFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(HrDocumentFolder::class, 'parent_id');
    }

    public function documents()
    {
        return $this->hasMany(HrDocumentLibrary::class, 'folder_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recursive path helper for breadcrumbs.
     */
    public function getAncestors()
    {
        $ancestors = collect([]);
        $current = $this->parent;
        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }
        return $ancestors;
    }
}
