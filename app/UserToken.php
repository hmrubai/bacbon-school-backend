<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    // protected $table = ''
    protected $fillable = ['user_id', 'token', 'created_at'];
    public $timestamps = false;

}
