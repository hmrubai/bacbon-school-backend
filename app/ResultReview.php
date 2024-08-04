<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultReview extends Model
{
    protected $fillable = ['user_id', 'review_exam_id', 'mark', 'total_mark'];

    public function questions () {
        return $this->hasMany('App\ResultReviewAnswer', 'result_review_id', 'id')
        ->join('lecture_questions', 'result_review_answers.question_id', 'lecture_questions.id')
        ->select('lecture_questions.*', 'result_review_answers.result_review_id',
        'result_review_answers.answer as given_answer',
        'result_review_answers.answer2 as given_answer2',
        'result_review_answers.answer3 as given_answer3',
        'result_review_answers.answer4 as given_answer4',
        'result_review_answers.answer5 as given_answer5',
        'result_review_answers.answer6 as given_answer6'
    );
    }

}
