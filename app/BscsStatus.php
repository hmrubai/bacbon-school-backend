<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BscsStatus extends Model
{
    protected $fillable = ["is_active"];
    protected $casts = [
        'is_active' => 'boolean'
    ];
}
