<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\ResultSpecialModelTest;
use App\ResultSpecialModelTestAnswer;
use App\SpecialModelTest;
use App\SpecialModelTestQuestion;
use App\User;
use Illuminate\Http\Request;

class ResultSpecialModelTestController extends Controller
{


    public function getSpecialModelExamResult($userId, $examId)
    {
        $result = ResultSpecialModelTest::where('result_special_model_tests.user_id', $userId)->where('result_special_model_tests.special_model_test_id', $examId)
            ->join('special_model_tests', 'result_special_model_tests.special_model_test_id', 'special_model_tests.id')
            ->join('courses', 'special_model_tests.course_id', 'courses.id')
            ->select(
                'resultspecial__model_tests.*',
                'courses.name as course_name',
                'special_model_tests.exam_name',
                'special_model_tests.exam_name_bn',
                'special_model_tests.duration',
                'special_model_tests.total_mark',
                'special_model_tests.positive_mark',
                'special_model_tests.negative_mark',
                'special_model_tests.question_number'
            )
            ->with('questions')->get();

        return FacadeResponse::json($result);
    }


    public function startSpecialModelTest(Request $request){

        $response = new ResponseObject;
        $exam = SpecialModelTest::where('id', $request->exam_id)->first();

       ResultSpecialModelTest::where('user_id',$request->user_id)
        ->where('special_model_test_id',$request->exam_id)
        ->where('is_last_exam',true)
        ->update(['is_last_exam' => false ]);


        ResultSpecialModelTest::create([
            "user_id" => $request->user_id,
            "special_model_test_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
            "is_last_exam" => true,
            "can_retake" => false
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Your exam has been started";
        return FacadeResponse::json($response);

    }

    public function submitSpecialModelTestResult(Request $request)
    {
        $response = new ResponseObject;
        $exam = SpecialModelTest::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;


        $resultSpecialModelExam = ResultSpecialModelTest::where('user_id',$request->user_id)
        ->where('special_model_test_id',$request->exam_id)
        ->where('is_last_exam',true)
        ->where('can_retake',false)->first();


        foreach ($request->answers as $ans) {
            ResultSpecialModelTestAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_special_model_test_id" => $resultSpecialModelExam->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = SpecialModelTestQuestion::where('id', $ans['question_id'])->select(
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

        ResultSpecialModelTest::where('id', $resultSpecialModelExam->id)->update([
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


    public function getParticipatedUserIds(Request $request)
    {
        $userIds = ResultSpecialModelTest::where('special_model_test_id', $request->examId)->distinct('user_id')->pluck('user_id')->toArray();
        $results = [];
        foreach ($userIds as $userId) {
            $results[] = ResultSpecialModelTest::where('user_id', $userId)->where('is_last_exam', true)->where('special_model_test_id', $request->examId)->orderBy('result_special_model_tests.id', 'desc')
                ->join('users', 'result_special_model_tests.user_id', 'users.id')
                ->select(
                    'result_special_model_tests.id',
                    'result_special_model_tests.mark',
                    'result_special_model_tests.total_mark',
                    'users.name',
                    'users.image'
                )
                ->first();
        }

        $keys = array_column($results, 'mark');

        array_multisort($keys, SORT_DESC, $results);

        return FacadeResponse::json($results);
    }



    public function getUserSpecialModelTestResults(Request $request)
    {
        $list = [];
        $users = User::select('id', 'name', 'mobile_number')
        ->where(function($query){
            $query->orWhere('is_c_unit_purchased', true)
                  ->orWhere('is_b_unit_purchased', true)
                  ->orWhere('is_d_unit_purchased', true);
        })->get();
        foreach ($users as $user) {
            $user->result = $this->modelTestResult($user->id, $request->examId);
        }
        return FacadeResponse::json($users);
    }

    private function modelTestResult($userId, $examId)
    {
        $exam = SpecialModelTest::where('id', $examId)->first();
        $user = User::where('users.id', $userId)
            ->leftJoin('subjects', 'users.c_unit_optional_subject_id', 'subjects.id')
            ->select('users.id', 'users.name', 'users.c_unit_optional_subject_id', 'subjects.name as subject_name')
            ->first();
        $subjects = SpecialModelTestQuestion::where('special_model_test_questions.special_model_test_id', $exam->id)
            // ->where('model_test_questions.subject_id', '!=', $user->c_unit_optional_subject_id)
            ->join('subjects', 'special_model_test_questions.subject_id', 'subjects.id')
            ->groupBy('special_model_test_questions.subject_id', 'subjects.name')
            ->select('special_model_test_questions.subject_id', 'subjects.name')
            ->get();
        // return FacadeResponse::json($subjects);
        $rs = ResultSpecialModelTest::where('result_special_model_tests.user_id', $userId)->where('is_last_exam', true)->where('result_special_model_tests.special_model_test_id', $examId)
            ->join('special_model_tests', 'result_special_model_tests.special_model_test_id', 'special_model_tests.id')
            ->join('courses', 'special_model_tests.course_id', 'courses.id')
            ->select(
                'result_special_model_tests.id',
                'result_special_model_tests.mark',
                'courses.name as course_name',
                'special_model_tests.total_mark',
                'special_model_tests.positive_mark',
                'special_model_tests.negative_mark'
            )->orderBy('result_model_tests.id', 'desc')->first();
        if (!is_null($rs)) {
            $gainMark = 0;
            $list = [];
            foreach ($subjects as $subject) {
                $answers = ResultSpecialModelTestAnswer::where('result_special_model_test_answers.result_special_model_test_id', $rs->id)
                    ->join('special_model_test_questions', 'result_special_model_test_answers.question_id', 'special_model_test_questions.id')
                    ->where('special_model_test_questions.subject_id', $subject->subject_id)
                    ->select(
                        'special_model_test_questions.id',
                        'special_model_test_questions.correct_answer',
                        'special_model_test_questions.correct_answer2',
                        'special_model_test_questions.correct_answer3',
                        'special_model_test_questions.correct_answer4',
                        'special_model_test_questions.correct_answer5',
                        'special_model_test_questions.correct_answer6',
                        'result_special_model_test_answers.answer',
                        'result_special_model_test_answers.answer2',
                        'result_special_model_test_answers.answer3',
                        'result_special_model_test_answers.answer4',
                        'result_special_model_test_answers.answer5',
                        'result_special_model_test_answers.answer6'
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
