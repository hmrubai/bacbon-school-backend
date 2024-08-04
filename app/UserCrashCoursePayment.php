<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCrashCoursePayment extends Model {

    protected $fillable = ["user_id","type","course_id", "course_amount","paid_amount","payment_method","transaction_id","status","transaction_status","name", "email","phone","address","currency"];

    public static $rules = [
        "user_id" => "required",
        "course_id" => "required",
        "paid_amount" => "required",
        "payment_method" => "required",
        "transaction_id" => "required"
    ];  

}
