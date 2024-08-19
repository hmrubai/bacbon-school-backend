<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentJoinHistory extends Model
{
    protected $fillable = [
        'paid_course_class_schedule_id',
        'student_id',
        'join_time',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
