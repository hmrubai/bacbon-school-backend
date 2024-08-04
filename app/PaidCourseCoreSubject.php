<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseCoreSubject extends Model
{
    protected $fillable = ['name','optional_subject_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

