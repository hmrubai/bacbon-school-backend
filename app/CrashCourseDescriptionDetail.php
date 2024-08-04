<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrashCourseDescriptionDetail extends Model
{
    protected $fillable = ['name', 'crash_course_description_title_id'];

    public function crash_course_description_title()
    {
        return $this->belongsTo('App\CrashCourseDescriptionTitle', 'crash_course_description_title_id', 'id');
    }
  
}
