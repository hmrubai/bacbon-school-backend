<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Institute extends Model {

    protected $fillable = ["institute_type_id", "name", "name_bn", "logo", "keywords"];

    protected $dates = [];

    public static $rules = [
        "name" => "required",
    ];

    // Relationships

}

