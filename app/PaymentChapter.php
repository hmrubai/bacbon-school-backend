<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentChapter extends Model
{
    protected $fillable = ['payment_id', 'user_id', 'course_id', 'subject_id', 'chapter_id', 'amount', 'is_based'];
    public $timestamps = false;
}
