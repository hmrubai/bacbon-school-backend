<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuizContest extends Model
{
    protected $fillable = ['question', 'option1', 'option2', 'option3', 'option4', 'correct_answer', 'prize_amount', 'contest_date'];

    public function correctAnswers () {
        return $this->hasMany('App\QuizContestAnswer', 'quiz_id', 'id')
        ->join('users', 'quiz_contest_answers.user_id', 'users.id')
        ->where('quiz_contest_answers.is_correct', true)
        ->select('quiz_contest_answers.*', 'users.name', 'users.image');
    }

    public function wrongAnswers () {
        return $this->hasMany('App\QuizContestAnswer', 'quiz_id', 'id')
        ->join('users', 'quiz_contest_answers.user_id', 'users.id')
        ->where('quiz_contest_answers.is_correct', false)
        ->select('quiz_contest_answers.*', 'users.name', 'users.image');
    }

    public function winner() {
        return $this->hasOne('App\QuizContestAnswer', 'quiz_id', 'id')
        ->join('users', 'quiz_contest_answers.user_id', 'users.id')
        ->where('quiz_contest_answers.is_winner', true)
        ->select('quiz_contest_answers.*', 'users.name', 'users.image');
    }
}
