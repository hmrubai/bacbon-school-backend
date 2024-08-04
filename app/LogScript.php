<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogScript extends Model
{
    protected $fillable = ['course_id', 'subject_id', 'chapter_id', 'lecture_id', 'script_id', 'user_id', 'start_time', 'end_time'];
}
