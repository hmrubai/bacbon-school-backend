<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultPaidCouresQuiz extends Model
{
    protected $table ='result_paid_coures_quizzes';
    protected $fillable = ['user_id', 'paid_course_material_id', 'mark', 'total_mark', 'written_marks', 'submission_status'];

    public function questions () {
        return $this->hasMany('App\ResultPaidCouresQuizAnswer', 'result_paid_coures_quiz_id', 'id')
        ->join('paid_course_quiz_questions', 'result_paid_coures_quiz_answers.paid_course_quiz_question_id', 'paid_course_quiz_questions.id')
        ->select('paid_course_quiz_questions.*', 'result_paid_coures_quiz_answers.result_paid_coures_quiz_id',
        'result_paid_coures_quiz_answers.answer as given_answer',
        'result_paid_coures_quiz_answers.answer2 as given_answer2',
        'result_paid_coures_quiz_answers.answer3 as given_answer3',
        'result_paid_coures_quiz_answers.answer4 as given_answer4',
        'result_paid_coures_quiz_answers.answer5 as given_answer5',
        'result_paid_coures_quiz_answers.answer6 as given_answer6'
    );
    }

}
