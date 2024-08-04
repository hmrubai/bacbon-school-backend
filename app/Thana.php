<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Thana extends Model {

    protected $fillable = ['district_id', 'name', 'name_bn', 'name_jp'];

    protected $dates = [];

    public static $rules = [
        "name" => "required|max:50"
    ];

    // Relationships

}
