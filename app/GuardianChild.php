<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardianChild extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_accepted_by_student' => 'boolean'
    ];
}
