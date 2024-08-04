<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultPaidCourseWrittenMark extends Model
{
    protected $table ='result_paid_course_written_marks';
    protected $fillable = ['paid_course_material_id', 'paid_course_quiz_result_id', 'user_id', 'question_no', 'mark', 'marks_givenby_id'];
}
