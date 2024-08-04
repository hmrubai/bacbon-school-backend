<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Throwable;
use Exception;

use App\SpecialModelTest;
use App\SpecialModelTestQuestion;
use App\SpecialModelSubjectQuestionQuantity;

use App\ResultSpecialModelTest;
use App\ResultSpecialModelTestAnswer;

use App\Chapter;
use App\CourseSubject;
use App\User;
use App\ChapterExam;
use Illuminate\Http\Request;

class SpecialModelTestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function createSpecialModelTest(Request $request)
    {
        $response = new ResponseObject;


        try {
            DB::beginTransaction();

            $modelTest = SpecialModelTest::create([
                'course_id' => $request->course_id,
                'exam_name' => $request->model_test_name,
                'exam_name_bn' => $request->model_test_name_bn ? $request->model_test_name_bn : $request->model_test_name,
                'duration' => $request->duration,
                'positive_mark' => $request->positive_mark ? $request->positive_mark : 1,
                'negative_mark' => $request->negative_mark ? $request->negative_mark : 0,
                'total_mark' => $request->total_mark,
                'question_number' => $request->question_number,
                'question_number_per_subject' => $request->question_number_per_subject,
                'unit' => $request->unit,
                'status' => $request->status
            ]);



            foreach ($request->questions as $question) {


                SpecialModelTestQuestion::create([
                    'special_model_test_id' => $modelTest->id,
                    'subject_id' => $question['subject_id'],
                    'question' => $question['question'],
                    'option1' => $question['option1'],
                    'option2' => $question['option2'],
                    'option3' => $question['option3'],
                    'option4' => $question['option4'],
                    'option5' => $question['option5'],
                    'option6' => $question['option6'],
                    'correct_answer' => $question['correct_answer'],
                    'correct_answer2' => $question['correct_answer2'],
                    'correct_answer3' => $question['correct_answer3'],
                    'correct_answer4' => $question['correct_answer4'],
                    'correct_answer5' => $question['correct_answer5'],
                    'correct_answer6' => $question['correct_answer6']
                ]);
            }

            $subjectIdList = [];
            $subjectIdList = CourseSubject::where('course_id', $request->course_id)->select('subject_id as id')->get();
            if ($request->unit == 'c') {

                foreach ($subjectIdList as $subject) {
                    SpecialModelSubjectQuestionQuantity::create([
                        'course_id' => $request->course_id,
                        'special_model_test_id' => $modelTest->id,
                        'subject_id' => $subject->id,
                        'question_number' => $request->question_number_per_subject
                    ]);
                }
            } else if ($request->unit == 'b') {

                foreach ($subjectIdList as $subject) {
                    SpecialModelSubjectQuestionQuantity::create([
                        'course_id' => 13,
                        'special_model_test_id' => $modelTest->id,
                        'subject_id' => $subject->id,
                        'question_number' => $request->question_number_per_subject
                    ]);
                }

            } else  {

                foreach ($subjectIdList as $subject) {
                    SpecialModelSubjectQuestionQuantity::create([
                        'course_id' => 15,
                        'special_model_test_id' => $modelTest->id,
                        'subject_id' => $subject->id,
                        'question_number' => $request->question_number_per_subject
                    ]);
                }
            }


            DB::commit();

            $response->status          = $response::status_ok;
            $response->messages =       "Special Model test has been created";
            $response->data            = [];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response->status          = $response::status_fail;
            $response->message         = $e->getMessage();
            $response->data            = [];
            return response()->json($response);
        }
    }

    public function getSpecialModelTestList () {
        $testList = SpecialModelTest::orderBy('id', 'desc')->get();
        return FacadeResponse::json($testList);
    }

    public function getSpecialModelTestListByType(Request $request)
    {

        //  $new_time = date("Y-m-d H:i:s", strtotime('+6 hours'));
        $userId = $request->userId;
        $unit = $request->unit;

        $examList = SpecialModelTest::select('special_model_tests.*', 'result_special_model_tests.user_id')
            ->leftJoin('result_special_model_tests', function ($join) use ($userId) {
                $join->on('result_special_model_tests.special_model_test_id', '=', 'special_model_tests.id');
                $join->where('result_special_model_tests.user_id', '=', $userId);
                $join->where('result_special_model_tests.is_last_exam', true);
                $join->where('result_special_model_tests.can_retake', false);
            })
            ->where('special_model_tests.unit', $unit)
            ->where('status', '!=', 'close')
            //->where('model_tests.appeared_from', '<=', $new_time)
            ->groupby('special_model_tests.id')
            ->get();

        // $sqlEmp = DB::select("SELECT model_tests.*, result_model_tests.user_id FROM  model_tests LEFT JOIN result_model_tests ON model_tests.id =result_model_tests.model_test_id AND result_model_tests.user_id=$userId WHERE model_tests.unit ='$unit' AND model_tests.appeared_from <= '$new_time' GROUP BY model_tests.id");

        foreach ($examList as $exam) {
            $exam->details_url = "api/getSpecialModelTestQuestionsById/" . $exam->id . '/' . $userId;
        }

        $obj = (object) [
            "notice" => $examList->count() > 0 ? '' : "No model test found",
            "records" => $examList
        ];
        return FacadeResponse::json($obj);
    }



    public function getSpecialModelTestQuestionsById($examId, $userId)
    {
        $user = User::where('id', $userId)->select('id', 'c_unit_optional_subject_id')->first();
        $exam = SpecialModelTest::where('id', $examId)->first();
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

            $quantity = SpecialModelSubjectQuestionQuantity::where('special_model_test_id', $examId)->where('subject_id', $subject->id)->first();
            // return FacadeResponse::json($quantity);
            $questionNumber = is_null($quantity) ? $exam->question_number_per_subject : $quantity->question_number;
            $list = SpecialModelTestQuestion::where('special_model_test_id', $examId)
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
            "exam_id" => (int)$examId,
            "submission_url" => "api/submitSpecialModelTestResult",
            "start_url" => "api/startSpecialModelTest"
        ];
        return FacadeResponse::json($obj);
    }
}
