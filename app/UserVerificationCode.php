<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserVerificationCode extends Model
{
    protected $guarded = [];
    protected $table='user_verification_codes';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
