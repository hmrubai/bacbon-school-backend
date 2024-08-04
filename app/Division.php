<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    // protected $table = 'divisions';
    protected $fillable = ['name', 'name_bn', 'name_jp'];


    public static $rules = [
        "name" => "required|max:150",
    ];

    // Relationships
    public function districts () {
        return $this->hasMany('App\District', 'division_id', 'id');
    }
}
