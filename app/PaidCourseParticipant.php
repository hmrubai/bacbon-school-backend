<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseParticipant extends Model
{
    protected $fillable = ['paid_course_id','user_id','course_amount','paid_amount','is_fully_paid','is_trial_taken', 'is_lc_activated', 'trial_expiry_date','is_active'];


    protected $casts = [
        'paid_course_id' => 'integer',
        'user_id' => 'integer',
        'course_amount' => 'float',
        'paid_amount' => 'float',
        'is_fully_paid' => 'boolean',
        'is_active' => 'boolean',
        'is_trial_taken' => 'boolean',
        'is_lc_activated' => 'boolean'
    ];


}

