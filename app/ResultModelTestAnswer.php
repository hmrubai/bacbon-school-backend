<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultModelTestAnswer extends Model
{
    protected $fillable = ['result_model_test_id', 'question_id', 'user_id', 'answer', 'answer2', 'answer3', 'answer4', 'answer5', 'answer6'];
}
