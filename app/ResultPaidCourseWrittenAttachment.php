<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultPaidCourseWrittenAttachment extends Model
{
    protected $table ='result_paid_course_written_attachments';
    protected $fillable = ['paid_course_material_id', 'paid_course_quiz_result_id', 'user_id', 'attachment_url'];
}
