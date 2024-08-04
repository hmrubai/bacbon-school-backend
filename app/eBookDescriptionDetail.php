<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class eBookDescriptionDetail extends Model
{
    protected $table = 'e_book_description_details';

    protected $fillable = ['name', 'e_book_description_title_id'];

    public function e_book_description_title()
    {
        return $this->belongsTo('App\eBookDescriptionTitle', 'e_book_description_title_id', 'id');
    }
  
}
