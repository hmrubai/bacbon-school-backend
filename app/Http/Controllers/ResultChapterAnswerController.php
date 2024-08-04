<?php

namespace App\Http\Controllers;

use App\ResultChapterAnswer;
use Illuminate\Http\Request;

class ResultChapterAnswerController extends Controller
{
    public function getChapterExamDetailsByExamIdUserId($exam_id, $user_id)
    {
        $questionWithAnswers = ResultChapterAnswer::join('chapter_questions', 'result_chapter_answers.question_id', 'chapter_questions.id')
        ->where('result_chapter_answers.result_chapter_id', $exam_id)
        ->where('result_chapter_answers.user_id', $user_id)
        ->select(
            'result_chapter_answers.id as id',
            'result_chapter_answers.question_id as question_id',
            'result_chapter_answers.answer as answer',
            'chapter_questions.correct_answer as correct_answer',
            'chapter_questions.explanation as explanation',
            'chapter_questions.explanation_text as explanation_text',
            'chapter_questions.question as question',
            'chapter_questions.option1 as option1',
            'chapter_questions.option2 as option2',
            'chapter_questions.option3 as option3',
            'chapter_questions.option4 as option4',
            'chapter_questions.option5 as option5',
            'chapter_questions.option6 as option6'
        )
        ->get();
        return $questionWithAnswers;
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ResultChapterAnswer  $resultChapterAnswer
     * @return \Illuminate\Http\Response
     */
    public function show(ResultChapterAnswer $resultChapterAnswer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ResultChapterAnswer  $resultChapterAnswer
     * @return \Illuminate\Http\Response
     */
    public function edit(ResultChapterAnswer $resultChapterAnswer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ResultChapterAnswer  $resultChapterAnswer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ResultChapterAnswer $resultChapterAnswer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ResultChapterAnswer  $resultChapterAnswer
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResultChapterAnswer $resultChapterAnswer)
    {
        //
    }
}
