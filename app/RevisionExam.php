<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RevisionExam extends Model
{
    protected $fillable = [
        'course_id',
        'exam_name',
        'exam_name_bn',
        'duration',
        'positive_mark',
        'negative_mark',
        'total_mark',
        'question_number',
        'question_number_per_subject',
        'status',
        'week_number',
        'month_number',
        'sequence',
        'type',
        'unit',
        'appeared_from',
        'appeared_to'
    ];

    public function questions () {
        return $this->hasMany('App\ReviewExamQuestion', 'result_revision_exam_id', 'id')
        ->join('revision_exam_questions', 'result_revision_exam_answers.question_id', 'revision_exam_questions.id')
        ->select('revision_exam_questions.*', 'result_revision_exam_answers.result_revision_exam_id',
        'result_revision_exam_answers.answer as given_answer',
        'result_revision_exam_answers.answer2 as given_answer2',
        'result_revision_exam_answers.answer3 as given_answer3',
        'result_revision_exam_answers.answer4 as given_answer4',
        'result_revision_exam_answers.answer5 as given_answer5',
        'result_revision_exam_answers.answer6 as given_answer6'
    );
    }
}


