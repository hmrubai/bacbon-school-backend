<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureExam extends Model
{
    protected $fillable = [
        'course_id',
        'subject_id',
        'chapter_id',
        'lecture_id',
        'exam_name',
        'exam_name_bn',
        'exam_name_jp',
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
    
    
    public function questionIds() {
        return $this->hasMany('App\LectureExamQuestion', 'exam_id', 'id');
    }

    public function questions () {
        return $this->hasMany('App\LectureExamQuestion', 'exam_id', 'id')
        ->join('lecture_questions', 'lecture_exam_questions.question_id', 'lecture_questions.id')
        ->select('lecture_questions.*', 'lecture_exam_questions.id as id', 'lecture_exam_questions.exam_id as exam_id');
    }
}
