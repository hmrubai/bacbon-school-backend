<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultCrashCouresQuizAnswer extends Model
{
    protected $table ='result_crash_course_quiz_answers';
    protected $fillable = ['crash_course_quiz_question_id', 'result_crash_coures_quiz_id', 'user_id', 'answer', 'answer2', 'answer3', 'answer4', 'answer5', 'answer6'];
}
