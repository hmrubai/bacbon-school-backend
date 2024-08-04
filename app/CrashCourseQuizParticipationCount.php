<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrashCourseQuizParticipationCount extends Model
{
    protected $fillable = ['crash_course_material_id','user_id','number_of_participation'];


    protected $casts = [
        'crash_course_material_id' => 'integer',
        'user_id' => 'integer',
        'number_of_participation' => 'integer'
    ];


}

