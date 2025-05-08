<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubDepartment extends Model
{
 
    protected $table = 'sub_departments';
    // protected $fillable = [
    //     'name',
    //     'created_by',
    // ];

    // public function department(){
    //     return $this->hasOne('App\Models\Department','id','department_id');
    // }
}
