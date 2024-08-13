<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MentorZoomLink extends Model
{
    protected $fillable = [
        'mentor_id',
        'live_link',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
