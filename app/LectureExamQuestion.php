<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureExamQuestion extends Model
{
    protected $fillable = [
        'subject_id',
        'chapter_id',
        'lecture_id',
        'exam_id',
        'question_id',
        'status'
    ];
}
