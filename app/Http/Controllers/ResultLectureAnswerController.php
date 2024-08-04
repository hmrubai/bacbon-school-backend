<?php

namespace App\Http\Controllers;

use App\ResultLectureAnswer;
use Illuminate\Http\Request;

class ResultLectureAnswerController extends Controller
{

    public function getLectureExamDetailsByExamIdUserId($exam_id, $user_id)
    {
        $questionWithAnswers = ResultLectureAnswer::join('lecture_questions', 'result_lecture_answers.question_id', 'lecture_questions.id')
        ->where('result_lecture_answers.result_lecture_id', $exam_id)
        ->where('result_lecture_answers.user_id', $user_id)
        ->select(
            'result_lecture_answers.id as id',
            'result_lecture_answers.question_id as question_id',
            'result_lecture_answers.answer as answer',
            'lecture_questions.correct_answer as correct_answer',
            'lecture_questions.explanation as explanation',
            'lecture_questions.explanation_text as explanation_text',
            'lecture_questions.question as question',
            'lecture_questions.option1 as option1',
            'lecture_questions.option2 as option2',
            'lecture_questions.option3 as option3',
            'lecture_questions.option4 as option4',
            'lecture_questions.option5 as option5',
            'lecture_questions.option6 as option6'
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
     * @param  \App\ResultLectureAnswer  $resultLectureAnswer
     * @return \Illuminate\Http\Response
     */
    public function show(ResultLectureAnswer $resultLectureAnswer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ResultLectureAnswer  $resultLectureAnswer
     * @return \Illuminate\Http\Response
     */
    public function edit(ResultLectureAnswer $resultLectureAnswer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ResultLectureAnswer  $resultLectureAnswer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ResultLectureAnswer $resultLectureAnswer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ResultLectureAnswer  $resultLectureAnswer
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResultLectureAnswer $resultLectureAnswer)
    {
        //
    }
}
