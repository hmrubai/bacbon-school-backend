<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentLectureScript extends Model
{
    protected $table = "payment_lecture_script";
    protected $fillable = ["user_id", "lecture_script_id", "amount", "is_complete"];
}
