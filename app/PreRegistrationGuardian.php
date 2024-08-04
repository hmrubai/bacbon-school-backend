<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PreRegistrationGuardian extends Model
{
    protected $fillable =      [    'name',
                                    'mobile_number',
                                    'email',
                                    'otp',
                                    'otp_expired_at',
                                    'referred_code'
                                ];


}
