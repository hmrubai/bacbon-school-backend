<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use Illuminate\Support\Facades\DB;
use App\ModelTest;
use App\ModelTestQuestion;
use App\ModelSubjectQuestionQuantilty;
use App\Chapter;
use App\CourseSubject;
use App\User;
use App\ChapterExam;

use App\SelectionTest;
use App\SelectionTestQuestion;

use Illuminate\Http\Request;

class ModelTestController extends Controller
{
    public function createModelTest(Request $request)
    {
        $response = new ResponseObject;

        // $chapters = Chapter::where('course_id', 27)
        // ->when($request->name, function ($query) use ($request){
        //     return $query->where('name', $request->name);
        // })
        // ->when($request->ids, function ($query) use ($request){
        //     return $query->whereIn('id', $request->ids);
        // })
        // ->with('exam', 'exam.questions')->get();

        // $modelTest = ModelTest::create([
        //     'course_id' => $request->course_id,
        //     'exam_name' => $request->model_test_name,
        //     'exam_name_bn' => $request->model_test_name_bn ? $request->model_test_name_bn :$request->model_test_name,
        //     'duration' => $request->duration,
        //     'positive_mark' => $request->positive_mark ? $request->positive_mark: 1,
        //     'negative_mark' => $request->negative_mark ? $request->negative_mark: 0,
        //     'total_mark' => $request->total_mark,
        //     'question_number' => $request->question_number,
        //     'question_number_per_subject' => $request->question_number_per_subject,
        //     'unit' =>$request->unit,
        //     'status' => $request->status
        // ]);
        // foreach ($chapters as $chapter) {
        //     if (!is_null($chapter->exam)) {
        //         foreach ($chapter->exam as $ex) {
        //             foreach ($ex->questions as $question) {
        //                 ModelTestQuestion::create([
        //                     'model_test_id' => $modelTest->id,
        //                     'subject_id' => $chapter->subject_id,
        //                     'question' => $question->question,
        //                     'option1' => $question->option1,
        //                     'option2' => $question->option2,
        //                     'option3' => $question->option3,
        //                     'option4' => $question->option4,
        //                     'option5' => $question->option5,
        //                     'option6' => $question->option6,
        //                     'correct_answer' => $question->correct_answer,
        //                     'correct_answer2' => $question->correct_answer2,
        //                     'correct_answer3' => $question->correct_answer3,
        //                     'correct_answer4' => $question->correct_answer4,
        //                     'correct_answer5' => $question->correct_answer5,
        //                     'correct_answer6' => $question->correct_answer6,
        //                     'explanation' => $question->explanation,
        //                     'explanation_text' => $question->explanation_text,
        //                     'status' => $question->status
        //                 ]);
        //             }
        //         }
        //     }
        // }


        // if ($request->examIds) {
        //     $chapExams = ChapterExam::whereIn('id', $request->examIds)->with('questions')->get();
        //     foreach ($chapExams as $ex) {
        //         foreach ($ex->questions as $question) {
        //             ModelTestQuestion::create([
        //                 'model_test_id' => 56,
        //                 'subject_id' => $ex->subject_id,
        //                 'question' => $question->question,
        //                 'option1' => $question->option1,
        //                 'option2' => $question->option2,
        //                 'option3' => $question->option3,
        //                 'option4' => $question->option4,
        //                 'option5' => $question->option5,
        //                 'option6' => $question->option6,
        //                 'correct_answer' => $question->correct_answer,
        //                 'correct_answer2' => $question->correct_answer2,
        //                 'correct_answer3' => $question->correct_answer3,
        //                 'correct_answer4' => $question->correct_answer4,
        //                 'correct_answer5' => $question->correct_answer5,
        //                 'correct_answer6' => $question->correct_answer6,
        //                 'explanation' => $question->explanation,
        //                 'explanation_text' => $question->explanation_text,
        //                 'status' => $question->status
        //             ]);

        //             ModelTestQuestion::create([
        //                 'model_test_id' => 57,
        //                 'subject_id' => $ex->subject_id,
        //                 'question' => $question->question,
        //                 'option1' => $question->option1,
        //                 'option2' => $question->option2,
        //                 'option3' => $question->option3,
        //                 'option4' => $question->option4,
        //                 'option5' => $question->option5,
        //                 'option6' => $question->option6,
        //                 'correct_answer' => $question->correct_answer,
        //                 'correct_answer2' => $question->correct_answer2,
        //                 'correct_answer3' => $question->correct_answer3,
        //                 'correct_answer4' => $question->correct_answer4,
        //                 'correct_answer5' => $question->correct_answer5,
        //                 'correct_answer6' => $question->correct_answer6,
        //                 'explanation' => $question->explanation,
        //                 'explanation_text' => $question->explanation_text,
        //                 'status' => $question->status
        //             ]);
        //         }
        //     }
        // }

        $response->status = $response::status_ok;
        $response->messages = "Model test has been created";
        return FacadeResponse::json($response);
    }

