<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseType extends Model
{
    protected $fillable = ['name', 'name_bn', 'name_jp', 'status', 'sequence'];

    public function courses() {
        return $this->hasMany('App\Course', 'course_type_id', 'id')->select('id', 'course_type_id', 'name', 'name_bn', 'name_jp', 'status')->where('isSubCourse', false)->where('status', 'Active');
    }

    public function scholarships() {
        return $this->hasMany('App\Course', 'course_type_id', 'id')->select('id', 'course_type_id', 'name', 'name_bn', 'name_jp', 'status');
    }
}
