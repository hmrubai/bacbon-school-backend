<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureVideoParticipant extends Model
{
    protected $table = 'lecture_video_participants';

    protected $fillable = ['user_id', 'course_id', 'subject_id', 'course_subject_id', 'total_amount',
        'paid_amount', 'is_fully_paid', 'is_trial_taken', 'is_active', 'trial_expiry_date', 'payment_status'
    ];

    protected $guarded = [];

    protected $casts = [
        'course_id' => 'int',
        'subject_id' => 'int',
        'course_subject_id' => 'int',
        'is_fully_paid' => 'boolean',
        'is_trial_taken' => 'boolean',
        'is_active' => 'boolean',
        'is_fully_paid' => 'boolean'
    ];
}
