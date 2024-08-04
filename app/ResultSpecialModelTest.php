<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultSpecialModelTest extends Model
{
    protected $fillable = ['user_id','is_last_exam','can_retake', 'special_model_test_id', 'mark', 'total_mark'];

    public function questions () {
        return $this->hasMany('App\ResultSpecialModelTestAnswer', 'result_special_model_test_id', 'id')
        ->join('special_model_test_questions', 'result_special_model_test_answers.question_id', 'special_model_test_questions.id')
        ->select('special_model_test_questions.*', 'result_special_model_test_answers.result_special_model_test_id',
        'result_special_model_test_answers.answer as given_answer',
        'result_special_model_test_answers.answer2 as given_answer2',
        'result_special_model_test_answers.answer3 as given_answer3',
        'result_special_model_test_answers.answer4 as given_answer4',
        'result_special_model_test_answers.answer5 as given_answer5',
        'result_special_model_test_answers.answer6 as given_answer6'
    );
    }

}
