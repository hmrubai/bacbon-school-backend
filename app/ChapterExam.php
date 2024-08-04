<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChapterExam extends Model
{
    protected $fillable = [
    'course_id',
    'subject_id',
    'chapter_id',
    'exam_name',
    'duration',
    'positive_mark',
    'negative_mark',
    'total_mark',
    'question_number',
    'sequence',
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
    return $this->hasMany('App\ChapterExamQuestion', 'exam_id', 'id')
    ->join('chapter_questions', 'chapter_exam_questions.question_id', 'chapter_questions.id')
    ->select('chapter_questions.*', 'chapter_exam_questions.id as id', 'chapter_exam_questions.exam_id as exam_id');
}


public function questionIds () {
    return $this->hasMany('App\ChapterExamQuestion', 'exam_id', 'id');
}



}
