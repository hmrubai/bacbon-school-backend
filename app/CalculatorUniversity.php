<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CalculatorUniversity extends Model
{
    protected $fillable = ["name", "name_bn", "short_name", "short_name_bn"];

    public function units () {
        return $this->hasMany('App\CalculatorUnit', 'university_id', 'id');
    }
}
