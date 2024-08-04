<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourse extends Model {

    protected $fillable = ["name", "name_bn","folder_name", "course_type", "gp_product_id", "description","description_features",
    "youtube_url","thumbnail","paid_course_icon","paid_course_schedule","paid_solve_class_schedule","number_of_students_enrolled","number_of_videos",
    "number_of_scripts","number_of_quizzes","number_of_model_tests","coupon_code","regular_amount",
    "sales_amount", "discount_percentage", "package_details","is_active","is_cunit","promo_status","has_trail","trail_day","is_only_test",
    "appeared_from","appeared_to","sort", "is_lc_enable"];


    public static $rules = [
        "name" => "required",
        "sort" => "required"
    ];

    public function paid_course_feature (){
        return $this->hasMany('App\PaidCourseFeature');
    }

    public function paid_course_trail_feature (){
        return $this->hasMany('App\PaidCourseTrailFeature');
    }

    public function paid_course_description_title (){
        return $this->hasMany('App\PaidCourseDescriptionTitle');
    }

    // public function correctAnswers () {
    //     return $this->hasMany('App\QuizContestAnswer', 'quiz_id', 'id')
    //     ->join('users', 'quiz_contest_answers.user_id', 'users.id')
    //     ->where('quiz_contest_answers.is_correct', true)
    //     ->select('quiz_contest_answers.*', 'users.name', 'users.image');
    // }

    public function paid_course_subject (){
        return $this->hasMany('App\PaidCourseSubject');
    }


    protected $casts = [
        'sort' => 'integer',
        'is_active' => 'boolean',
        'has_trail' => 'boolean',
        'is_cunit' => 'boolean'
        
    ];

}
