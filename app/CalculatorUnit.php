<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CalculatorUnit extends Model
{
    protected $fillable = ["university_id", "name", "name_bn", "group", "group_bn", "hsc_ssc_point", "ssc_point", "hsc_point"];
}