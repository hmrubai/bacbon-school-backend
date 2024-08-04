<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BscsExam extends Model
{
    protected $fillable = [
        'exam_name',
        'duration',
        'positive_mark',
        'negative_mark',
        'total_mark',
        'question_number',
        'status',
        'sequence',
        'appeared_from',
        'appeared_to',
        'is_active'
    ];

}
