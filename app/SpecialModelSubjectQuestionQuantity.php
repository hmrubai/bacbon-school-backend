<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpecialModelSubjectQuestionQuantity extends Model
{
    protected $fillable = ['course_id', 'special_model_test_id', 'subject_id', 'question_number'];
}
