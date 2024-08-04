<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAllPaymentDetails extends Model {

    protected $table='user_all_payment_details';
    protected $fillable = ["payment_id","amount"];

    public static $rules = [
        "payment_id" => "required",
        "amount" => "required"
    ];

}
