<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject

{
    use Notifiable;

   protected $fillable = [
        'name', 'user_code','email', 'password','institute','mobile_number','address','current_course_id', 'image', 'gender', 'points','fcm_id', 'refference_id', 'university_id', 'isLampFormSubmitted', 'isBangladeshi', 'is_applied_scholarship',
        'lamp_aplication_date', 'isCompleteRegistration', 'isSetPassword', 'is_staff', 'is_e_edu_3', 'is_e_edu_4', 'is_e_edu_c_unit', 'is_e_edu_5', 'is_chandpur', 'e_edu_id',
        'b_unit_start_date','c_unit_start_date', 'd_unit_start_date', 'c_unit_optional_subject_id','division_id','district_id','thana_id','device_id','is_c_unit_purchased','is_b_unit_purchased','is_d_unit_purchased','is_bae_3','is_bae_4','is_jicf_teacher','is_e_edu_admission_2022',
        'user_type'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'current_course_id' => 'int',
        'isSetPassword' => 'boolean',
        'is_staff' => 'boolean',
        'is_e_edu_3' => 'boolean',
        'is_e_edu_4' => 'boolean',
        'is_e_edu_5' => 'boolean',
        'is_bae_3' => 'boolean',
        'is_bae_4' => 'boolean',
        'is_chandpur' => 'boolean',
        'is_c_unit_purchased' => 'boolean',
        'is_b_unit_purchased' => 'boolean',
        'is_d_unit_purchased' => 'boolean',
        'is_e_edu_c_unit' => 'boolean',
        'is_jicf_teacher' => 'boolean',
        'is_e_edu_admission_2022' => 'boolean'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function userLog() {
        return $this->hasMany('App\LogLectureVideo', 'user_id', 'id');
    }


    public function quizResults () {
        return $this->hasMany('App\ResultLecture', 'user_id', 'id')
        ->join('lecture_exams', 'result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->join('courses', 'lecture_exams.course_id', 'courses.id')
        ->select('result_lectures.*', 'lecture_exams.exam_name', 'courses.name');
    }


    public function fcmMessage() {
        return $this->hasMany('App\UserFCMMessage', 'user_id', 'id');
    }

}
