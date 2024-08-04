<?php

namespace App\Http\Controllers;

use App\QuizContestAnswer;
use App\QuizContest;

use Validator;
use Carbon\Carbon;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseObject;

class QuizContestAnswerController extends Controller
{

    public function submitContestQuizAnswer(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->all();
        $validator = Validator::make($data, [
            'user_id' => 'required',
            'quiz_id' => 'required',
            'given_answer' => 'required'

        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        $isExist = QuizContestAnswer::where('user_id', $request->user_id)->where('quiz_id', $request->quiz_id)->count();
        if ($isExist) {
            $response->status = $response::status_fail;
            $response->messages = "You have already submitted answer";
            return FacadeResponse::json($response);
        }
        $contest = QuizContest::where('id', $request->quiz_id)->first();

        $answer = QuizContestAnswer::create([
            "user_id" => $request->user_id,
            "quiz_id" => $request->quiz_id,
            "given_answer" => $request->given_answer,
            "is_correct" =>$contest->correct_answer == $request->given_answer ? true : false
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Thank you for participating";
        $quizContestController = new QuizContestController();
        $response->result = $quizContestController->getQuizDetails($request->user_id);
        return FacadeResponse::json($response);
    }


    public function uploadWinnerBannerImage(Request $request) {

        $response = new ResponseObject;

        if ($request->file) {
            $destinationPath = 'uploads/daily_quiz_banner/';
            $file = base64_decode($request->file);
            $ext = $request->ext;
            $fileName =  time(). $request->id .'.' . $ext;
            // $uploadedFile = $destinationPath.$fileName;
            $success = file_put_contents($destinationPath . $fileName, $file);

            QuizContestAnswer::where('id', $request->id)->update([
                "winner_cover_image" => url('/').'/'.$destinationPath.'/'.$fileName
            ]);

        $response->status = $response::status_ok;
        $response->messages = "Image has been uploaded";
        $response->result = null;
        }  else {

        $response->status = $response::status_fail;
        $response->messages = "No image has been selected";
        $response->result = null;
        }
        return FacadeResponse::json($response);
    }

    public function makeWinner (Request $request) {

        $response = new ResponseObject;
        $existingWinner = QuizContestAnswer::where('quiz_id', $request->quizId)->where('is_winner', true)->first();
        if (is_null($existingWinner)) {

            $answer = QuizContestAnswer::where('quiz_id', $request->quizId)->where('is_correct', true)->inRandomOrder(time())->first();
            $answer->update(['is_winner' => true]);

            $winner = QuizContestAnswer::where('quiz_id', $request->quizId)->where('is_winner', true)
            ->join('users', 'quiz_contest_answers.user_id', 'users.id')
            ->select('quiz_contest_answers.id', 'quiz_contest_answers.user_id', 'quiz_contest_answers.winner_cover_image', 'users.name', 'users.image as profile_pic')
            ->first();
            $response->status = $response::status_ok;
            $response->messages = "Winner has been selected";
            $response->result = $winner;
        } else {

            $response->status = $response::status_ok;
            $response->messages = "Winner is already created before";
            $response->result = $existingWinner;
        }
        return FacadeResponse::json($response);
    }

    public function deleteBannerImage (Request $request) {

        $response = new ResponseObject;
        $answer = QuizContestAnswer::where('id', $request->id)->first();

        $url = explode('https://api.bacbonschool.com/', $answer->winner_cover_image);

        unlink($url[1]);
        $answer->update([
            "winner_cover_image" => null
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Banner is deleted successfully";
        $response->result = null;
        return FacadeResponse::json($response);
    }
}
