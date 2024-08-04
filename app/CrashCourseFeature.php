<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrashCourseFeature extends Model
{
    protected $fillable = ['name', 'crash_course_id'];

    public function crash_course()
    {
        return $this->belongsTo('App\CrashCourse', 'crash_course_id', 'id');
    }
  
}
