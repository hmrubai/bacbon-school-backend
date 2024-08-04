<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseWrittenQuestion extends Model
{
    protected $fillable = [
        'paid_course_material_id',
        'question',
        'mark',
        'question_type',
        'is_active',
        'explanation_text'
    ];
}


