<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseApplyCoupon extends Model
{
    protected $fillable = [
        'user_id',
        'paid_course_id',
        'coupon_id',
        'applied_from',
        'applied_status',
    ];
}


