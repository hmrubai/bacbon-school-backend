<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BmoocCorner extends Model
{
    protected $fillable = ['title', 'description', 'url', 'thumbnail', 'duration', 'status'];

    protected $casts = [
        'duration' => 'int',
    ];
}
