<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class eBookFeature extends Model
{
    protected $fillable = ['name', 'e_book_id'];

    public function e_book()
    {
        return $this->belongsTo('App\eBook', 'e_book_id', 'id');
    }
  
}
