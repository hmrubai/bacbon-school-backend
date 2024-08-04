<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultPaidCourseQuizSubjectWiseAnswer extends Model
{
    protected $table ='result_paid_course_quiz_subject_wise_answers';
    protected $fillable = [
        'paid_course_material_subject_id', 
        'user_id', 
        'result_paid_coures_quiz_id', 
        'paid_coures_quiz_material_id', 
        'positive_count', 
        'negetive_count'
    ];
}
