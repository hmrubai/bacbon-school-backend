<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureVideo extends Model
{
    protected $table = 'lecture_videos';
    protected $fillable = ['title', 'title_bn', 'tutor_name', 'tutor_info',
    'description', 'url', 'full_url', 'youtube_url', 'youtube_video_id',
    'vimeo_url', 'audio_book', 'audio_book_aws', 'thumbnail', 'duration', 'price', 'course_id', 'subject_id', 'chapter_id', 'isFree', 'status',
    'code',
    'sequence',
    'download_url',
    'is_downloadable',
    'rating'];
    protected $guarded = [];
    protected $casts = [
        'course_id' => 'int',
        'subject_id' => 'int',
        'chapter_id' => 'int',
        'price' => 'float',
        'duration' => 'int',
        'is_downloadable' => 'boolean',
    ];
    public function chapter()
    {
        return $this->belongsTo('App\Chapter', 'chapter_id')->select('id', 'name', 'name_bn');
    }
    public function subject()
    {
        return $this->belongsTo('App\Subject', 'subject_id')->select('id', 'name', 'name_bn');
    }

    public function course()
    {
        return $this->belongsTo('App\Course', 'course_id')->select('id', 'name', 'name_bn');
    }

    public function lectureScripts () {
        return $this->hasMany('App\LectureScript', 'lecture_id', 'id');
    }

    public function lectureRating () {
        return $this->hasMany('App\LectureRating', 'lecture_id', 'id')
        ->join('users', 'lecture_ratings.user_id', 'users.id')
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

    public function exams() {
        return $this->hasMany('App\LectureExam', 'lecture_id')->where('status', 'published')
        ->select('id', 'lecture_id', 'exam_name', 'exam_name_bn', 'exam_name_jp', 'duration', 'total_mark', 'negative_mark', 'question_number');
    }

    public function examList() {
        return $this->hasMany('App\LectureExam', 'lecture_id')
        ->withCount('questions');
    }
}
