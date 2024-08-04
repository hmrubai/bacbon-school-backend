<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentEBook extends Model
{
    protected $fillable = ["user_id", "e_book_id", "amount", "is_complete"];
}
