<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelSubjectQuestionQuantilty extends Model
{
    protected $fillable = ['course_id', 'model_test_id', 'subject_id', 'question_number'];
}
