<?php

namespace App;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;

class admin extends Model implements JWTSubject
{
    protected $fillable = ['name', 'username', 'email', 'password', 'gender', 'role', 'address', 'role_sequence'];

    protected $hidden = [
        'remember_token',
    ];

    public function getAuthIdentifierName() {
        //
    }
    public function getAuthIdentifier() {
        //
    }
    public function getAuthPassword() {
        //
    }
    public function getRememberToken() {
        //
    }
    public function setRememberToken($value) {
        //
    }
    public function getRememberTokenName(){
        //
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    // public function setPasswordAttribute($password)
    // {
    //     if ( !empty($password) ) {
    //         $this->attributes['password'] = bcrypt($password);
    //     }
    // }

}
