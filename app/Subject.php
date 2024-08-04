<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'name_bn', 'color_name'];

    public function chapters() {
        return $this->hasMany('App\Chapter', 'subject_id');
    }

    public function exams() {
        return $this->hasMany('App\SubjectExam', 'subject_id', 'id')->where('status', 'published')
                    ->select('id', 'subject_id', 'exam_name', 'duration', 'total_mark', 'negative_mark', 'question_number');
    }


    public function chapterScripts() {
        return $this->hasMany('App\ChapterScript', 'subject_id')->orderBy('sequence', 'asc');
    }

    public function chapterExams() {
        return $this->hasMany('App\ChapterExam', 'subject_id')->orderBy('sequence', 'asc');
    }

    public function lectureScripts() {
        return $this->hasMany('App\LectureScript', 'subject_id');
    }

    public function lectureExams() {
        return $this->hasMany('App\LectureExam', 'subject_id');
    }
}
