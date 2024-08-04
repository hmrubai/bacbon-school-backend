<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewExamDetail extends Model
{
    protected $fillable = ['review_exam_id', 'lecture_exam_id'];
}
