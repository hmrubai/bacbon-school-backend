<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['course_type_id', 'name', 'name_bn', 'status', 'code', 'price', 'sequence'];

    public function course_subjects()
    {
        return $this->hasMany('App\CourseSubject');
    }

    public function subjects()
    {
        return $this->hasMany('App\CourseSubject')
        ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
        ->select('course_subjects.course_id as course_id','course_subjects.id as id', 'course_subjects.code as code',
         'subjects.id as subject_id', 'subjects.name as name',  'subjects.name_bn as name_bn',  'subjects.name_jp as name_jp', 'subjects.color_name as color_name', 'subjects.icon' )
         ->orderBy('course_subjects.sequence', 'asc');
    }

    public function nested_subjects()
    {
        return $this->hasMany('App\CourseSubject')
        ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
        ->select('course_subjects.id as id', 'course_subjects.course_id as course_id', 'subjects.id as main_subject_id', 'subjects.name as name')
         ->orderBy('course_subjects.sequence', 'asc');
    }

    public function chapters()
    {
        return $this->hasMany('App\Chapter');
    }
    
    public function eBooks () {
        return $this->hasMany('App\eBook', 'course_id', 'id');
    }

}
