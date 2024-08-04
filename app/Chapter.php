<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = ['name', 'name_bn','course_id','subject_id', 'status', 'code', 'price', 'sequence'];
    protected $casts = [
        'course_id' => 'int',
        'subject_id' => 'int',
    ];

    public function course()
    {
        return $this->belongsTo('App\Course', 'course_id')->select('id', 'name');
    }
    public function subject()
    {
        return $this->belongsTo('App\Subject', 'subject_id')->select('id', 'name');
    }
    public function courses()
    {
        return $this->belongsTo('App\Course', 'course_id');
    }
    public function subjects()
    {
        return $this->belongsTo('App\Subject', 'subject_id');
    }

    public function videosNameAsc()
    {
        return $this->hasMany('App\LectureVideo', 'chapter_id', 'id')->orderBy('title', 'asc');
    }

    public function videos()
    {
        return $this->hasMany('App\LectureVideo', 'chapter_id', 'id');
    }
    public function audios()
    {
        return $this->hasMany('App\LectureVideo', 'chapter_id', 'id');
    }
    public function lectureVideos()
    {
        return $this->hasMany('App\LectureVideo', 'chapter_id', 'id')
        ->select('id', 'chapter_id', 'code', 'title', 'title_bn', 'title_jp', 'tutor_name', 'tutor_info', 'description', 'url', 'full_url','download_url','is_downloadable', 'youtube_url', 'youtube_video_id', 'vimeo_url', 'audio_book',
        'audio_book_aws', 'thumbnail', 'duration', 'price', 'status', 'isFree', 'rating')
        ->orderBy('sequence', 'asc');
    }

    public function scripts()
    {
        return $this->hasMany('App\ChapterScript', 'chapter_id', 'id');
    }
    // protected $appends = array('lectureVideos_count');

    public function getLectureVideosCountAttribute()
    {
        return $this->lectureVideos->count();
    }
    public function examArray() {
        return $this->hasMany('App\ChapterExam', 'chapter_id');
    }

    public function exam() {
        return $this->hasMany('App\ChapterExam', 'chapter_id', 'id');
    }
    public function exams() {
        return $this->hasMany('App\ChapterExam', 'chapter_id')->where('status', 'published')
        ->select('id', 'chapter_id', 'exam_name', 'exam_name_bn', 'exam_name_jp', 'duration', 'total_mark', 'negative_mark', 'question_number');
    }

    public function examList() {
        return $this->hasMany('App\ChapterExam', 'chapter_id')->withCount('questions');
        // ->select('id', 'chapter_id', 'exam_name', 'duration', 'total_mark', 'negative_mark', 'question_number');
    }
}
