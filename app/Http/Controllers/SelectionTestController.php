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
use App\SelectionTestQuota;
use App\SelectionTestQuestion;
use App\ResultSelectionTest;
use App\ResultSelectionTestAnswer;
use App\SelectionTestWrittenQuestion;
use App\ResultSelectionTestWrittenAnswer;

use Illuminate\Http\Request;
use Excel;

class SelectionTestController extends Controller
{
    public function SelectionTestSummary(Request $request)
    {
        $response = new ResponseObject;

        $selection_test = SelectionTest::all();

        $total_test = $selection_test->count() ? $selection_test->count() : 0;

        $new_time = date("Y-m-d H:i:s", strtotime('+6 hours'));

        $active_test = 0;
        foreach ($selection_test as $exam) 
        {
            if($exam->appeared_to > $new_time){
                $active_test++;
            }
        }

        $total_participant = SelectionTestQuota::distinct('user_id')->count('user_id');

        $return_object = ["total_test" => $total_test, "active_test" => $active_test, "total_participant" => $total_participant];

        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $return_object;
        return FacadeResponse::json($response);
    }

    public function createSelectionTest(Request $request)
    {
        $response = new ResponseObject;
        $data  = $request->all();

        try {
            if($request->id){
                SelectionTest::where('id', $request->id)->update([
                        "exam_name" => $request->exam_name,
                        "duration" => $request->duration,
                        "positive_mark" => $request->positive_mark,
                        "negative_mark" => $request->negative_mark,
                        "total_mark" => $request->total_mark,
                        "appeared_from" => date("Y-m-d H:i:s", strtotime($request->appeared_from)),
                        "appeared_to" => date("Y-m-d H:i:s", strtotime($request->appeared_to))
                ]);
                $response->status   = $response::status_ok;
                $response->message  = "Selection Test has been updated successfully";

            } else {
                SelectionTest::create([
                    "exam_name" => $request->exam_name,
                    "duration" => $request->duration,
                    "positive_mark" => $request->positive_mark,
                    "negative_mark" => $request->negative_mark,
                    "total_mark" => $request->total_mark,
                    "question_number" => 0,
                    "appeared_from" => date("Y-m-d H:i:s", strtotime($request->appeared_from)),
                    "appeared_to" => date("Y-m-d H:i:s", strtotime($request->appeared_to))
                ]);

                $response->status   = $response::status_ok;
                $response->message  = "Selection Test has been created successfully";
            }

        } catch (Exception $e) {
            $response->status       = $response::status_fail;
            $response->message      = $e->getMessage();
        }

        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function getSelectionTestList(Request $request)
    {
        $response = new ResponseObject;
        $userId = $request->userId;

        if(!$userId){
            $response->status = $response::status_fail;
            $response->messages = "Please, Enter User ID";
            return FacadeResponse::json($response);
        }

        $new_time = date("Y-m-d H:i:s", strtotime('+6 hours'));
        $examList = SelectionTest::where('appeared_from', "<=", $new_time)->where('appeared_to', ">=", $new_time)->get();

        foreach ($examList as $exam) 
        {
            $is_participate = SelectionTestQuota::where('selection_test_id', $exam->id)->where('user_id', $userId)->first();

            if(!empty($is_participate)){
                if($is_participate->quota == $is_participate->consumption){
                    $exam->is_quota_available = false;
                }
                else{
                    $exam->is_quota_available = true;
                }
            }else{
                $exam->is_quota_available = true;
            }

            $exam->details_url = "api/getSelectionTestQuestionsById/" . $exam->id . '/' . $userId;
        }
        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $examList;
        return FacadeResponse::json($response);
    }

    public function addUpdateSelectionTestWrittenQuestion(Request $request)
    {
        $response = new ResponseObject;

        $selection_test_id = $request->selection_test_id ? $request->selection_test_id : 0;

        if(!$selection_test_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        if($request->id){
            SelectionTestWrittenQuestion::where('id', $request->id)->update([
                'selection_test_id' => $selection_test_id,
                'question' => $request->question,
                'mark' => 0
            ]);
            $response->status = $response::status_ok;
            $response->messages = "Question has been updated successful.";
        }else{
            SelectionTestWrittenQuestion::create([
                'selection_test_id' => $selection_test_id,
                'question' => $request->question,
                'mark' => 0
            ]);
            $response->status = $response::status_ok;
            $response->messages = "Question has been added successful.";
        }

        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function getSelectionTestWrittenQuestionList(Request $request)
    {
        $response = new ResponseObject;
        $selectionTestID = $request->selection_test_id;

        if(!$selectionTestID){
            $response->status = $response::status_fail;
            $response->messages = "Please, Enter selection Test ID";
            return FacadeResponse::json($response);
        }

        $questionList = SelectionTestWrittenQuestion::where('selection_test_id', $selectionTestID)->get();

        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $questionList;
        return FacadeResponse::json($response);
    }

    public function getSelectionTestQuestionList(Request $request)
    {
        $response = new ResponseObject;
        $selectionTestID = $request->selectionTestID;
        $userId = $request->userId;

        if(!$userId || !$selectionTestID){
            $response->status = $response::status_fail;
            $response->messages = "Please, Check ID";
            return FacadeResponse::json($response);
        }

        $questionList = SelectionTestQuestion::where('selection_test_id', $selectionTestID)->get();
        $writtenQuestionList = SelectionTestWrittenQuestion::where('selection_test_id', $selectionTestID)->get();

        if(!sizeof($questionList)){
            $response->status = $response::status_fail;
            $response->messages = "No question found!";
            return FacadeResponse::json($response);
        }

        $obj = (Object) [
            "data"                      => $questionList,
            "written_question"          => $writtenQuestionList,
            "exam_id"                   => intval($selectionTestID),
            "result_expanation_enabled" => false,
            "submission_url"            => "api/selectionTestSubmit",
            "written_submission_url"    => "api/selectionTestWrittenSubmit",
            "start_url"                 => "api/selectionTestStart"
        ];

        return FacadeResponse::json($obj);
    }

    public function selectionTestStart(Request $request)
    {
        $response = new ResponseObject;
        $selectionTestID = $request->exam_id;
        $userId = $request->user_id;

        if(!$userId || !$selectionTestID){
            $response->status = $response::status_fail;
            $response->messages = "Please, Check ID";
            return FacadeResponse::json($response);
        }

        $is_participate = SelectionTestQuota::where('selection_test_id', $selectionTestID)->where('user_id', $userId)->first();

        $status = "OK";

        if(empty($is_participate)){
            SelectionTestQuota::create([
                "user_id" => $userId,
                "selection_test_id" => $selectionTestID,
                "quota" => 1,
                "consumption" => 1,
                "participation_date" => date("Y-m-d H:i:s", strtotime('+6 hours'))
            ]);
        }else{
            if($is_participate->quota == $is_participate->consumption){
                $response->status = "NotOK";
                $response->messages = "You have already participated on selection test!";
                return FacadeResponse::json($response);
            }else{
                SelectionTestQuota::where('id', $is_participate->id)->update([
                    "consumption" => $is_participate->consumption + 1,
                ]); 
            }
        }

        $response->status = $status;
        $response->messages = "Successfull!";
        return FacadeResponse::json($response);
    }

    public function submitSelectionExamResult(Request $request)
    {
        $response = new ResponseObject;
        $exam = SelectionTest::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;

        $resultRevisionExam = ResultSelectionTest::create([
            "user_id" => $request->user_id,
            "selection_test_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);

        foreach ($request->answers as $ans) {
            ResultSelectionTestAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_selection_test_id" => $resultRevisionExam->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);

            $question = SelectionTestQuestion::where('id', $ans['question_id'])->select(
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
        ResultSelectionTest::where('id', $resultRevisionExam->id)->update([
            "mark" => $mark,
            "total_positive_count" => $count,
            "total_negetive_count" => $negCount,
            "total_positive_marks" => $count * $exam->positive_mark,
            "total_negetive_marks" => $negCount * $exam->negative_mark,
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

    public function selectionTestWrittenSubmit(Request $request)
    {
        $response = new ResponseObject;

        if(!$request->user_id || !$request->exam_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Check ID";
            $response->result = [];
            return FacadeResponse::json($response);
        }

        if(!sizeof($request->answers)){
            $response->status = $response::status_fail;
            $response->messages = "Please, attach answer!";
            $response->result = [];
            return FacadeResponse::json($response); 
        }

        ResultSelectionTestWrittenAnswer::where("selection_test_id", $request->exam_id)->where('user_id', $request->user_id)->delete();
        
        foreach ($request->answers as $ans) {
            ResultSelectionTestWrittenAnswer::create([
                "user_id" => $request->user_id,
                "selection_test_id" => $request->exam_id,
                "selection_test_written_question_id" => $ans["question_id"],
                "answer" => $ans["answer"]
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function jwtTest(Request $request){
        $response = new ResponseObject;

        $response->status = $response::status_ok;
        $response->messages = "JWT Successfull!";
        return FacadeResponse::json($response);
    }

    public function getAllSelectionTestList(Request $request)
    {
        $response = new ResponseObject;

        $new_time = date("Y-m-d H:i:s", strtotime('+6 hours'));
        $examList = SelectionTest::orderby('id', 'desc')->get();

        foreach ($examList as $exam) 
        {
            if($exam->appeared_to < $new_time){
                $exam->finished = true;
            }else{
                $exam->finished = false;
            }

            $exam->total_participated = SelectionTestQuota::where('selection_test_id', $exam->id)->get()->count();
        }

        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $examList;
        return FacadeResponse::json($response);
    }

    public function deleteSelectionTest(Request $request)
    {
        $response = new ResponseObject;
        $selection_test_id = $request->id ? $request->id : 0;

        if(!$selection_test_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Enter ID";
            return FacadeResponse::json($response);
        }

        $total_participant = SelectionTestQuota::where('selection_test_id', $selection_test_id)->get()->count();

        if($total_participant){
            $response->status = $response::status_fail;
            $response->messages = "There are some participant already participated on this eam. You can not delete this exam.";
            return FacadeResponse::json($response);
        }

        $SelectionTest = SelectionTest::where('id', $selection_test_id)->first();
        if (!$SelectionTest) {
            $response->status = $response::status_fail;
            $response->messages = "No Selection Test found";
            return FacadeResponse::json($response);
        }

        $SelectionTest->delete();

        $response->status = $response::status_ok;
        $response->messages = "Selection Test has been deleted";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function getAllSelectionTestQuotaList(Request $request)
    {
        $response = new ResponseObject;
        $selection_test_id = $request->id ? $request->id : 0;

        if(!$selection_test_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Enter ID";
            return FacadeResponse::json($response);
        }

        $participant = SelectionTestQuota::select(
            "selection_test_quotas.*",
            'users.name',
            'users.user_code',
            'users.email',
            'users.mobile_number',
            'users.address'
        )
        ->leftJoin('users', 'users.id', 'selection_test_quotas.user_id')
        ->where('selection_test_id', $selection_test_id)
        ->get();

        foreach ($participant as $item) {
            $user_id = $item->user_id;
            $result = ResultSelectionTest::where('selection_test_id', $selection_test_id)->where('user_id', $user_id)->orderby('id', 'desc')->first();
            if(!empty($result)){
                $item->exam_mark = $result->total_mark;
                $item->achieved = $result->mark;
            }else{
                $item->exam_mark = 0;
                $item->achieved = 0;
            }
        }

        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $participant;
        return FacadeResponse::json($response);
    }

    public function updateQuota(Request $request)
    {
        $response = new ResponseObject;
        $quota_id = $request->id ? $request->id : 0;

        if(!$quota_id || !$request->quota){
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details!";
            return FacadeResponse::json($response);
        }

        SelectionTestQuota::where("id", $quota_id)->update([
            "quota" => $request->quota,
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Quota updated successful.";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function getSelectionTestAllQuestionList(Request $request)
    {
        $response = new ResponseObject;
        $selection_test_id = $request->selection_test_id;

        if(!$selection_test_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Check ID";
            return FacadeResponse::json($response);
        }

        $questionList = SelectionTestQuestion::where('selection_test_id', $selection_test_id)->get();
        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $questionList;
        return FacadeResponse::json($response);
    }

    public function getSelectionTestDetails(Request $request)
    {
        $response = new ResponseObject;
        $selection_test_id = $request->selection_test_id;

        if(!$selection_test_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Check ID";
            return FacadeResponse::json($response);
        }
        $SelectionTest = SelectionTest::where('id', $selection_test_id)->first();
        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $SelectionTest;
        return FacadeResponse::json($response);
    }

    public function deleteSelectionTestQuestion(Request $request)
    {
        $response = new ResponseObject;
        $question_id = $request->id ? $request->id : 0;

        if(!$question_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Enter ID";
            return FacadeResponse::json($response);
        }

        $is_question_exist = SelectionTestQuestion::where('id', $question_id)->first();

        $selection_test_id = $is_question_exist->selection_test_id;

        if(empty($is_question_exist)){
            $response->status = $response::status_fail;
            $response->messages = "No question found.";
            return FacadeResponse::json($response);
        }

        $SelectionTestQuestion = SelectionTestQuestion::where('id', $question_id)->first();
        if (empty($SelectionTestQuestion)) {
            $response->status = $response::status_fail;
            $response->messages = "No Question found";
            return FacadeResponse::json($response);
        }

        $SelectionTestQuestion->delete();

        $total_question_no = SelectionTestQuestion::where('selection_test_id', $selection_test_id)->get()->count();

        SelectionTest::where('id', $selection_test_id)->update([
            "question_number" => $total_question_no,
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Question has been deleted successful.";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function uploadSelectionTestQuestion(Request $request)
    {
        $response = new ResponseObject;
        $selection_test_id = $request->selection_test_id ? $request->selection_test_id : 0;

        $questions = $request->questions;

        if(!$selection_test_id || !sizeof($questions)){
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        foreach ($questions as $item) 
        {
            SelectionTestQuestion::create([
                'selection_test_id' => $selection_test_id,
                'question' => $item['question'],
                'option1' => $item['option1'] ? $item['option1'] : '',
                'option2' => $item['option2'] ? $item['option2'] : '',
                'option3' => $item['option3'] ? $item['option3'] : '',
                'option4' => $item['option4'] ? $item['option4'] : '',
                'option5' => $item['option5'] ?? '',
                'option6' => $item['option6'] ?? '',
                'correct_answer' => $item['correct_answer'] ?? NULL,
                'correct_answer2' => $item['correct_answer2'] ?? NULL,
                'correct_answer3' => $item['correct_answer3'] ?? NULL,
                'correct_answer4' => $item['correct_answer4'] ?? NULL,
                'correct_answer5' => $item['correct_answer5'] ?? NULL,
                'correct_answer6' => $item['correct_answer6'] ?? NULL,
                'explanation_text' => $item['explanation_text'] ?? '',
            ]);
        }

        $total_question_no = SelectionTestQuestion::where('selection_test_id', $selection_test_id)->get()->count();

        SelectionTest::where('id', $selection_test_id)->update([
            "question_number" => $total_question_no,
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Question has been uploaded successful.";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function addSelectionTestQuestion(Request $request)
    {
        $response = new ResponseObject;

        $selection_test_id = $request->selection_test_id ? $request->selection_test_id : 0;

        if(!$selection_test_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        SelectionTestQuestion::create([
            'selection_test_id' => $selection_test_id,
            'question' => $request->question,
            'option1' => $request->option1 ? $request->option1 : '',
            'option2' => $request->option2 ? $request->option2 : '',
            'option3' => $request->option3 ? $request->option3 : '',
            'option4' => $request->option4 ? $request->option4 : '',
            'option5' => $request->option5 ?? '',
            'option6' => $request->option6 ?? '',
            'correct_answer' => $request->correct_answer ?? NULL,
            'correct_answer2' => $request->correct_answer2 ?? NULL,
            'correct_answer3' => $request->correct_answer3 ?? NULL,
            'correct_answer4' => $request->correct_answer4 ?? NULL,
            'correct_answer5' => $request->correct_answer5 ?? NULL,
            'correct_answer6' => $request->correct_answer6 ?? NULL,
            'explanation_text' => $request->explanation_text ?? '',
        ]);

        $total_question_no = SelectionTestQuestion::where('selection_test_id', $selection_test_id)->get()->count();

        SelectionTest::where('id', $selection_test_id)->update([
            "question_number" => $total_question_no,
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Question has been added successful.";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function updateSelectionTestQuestion(Request $request)
    {
        $response = new ResponseObject;
        $question_id = $request->id;

        if(!$question_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, select question";
            return FacadeResponse::json($response);
        }

        SelectionTestQuestion::where('id', $question_id)->update([
            'question' => $request->question,
            'option1' => $request->option1 ? $request->option1 : '',
            'option2' => $request->option2 ? $request->option2 : '',
            'option3' => $request->option3 ? $request->option3 : '',
            'option4' => $request->option4 ? $request->option4 : '',
            'option5' => $request->option5 ?? '',
            'option6' => $request->option6 ?? '',
            'correct_answer' => $request->correct_answer ?? NULL,
            'correct_answer2' => $request->correct_answer2 ?? NULL,
            'correct_answer3' => $request->correct_answer3 ?? NULL,
            'correct_answer4' => $request->correct_answer4 ?? NULL,
            'correct_answer5' => $request->correct_answer5 ?? NULL,
            'correct_answer6' => $request->correct_answer6 ?? NULL,
            'explanation_text' => $request->explanation_text ?? '',
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Question has been updated successful.";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function getSelectionTestResult(Request $request)
    {

        $response = new ResponseObject;
        $selection_test_id = $request->id ? $request->id : 0;

        if(!$selection_test_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Enter ID";
            return FacadeResponse::json($response);
        }

        $selection_test = SelectionTest::where('id', $selection_test_id)->first();

        $participant = SelectionTestQuota::select(
            "selection_test_quotas.*",
            'users.name',
            'users.user_code',
            'users.email',
            'users.mobile_number',
            'users.address'
        )
        ->leftJoin('users', 'users.id', 'selection_test_quotas.user_id')
        ->where('selection_test_id', $selection_test_id)
        ->get();

        $titles = ["Name", "Phone", "Email", "Test Name", "Total Mark", "Positive Mark", "Negative Mark", "Achieved Mark", "Wrong Answered", "Right Answered", "Total Negative Marks", "Total Positive Mark", "Date"];
        
        $writtenQuestionList = SelectionTestWrittenQuestion::where('selection_test_id', $selection_test_id)->get();

        foreach ($writtenQuestionList as $written_item) {
            array_push($titles, $written_item->question);
        }

        $gap = [];
        for($i=1; $i<=sizeof($titles);$i++){
            array_push($gap, "");
        }

        $data_array = array(
            $titles, $gap
        );

        foreach ($participant as $item) {
            $user_id = $item->user_id;
            $result = ResultSelectionTest::where('selection_test_id', $selection_test_id)->where('user_id', $user_id)->orderby('id', 'desc')->first();
            
            $total_positive_count = 0;
            $total_negetive_count = 0;
            $total_positive_marks = 0;
            $total_negetive_marks = 0;

            $achieved = 0;
            $participation_date = '';
            if(!empty($result)){
                $achieved = $result->mark ? $result->mark : 0;
                $participation_date = $result->created_at;

                $total_positive_count = $result->total_positive_count ? $result->total_positive_count : 0;
                $total_negetive_count = $result->total_negetive_count ? $result->total_negetive_count : 0;
                $total_positive_marks = $result->total_positive_marks ? $result->total_positive_marks : 0;
                $total_negetive_marks = $result->total_negetive_marks ? $result->total_negetive_marks : 0;
            }

            $written_answer = array(
                $item->name,
                $item->mobile_number,
                $item->email,
                $selection_test->exam_name,
                $selection_test->total_mark,
                $selection_test->positive_mark,
                $selection_test->negative_mark,
                $achieved,
                $total_negetive_count,
                $total_positive_count,
                $total_negetive_marks,
                $total_positive_marks,
                $participation_date,
            );
            $get_writen_answer = ResultSelectionTestWrittenAnswer::where("selection_test_id", $selection_test_id)->where('user_id', $user_id)->get();
            foreach ($get_writen_answer as $w_answer) {
                array_push($written_answer, $w_answer->answer);
            }

            $data_array[] = $written_answer;;
        }

        $export = new ExportSelectionTest($data_array);
        $time = date("Y-m-d h:i:s");
        return Excel::download($export, 'Selection Test Result - ' . $time . ' .xlsx');
    }

}
