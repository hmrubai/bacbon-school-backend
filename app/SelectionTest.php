<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SelectionTest extends Model
{
    protected $fillable = [ 
        'course_id', 
        'exam_name', 
        'exam_name_bn', 
        'duration', 
        'positive_mark', 
        'negative_mark', 
        'total_mark', 
        'question_number', 
        'status',
        'appeared_from',
        'appeared_to'
    ];
}
