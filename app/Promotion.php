<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model {

    protected $fillable = ["title","award","award_bn","participant_count","participant_count_bn", "promo_image_url","navigate_to_web_url","navigate_to_app_location","data", "should_cache","is_active","type"];



    public static $rules = [
        "title" => "required",
        "promo_image_url" => "required"
    ];

    public $timestamps = false;

    // Relationships

    protected $casts = [
        'should_cache' => 'boolean',
        'is_active' => 'boolean'
    ];
}
