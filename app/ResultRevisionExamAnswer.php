<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultRevisionExamAnswer extends Model
{
    protected $fillable = [
        'question_id',
        'user_id',
        'result_revision_exam_id',
        'answer',
        'answer2',
        'answer3',
        'answer4',
        'answer5',
        'answer6'
    ];
}

