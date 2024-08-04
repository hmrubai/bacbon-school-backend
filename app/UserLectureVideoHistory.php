<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLectureVideoHistory extends Model
{
    protected $table = 'user_lecture_video_history';
    protected $fillable = ['user_id', 'lecture_video_id'];

    protected $casts = [
        'user_id' => 'int',
        'lecture_video_id' => 'int',
    ];
}
