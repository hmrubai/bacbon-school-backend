<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCode extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
