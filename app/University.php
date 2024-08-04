<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = ['short_name', 'name'];

    public function students () {
        return $this->hasMany('App\User', 'university_id', 'id')->select('name', 'email', 'university_id', 'mobile_number as phone');
    }
}

