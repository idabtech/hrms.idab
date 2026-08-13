<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SystemModule extends Model
{
    use HasFactory;

    protected $table = 'system_modules';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'module_type',
        'available_for',
        'parent_id',
        'sort_order',
        'status',
        'is_always_included',
        'is_auto_included',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_always_included' => 'boolean',
        'is_auto_included' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(SystemModule::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(SystemModule::class, 'parent_id')->orderBy('sort_order', 'asc');
    }

    public function getPermissionsCountAttribute(): int
    {
        return \Spatie\Permission\Models\Permission::where('name', 'like', '%' . $this->name)->count();
    }
}
