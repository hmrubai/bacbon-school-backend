<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class InstituteType extends Model {

    protected $fillable = ["type", "education_level"];

    protected $dates = [];

    public function institutes () {
        return $this->hasMany('App\Institute', 'institute_type_id', 'id');
    }
    public static $rules = [
        "type" => "required",
    ];

    // Relationships

}
