<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureQuestion extends Model
{
    protected $fillable = [
        'subject_id',
        'chapter_id',
        'lecture_id',
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
protected $casts = [
    'subject_id' => 'int',
    'chapter_id' => 'int',
    'lecture_id' => 'int',
    'correct_answer' => 'integer',
    'correct_answer2' => 'integer',
    'correct_answer3' => 'integer',
    'correct_answer4' => 'integer',
    'correct_answer5' => 'integer',
    'correct_answer6' => 'integer'
];
}
