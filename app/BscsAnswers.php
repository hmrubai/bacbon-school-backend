<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BscsAnswers extends Model
{
    protected $fillable = ['bscs_result_id', 'question_id', 'user_id', 'answer', 'answer2', 'answer3', 'answer4', 'answer5', 'answer6'];
}
