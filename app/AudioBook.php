<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AudioBook extends Model
{    
    protected $fillable = ['title', 'title_bn', 'description', 'url', 'thumbnail', 'duration', 'price', 'course_id', 'subject_id', 'chapter_id', 'status', 'code'];
    protected $guarded = [];
    protected $casts = [
        'course_id' => 'int',
        'subject_id' => 'int',
        'chapter_id' => 'int',
        'price' => 'float',
        'duration' => 'int',
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


}


