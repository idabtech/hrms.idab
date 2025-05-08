<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wegis extends Model
{
    use HasFactory;

    protected $table = 'wegis';

    
    public static $wegis_type = [
        'highly_skilled' => 'Highly Skilled',
        'skilled' => 'Skilled',
        'semi_skilled' => 'Semi Skilled',
        'un_skilled' => 'Un-Skilled'
    ];
}
