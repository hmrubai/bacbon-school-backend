<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultSubject extends Model
{
    protected $fillable = ['user_id', 'subject_exam_id', 'total_mark', 'mark'];
}
