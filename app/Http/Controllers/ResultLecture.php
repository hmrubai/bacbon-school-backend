<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultLecture extends Model
{
    protected $fillable = ['user_id', 'lecture_exam_id', 'mark', 'total_mark'];


    public function questions () {
        return $this->hasMany('App\ResultLectureAnswer', 'result_lecture_id', 'id')
        ->join('lecture_questions', 'result_lecture_answers.question_id', 'lecture_questions.id')
        ->select('lecture_questions.*', 'result_lecture_answers.result_lecture_id',
        'result_lecture_answers.answer as given_answer',
        'result_lecture_answers.answer2 as given_answer2',
        'result_lecture_answers.answer3 as given_answer3',
        'result_lecture_answers.answer4 as given_answer4',
        'result_lecture_answers.answer5 as given_answer5',
        'result_lecture_answers.answer6 as given_answer6'
    );
    }
}
