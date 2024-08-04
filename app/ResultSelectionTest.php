<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultSelectionTest extends Model
{
    protected $fillable = [
        'user_id',
        'selection_test_id',
        'mark',
        'total_mark',
        'total_positive_count',
        'total_negetive_count',
        'total_positive_marks',
        'total_negetive_marks',
    ];


    // public function questions () {
    //     return $this->hasMany('App\ResultRevisionExamAnswer', 'result_revision_exam_id', 'id')
    //     ->join('revision_exam_questions', 'result_revision_exam_answers.question_id', 'revision_exam_questions.id')
    //     ->select('revision_exam_questions.*', 'result_revision_exam_answers.result_revision_exam_id',
    //      'result_revision_exam_answers.answer as given_answer',
    //      'result_revision_exam_answers.answer2 as given_answer2',
    //      'result_revision_exam_answers.answer3 as given_answer3',
    //      'result_revision_exam_answers.answer4 as given_answer4',
    //      'result_revision_exam_answers.answer5 as given_answer5',
    //      'result_revision_exam_answers.answer6 as given_answer6'
    //     );
    // }
}



