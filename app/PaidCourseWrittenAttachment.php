<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseWrittenAttachment extends Model
{
    protected $fillable = [
        'paid_course_material_id',
        'attachment_url',
        'total_marks',
        'no_of_questions',
        'is_active'
    ];
}


