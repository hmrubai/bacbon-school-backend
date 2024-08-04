<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RevisionSubjectQuestionQuantilty extends Model
{
    protected $fillable = ['revision_exam_id', 'subject_id', 'question_number'];

}
