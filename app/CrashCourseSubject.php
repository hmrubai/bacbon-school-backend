<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrashCourseSubject extends Model
{
    protected $fillable = ['crash_course_id','name','name_bn','folder_name','sort'];

    public function crash_course()
    {
        return $this->belongsTo('App\CrashCourse', 'crash_course_id', 'id');
    }

    public function crash_course_material (){
        return $this->hasMany('App\CrashCourseMaterial');
    }

    protected $casts = [
        'sort' => 'integer'
    ];


}

