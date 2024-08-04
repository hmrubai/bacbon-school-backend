<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $fillable = ['user_id', 'discipline', 'institution_name', 'current_class', 'exam_year', 'board'];


    public static $rules = [
        "user_id" => "required",
        "discipline" => "max:50",
        "board" => "max:50",
        "current_class" => "max:15",
        "exam_year" => "max:10",
        "institution_name" => "max:200"
    ];
}
