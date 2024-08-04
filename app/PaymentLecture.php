<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentLecture extends Model
{
    protected $fillable = ['payment_id', 'user_id', 'course_id', 'subject_id', 'lecture_id', 'chapter_id', 'is_based', 'amount'];
    public $timestamps = false;
}
