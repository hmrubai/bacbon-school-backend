<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class eBookDescriptionTitle extends Model
{
    protected $table = 'e_book_description_titles';

    protected $fillable = ['name', 'e_book_id'];

    public function e_book()
    {
        return $this->belongsTo('App\eBook', 'e_book_id', 'id');
    }

    public function e_book_description_detial (){
        return $this->hasMany('App\eBookDescriptionDetail');
    }
  
}
