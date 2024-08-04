<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewExamQuestion extends Model
{
    protected $table = "revision_exam_questions";
    protected $fillable = [
        'revision_exam_id',
        'subject_id',
        'question',
        'option1',
        'option2',
        'option3',
        'option4',
        'option5',
        'option6',
        'correct_answer',
        'correct_answer2',
        'correct_answer3',
        'correct_answer4',
        'correct_answer5',
        'correct_answer6',
        'explanation',
        'explanation_text',
        'status'
    ];

}
