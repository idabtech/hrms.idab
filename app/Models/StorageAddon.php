<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'price',
        'storage_amount',
        'addon_type',
        'duration_value',
        'duration_type',
        'description',
        'is_active',
        'created_by',
    ];

    public static $addonTypes = [
        'plan_expiry' => 'Co-terminus with Active Plan Expiry',
        'fixed_duration' => 'Fixed Duration (Validity Period)'
    ];

    public static $durationTypes = [
        'year' => 'Year(s)',
        'month' => 'Month(s)',
        'day' => 'Day(s)'
    ];

    /**
     * Get formatted storage string (e.g. "5000 MB (4.88 GB)").
     */
    public function getFormattedStorageAttribute(): string
    {
        $mb = (float) $this->storage_amount;
        if ($mb >= 1024) {
            $gb = round($mb / 1024, 2);
            return number_format($mb) . " MB ({$gb} GB)";
        }
        return number_format($mb) . " MB";
    }

    /**
     * Get formatted duration string.
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->addon_type === 'plan_expiry') {
            return __('Plan Expiry Date');
        }

        $val = $this->duration_value ?? 1;
        $unit = ucfirst($this->duration_type ?? 'year');
        return "{$val} {$unit}" . ($val > 1 ? 's' : '');
    }
}
