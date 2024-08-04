<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuizContestAnswer extends Model
{
    protected $fillable = ['user_id', 'quiz_id', 'given_answer', "is_correct", 'is_winner', 'winner_cover_image'];
}