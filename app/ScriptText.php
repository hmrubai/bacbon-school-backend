<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScriptText extends Model
{
    protected $fillable = ['lecture_script_id', 'title', 'image', 'description'];

    public function lecture() {
        return $this->belongsTo('App/LectureScript');
    }
}

