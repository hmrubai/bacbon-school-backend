<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use App\User;
use App\BscsExam;
use App\BscsExamQuestion;
use App\BscsResults;
use App\BscsAnswers;
use Illuminate\Http\Request;

class BscsResultsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitBscsExamResult(Request $request)
    {
        $response = new ResponseObject;
        $exam = BscsExam::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;

        $bscsExam = BscsResults::create([
            "user_id" => $request->user_id,
            "bscs_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);


        // foreach($request->answers as $ans) {
        //     BscsAnswers::insert([
        //         "bscs_result_id" => $bscsExam->id,
        //         "question_id" => $ans['question_id'],
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = BscsExamQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }


        foreach($request->answers as $ans) {
            BscsAnswers::insert([
                "bscs_result_id" => $bscsExam->id,
                "question_id" => $ans['question_id'],
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = BscsExamQuestion::where('id', $ans['question_id'])->select(
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

        BscsResults::where('id', $bscsExam->id)->update([
            "mark" => $mark
        ]);

        // $user = User::where('id', $request->user_id)->first();
        // $points = $mark + $user->points;
        // User::where('id', $request->user_id)->update(['points' => $points]);

        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";
        $verifyController = new VerifyCodeController();
        $response->result = $verifyController->getLoginData($request->user_id);
        $data = (Object) [
            "user_id" => $request->user_id,
            "total_mark" => $exam->total_mark,
            "mark" => $mark
        ];
        $this->sendResultInMail($data, $exam->exam_name);
        return FacadeResponse::json($response);
    }

    public function sendResultInMail($data, $exam_name) {
        $user = User::where('id', $data->user_id)->first();
        // Recipient
        $toEmail = 'hr@bacbonltd.com';

        // Sender
        $from = $user->email? $user->email: 'slc@bacbonltd.com';
        $fromName = 'Bacbon School | SLC';

        // Subject
        $emailSubject = 'SLC Result of ' . $user->name;

        $htmlContent = '<html><body>';
        $htmlContent .= '<h2 style="background: #1d72ba; color: #fff; padding: 5px;">Result of SLC Quiz</h2>';
        $htmlContent .= '<p><b>Name:</b> '. $user->name.'</p>';
        $htmlContent .= '<p><b>Phone number:</b> ' .  $user->mobile_number . '</p>';
        $htmlContent .= '<p><b>Email:</b> ' . $user->email . '</p>';
        $htmlContent .= '<p><b>Gender:</b> ' .  $user->gender . '</p>';
        $htmlContent .= '<p><b>Exam:</b> ' .  $exam_name . '</p>';
        $htmlContent .= '<p><b>Total mark:</b> ' .  $data->total_mark . '</p>';
        $htmlContent .= '<p><b>Gained mark:</b> ' .  $data->mark . '</p>';

        $htmlContent .= '</body></html>';


        $headers = "From: $fromName" . " <" . $from . ">";
        $headers .= "\r\n" . "MIME-Version: 1.0";
        $headers .= "\r\n" . "Content-Type: text/html; charset=ISO-8859-1";
        $headers .= "Reply-To: ". $from. "\r\n";
        $headers .= "Return-Path: ". $from. "\r\n";

        return mail($toEmail, $emailSubject, $htmlContent, $headers);
    }
}
