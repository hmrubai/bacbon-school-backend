<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdmissionAid extends Model
{
    protected $fillable = [
        'name',
        'course_id',
        'name_bn',
        'name_jp',
        'pdf_url',
        'image_url'
    ];
    public static $rules = [
        "name" => "required",
        "course_id" => "required",
    ];

}