    public function getModelTestListByType($userId, $unit)
    {


        $new_time = date("Y-m-d H:i:s", strtotime('+6 hours'));
        $examList = ModelTest::where('unit', $unit)->where('appeared_from', "<=", $new_time)->get();

        // $examList = ModelTest::select('model_tests.*','result_model_tests.user_id')
        // ->leftJoin('result_model_tests', function($join) use ($userId)
        // {
        //     $join->on('result_model_tests.model_test_id', '=', 'model_tests.id');
        //     $join->where('result_model_tests.user_id','=', $userId);


        // })
        // ->where('model_tests.unit', $unit)
        // //->where('model_tests.appeared_from', '<=', $new_time)
        // ->groupby('model_tests.id')
        // ->get();

        foreach ($examList as $exam) {
            $exam->details_url = "api/getModelTestQuestionsById/" . $exam->id . '/' . $userId;
        }
        return $examList;
    }

    public function getModelTestList(Request $request)
    {
        $userId = $request->userId;
        $examList = ModelTest::where('status', '!=', 'close')->get();
        foreach ($examList as $exam) {

            $exam->details_url = "api/getModelTestQuestionsById/" . $exam->id . '/' . $userId;
        }
        return FacadeResponse::json($examList);
    }

    public function getModelTestListWeb(Request $request)
    {
        $unit = $request->unit;
        $examList = ModelTest::where('unit', $unit)->where('status', '!=', 'close')->get();
        return FacadeResponse::json($examList);
    }

    public function getModelTestQuestionsByIdWeb(Request $request)
    {
        $examId = $request->examId;
        $userId = $request->userId;
        $user = User::where('id', $userId)->select('id', 'c_unit_optional_subject_id')->first();
        $exam = ModelTest::where('id', $examId)->first();

        $courseId = 27;
        if ($exam->unit == "b")
            $courseId = 13;
        if ($exam->unit == "d")
            $courseId = 15;
        $subjectIdList = [];
        if ($courseId == 27)
            $subjectIdList = CourseSubject::where('subject_id', '!=', $user->c_unit_optional_subject_id)->where('course_id', $courseId)->select('subject_id as id')->get();
        else
            $subjectIdList = CourseSubject::where('course_id', $courseId)->select('subject_id as id')->get();

        $questions = [];
        foreach ($subjectIdList as $subject) {
            $quantity = ModelSubjectQuestionQuantilty::where('model_test_id', $examId)->where('subject_id', $subject->id)->first();
            // return FacadeResponse::json($quantity);
            $questionNumber = is_null($quantity) ? $exam->question_number_per_subject : $quantity->question_number;

            $list = ModelTestQuestion::where('model_test_id', $examId)
                ->where('subject_id', $subject->id)
                ->inRandomOrder(time())
                ->limit($questionNumber)
                ->get();
            foreach ($list as $item) {
                $questions[] = $item;
            }
        }
        // shuffle($questions);
        // return FacadeResponse::json($questions);

        $obj = (object) [
            "data" => $questions,
            "exam_name" => $exam->exam_name,
            "duration" => $exam->duration,
            "question_number" => $exam->question_number,
            "total_mark" => $exam->total_mark
        ];
        return FacadeResponse::json($obj);
    }

    public function getModelTestQuestionsByIdOld($examId, $userId)
    {
        $user = User::where('id', $userId)->select('id', 'c_unit_optional_subject_id')->first();
        $exam = ModelTest::where('id', $examId)->first();
        $subjectIdList = CourseSubject::where('subject_id', '!=', $user->c_unit_optional_subject_id)->where('course_id', 27)->select('subject_id as id')->get();
        $questions = [];
        foreach ($subjectIdList as $subject) {
            $list = ModelTestQuestion::where('model_test_id', $examId)
                ->where('subject_id', $subject->id)
                ->inRandomOrder(time())
                ->limit($exam->question_number_per_subject)
                ->get();
            foreach ($list as $item) {
                $questions[] = $item;
            }
        }
        // shuffle($questions);
        $obj = (object) [
            "data" => $questions,
            "submission_url" => "api/submitModelTestResult"
        ];
        return FacadeResponse::json($obj);
    }

    public function getModelTestQuestionsById($examId, $userId)
    {

        $user = User::where('id', $userId)->select('id', 'c_unit_optional_subject_id')->first();
        $exam = ModelTest::where('id', $examId)->first();
        $courseId = $exam->course_id;
        $questions = [];
        $subjectIdList = [];
        if ($courseId == 27) {
            $excluded = null;
            if ($user->c_unit_optional_subject_id == 18) {
                $excluded = 39;
            } else if ($user->c_unit_optional_subject_id == 39) {
                $excluded = 18;
            }
            $subjectIdList = CourseSubject::where('subject_id', '!=', $excluded)->where('course_id', $courseId)->select('subject_id as id')->get();
        } else {
            $subjectIdList = CourseSubject::where('course_id', $courseId)->select('subject_id as id')->get();
        }

        foreach ($subjectIdList as $subject) {

            $quantity = ModelSubjectQuestionQuantilty::where('model_test_id', $examId)->where('subject_id', $subject->id)->first();
            // return FacadeResponse::json($quantity);
            $questionNumber = is_null($quantity) ? $exam->question_number_per_subject : $quantity->question_number;
            $list = ModelTestQuestion::where('model_test_id', $examId)
                ->where('subject_id', $subject->id)
                ->inRandomOrder(time())
                ->limit($questionNumber)
                ->get();
            foreach ($list as $item) {
                $questions[] = $item;
            }
        }

        $obj = (object) [
            "data" => $questions,
            "submission_url" => "api/submitModelTestResult"
        ];
        return FacadeResponse::json($obj);
    }


}
