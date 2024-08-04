<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseTrailFeature extends Model
{
    protected $fillable = ['name', 'paid_course_id'];

    public function paid_course()
    {
        return $this->belongsTo('App\PaidCourse', 'paid_course_id', 'id');
    }
  
}
