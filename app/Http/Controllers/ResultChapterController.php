<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\ResultChapter;
use App\ResultChapterAnswer;
use App\ChapterQuestion;
use App\ChapterExamQuestion;
use App\ChapterExam;
use App\User;
use Illuminate\Http\Request;

class ResultChapterController extends Controller
{

    public function getChapterExamResult($userId, $examId)
    {
        $results = ResultChapter::where('result_chapters.user_id', $userId)->where('result_chapters.chapter_exam_id', $examId)
        ->join('chapter_exams', 'result_chapters.chapter_exam_id', 'chapter_exams.id')
        ->join('courses', 'chapter_exams.course_id', 'courses.id')
        ->join('subjects', 'chapter_exams.subject_id', 'subjects.id')
        ->join('chapters', 'chapter_exams.chapter_id', 'chapters.id')
        ->select(
            'result_chapters.*',
            'courses.name as course_name',
            'subjects.name as subject_name',
            'chapters.name as chaptername',
            'chapter_exams.exam_name',
            'chapter_exams.exam_name_bn',
            'chapter_exams.duration',
            'chapter_exams.total_mark',
            'chapter_exams.positive_mark',
            'chapter_exams.negative_mark',
            'chapter_exams.question_number'
            )
            ->with('questions')->get();

            foreach ($results as $result) {
                $count = 0;
                $negCount = 0;

                $answers = ResultChapterAnswer::where('result_chapter_id',$result->id)->get();
                    foreach ($answers as $ans) {

                        $question = ChapterQuestion::where('id', $ans->question_id)->select(
                            'id',
                            'correct_answer',
                            'correct_answer2',
                            'correct_answer3',
                            'correct_answer4',
                            'correct_answer5',
                            'correct_answer6'
                            )->first();

                        $given_answer_array = [];
                        if($ans->answer){
                            array_push($given_answer_array,$ans->answer);
                        }

                        if($ans->answer2){
                            array_push($given_answer_array,$ans->answer2);
                        }

                        if($ans->answer3){
                            array_push($given_answer_array,$ans->answer3);
                        }

                        if($ans->answer4){
                            array_push($given_answer_array,$ans->answer4);
                        }

                        if($ans->answer5){
                            array_push($given_answer_array,$ans->answer5);
                        }

                        if($ans->answer6){
                            array_push($given_answer_array,$ans->answer6);
                        }


                        $correct_answer_array = [];
                        if($question->correct_answer){
                            array_push($correct_answer_array,$question->correct_answer);
                        }

                        if($question->correct_answer2){
                            array_push($correct_answer_array,$question->correct_answer2);
                        }

                        if($question->correct_answer3){
                            array_push($correct_answer_array,$question->correct_answer3);
                        }

                        if($question->correct_answer4){
                            array_push($correct_answer_array,$question->correct_answer4);
                        }

                        if($question->correct_answer5){
                            array_push($correct_answer_array,$question->correct_answer5);
                        }

                        if($question->correct_answer6){
                            array_push($correct_answer_array,$question->correct_answer6);
                        }

                        if(sizeof($given_answer_array) == sizeof($correct_answer_array)){
                            if($given_answer_array == $correct_answer_array){
                                $count++;
                            }else {
                                $negCount++;
                            }
                        } else {
                            if(sizeof($given_answer_array) > sizeof($correct_answer_array)){
                                $negCount++;
                            }
                        }

                    }

                $result->gained_mark = $count * $result->positive_mark - $negCount * $result->negative_mark;
                $result->total_positive_mark = $count * $result->positive_mark;
                $result->total_negative_mark = $negCount * $result->negative_mark;

                foreach($result->questions as $que){
                   $_isCorrectOption1 = false;
                   $_isCorrectOption2 = false;
                   $_isCorrectOption3 = false;
                   $_isCorrectOption4 = false;

                   $_isSelectedOption1 = false;
                   $_isSelectedOption2 = false;
                   $_isSelectedOption3 = false;
                   $_isSelectedOption4 = false;


                   if( $que->correct_answer > 1 || $que->given_answer > 1){
                    //old fashion (single selection)
                    if($que->correct_answer == 1){ $_isCorrectOption1 = true;}
                    else if($que->correct_answer == 2){ $_isCorrectOption2 = true;}
                    else if($que->correct_answer == 3){ $_isCorrectOption3 = true;}
                    else if($que->correct_answer == 4){ $_isCorrectOption4 = true;}

                    if($que->given_answer == 1){  $_isSelectedOption1 = true;}
                    else if($que->given_answer == 2){  $_isSelectedOption2 = true;}
                    else if($que->given_answer == 3){  $_isSelectedOption3 = true;}
                    else if($que->given_answer == 4){  $_isSelectedOption4 = true;}

                    else if($que->given_answer2 == 2){  $_isSelectedOption2 = true;}
                    else if($que->given_answer3 == 3){  $_isSelectedOption3 = true;}
                    else if($que->given_answer4 == 4){  $_isSelectedOption4 = true;}
                  }else{
                    //new fashion (multiple selection)
                    $_isCorrectOption1 = $que->correct_answer == 1;
                    $_isCorrectOption2 = $que->correct_answer2 == 2;
                    $_isCorrectOption3 = $que->correct_answer3 == 3;
                    $_isCorrectOption4 = $que->correct_answer4 == 4;

                    $_isSelectedOption1 = $que->given_answer == 1;
                    $_isSelectedOption2 = $que->given_answer2 == 2;
                    $_isSelectedOption3 = $que->given_answer3 == 3;
                    $_isSelectedOption4 = $que->given_answer4 == 4;
                  }

                  $que->_isCorrectOption1 = $_isCorrectOption1;
                  $que->_isCorrectOption2 = $_isCorrectOption2;
                  $que->_isCorrectOption3 = $_isCorrectOption3;
                  $que->_isCorrectOption4 = $_isCorrectOption4;

                  $que->_isSelectedOption1 = $_isSelectedOption1;
                  $que->_isSelectedOption2 = $_isSelectedOption2;
                  $que->_isSelectedOption3 = $_isSelectedOption3;
                  $que->_isSelectedOption4 = $_isSelectedOption4;

                }
            }

        return FacadeResponse::json($results);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function submitChapterExamResult(Request $request)
    {
        $response = new ResponseObject;
        $exam = ChapterExam::where('id', $request->exam_id)->first();
        $count = 0;
        $resultChapter = ResultChapter::create([
            "user_id" => $request->user_id,
            "chapter_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);

        $negCount = 0;
        // foreach($request->answers as $ans) {
        //     ResultChapterAnswer::insert([
        //         "question_id" => $ans['question_id'],
        //         "result_chapter_id" => $resultChapter->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = ChapterQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }


        foreach($request->answers as $ans) {
            ResultChapterAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_chapter_id" => $resultChapter->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = ChapterQuestion::where('id', $ans['question_id'])->select(
                'id',
                'correct_answer',
                'correct_answer2',
                'correct_answer3',
                'correct_answer4',
                'correct_answer5',
                'correct_answer6'
                )->first();


            $given_answer_array = [];
            if($ans['answer']){
                array_push($given_answer_array,$ans['answer']);
            }

            if($ans['answer2']){
                array_push($given_answer_array,$ans['answer2']);
            }

            if($ans['answer3']){
                 array_push($given_answer_array,$ans['answer3']);
            }

            if($ans['answer4']){
                 array_push($given_answer_array,$ans['answer4']);
            }

            if($ans['answer5']){
                 array_push($given_answer_array,$ans['answer5']);
            }

            if($ans['answer6']){
                 array_push($given_answer_array,$ans['answer6']);
            }


            $correct_answer_array = [];
            if($question->correct_answer){
                array_push($correct_answer_array,$question->correct_answer);
            }

            if($question->correct_answer2){
                array_push($correct_answer_array,$question->correct_answer2);
            }

            if($question->correct_answer3){
                array_push($correct_answer_array,$question->correct_answer3);
            }

            if($question->correct_answer4){
                array_push($correct_answer_array,$question->correct_answer4);
            }

            if($question->correct_answer5){
                array_push($correct_answer_array,$question->correct_answer5);
            }

            if($question->correct_answer6){
                array_push($correct_answer_array,$question->correct_answer6);
            }

            if(sizeof($given_answer_array) == sizeof($correct_answer_array)){
                if($given_answer_array == $correct_answer_array){
                    $count++;
                }else {
                    $negCount++;
                }
            } else {
                if(sizeof($given_answer_array) > sizeof($correct_answer_array)){
                    $negCount++;
                }
            }
        }


        ResultChapter::where('id', $resultChapter->id)->update([
            "mark" => $count * $exam->positive_mark - $negCount * $exam->negative_mark
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";
        $verifyController = new VerifyCodeController();
        $response->result = $verifyController->getLoginData($request->user_id);

        return FacadeResponse::json($response);
    }



    public function saveChapterExamResultWeb(Request $request)
    {
        $response = new ResponseObject;
        $exam = ChapterExam::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;
        $resultChapter = ResultChapter::create([
            "user_id" => $request->user_id,
            "chapter_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);

        // foreach($request->answers as $ans) {
        //     ResultChapterAnswer::insert([
        //         "question_id" => $ans['question_id'],
        //         "result_chapter_id" => $resultChapter->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = ChapterQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }


        foreach($request->answers as $ans) {
            ResultChapterAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_chapter_id" => $resultChapter->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = ChapterQuestion::where('id', $ans['question_id'])->select(
                'id',
                'correct_answer',
                'correct_answer2',
                'correct_answer3',
                'correct_answer4',
                'correct_answer5',
                'correct_answer6'
                )->first();


            $given_answer_array = [];
            if($ans['answer']){
                array_push($given_answer_array,$ans['answer']);
            }

            if($ans['answer2']){
                array_push($given_answer_array,$ans['answer2']);
            }

            if($ans['answer3']){
                 array_push($given_answer_array,$ans['answer3']);
            }

            if($ans['answer4']){
                 array_push($given_answer_array,$ans['answer4']);
            }

            if($ans['answer5']){
                 array_push($given_answer_array,$ans['answer5']);
            }

            if($ans['answer6']){
                 array_push($given_answer_array,$ans['answer6']);
            }


            $correct_answer_array = [];
            if($question->correct_answer){
                array_push($correct_answer_array,$question->correct_answer);
            }

            if($question->correct_answer2){
                array_push($correct_answer_array,$question->correct_answer2);
            }

            if($question->correct_answer3){
                array_push($correct_answer_array,$question->correct_answer3);
            }

            if($question->correct_answer4){
                array_push($correct_answer_array,$question->correct_answer4);
            }

            if($question->correct_answer5){
                array_push($correct_answer_array,$question->correct_answer5);
            }

            if($question->correct_answer6){
                array_push($correct_answer_array,$question->correct_answer6);
            }

            if(sizeof($given_answer_array) == sizeof($correct_answer_array)){
                if($given_answer_array == $correct_answer_array){
                    $count++;
                }else {
                    $negCount++;
                }
            } else {
                if(sizeof($given_answer_array) > sizeof($correct_answer_array)){
                    $negCount++;
                }
            }
        }

        $mark = $count * $exam->positive_mark - $negCount * $exam->negative_mark;

        ResultChapter::where('id', $resultChapter->id)->update([
            "mark" => $mark
        ]);


        $user = User::where('id', $request->user_id)->first();
        $points = $mark + $user->points;
        User::where('id', $request->user_id)->update(['points' => $points]);


        $exam = ChapterExam::where('id', $request->exam_id)->first();
        $userResult = ResultChapter::where('result_chapters.id', $resultChapter->id)
        ->join('chapter_exams', 'result_chapters.chapter_exam_id', 'chapter_exams.id')
        ->select('result_chapters.*', 'chapter_exams.exam_name', 'chapter_exams.positive_mark', 'chapter_exams.negative_mark', 'chapter_exams.question_number')
        ->first();


        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";



        $final_result = ChapterExamQuestion::join('chapter_questions', 'chapter_exam_questions.question_id', 'chapter_questions.id')
        ->leftJoin('result_chapter_answers', 'chapter_questions.id', 'result_chapter_answers.question_id')
        ->where('chapter_exam_questions.exam_id', $request->exam_id)
        ->where('result_chapter_answers.result_chapter_id', $userResult->id)
        ->select(
            'chapter_questions.*',
            'result_chapter_answers.answer as given_answer',
            'result_chapter_answers.answer2 as given_answer2',
            'result_chapter_answers.answer3 as given_answer3',
            'result_chapter_answers.answer4 as given_answer4',
            'result_chapter_answers.answer5 as given_answer5',
            'result_chapter_answers.answer6 as given_answer6'
            )
        ->get();
        $userResult->correct_answer_number = $count;
        $userResult->wrong_answer_number = $negCount;
        $userResult->questions = $final_result;

        $response->result = $userResult;

        return FacadeResponse::json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ResultChapter  $resultChapter
     * @return \Illuminate\Http\Response
     */
    public function show(ResultChapter $resultChapter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ResultChapter  $resultChapter
     * @return \Illuminate\Http\Response
     */
    public function edit(ResultChapter $resultChapter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ResultChapter  $resultChapter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ResultChapter $resultChapter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ResultChapter  $resultChapter
     * @return \Illuminate\Http\Response
     */
    public function destroy(ResultChapter $resultChapter)
    {
        //
    }
}
