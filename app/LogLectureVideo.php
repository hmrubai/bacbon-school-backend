<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogLectureVideo extends Model
{
    protected $fillable = ['course_id', 'subject_id', 'chapter_id', 'lecture_id', 'user_id', 'duration', 'start_position', 'end_position', 'start_time', 'end_time', 'is_skipped'];
}
