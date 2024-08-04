<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\BscsWrittenExam;
use App\BscsWrittenExamAnswer;
use Illuminate\Http\Request;

class BscsWrittenExamController extends Controller
{


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function startBscsWrittenExam (Request $request)
    {
        $response = new ResponseObject;

        BscsWrittenExamAnswer::create([
            "user_id" => $request->user_id,
            "bscs_written_exam_id" => $request->written_exam_id,
            "start_time" => date("Y-m-d H:i:s"),
            "status" =>"started"
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Written exam successfully started";
        $bscsExam = new BscsExamController();
        $response->result = $bscsExam->getWrittenDetailsRaw($request->written_exam_id, $request->user_id);


        return FacadeResponse::json($response);
    }


    public function submitBscsWrittenExamAnswer (Request $request)
    {
        $response = new ResponseObject;

        $bscsExam = BscsWrittenExamAnswer::where('user_id', $request->user_id)
        ->where('bscs_written_exam_id', $request->written_exam_id)->update([
            "answer" => $request->answer,
            "end_time" => date("Y-m-d H:i:s"),
            "status" =>"submitted"
            ]);

        $response->status = $response::status_ok;
        $response->messages = "Written exam successfully submitted";
        $bscsExam = new BscsExamController();
        $response->result = $bscsExam->getWrittenDetailsRaw($request->written_exam_id, $request->user_id);


        return FacadeResponse::json($response);
    }





    // public function submitBscsExamResult (Request $request)
    // {
    //     $response = new ResponseObject;
    //     $exam = BscsExam::where('id', $request->exam_id)->first();
    //     $count = 0;
    //     $negCount = 0;

    //     $bscsExam = BscsResults::create([
    //         "user_id" => $request->user_id,
    //         "bscs_exam_id" => $request->exam_id,
    //         "mark" => 0,
    //         "total_mark" => $exam->total_mark,
    //     ]);
    //     foreach($request->answers as $ans) {
    //         BscsAnswers::insert([
    //             "question_id" => $ans['question_id'],
    //             "user_id" =>  $request->user_id,
    //             "answer" =>  $ans['answer']
    //         ]);
    //         $question = BscsExamQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
    //         if ($ans['answer'] == $question->correct_answer )
    //             $count++;
    //         else
    //             $negCount++;
    //     }

    //     $mark = $count * $exam->positive_mark - $negCount * $exam->negative_mark;

    //     BscsResults::where('id', $bscsExam->id)->update([
    //         "mark" => $mark
    //     ]);


    //     $response->status = $response::status_ok;
    //     $response->messages = "Thank you. Your result has been submitted";
    //     $verifyController = new VerifyCodeController();
    //     $response->result = $verifyController->getLoginData($request->user_id);

    //     return FacadeResponse::json($response);
    // }

}
