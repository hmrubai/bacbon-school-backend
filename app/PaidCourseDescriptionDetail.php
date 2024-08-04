<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseDescriptionDetail extends Model
{
    protected $fillable = ['name', 'paid_course_description_title_id'];

    public function paid_course_description_title()
    {
        return $this->belongsTo('App\PaidCourseDescriptionTitle', 'paid_course_description_title_id', 'id');
    }
  
}
