<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewExam extends Model
{
    protected $fillable = ['course_id', 'subject_id', 'user_id', 'exam_name', 'exam_name_bn', 'duration', 'positive_mark', 'negative_mark', 'total_mark', 'question_number', 'status'];


    protected $casts = [
        'duration' => 'int',
        'question_number' => 'int',
        'positive_mark' => 'double',
        'negative_mark' => 'double',
        'total_mark' => 'double',
    ];
    
}


