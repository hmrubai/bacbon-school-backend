<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppRating extends Model
{
    protected $fillable = ['user_id', 'rating', 'comments', 'status'];


}
