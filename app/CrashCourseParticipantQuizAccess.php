<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrashCourseParticipantQuizAccess extends Model
{
    protected $fillable = ['crash_course_material_id','user_id','access_count'];


    protected $casts = [
        'crash_course_material_id' => 'integer',
        'user_id' => 'integer',
        'access_count' => 'integer'
    ];


}

