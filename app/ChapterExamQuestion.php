<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChapterExamQuestion extends Model
{
    protected $fillable = [
        'subject_id',
        'chapter_id',
        'exam_id',
        'question_id',
        'status'
    ];
}
