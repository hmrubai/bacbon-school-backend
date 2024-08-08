<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseStudentMapping extends Model
{
    protected $fillable = [
        'paid_course_id',
        'student_id',
        'mentor_id',
        'is_active'
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
