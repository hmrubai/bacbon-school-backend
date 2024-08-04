<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseSubject extends Model
{
    protected $fillable = ['course_id','subject_id', 'e_book_url', 'e_book_url_aws', 'status', 'code', 'price', 'keywords'];

    public function course()
    {
        return $this->belongsTo('App\Course', 'course_id', 'id');
    }

    public function subject()
    {
        return $this->belongsTo('App\Subject')->select(array('id','name', 'name_bn','name_jp' ));
    }

    public function chapters () {
        return $this->hasMany('App\Chapter', 'subject_id', 'subject_id');
    }
    public function exams () {
        return $this->hasMany('App\SubjectExam', 'subject_id', 'subject_id')
            ->select(
                'subject_exams.id as id',
                'subject_exams.course_id as course_id',
                'subject_exams.subject_id as subject_id',
                'subject_exams.exam_name as exam_name',
                'subject_exams.exam_name_bn as exam_name_bn',
                'subject_exams.exam_name_jp as exam_name_jp',
                'subject_exams.duration as duration',
                'subject_exams.positive_mark as positive_mark',
                'subject_exams.negative_mark as negative_mark',
                'subject_exams.total_mark as total_mark',
                'subject_exams.question_number as question_number',
                'subject_exams.status as status'
             );
    }

    public function examsAndResult () {
        return $this->hasMany('App\SubjectExam', 'subject_id', 'subject_id')
            ->leftJoin('result_subjects', 'subject_exams.id', 'result_subjects.subject_exam_id');
    }

    protected $casts = [
        'is_free' => 'boolean',
    ];
}

