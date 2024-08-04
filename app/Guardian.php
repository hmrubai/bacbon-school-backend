<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{    protected $fillable = [
            'name', 'user_code','email', 'password', 'mobile_number','address', 'image', 'gender', 'email_verified_at', 'status', 'refference_id', 'is_bangladeshi'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'email_verified_at'
    ];

    protected $casts = [
        'is_bangladeshi' => 'boolean'
    ];
    public function children () {
        return $this->hasMany('App\GuardianChild', 'guardian_id', 'id')
        ->join('users', 'guardian_children.user_id', 'users.id')
        ->select('guardian_children.*', 'users.name', 'users.mobile_number', 'users.user_code', 'users.image');
    }
}
