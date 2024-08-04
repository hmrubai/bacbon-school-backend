<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentSubject extends Model
{
    protected $fillable = ['payment_id', 'user_id', 'course_id', 'subject_id', 'amount', 'is_based'];
    public $timestamps = false;
}
