<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogLectureWatchComplete extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'subject_id',
        'chapter_id',
        'lecture_id',
        'total_duration',
        'watch_duration',
        'last_watched_log_id',
        'is_full_watched'
    ];
}
