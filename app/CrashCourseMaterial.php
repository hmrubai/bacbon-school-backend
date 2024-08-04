<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrashCourseMaterial extends Model
{

    protected $fillable = ['crash_course_subject_id', 'type', 'name', 'name_bn',
    'quiz_duration', 'quiz_positive_mark', 'quiz_negative_mark', 'quiz_total_mark', 'quiz_question_number',
    'video_author', 'video_author_info', 'description', 'thumbnail', 'video_url','video_code','video_full_url', 'video_download_url', 'video_is_downloadable', 'video_duration', 'script_url', 'sort', 'status','is_active','is_accessible'];
    // protected $guarded = [];
    protected $casts = [
        'crash_course_subject_id' => 'int',
        'video_duration' => 'int',
        'duration' => 'int',
        'sort' => 'int',
        'price' => 'float',
        'is_active' => 'boolean',
        'is_accessible' => 'boolean',
    ];

    public function crash_course_subject()
    {
        return $this->belongsTo('App\CrashCourseSubjct', 'crash_course_subject_id', 'id');
    }

}
