<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserVideoHistory extends Model
{

    protected $table = 'user_video_history';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function video()
    {
        return $this->belongsTo(User::class, 'video_id');
    }
}
