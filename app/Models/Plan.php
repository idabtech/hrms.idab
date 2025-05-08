<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'discount',
        'discount_price',
        'per_user_or_company',
        'set_currency',
        'pms',
        'duration',
        'max_users',
        'max_employees',
        'max_venders',
        'description',
        'image',
    ];

    public static $arrDuration = [
        'Unlimited' => 'Unlimited',
        'month' => 'Per Month',
        'year' => 'Per Year',
    ];

    public static $arrPerUserOrCompany = [
        'User' => 'Per User',
        'Company' => 'Per Company',
    ];

    public static $arrCurrency = [
        '1' => 'INR',
        '2' => 'GBP',
        '3' => 'USD',
    ];

    public static $arrPms = [
        'With PMS' => 'With PMS',
        'Without PMS' => 'Without PMS',
    ];

    public function status()
    {
        return [
            __('Unlimited'),
            __('Per Month'),
            __('Per Year'),
        ];
    }

    public static function total_plan()
    {
        return Plan::count();
    }
        
    public static function most_purchese_plan()
    {
        $free_plan = Plan::where('price', '<=', 0)->first()->id;

        return User:: select('plans.name','plans.id', DB::raw('count(*) as total'))
                   ->join('plans', 'plans.id' ,'=', 'users.plan')
                   ->where('type', '=', 'company')
                   ->where('plan', '!=', $free_plan)
                   ->orderBy('total','Desc')
                   ->groupBy('plans.name','plans.id')
                   ->first();
    }
}
