<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFCMMessage extends Model {

    protected $fillable = ["user_id", "title", "body","image","seen","navigate_to_app_location"];

    protected $table = 'user_fcm_messages';

    public static $rules = [
        "user_id" => "required",
        "title" => "required",
        "body" => "required"
    ];

    // Relationships


    protected $casts = [
        'user_id' => 'integer',
        'seen' => 'boolean'
    ];

}
