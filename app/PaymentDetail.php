<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $fillable = [
        'user_id',
        'lecture_id',
        'payment_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'float'
    ];
}
