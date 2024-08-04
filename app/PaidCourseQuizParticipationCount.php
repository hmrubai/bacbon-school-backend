<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseQuizParticipationCount extends Model
{
    protected $fillable = ['paid_course_material_id','user_id','number_of_participation'];


    protected $casts = [
        'paid_course_material_id' => 'integer',
        'user_id' => 'integer',
        'number_of_participation' => 'integer'
    ];


}

