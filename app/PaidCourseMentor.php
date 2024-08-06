<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseMentor extends Model
{
    protected $fillable = [
        'paid_course_id',
        'user_id',
        'is_active'
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
