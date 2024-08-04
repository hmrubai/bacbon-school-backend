<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultChapter extends Model
{
    protected $fillable = ['user_id', 'chapter_exam_id', 'mark', 'total_mark'];


    public function questions () {
        return $this->hasMany('App\ResultChapterAnswer', 'result_chapter_id', 'id')
        ->join('chapter_questions', 'result_chapter_answers.question_id', 'chapter_questions.id')
        ->select('chapter_questions.*', 'result_chapter_answers.result_chapter_id',
        'result_chapter_answers.answer as given_answer',
        'result_chapter_answers.answer2 as given_answer2',
        'result_chapter_answers.answer3 as given_answer3',
        'result_chapter_answers.answer4 as given_answer4',
        'result_chapter_answers.answer5 as given_answer5',
        'result_chapter_answers.answer6 as given_answer6'
    );
    }
}
