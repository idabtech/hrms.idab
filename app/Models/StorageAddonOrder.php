<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageAddonOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'storage_addon_id',
        'addon_title',
        'storage_amount',
        'price',
        'price_currency',
        'payment_type',
        'payment_status',
        'receipt',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function storageAddon()
    {
        return $this->hasOne(StorageAddon::class, 'id', 'storage_addon_id');
    }

    public function getFormattedStorageAttribute(): string
    {
        $mb = (float) $this->storage_amount;
        if ($mb >= 1024) {
            $gb = round($mb / 1024, 2);
            return number_format($mb) . " MB ({$gb} GB)";
        }
        return number_format($mb) . " MB";
    }

    public function getStartDateFormattedAttribute(): string
    {
        return \Auth::user() ? \Auth::user()->dateFormat($this->created_at) : date('d-m-Y', strtotime($this->created_at));
    }

    public function getExpireDateFormattedAttribute(): string
    {
        $addon = $this->storageAddon;
        if (!$addon) {
            return '-';
        }

        if ($addon->addon_type === 'plan_expiry') {
            $user = $this->user;
            if ($user && !empty($user->plan_expire_date)) {
                return \Auth::user() ? \Auth::user()->dateFormat($user->plan_expire_date) : date('d-m-Y', strtotime($user->plan_expire_date));
            }
            return __('Plan Expiry Date');
        }

        $duration = (int) ($addon->duration ?? 1);
        $durationType = $addon->duration_type ?? 'year';
        $createdAt = \Carbon\Carbon::parse($this->created_at);

        if ($durationType === 'day') {
            $expireDate = $createdAt->addDays($duration);
        } elseif ($durationType === 'month') {
            $expireDate = $createdAt->addMonths($duration);
        } elseif ($durationType === 'year') {
            $expireDate = $createdAt->addYears($duration);
        } else {
            return '-';
        }

        return \Auth::user() ? \Auth::user()->dateFormat($expireDate) : $expireDate->format('d-m-Y');
    }

    public function isExpired(): bool
    {
        if (!in_array($this->payment_status, ['succeeded', 'Approved'])) {
            return true;
        }

        $addon = $this->storageAddon;
        if (!$addon) {
            return false;
        }

        if ($addon->addon_type === 'plan_expiry') {
            $user = $this->user;
            if ($user && !empty($user->plan_expire_date) && $user->plan_expire_date !== 'lifetime') {
                return \Carbon\Carbon::parse($user->plan_expire_date)->endOfDay()->isPast();
            }
            return false;
        }

        $duration = (int) ($addon->duration ?? 1);
        $durationType = $addon->duration_type ?? 'year';
        $createdAt = \Carbon\Carbon::parse($this->created_at);

        if ($durationType === 'day') {
            $expireDate = $createdAt->addDays($duration);
        } elseif ($durationType === 'month') {
            $expireDate = $createdAt->addMonths($duration);
        } elseif ($durationType === 'year') {
            $expireDate = $createdAt->addYears($duration);
        } else {
            return false;
        }

        return $expireDate->endOfDay()->isPast();
    }
}
