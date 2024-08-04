<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseDescriptionTitle extends Model
{
    protected $fillable = ['name', 'paid_course_id'];

    public function paid_course()
    {
        return $this->belongsTo('App\PaidCourse', 'paid_course_id', 'id');
    }

    public function paid_course_description_detial (){
        return $this->hasMany('App\PaidCourseDescriptionDetail');
    }
  
}
