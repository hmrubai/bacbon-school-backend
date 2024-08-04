<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureViewLog extends Model
{
    protected $fillable = ['lecture_id', 'user_id'];

    public static $rules = [
        "user_id" => "required",
        "lecture_id" => "required"
    ];
}
