<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureFavorite extends Model
{
    protected $fillable = ['lecture_id', 'user_id'];


    public function lectureScripts () {
        return $this->hasMany('App\LectureScript', 'lecture_id', 'lecture_id')->select(['lecture_id', 'id', 'title', 'url']);
    }

    public function exams() {
        return $this->hasMany('App\LectureExam', 'lecture_id', 'lecture_id')->where('status', 'published')
        ->select('id', 'lecture_id', 'exam_name', 'duration', 'total_mark', 'negative_mark', 'question_number');
    }
    public function lectureRating () {
        return $this->hasMany('App\LectureRating', 'lecture_id', 'lecture_id')
        ->join('users', 'lecture_ratings.user_id', 'users.id')
        ->where('lecture_ratings.status', 'Approved')
        ->select('lecture_ratings.id as id',
        'lecture_ratings.rating',
        'lecture_ratings.user_id',
        'lecture_ratings.lecture_id',
        'users.name',
        'lecture_ratings.comment',
        'lecture_ratings.created_at',
        'lecture_ratings.updated_at'
    );
    }

}
