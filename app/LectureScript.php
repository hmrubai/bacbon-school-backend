<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureScript extends Model
{
    protected $fillable = [
        'course_id',
        'subject_id',
        'chapter_id',
        'lecture_id',
        'title', 'title_bn', 'title_jp',
        'url',
        'status',
        'is_premium',
        'price',
        'price_text',
        'sequence'
];

    public function chapters()
    {
        return $this->belongsTo('App\Chapter', 'chapter_id')->select('id','name');
    }

    public function scriptContents() {
        return $this->hasMany('App\ScriptText', 'lecture_script_id', 'id');
    }
    
        protected $casts = [
        'price' => 'double',
        'is_premium' => 'boolean'
    ];
}
