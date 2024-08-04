<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class eBook extends Model
{
    protected $fillable = ["course_id", "name", "name_bn", "gp_product_id", "description", "e_book_url", "e_book_url_aws", "thumbnails","regular_price", "price", "is_premium","number_of_puchased","promo_status","is_active","sort", "price_text" ];

    protected $casts = [
        'price' => 'double',
        'is_premium' => 'boolean'
    ];

    public function e_book_feature (){
        return $this->hasMany('App\eBookFeature');
    }

    public function e_book_description_title (){
        return $this->hasMany('App\eBookDescriptionTitle');
    }

}

