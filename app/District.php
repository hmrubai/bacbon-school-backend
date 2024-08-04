<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['division_id', 'name', 'name_bn', 'name_jp'];

    public $timestamps = false;
    public static $rules = [
        "division_id" => "required",
        "name" => "required|max:150",
    ];

    public static $updateRules = [
        "id" => "required",
        "division_id" => "required",
        "name" => "required|max:150",
    ];

        // Relationships
        public function thanas () {
            return $this->hasMany('App\Thana', 'district_id', 'id');
        }
}
