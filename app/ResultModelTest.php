<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultModelTest extends Model
{
    protected $fillable = ['user_id', 'model_test_id', 'mark', 'total_mark'];

    public function questions () {
        return $this->hasMany('App\ResultModelTestAnswer', 'result_model_test_id', 'id')
        ->join('model_test_questions', 'result_model_test_answers.question_id', 'model_test_questions.id')
        ->select('model_test_questions.*', 'result_model_test_answers.result_model_test_id',
        'result_model_test_answers.answer as given_answer',
        'result_model_test_answers.answer2 as given_answer2',
        'result_model_test_answers.answer3 as given_answer3',
        'result_model_test_answers.answer4 as given_answer4',
        'result_model_test_answers.answer5 as given_answer5',
        'result_model_test_answers.answer6 as given_answer6'
    );
    }

}
