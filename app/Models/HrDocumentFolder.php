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

    /**
     * Get IDs of all descendant folders recursively (including self).
     */
    public function getAllSubfolderIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllSubfolderIds());
        }
        return array_unique($ids);
    }

    /**
     * Total recursive files count inside this folder and all its subfolders.
     */
    public function getTotalFilesCountAttribute(): int
    {
        $folderIds = $this->getAllSubfolderIds();
        $query = HrDocumentLibrary::whereIn('folder_id', $folderIds);

        $user = \Auth::user();
        if ($user && $user->type !== 'super admin' && !$user->isSuperAdminSideUser()) {
            $superAdmin = User::where('type', 'super admin')->first();
            $superAdminId = $superAdmin ? $superAdmin->id : 1;
            $allowedIds = array_values(array_unique([
                $superAdminId,
                $user->creatorId(),
                $user->id,
            ]));
            $query->whereIn('created_by', $allowedIds);
        }

        return $query->count();
    }
}
