<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultPaidCouresQuizAnswer extends Model
{
    protected $table ='result_paid_course_quiz_answers';
    protected $fillable = ['paid_course_quiz_question_id', 'result_paid_coures_quiz_id', 'paid_course_material_subject_id', 'user_id', 'answer', 'answer2', 'answer3', 'answer4', 'answer5', 'answer6'];
}
