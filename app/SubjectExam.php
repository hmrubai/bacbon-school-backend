<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubjectExam extends Model
{
    protected $fillable = [
        'course_id',
        'subject_id',
        'exam_name',
        'duration',
        'positive_mark',
        'negative_mark',
        'total_mark',
        'question_number',
        'status'
    ];

    protected $casts = [
        'duration' => 'int',
        'question_number' => 'int',
        'positive_mark' => 'double',
        'negative_mark' => 'double',
        'total_mark' => 'double',
    ];
    public function questions () {
        return $this->hasMany('App\SubjectExamQuestion', 'exam_id', 'id')
        ->join('subject_questions', 'subject_exam_questions.question_id', 'subject_questions.id')
        ->select('subject_questions.*', 'subject_exam_questions.id as id', 'subject_exam_questions.exam_id as exam_id');
    }
}
