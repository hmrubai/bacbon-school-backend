<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureRating extends Model
{
    protected $fillable = ['lecture_id', 'user_id', 'rating', 'comment', 'status'];

}
