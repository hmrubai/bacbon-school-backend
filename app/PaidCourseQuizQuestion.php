<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseQuizQuestion extends Model
{
    protected $fillable = [
        'paid_course_material_id',
        'paid_course_material_subject_id',
        'question_set_id',
        'question',
        'question_image',
        'option1',
        'option1_image',
        'option2',
        'option2_image',
        'option3',
        'option3_image',
        'option4',
        'option4_image',
        'option5',
        'option5_image',
        'option6',
        'option6_image',
        'correct_answer',
        'correct_answer2',
        'correct_answer3',
        'correct_answer4',
        'correct_answer5',
        'correct_answer6',
        'explanation',
        'explanation_text',
        'status'
    ];
}


