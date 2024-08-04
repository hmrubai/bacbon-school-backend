<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseCoupon extends Model
{
    protected $fillable = [
        'coupon_code',
        'coupon_value',
        'paid_course_id',
        'limit',
        'expiry_date',
        'is_active',
        'remarks',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}


