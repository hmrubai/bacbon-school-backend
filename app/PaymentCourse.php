<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentCourse extends Model
{
    protected $fillable = ['payment_id', 'user_id', 'course_id', 'amount'];
    public $timestamps = false;
}
