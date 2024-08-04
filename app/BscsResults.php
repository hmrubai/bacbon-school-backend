<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BscsResults extends Model
{
    protected $fillable = [
        'user_id',
        'bscs_exam_id',
        'mark',
        'total_mark'
    ];

    public function questions () {
        return $this->hasMany('App\BscsAnswer', 'bscs_result_id', 'id')
        ->join('bscs_exam_questions', 'bscs_exam_answers.question_id', 'bscs_exam_questions.id')
        ->select('bscs_exam_questions.*', 'bscs_exam_answers.bscs_exam_id',
        'bscs_exam_answers.answer as given_answer',
        'bscs_exam_answers.answer2 as given_answe2',
        'bscs_exam_answers.answer3 as given_answe3',
        'bscs_exam_answers.answer4 as given_answe4',
        'bscs_exam_answers.answer5 as given_answe5',
        'bscs_exam_answers.answer6 as given_answe6'
    );
    }
}
