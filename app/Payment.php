<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'payment_date',
        'payment_method',
        'due',
        'discount',
        'amount_from_balance',
        'amount_to_balance'
];

protected $casts = [
    'amount' => 'float',
    'due' => 'float',
    'discount' => 'float',
    'balance' => 'float',
];
}
