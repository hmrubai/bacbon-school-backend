<?php

namespace App\Http\Controllers;

use App\ResultSubjectAnswer;
use App\ResultSubject;
use Illuminate\Http\Request;

class ResultSubjectAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSubjectExamDetailsByExamIdUserId($exam_id, $user_id)
    {
        $questionWithAnswers = ResultSubjectAnswer::join('subject_questions', 'result_subject_answers.question_id', 'subject_questions.id')
        ->where('result_subject_answers.result_subject_id', $exam_id)
        ->where('result_subject_answers.user_id', $user_id)
        ->select(
            'result_subject_answers.id as id',
            'result_subject_answers.question_id as question_id',
            'result_subject_answers.answer as answer',
            'subject_questions.correct_answer as correct_answer',
            'subject_questions.explanation as explanation',
            'subject_questions.explanation_text as explanation_text',
            'subject_questions.question as question',
            'subject_questions.option1 as option1',
            'subject_questions.option2 as option2',
            'subject_questions.option3 as option3',
            'subject_questions.option4 as option4',
            'subject_questions.option5 as option5',
            'subject_questions.option6 as option7'
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
     * @param  \App\ResultSubjectAnswer  $resultSubjectAnswer
     * @return \Illuminate\Http\Response
     */
    public function show(ResultSubjectAnswer $resultSubjectAnswer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ResultSubjectAnswer  $resultSubjectAnswer
     * @return \Illuminate\Http\Response
     */
    public function edit(ResultSubjectAnswer $resultSubjectAnswer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ResultSubjectAnswer  $resultSubjectAnswer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ResultSubjectAnswer $resultSubjectAnswer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ResultSubjectAnswer  $resultSubjectAnswer
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResultSubjectAnswer $resultSubjectAnswer)
    {
        //
    }
}
