<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CrashCourse extends Model {

    protected $fillable = ["name", "name_bn", "gp_product_id", "folder_name", "description","description_features","youtube_url","thumbnail","crash_course_icon","number_of_students_enrolled","number_of_videos","number_of_scripts","number_of_quizzes","number_of_model_tests","coupon_code","regular_amount","sales_amount", "discount_percentage", "package_details","is_active","promo_status","has_trail","trail_day","is_only_test","appeared_from","appeared_to","sort"];


    public static $rules = [
        "name" => "required",
        "sort" => "required"
    ];

    public function crash_course_feature (){
        return $this->hasMany('App\CrashCourseFeature');
    }

    public function crash_course_trail_feature (){
        return $this->hasMany('App\CrashCourseTrailFeature');
    }

    public function crash_course_description_title (){
        return $this->hasMany('App\CrashCourseDescriptionTitle');
    }

    // public function correctAnswers () {
    //     return $this->hasMany('App\QuizContestAnswer', 'quiz_id', 'id')
    //     ->join('users', 'quiz_contest_answers.user_id', 'users.id')
    //     ->where('quiz_contest_answers.is_correct', true)
    //     ->select('quiz_contest_answers.*', 'users.name', 'users.image');
    // }

    public function crash_course_subject (){
        return $this->hasMany('App\CrashCourseSubject');
    }


    protected $casts = [
        'sort' => 'integer',
        'is_active' => 'boolean',
        'has_trail' => 'boolean'
    ];

}
