<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\ResultCrashCouresQuiz;
use App\ResultCrashCouresQuizAnswer;

use App\CrashCourseMaterial;
use App\CrashCourseQuizQuestion;

use App\CrashCourseParticipantQuizAccess;
use App\CrashCourseQuizParticipationCount;



use App\ResultSpecialModelTest;
use App\ResultSpecialModelTestAnswer;
use App\SpecialModelTest;
use App\SpecialModelTestQuestion;


use App\User;
use Illuminate\Http\Request;

class ResultCrashCourseQuizController extends Controller
{



    public function startCrashCouseQuiz(Request $request){

        $response = new ResponseObject;
        $number_of_participation = 0;

        $exam = CrashCourseMaterial::where('id', $request->exam_id)->first();
       $access_count = CrashCourseParticipantQuizAccess::where('user_id',$request->user_id)
        ->where('crash_course_material_id',$request->exam_id)->first();

       $participation_count = CrashCourseQuizParticipationCount::where('user_id',$request->user_id)
       ->where('crash_course_material_id',$request->exam_id)->first();

       if(!empty($participation_count)){
        $number_of_participation = $participation_count->number_of_participation;
       }

       if($number_of_participation > $access_count->access_count){
        $response->status = $response::status_fail;
        $response->messages = "Your exam quota limit is over";
        return FacadeResponse::json($response);
       }

        ResultCrashCouresQuiz::create([
            "user_id" => $request->user_id,
            "crash_course_material_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->quiz_total_mark
        ]);

        if($number_of_participation == 0){
            CrashCourseQuizParticipationCount::create([
                "user_id" => $request->user_id,
                "crash_course_material_id" => $request->exam_id,
                "number_of_participation" => 1
            ]);
        } else {
            CrashCourseQuizParticipationCount::where('user_id',$request->user_id)
            ->where('crash_course_material_id',$request->exam_id)->update([
                "number_of_participation" => $participation_count->number_of_participation + 1
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = "Your exam has been started";


        return FacadeResponse::json($response);

    }

    public function submitCrashCourseQuizResult(Request $request)
    {
        $response = new ResponseObject;
        $exam = CrashCourseMaterial::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;


        $resultCrashCourseQuiz = ResultCrashCouresQuiz::where('user_id',$request->user_id)
        ->where('crash_course_material_id',$request->exam_id)->orderBy('id','DESC')->first();

        foreach ($request->answers as $ans) {

            ResultCrashCouresQuizAnswer::insert([
                "crash_course_quiz_question_id" => $ans['question_id'],
                "result_crash_coures_quiz_id" => $resultCrashCourseQuiz->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = CrashCourseQuizQuestion::where('id', $ans['question_id'])->select(
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


        $mark = $count * $exam->quiz_positive_mark - $negCount * $exam->quiz_negative_mark;

        ResultCrashCouresQuiz::where('id', $resultCrashCourseQuiz->id)->update([
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


    public function saveCrashCourseExamResultWeb(Request $request)
    {
        $response = new ResponseObject;
        $exam = CrashCourseMaterial::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;

        $resultCrashCourseQuiz = ResultCrashCouresQuiz::where('user_id',$request->user_id)
        ->where('crash_course_material_id',$request->exam_id)->orderBy('id','DESC')->first();


        foreach ($request->answers as $ans) {

            ResultCrashCouresQuizAnswer::insert([
                "crash_course_quiz_question_id" => $ans['question_id'],
                "result_crash_coures_quiz_id" => $resultCrashCourseQuiz->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = CrashCourseQuizQuestion::where('id', $ans['question_id'])->select(
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


        $mark = $count * $exam->quiz_positive_mark - $negCount * $exam->quiz_negative_mark;

        ResultCrashCouresQuiz::where('id', $resultCrashCourseQuiz->id)->update([
            "mark" => $mark
        ]);

        $user = User::where('id', $request->user_id)->first();
        $points = $mark + $user->points;
        User::where('id', $request->user_id)->update(['points' => $points]);


        $exam = CrashCourseMaterial::where('id', $request->exam_id)->first();
        $userResult = ResultCrashCouresQuiz::where('result_crash_coures_quizzes.id', $resultCrashCourseQuiz->id)
        ->join('crash_course_materials', 'result_crash_coures_quizzes.crash_course_material_id', 'crash_course_materials.id')
        ->select('result_crash_coures_quizzes.*', 'crash_course_materials.name as exam_name', 'crash_course_materials.quiz_positive_mark as positive_mark', 'crash_course_materials.quiz_negative_mark as negative_mark', 'crash_course_materials.quiz_question_number as question_number')
        ->first();


        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";


        $final_result = CrashCourseQuizQuestion::leftJoin('result_crash_course_quiz_answers', 'crash_course_quiz_questions.id', 'result_crash_course_quiz_answers.crash_course_quiz_question_id')
        ->where('crash_course_quiz_questions.crash_course_material_id', $request->exam_id)
        ->where('result_crash_course_quiz_answers.result_crash_coures_quiz_id', $userResult->id)
        ->select(
            'crash_course_quiz_questions.*',
            'result_crash_course_quiz_answers.answer as given_answer',
            'result_crash_course_quiz_answers.answer2 as given_answer2',
            'result_crash_course_quiz_answers.answer3 as given_answer3',
            'result_crash_course_quiz_answers.answer4 as given_answer4',
            'result_crash_course_quiz_answers.answer5 as given_answer5',
            'result_crash_course_quiz_answers.answer6 as given_answer6'
            )
        ->get();
        $userResult->correct_answer_number = $count;
        $userResult->wrong_answer_number = $negCount;
        $userResult->questions = $final_result;
        $response->result = $userResult;

        return FacadeResponse::json($response);
    }


}
