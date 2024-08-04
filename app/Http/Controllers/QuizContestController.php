<?php

namespace App\Http\Controllers;

use App\QuizContest;
use App\QuizContestAnswer;
use Validator;
use Carbon\Carbon;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseObject;

class QuizContestController extends Controller
{

    public function getDailyQuizList () {
        $quizList = QuizContest::orderBy('id', 'desc')->get();
        return FacadeResponse::json($quizList);
    }

    public function getContestDetailsAdmin ($id) {
        $quiz = QuizContest::where('id', $id)->with(['correctAnswers', 'wrongAnswers', 'winner'])->first();
        return FacadeResponse::json($quiz);

    }

    public function getContestQuiz(Request $request)
    {
        return FacadeResponse::json($this->getQuizDetails($request->userId));
    }

    public function getQuizDetails ($userId) {

        $current_date_time = Carbon::now();
        $current_date_time->add(6, 'hour');

        $year = $current_date_time->year;
        $month = $current_date_time->month;


        $isEligible = true;
        $givenAnswer = null;
        $is_applied = false;
        $winner = null;
        $submittedAnswer = QuizContestAnswer::where('user_id', $userId)->whereDate('created_at', $current_date_time)->first();
        if (!is_null($submittedAnswer)) {
            $givenAnswer = $submittedAnswer->given_answer;
            $is_applied = true;
        }

        $contest = QuizContest::whereDate('contest_date', $current_date_time)->first();
        if (is_null($contest))
            $isEligible = false;
        if ($current_date_time->hour > 21) {

            $winner = QuizContestAnswer::whereDate('quiz_contest_answers.created_at', $current_date_time)->where('is_winner', true)
            ->join('users', 'quiz_contest_answers.user_id', 'users.id')
                ->select('quiz_contest_answers.id', 'quiz_contest_answers.user_id', 'quiz_contest_answers.winner_cover_image', 'users.name', 'users.image as profile_pic')
            ->first();
            if (is_null($winner)) {

              $answer = QuizContestAnswer::whereDate('created_at', $current_date_time)->where('is_correct', true)->inRandomOrder(time())->first();

               if (is_null($answer)) {

                   $winner = (Object) [
                       "id" => 0,
                       "user_id" => 0,
                       "winner_cover_image" => null,
                       "name" => "No Winner Found",
                       "profile_pic" => 0,
                       ];

               } else {

                ////////////// check winner /////////////
                $check_winner_count = QuizContestAnswer::where('user_id',$answer->user_id)
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->where('is_winner',true)->count();

                if($check_winner_count > 0){
                    $answer = QuizContestAnswer::whereNotIn('user_id',[$answer->user_id])->whereDate('created_at', $current_date_time)->where('is_correct', true)->inRandomOrder(time())->first();
                }
                ////////////// check winner /////////////


                $answer->update(['is_winner' => true]);

                $winner = QuizContestAnswer::whereDate('quiz_contest_answers.created_at', $current_date_time)->where('is_winner', true)
                ->join('users', 'quiz_contest_answers.user_id', 'users.id')
                ->select('quiz_contest_answers.id', 'quiz_contest_answers.user_id', 'quiz_contest_answers.winner_cover_image as profile_pic', 'users.name', 'users.image ')
                ->first();
               }
            }

            $winner->profile_pic = $winner->profile_pic ? "https://api.bacbonschool.com/uploads/userImages/".$winner->profile_pic : null ;
        }
           return $result = array (
                'user_id' => (int)$userId,
                'given_answer' => $givenAnswer,
                'is_applied' => $is_applied,
                "promo_image" => "http://api.bacbonschool.com/uploads/images/img_mogoj_dholai_promo.png",
                'is_eligible' => $isEligible,
                'contest' => !is_null($contest) ? $contest : null,
                'winner' => !is_null($winner) ? $winner : null
                );

    }

    public function storeDailyQuiz(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->all();
        $validator = Validator::make($data, [
            'question' => 'required',
            'option1' => 'required',
            'option2' => 'required',
            'option3' => 'required',
            'option4' => 'required',
            'correct_answer' => 'required|numeric',
            'contest_date' => 'required',
            'prize_amount' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        if ($request->id) {

            $isExist = QuizContest::whereDate('contest_date', $request->contest_date)->where('id', "!=", $request->id)->count();
            if ($isExist) {
                $response->status = $response::status_fail;
                $response->messages = "Already quiz is available for " . $request->contest_date;
                return FacadeResponse::json($response);
            }
            QuizContest::where('id', $request->id)->update($data);
        }
        else {

            $isExist = QuizContest::whereDate('contest_date', $request->contest_date)->count();
            if ($isExist) {
                $response->status = $response::status_fail;
                $response->messages = "Already quiz is available for " . $request->contest_date;
                return FacadeResponse::json($response);
            }
            QuizContest::create($data);
        }
        $response->status = $response::status_ok;
        $response->messages = "Quiz has been created";
        return FacadeResponse::json($response);
    }

    public function  getContestQuizWinnerList(){
        $users = QuizContest::join('quiz_contest_answers', 'quiz_contests.id', 'quiz_contest_answers.quiz_id')
        ->join('users', 'quiz_contest_answers.user_id', 'users.id')
        ->where('quiz_contest_answers.is_winner', true)
        ->select('quiz_contests.id','quiz_contests.contest_date','quiz_contests.question','users.name','users.mobile_number')
        ->orderBy('quiz_contests.contest_date', 'desc')
        ->get();
        return FacadeResponse::json($users);
    }

}


