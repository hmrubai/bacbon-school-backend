<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidCourseMaterial extends Model
{

    protected $fillable = ['paid_course_id', 'paid_course_subject_id', 'type', 'name', 'name_bn',
    'quiz_duration', 'quiz_positive_mark', 'quiz_negative_mark', 'quiz_total_mark', 'quiz_question_number', 'sufficient_question',
    'video_author', 'video_author_info', 'description', 'thumbnail', 'video_url','video_code','video_full_url',
    'video_download_url', 'video_is_downloadable', 'video_duration', 'script_url', 'appeared_from', 'appeared_to',
    'sort', 'status', 'has_schedule', 'test_type', 'is_active','is_accessible', 'optional_subject_id', 'optional_subject_name'];

    // protected $guarded = [];
    protected $casts = [
        'paid_course_id' => 'int',
        'paid_course_subject_id' => 'int',
        'video_duration' => 'int',
        'duration' => 'int',
        'sort' => 'int',
        'is_active' => 'boolean',
        'is_accessible' => 'boolean',
        'has_schedule' => 'boolean',
        'sufficient_question' => 'boolean'
    ];

    public function paid_course_subject()
    {
        return $this->belongsTo('App\PaidCourseSubjct', 'paid_course_subject_id', 'id');
    }

}
