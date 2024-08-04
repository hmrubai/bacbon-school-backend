<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureSheet extends Model
{
    protected $fillable = ["name", "name_bn","description", "url", "url_aws", "thumbnails","number_of_puchased","regular_price", "price","number_of_puchased", "promo_status","is_active","sort", "price_text" ];

    protected $casts = [
        'price' => 'double',
        'regular_price' => 'double',
        'is_active' => 'boolean'
    ];

    public function lecture_sheet_feature (){
        return $this->hasMany('App\LectureSheetFeature');
    }

    public function lecture_sheet_description_title (){
        return $this->hasMany('App\LectureSheetDescriptionTitle');
    }

}

