<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseSubject extends Model
{
    protected $fillable = ['paid_course_id','name','name_bn', 'url', 'course_id', 'subject_id', 'is_optional', 'is_active', 'folder_name','sort'];

    public function paid_course()
    {
        return $this->belongsTo('App\PaidCourse', 'paid_course_id', 'id');
    }

    public function paid_course_material (){
        return $this->hasMany('App\PaidCourseMaterial');
    }

    protected $casts = [
        'sort' => 'integer',
        'is_optional' => 'boolean',
        'is_active' => 'boolean'
    ];


}

