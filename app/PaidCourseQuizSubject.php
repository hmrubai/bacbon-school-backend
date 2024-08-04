<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseQuizSubject extends Model
{
    protected $fillable = ['paid_course_material_id','name','number_of_questions', 'is_active', 'is_optional', 'optional_subject_id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_optional' => 'boolean'
    ];
}

