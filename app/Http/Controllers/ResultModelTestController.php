<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\ResultModelTest;
use App\ResultModelTestAnswer;
use App\ModelTest;
use App\ModelTestQuestion;
use App\User;
use Illuminate\Http\Request;

class ResultModelTestController extends Controller
{


    public function getModelExamResult($userId, $examId)
    {
        $results = ResultModelTest::where('result_model_tests.user_id', $userId)->where('result_model_tests.model_test_id', $examId)
            ->join('model_tests', 'result_model_tests.model_test_id', 'model_tests.id')
            ->join('courses', 'model_tests.course_id', 'courses.id')
            ->select(
                'result_model_tests.*',
                'courses.name as course_name',
                'model_tests.exam_name',
                'model_tests.exam_name_bn',
                'model_tests.duration',
                'model_tests.total_mark',
                'model_tests.positive_mark',
                'model_tests.negative_mark',
                'model_tests.question_number'
            )->with('questions')->get();

            foreach ($results as $result) {
                $count = 0;
                $negCount = 0;

                $answers = ResultModelTestAnswer::where('result_model_test_id',$result->id)->get();
                    foreach ($answers as $ans) {

                        $question = ModelTestQuestion::where('id', $ans->question_id)->select(
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
                    $_isSelectedOption2 = $question->given_answer2 == 2;
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

    public function submitModelTestResult(Request $request)
    {
        $response = new ResponseObject;
        $exam = ModelTest::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;

        $resultModelExam = ResultModelTest::create([
            "user_id" => $request->user_id,
            "model_test_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);
        // foreach($request->answers as $ans) {
        //     ResultModelTestAnswer::insert([
        //         "question_id" => $ans['question_id'],
        //         "result_model_test_id" => $resultModelExam->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = ModelTestQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }


        foreach ($request->answers as $ans) {
            ResultModelTestAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_model_test_id" => $resultModelExam->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = ModelTestQuestion::where('id', $ans['question_id'])->select(
                'id',
                'correct_answer',
                'correct_answer2',
                'correct_answer3',
                'correct_answer4',
                'correct_answer5',
                'correct_answer6'
            )->first();


            $given_answer_array = [];
            if ($ans['answer']) {
                array_push($given_answer_array, $ans['answer']);
            }

            if ($ans['answer2']) {
                array_push($given_answer_array, $ans['answer2']);
            }

            if ($ans['answer3']) {
                array_push($given_answer_array, $ans['answer3']);
            }

            if ($ans['answer4']) {
                array_push($given_answer_array, $ans['answer4']);
            }

            if ($ans['answer5']) {
                array_push($given_answer_array, $ans['answer5']);
            }

            if ($ans['answer6']) {
                array_push($given_answer_array, $ans['answer6']);
            }


            $correct_answer_array = [];
            if ($question->correct_answer) {
                array_push($correct_answer_array, $question->correct_answer);
            }

            if ($question->correct_answer2) {
                array_push($correct_answer_array, $question->correct_answer2);
            }

            if ($question->correct_answer3) {
                array_push($correct_answer_array, $question->correct_answer3);
            }

            if ($question->correct_answer4) {
                array_push($correct_answer_array, $question->correct_answer4);
            }

            if ($question->correct_answer5) {
                array_push($correct_answer_array, $question->correct_answer5);
            }

            if ($question->correct_answer6) {
                array_push($correct_answer_array, $question->correct_answer6);
            }

            if (sizeof($given_answer_array) == sizeof($correct_answer_array)) {
                if ($given_answer_array == $correct_answer_array) {
                    $count++;
                } else {
                    $negCount++;
                }
            } else {
                if (sizeof($given_answer_array) > sizeof($correct_answer_array)) {
                    $negCount++;
                }
            }
        }


        $mark = $count * $exam->positive_mark - $negCount * $exam->negative_mark;

        ResultModelTest::where('id', $resultModelExam->id)->update([
            "mark" => $mark
        ]);

        $user = User::where('id', $request->user_id)->first();
        $points = $mark + $user->points;
        User::where('id', $request->user_id)->update(['points' => $points]);

        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";
        $verifyController = new VerifyCodeController();
        $response->result = $verifyController->getLoginData($request->user_id);

        return FacadeResponse::json($response);
    }


    public function submitModelTestResultWeb(Request $request)
    {
        $response = new ResponseObject;
        $exam = ModelTest::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;

        $resultModelExam = ResultModelTest::create([
            "user_id" => $request->user_id,
            "model_test_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);


        // foreach($request->answers as $ans) {
        //     ResultModelTestAnswer::insert([
        //         "question_id" => $ans['question_id'],
        //         "result_model_test_id" => $resultModelExam->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = ModelTestQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }


        foreach ($request->answers as $ans) {
            ResultModelTestAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_model_test_id" => $resultModelExam->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = ModelTestQuestion::where('id', $ans['question_id'])->select(
                'id',
                'correct_answer',
                'correct_answer2',
                'correct_answer3',
                'correct_answer4',
                'correct_answer5',
                'correct_answer6'
            )->first();


            $given_answer_array = [];
            if ($ans['answer']) {
                array_push($given_answer_array, $ans['answer']);
            }

            if ($ans['answer2']) {
                array_push($given_answer_array, $ans['answer2']);
            }

            if ($ans['answer3']) {
                array_push($given_answer_array, $ans['answer3']);
            }

            if ($ans['answer4']) {
                array_push($given_answer_array, $ans['answer4']);
            }

            if ($ans['answer5']) {
                array_push($given_answer_array, $ans['answer5']);
            }

            if ($ans['answer6']) {
                array_push($given_answer_array, $ans['answer6']);
            }


            $correct_answer_array = [];
            if ($question->correct_answer) {
                array_push($correct_answer_array, $question->correct_answer);
            }

            if ($question->correct_answer2) {
                array_push($correct_answer_array, $question->correct_answer2);
            }

            if ($question->correct_answer3) {
                array_push($correct_answer_array, $question->correct_answer3);
            }

            if ($question->correct_answer4) {
                array_push($correct_answer_array, $question->correct_answer4);
            }

            if ($question->correct_answer5) {
                array_push($correct_answer_array, $question->correct_answer5);
            }

            if ($question->correct_answer6) {
                array_push($correct_answer_array, $question->correct_answer6);
            }

            if (sizeof($given_answer_array) == sizeof($correct_answer_array)) {
                if ($given_answer_array == $correct_answer_array) {
                    $count++;
                } else {
                    $negCount++;
                }
            } else {
                if (sizeof($given_answer_array) > sizeof($correct_answer_array)) {
                    $negCount++;
                }
            }
        }


        $mark = $count * $exam->positive_mark - $negCount * $exam->negative_mark;

        ResultModelTest::where('id', $resultModelExam->id)->update([
            "mark" => $mark
        ]);

        $user = User::where('id', $request->user_id)->first();
        $points = $mark + $user->points;
        User::where('id', $request->user_id)->update(['points' => $points]);


        $userResult = ResultModelTest::where('result_model_tests.id', $resultModelExam->id)
            ->join('model_tests', 'result_model_tests.model_test_id', 'model_tests.id')
            ->select('result_model_tests.*', 'model_tests.exam_name', 'model_tests.positive_mark', 'model_tests.negative_mark', 'model_tests.question_number')
            ->first();

        $final_result = ModelTestQuestion::leftJoin('result_model_test_answers', 'model_test_questions.id', 'result_model_test_answers.question_id')
            ->where('model_test_questions.model_test_id', $request->exam_id)
            ->where('result_model_test_answers.result_model_test_id', $userResult->id)
            ->select(
                'model_test_questions.*',
                'result_model_test_answers.answer as given_answer',
                'result_model_test_answers.answer2 as given_answer2',
                'result_model_test_answers.answer3 as given_answer3',
                'result_model_test_answers.answer4 as given_answer4',
                'result_model_test_answers.answer5 as given_answer5',
                'result_model_test_answers.answer6 as given_answer6'
            )
            ->get();
        $userResult->correct_answer_number = $count;
        $userResult->wrong_answer_number = $negCount;
        $userResult->questions = $final_result;


        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";
        $response->result = $userResult;

        return FacadeResponse::json($response);
    }

    public function getParticipatedUserIds(Request $request)
    {
        $userIds = ResultModelTest::where('model_test_id', $request->examId)->distinct('user_id')->pluck('user_id')->toArray();
        $results = [];
        foreach ($userIds as $userId) {
            $results[] = ResultModelTest::where('user_id', $userId)->where('model_test_id', $request->examId)->orderBy('result_model_tests.id', 'desc')
                ->join('users', 'result_model_tests.user_id', 'users.id')
                ->select(
                    'result_model_tests.id',
                    'result_model_tests.mark',
                    'result_model_tests.total_mark',
                    'users.name',
                    'users.image'
                )
                ->first();
        }

        $keys = array_column($results, 'mark');

        array_multisort($keys, SORT_DESC, $results);

        return FacadeResponse::json($results);
    }





    public function getEEducationUserModelTestResults(Request $request)
    {
        $list = [];
        $users = User::where('is_bae_4', true)->select('id', 'name', 'e_edu_id', 'mobile_number')->get();
        foreach ($users as $user) {
            $user->result = $this->modelTestResult($user->id, $request->examId);
            //   if ($user->result != null) {
            //       $list[] = $user;
            //   }
        }
        return FacadeResponse::json($users);
    }

    private function modelTestResult($userId, $examId)
    {
        $exam = ModelTest::where('id', $examId)->first();
        $user = User::where('users.id', $userId)
            ->leftJoin('subjects', 'users.c_unit_optional_subject_id', 'subjects.id')
            ->select('users.id', 'users.name', 'users.c_unit_optional_subject_id', 'subjects.name as subject_name')
            ->first();
        $subjects = ModelTestQuestion::where('model_test_questions.model_test_id', $exam->id)
            // ->where('model_test_questions.subject_id', '!=', $user->c_unit_optional_subject_id)
            ->join('subjects', 'model_test_questions.subject_id', 'subjects.id')
            ->groupBy('model_test_questions.subject_id', 'subjects.name')
            ->select('model_test_questions.subject_id', 'subjects.name')
            ->get();
        // return FacadeResponse::json($subjects);
        $rs = ResultModelTest::where('result_model_tests.user_id', $userId)->where('result_model_tests.model_test_id', $examId)
            ->join('model_tests', 'result_model_tests.model_test_id', 'model_tests.id')
            ->join('courses', 'model_tests.course_id', 'courses.id')
            ->select(
                'result_model_tests.id',
                'result_model_tests.mark',
                'courses.name as course_name',
                'model_tests.total_mark',
                'model_tests.positive_mark',
                'model_tests.negative_mark'
            )->orderBy('result_model_tests.id', 'desc')->first();
        if (!is_null($rs)) {
            $gainMark = 0;
            $list = [];
            foreach ($subjects as $subject) {
                $answers = ResultModelTestAnswer::where('result_model_test_answers.result_model_test_id', $rs->id)
                    ->join('model_test_questions', 'result_model_test_answers.question_id', 'model_test_questions.id')
                    ->where('model_test_questions.subject_id', $subject->subject_id)
                    ->select(
                        'model_test_questions.id',
                        'model_test_questions.correct_answer',
                        'model_test_questions.correct_answer2',
                        'model_test_questions.correct_answer3',
                        'model_test_questions.correct_answer4',
                        'model_test_questions.correct_answer5',
                        'model_test_questions.correct_answer6',
                        'result_model_test_answers.answer',
                        'result_model_test_answers.answer2',
                        'result_model_test_answers.answer3',
                        'result_model_test_answers.answer4',
                        'result_model_test_answers.answer5',
                        'result_model_test_answers.answer6'
                    )
                    ->get();
                $gainMarks = 0;
                $negativeMarks = 0;
                $totalMarks = 0;
                $totalQuestion = 0;
                foreach ($answers as $answer) {
                    $totalMarks += $rs->positive_mark;

                    // if ($answer->answer != -1) {
                    //     if ($answer->answer == $answer->correct_answer) {
                    //         $gainMarks += $rs->positive_mark;
                    //     } else {
                    //         $negativeMarks += $rs->negative_mark;
                    //     }
                    // }


                    if ($answer->answer != -1) {

                        $given_answer_array = [];
                        if ($answer->answer) {
                            array_push($given_answer_array, $answer->answer);
                        }
                        if ($answer->answer2) {
                            array_push($given_answer_array, $answer->answer2);
                        }
                        if ($answer->answer3) {
                            array_push($given_answer_array, $answer->answer3);
                        }
                        if ($answer->answer4) {
                            array_push($given_answer_array, $answer->answer4);
                        }
                        if ($answer->answer5) {
                            array_push($given_answer_array, $answer->answer5);
                        }
                        if ($answer->answer6) {
                            array_push($given_answer_array, $answer->answer6);
                        }

                        $correct_answer_array = [];
                        if ($answer->correct_answer) {
                            array_push($correct_answer_array, $answer->correct_answer);
                        }
                        if ($answer->correct_answer2) {
                            array_push($correct_answer_array, $answer->correct_answer2);
                        }
                        if ($answer->correct_answer3) {
                            array_push($correct_answer_array, $answer->correct_answer3);
                        }
                        if ($answer->correct_answer4) {
                            array_push($correct_answer_array, $answer->correct_answer4);
                        }
                        if ($answer->correct_answer5) {
                            array_push($correct_answer_array, $answer->correct_answer5);
                        }
                        if ($answer->correct_answer6) {
                            array_push($correct_answer_array, $answer->correct_answer6);
                        }


                        if (sizeof($given_answer_array) == sizeof($correct_answer_array)) {
                            if ($given_answer_array == $correct_answer_array) {
                                $gainMarks += $rs->positive_mark;
                            } else {
                                $negativeMarks += $rs->negative_mark;
                            }
                        } else {
                            if (sizeof($given_answer_array) > sizeof($correct_answer_array)) {
                                $negativeMarks += $rs->negative_mark;
                            }
                        }
                    }
                }
                $subjectObj = (object) [
                    // "answers" => $answers,
                    "subject_name" => $subject->name,
                    "total_postive" => $gainMarks,
                    "total_negative" => $negativeMarks,
                    "mark" =>  $gainMarks - $negativeMarks,
                    "total_marks" => $totalMarks // $rs->question_number_per_subject * $rs->positive_mark
                ];
                $rs->{$subject->name} = $gainMarks - $negativeMarks;
                $gainMark += ($gainMarks - $negativeMarks);
                $list[] = $subjectObj;
            }
            $rs->mark = $gainMark;
            $rs->optional_subject = $user->subject_name;
            // $rs->subject_results = $list;
        }

        return $rs;
    }
}
