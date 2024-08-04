<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{
    protected $fillable = ['user_id', 'reset_code', 'reset_till'];
}
