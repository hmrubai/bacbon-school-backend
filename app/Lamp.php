<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lamp extends Model
{
    protected $fillable = [
        'user_id',
        'age',
        'passport',
        'organization',
        'reason',
        'background',
        'contributionProcess',
        'remark'
    ];
}
