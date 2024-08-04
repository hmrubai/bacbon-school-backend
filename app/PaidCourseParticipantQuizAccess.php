<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseParticipantQuizAccess extends Model
{
    protected $fillable = ['paid_course_material_id','user_id','access_count'];

    protected $casts = [
        'paid_course_material_id' => 'integer',
        'user_id' => 'integer',
        'access_count' => 'integer'
    ];
}

