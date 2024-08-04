<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultCrashCouresQuiz extends Model
{
    protected $table ='result_crash_coures_quizzes';
    protected $fillable = ['user_id', 'crash_course_material_id', 'mark', 'total_mark'];

    public function questions () {
        return $this->hasMany('App\ResultCrashCouresQuizAnswer', 'result_crash_coures_quiz_id', 'id')
        ->join('crash_course_quiz_questions', 'result_crash_coures_quiz_answers.crash_course_quiz_question_id', 'crash_course_quiz_questions.id')
        ->select('crash_course_quiz_questions.*', 'result_crash_coures_quiz_answers.result_crash_coures_quiz_id',
        'result_crash_coures_quiz_answers.answer as given_answer',
        'result_crash_coures_quiz_answers.answer2 as given_answer2',
        'result_crash_coures_quiz_answers.answer3 as given_answer3',
        'result_crash_coures_quiz_answers.answer4 as given_answer4',
        'result_crash_coures_quiz_answers.answer5 as given_answer5',
        'result_crash_coures_quiz_answers.answer6 as given_answer6'
    );
    }

}
