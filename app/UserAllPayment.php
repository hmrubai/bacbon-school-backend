<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAllPayment extends Model {

    protected $fillable = ["user_id","name","email","phone","address","currency","item_id","item_name","item_type","payable_amount","coupon_id","paid_amount","discount","card_type","transaction_id","transaction_status","status","payment_status","paid_from"];

    public static $rules = [
        "user_id" => "required",
        "item_id" => "required",
        "payable_amount" => "required",
        "paid_amount" => "required",
        "transaction_status" => "transaction_status",
        "transaction_id" => "required"
    ];

}
