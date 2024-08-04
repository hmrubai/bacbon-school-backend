<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubjectExamQuestion extends Model
{
    protected $fillable = [
        'subject_id',
        'exam_id',
        'question_id',
        'status'
    ];
}
