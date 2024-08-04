<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use DB;
use App\ResultSubject;
use App\ResultLecture;
use App\ResultChapter;
use App\ResultModelTest;
use App\ResultLectureAnswer;
use App\LectureQuestion;
use App\LectureExamQuestion;
use App\SubjectExam;
use App\LectureExam;
use App\ResultReview;
use App\ResultRevisionExam;
use App\ResultReviewAnswer;
use App\ReviewExam;
use App\CourseSubject;
use App\User;
use App\Course;
use Illuminate\Http\Request;

class ResultLectureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLectureExamResult($userId, $examId)
    {


        $results = ResultLecture::where('result_lectures.user_id', $userId)->where('result_lectures.lecture_exam_id', $examId)
        ->join('lecture_exams', 'result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->join('courses', 'lecture_exams.course_id', 'courses.id')
        ->join('subjects', 'lecture_exams.subject_id', 'subjects.id')
        ->join('chapters', 'lecture_exams.chapter_id', 'chapters.id')
        ->select(
            'result_lectures.*',
            'courses.name as course_name',
            'subjects.name as subject_name',
            'chapters.name as chaptername',
            'lecture_exams.exam_name',
            'lecture_exams.exam_name_bn',
            'lecture_exams.duration',
            'lecture_exams.total_mark',
            'lecture_exams.positive_mark',
            'lecture_exams.negative_mark',
            'lecture_exams.question_number'
            )
            ->with('questions')->get();

            foreach ($results as $result) {
                $count = 0;
                $negCount = 0;

                $answers = ResultLectureAnswer::where('result_lecture_id',$result->id)->get();
                foreach ($answers as $ans) {

                        $question = LectureQuestion::where('id', $ans->question_id)->select(
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

                foreach ($result['questions'] as $que){
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
                    $_isSelectedOption2 = $que->given_answer2 == 2;
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


    public function getReviewExamResult($userId, $examId)
    {

        $results = ResultReview::where('result_reviews.user_id', $userId)->where('result_reviews.review_exam_id', $examId)
        ->join('review_exams', 'result_reviews.review_exam_id', 'review_exams.id')
        ->join('courses', 'review_exams.course_id', 'courses.id')
        ->join('subjects', 'review_exams.subject_id', 'subjects.id')
        ->select(
            'result_reviews.*',
            'courses.name as course_name',
            'subjects.name as subject_name',
            'review_exams.exam_name',
            'review_exams.exam_name_bn',
            'review_exams.duration',
            'review_exams.total_mark',
            'review_exams.positive_mark',
            'review_exams.negative_mark',
            'review_exams.question_number'
            )
            ->with('questions')->get();

            foreach ($results as $result) {
                $count = 0;
                $negCount = 0;

                $answers = ResultReviewAnswer::where('result_review_id',$result->id)->get();
                foreach ($answers as $ans) {

                        $question = LectureQuestion::where('id', $ans->question_id)->select(
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

                foreach ($result['questions'] as $que){
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
                    $_isSelectedOption2 = $que->given_answer2 == 2;
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function submitLectureExamResult(Request $request)
    {
        $response = new ResponseObject;

        if ($request->type == "review") {
            return FacadeResponse::json($this->submitReviewExamResult($request));
        }
        $exam = LectureExam::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;
        $resultLecture = ResultLecture::create([
            "user_id" => $request->user_id,
            "lecture_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark
        ]);

        // foreach($request->answers as $ans) {
        //     ResultLectureAnswer::insert([
        //         "question_id" => $ans['question_id'],
        //         "result_lecture_id" => $resultLecture->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = LectureQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;

                foreach($request->answers as $ans) {
                    ResultLectureAnswer::insert([
                        "question_id" => $ans['question_id'],
                        "result_lecture_id" => $resultLecture->id,
                        "user_id" =>  $request->user_id,
                        "answer" =>  $ans['answer'],
                        "answer2" =>  $ans['answer2'],
                        "answer3" =>  $ans['answer3'],
                        "answer4" =>  $ans['answer4'],
                        "answer5" =>  $ans['answer5'],
                        "answer6" =>  $ans['answer6']
                    ]);
                    $question = LectureQuestion::where('id', $ans['question_id'])->select(
                        'id',
                        'correct_answer',
                        'correct_answer2',
                        'correct_answer3',
                        'correct_answer4',
                        'correct_answer5',
                        'correct_answer6'
                        )->first();

        ///////////////////given_answer///////////////////
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

        /////////////////////correct_answer///////////////
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

        ResultLecture::where('id', $resultLecture->id)->update([
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

    private function submitReviewExamResult ($request) {

        $response = new ResponseObject;
        $exam = ReviewExam::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;
        $resultReview = ResultReview::create([
            "user_id" => $request->user_id,
            "review_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark
        ]);

        // foreach($request->answers as $ans) {
        //     ResultReviewAnswer::create([
        //         "question_id" => $ans['question_id'],
        //         "result_review_id" => $resultReview->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = LectureQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }

        foreach($request->answers as $ans) {
            ResultReviewAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_review_id" => $resultReview->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = LectureQuestion::where('id', $ans['question_id'])->select(
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

        ResultReview::where('id', $resultReview->id)->update([
            "mark" => $mark
        ]);

        $user = User::where('id', $request->user_id)->first();
        $points = $mark + $user->points;
        User::where('id', $request->user_id)->update(['points' => $points]);

        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";
        $verifyController = new VerifyCodeController();
        $response->result = $verifyController->getLoginData($request->user_id);

        return $response;
    }

    public function submitReviewExamResults (Request $request) {

        $response = new ResponseObject;
        $exam = ReviewExam::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;
        $resultReview = ResultReview::create([
            "user_id" => $request->user_id,
            "review_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark
        ]);

        // foreach($request->answers as $ans) {
        //     ResultReviewAnswer::create([
        //         "question_id" => $ans['question_id'],
        //         "result_review_id" => $resultReview->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = LectureQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }



        foreach($request->answers as $ans) {
            ResultReviewAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_review_id" => $resultReview->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = LectureQuestion::where('id', $ans['question_id'])->select(
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

        ResultReview::where('id', $resultReview->id)->update([
            "mark" => $mark
        ]);

        $user = User::where('id', $request->user_id)->first();
        $points = $mark + $user->points;
        User::where('id', $request->user_id)->update(['points' => $points]);

        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";
        $response->result = $count * $exam->positive_mark - $negCount * $exam->negative_mark;

        return FacadeResponse::json($response);
    }

    public function saveLectureExamResultWeb(Request $request)
    {
        $response = new ResponseObject;
        $exam = LectureExam::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;
        $resultLecture = ResultLecture::create([
            "user_id" => $request->user_id,
            "lecture_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);

        // foreach($request->answers as $ans) {
        //     ResultLectureAnswer::insert([
        //         "question_id" => $ans['question_id'],
        //         "result_lecture_id" => $resultLecture->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = LectureQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }



        foreach($request->answers as $ans) {
            ResultLectureAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_lecture_id" => $resultLecture->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = LectureQuestion::where('id', $ans['question_id'])->select(
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

        ResultLecture::where('id', $resultLecture->id)->update([
            "mark" => $mark
        ]);


        $user = User::where('id', $request->user_id)->first();
        $points = $mark + $user->points;
        User::where('id', $request->user_id)->update(['points' => $points]);


        $exam = LectureExam::where('id', $request->exam_id)->first();
        $userResult = ResultLecture::where('result_lectures.id', $resultLecture->id)
        ->join('lecture_exams', 'result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->select('result_lectures.*', 'lecture_exams.exam_name', 'lecture_exams.positive_mark', 'lecture_exams.negative_mark', 'lecture_exams.question_number')
        ->first();


        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";


        $final_result = LectureExamQuestion::join('lecture_questions', 'lecture_exam_questions.question_id', 'lecture_questions.id')
        ->leftJoin('result_lecture_answers', 'lecture_questions.id', 'result_lecture_answers.question_id')
        ->where('lecture_exam_questions.exam_id', $request->exam_id)
        ->where('result_lecture_answers.result_lecture_id', $userResult->id)
        ->select(
            'lecture_questions.*',
            'result_lecture_answers.answer as given_answer',
            'result_lecture_answers.answer2 as given_answer2',
            'result_lecture_answers.answer3 as given_answer3',
            'result_lecture_answers.answer4 as given_answer4',
            'result_lecture_answers.answer5 as given_answer5',
            'result_lecture_answers.answer6 as given_answer6'
            )
        ->get();
        $userResult->correct_answer_number = $count;
        $userResult->wrong_answer_number = $negCount;
        $userResult->questions = $final_result;
        $response->result = $userResult;

        return FacadeResponse::json($response);
    }



    public function participatedQuizCountSubject ($courseId,$subjectId, $userId) {
        $sum = 0;
        $subject_count = ResultSubject::join('subject_exams','result_subjects.subject_exam_id', 'subject_exams.id')
        ->where('subject_exams.course_id', $courseId)
        ->where('subject_exams.subject_id', $subjectId)
        ->where('result_subjects.user_id', $userId)
        ->count();
        $sum += $subject_count;
        $lecture_count = ResultLecture::join('lecture_exams','result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->where('lecture_exams.course_id', $courseId)
        ->where('lecture_exams.subject_id', $subjectId)
        ->where('result_lectures.user_id', $userId)
        ->count();
        $sum += $lecture_count;
        $chapter_count = ResultChapter::join('chapter_exams','result_chapters.chapter_exam_id', 'chapter_exams.id')
        ->where('chapter_exams.course_id', $courseId)
        ->where('chapter_exams.subject_id', $subjectId)
        ->where('result_chapters.user_id', $userId)
        ->count();
        $sum += $chapter_count;
        return $sum;
    }

    public function participatedQuizCount ($courseId, $userId) {
        $sum = 0;
        $count = ResultLecture::join('lecture_exams','result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->where('lecture_exams.course_id', $courseId)
        ->where('result_lectures.user_id', $userId)
        ->count();
        $sum += $count;
        $chapter_count = ResultChapter::join('chapter_exams','result_chapters.chapter_exam_id', 'chapter_exams.id')
        ->where('chapter_exams.course_id', $courseId)
        ->where('result_chapters.user_id', $userId)
        ->count();

        $sum += $chapter_count;

        if ($courseId == 27 || $courseId == 13 || $courseId == 15 ) {
            $modelTestsCount = ResultModelTest::join('model_tests', 'result_model_tests.model_test_id', 'model_tests.id')
            ->where('result_model_tests.user_id', $userId)
            ->where('model_tests.course_id', $courseId)
            ->count();

            $sum += $modelTestsCount;


            $revisionTestsCount = ResultRevisionExam::join('revision_exams', 'result_revision_exams.revision_exam_id', 'revision_exams.id')
                                                ->where('result_revision_exams.user_id', $userId)
                                                ->where('revision_exams.course_id', $courseId)
                                                ->count();

            $sum += $revisionTestsCount;
        }
        return $sum;
    }

    public function participatedQuizResultPercentageSubject ($courseId,$subjectId, $userId) {
        $sum = 0;
        $lectureList = ResultLecture::join('lecture_exams','result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->where('lecture_exams.course_id', $courseId)
        ->where('lecture_exams.subject_id', $subjectId)
        ->where('result_lectures.user_id', $userId)
        ->get();
        foreach($lectureList as $item_lecture){
            $item_lecture->percentage = ($item_lecture->mark/$item_lecture->total_mark) * 100;
        }
        $sum += $lectureList->sum('percentage');
        $chapter_List = ResultChapter::join('chapter_exams','result_chapters.chapter_exam_id', 'chapter_exams.id')
        ->where('chapter_exams.course_id', $courseId)
        ->where('chapter_exams.subject_id', $subjectId)
        ->where('result_chapters.user_id', $userId)
        ->get();
        foreach($chapter_List as $item_chapter){
            $item_chapter->percentage = ($item_chapter->mark/$item_chapter->total_mark) * 100;
        }
        $sum += $chapter_List->sum('percentage');

        $subject_List = ResultSubject::join('subject_exams','result_subjects.subject_exam_id', 'subject_exams.id')
        ->where('subject_exams.course_id', $courseId)
        ->where('subject_exams.subject_id', $subjectId)
        ->where('result_subjects.user_id', $userId)
        ->get();
        foreach($subject_List as $item_subject){
            $item_subject->percentage = ($item_subject->mark/$item_subject->total_mark) * 100;
        }
        $sum += $subject_List->sum('percentage');

        return $sum;
    }

    public function participatedQuizResultPercentage ($courseId, $userId) {
        $sum = 0;
        $lectureList = ResultLecture::join('lecture_exams','result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->where('lecture_exams.course_id', $courseId)
        ->where('result_lectures.user_id', $userId)
        ->get();
        foreach($lectureList as $item){
            $item->percentage = ($item->mark/$item->total_mark) * 100;
        }
        $sum += $lectureList->sum('percentage');
        $chapter_List = ResultChapter::join('chapter_exams','result_chapters.chapter_exam_id', 'chapter_exams.id')
        ->where('chapter_exams.course_id', $courseId)
        ->where('result_chapters.user_id', $userId)
        ->get();
        foreach($chapter_List as $item){
            $item->percentage = ($item->mark/$item->total_mark) * 100;
        }
        $sum += $chapter_List->sum('percentage');

        if ($courseId == 27 || $courseId == 13 || $courseId == 15 ) {
            $modelTestsList = ResultModelTest::join('model_tests', 'result_model_tests.model_test_id', 'model_tests.id')
            ->where('result_model_tests.user_id', $userId)
            ->where('model_tests.course_id', $courseId)
            ->get();

            foreach($modelTestsList as $item){
                $item->percentage = ($item->mark/$item->total_mark) * 100;
            }

            $sum += $modelTestsList->sum('percentage');


            $revisionTestsList = ResultRevisionExam::join('revision_exams', 'result_revision_exams.revision_exam_id', 'revision_exams.id')
                                                ->where('result_revision_exams.user_id', $userId)
                                                ->where('revision_exams.course_id', $courseId)
                                                ->get();

            foreach($revisionTestsList as $item){
                $item->percentage = ($item->mark/$item->total_mark) * 100;
            }

            $sum += $revisionTestsList->sum('percentage');
        }
        return $sum;
    }

    public function participatedQuizParticipationDayWiseSubject ($courseId,$subjectId, $userId) {
        $sum = 0;
        $dayWiseParticipation = [];

        $subjectList = ResultSubject::join('subject_exams','result_subjects.subject_exam_id', 'subject_exams.id')
        ->select(DB::raw('DATE(result_subjects.created_at) as date'))
        ->where('subject_exams.course_id', $courseId)
        ->where('subject_exams.subject_id', $subjectId)
        ->where('result_subjects.user_id', $userId)
        ->get();

        foreach($subjectList as $subject_data){
            array_push($dayWiseParticipation, $subject_data);
        }

        $lectureList = ResultLecture::join('lecture_exams','result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->select(DB::raw('DATE(result_lectures.created_at) as date'))
        ->where('lecture_exams.course_id', $courseId)
        ->where('lecture_exams.subject_id', $subjectId)
        ->where('result_lectures.user_id', $userId)
        ->get();

        foreach($lectureList as $lecture_data){
            array_push($dayWiseParticipation, $lecture_data);
        }

        $chapter_List = ResultChapter::join('chapter_exams','result_chapters.chapter_exam_id', 'chapter_exams.id')
        ->select(DB::raw('DATE(result_chapters.created_at) as date'))
        ->where('chapter_exams.course_id', $courseId)
        ->where('chapter_exams.subject_id', $subjectId)
        ->where('result_chapters.user_id', $userId)
        ->get();

        foreach($chapter_List as $chapter_data){
            array_push($dayWiseParticipation, $chapter_data);
        }


        $date_array = [];
        foreach($dayWiseParticipation as $date){
            array_push($date_array, $date->date);
        }


        $date_count = [];
        foreach(array_count_values($date_array) as $key => $value){
            array_push($date_count, ['date' => $key, 'count' => $value]);
        }

        return $date_count;
    }

    public function participatedQuizParticipationDayWise ($courseId, $userId) {
        $sum = 0;
        $dayWiseParticipation = [];

        $subjectList = ResultSubject::join('subject_exams','result_subjects.subject_exam_id', 'subject_exams.id')
        ->select(DB::raw('DATE(result_subjects.created_at) as date'))
        ->where('subject_exams.course_id', $courseId)
        ->where('result_subjects.user_id', $userId)
        ->get();

        foreach($subjectList as $subject_data){
            array_push($dayWiseParticipation, $subject_data);
        }

        $lectureList = ResultLecture::join('lecture_exams','result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->select(DB::raw('DATE(result_lectures.created_at) as date'))
        ->where('lecture_exams.course_id', $courseId)
        ->where('result_lectures.user_id', $userId)
        ->get();

        foreach($lectureList as $lecture_data){
            array_push($dayWiseParticipation, $lecture_data);
        }

        $chapter_List = ResultChapter::join('chapter_exams','result_chapters.chapter_exam_id', 'chapter_exams.id')
        ->select(DB::raw('DATE(result_chapters.created_at) as date'))
        ->where('chapter_exams.course_id', $courseId)
        ->where('result_chapters.user_id', $userId)
        ->get();

        foreach($chapter_List as $chapter_data){
            array_push($dayWiseParticipation, $chapter_data);
        }


        if ($courseId == 27 || $courseId == 13 || $courseId == 15 ) {
            $modelTestsList = ResultModelTest::join('model_tests', 'result_model_tests.model_test_id', 'model_tests.id')
            ->select(DB::raw('DATE(result_model_tests.created_at) as date'))
            ->where('result_model_tests.user_id', $userId)
            ->where('model_tests.course_id', $courseId)
            ->get();

            foreach($modelTestsList as $model_data){
                array_push($dayWiseParticipation, $model_data);
            }


            $revisionTestsList = ResultRevisionExam::join('revision_exams', 'result_revision_exams.revision_exam_id', 'revision_exams.id')
                                                ->select(DB::raw('DATE(result_revision_exams.created_at) as date'))
                                                ->where('result_revision_exams.user_id', $userId)
                                                ->where('revision_exams.course_id', $courseId)
                                                ->get();

            foreach($revisionTestsList as $data){
                array_push($dayWiseParticipation, $data);
            }

        }

        $date_array = [];
        foreach($dayWiseParticipation as $date){
            array_push($date_array, $date->date);
        }


        $date_count = [];
        foreach(array_count_values($date_array) as $key => $value){
            array_push($date_count, ['date' => $key, 'count' => $value]);
        }

        return $date_count;
    }



    public function participatedIndividualQuizCount ($courseId, $userId) {
        $list = [];

        if ($courseId == 27 || $courseId == 13 || $courseId == 15 ) {
            $modelTests = ResultModelTest::join('model_tests', 'result_model_tests.model_test_id', 'model_tests.id')
            ->join('courses', 'model_tests.course_id', 'courses.id')
            ->where('result_model_tests.user_id', $userId)
            ->where('model_tests.course_id', $courseId)
            ->select(
                'model_tests.id',
                'model_tests.exam_name',
                'model_tests.exam_name_bn',
                'courses.name as course_name'
                )
            ->groupBy('id', 'exam_name', 'exam_name_bn', 'course_name')
            ->selectRaw('count(model_tests.id) as total_participation')
            ->get();

            foreach ($modelTests as $mtest) {
            $mtest->subject_name = null;
            $mtest->chapter_name = null;
            $mtest->type = 'model';
            $list[] = $mtest;
            }

            $revisionTests = ResultRevisionExam::join('revision_exams', 'result_revision_exams.revision_exam_id', 'revision_exams.id')
                                                ->join('courses', 'revision_exams.course_id', 'courses.id')
                                                ->where('result_revision_exams.user_id', $userId)
                                                ->where('revision_exams.course_id', $courseId)
                                                ->select(
                                                    'revision_exams.id',
                                                    'revision_exams.exam_name',
                                                    'revision_exams.exam_name_bn',
                                                    'courses.name as course_name'
                                                    )
                                                ->groupBy('id', 'exam_name', 'exam_name_bn', 'course_name')
                                                ->selectRaw('count(revision_exams.id) as total_participation')
                                                ->get();

            foreach ($revisionTests as $test) {
                $test->subject_name = null;
                $test->chapter_name = null;
                $test->type = 'revision';
                $list[] = $test;
            }

        }
        $lectureQuizs = ResultLecture::join('lecture_exams','result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->join('courses','lecture_exams.course_id', 'courses.id')
        ->join('subjects','lecture_exams.subject_id', 'subjects.id')
        ->join('chapters','lecture_exams.chapter_id', 'chapters.id')
        ->where('lecture_exams.course_id', $courseId)
        ->where('result_lectures.user_id', $userId)
        ->select(
            'lecture_exams.id',
            'lecture_exams.exam_name',
            'lecture_exams.exam_name_bn',
            'courses.name as course_name',
            'subjects.name as subject_name',
            'chapters.name as chapter_name'
            )
        ->groupBy('id', 'exam_name', 'exam_name_bn', 'course_name', 'subject_name', 'chapter_name')
        ->selectRaw('count(result_lectures.id) as total_participation')
        ->get();
            foreach ($lectureQuizs as $lq) {
                $lq->type = 'lecture';
                $list[] = $lq;
            }
        $quizes = ResultChapter::join('chapter_exams','result_chapters.chapter_exam_id', 'chapter_exams.id')
        ->join('courses','chapter_exams.course_id', 'courses.id')
        ->join('subjects','chapter_exams.subject_id', 'subjects.id')
        ->join('chapters','chapter_exams.chapter_id', 'chapters.id')
        ->where('chapter_exams.course_id', $courseId)
        ->where('result_chapters.user_id', $userId)
        ->select(
            'chapter_exams.id',
            'chapter_exams.exam_name',
            'chapter_exams.exam_name_bn',
            'courses.name as course_name',
            'subjects.name as subject_name',
            'chapters.name as chapter_name'
            )
        ->groupBy('id', 'exam_name', 'exam_name_bn', 'course_name', 'subject_name', 'chapter_name')
        ->selectRaw('count(result_chapters.id) as total_participation')
        ->get();
        foreach ($quizes as $quiz) {
            $quiz->type = 'chapter';
            $list[] = $quiz;
        }

        return $list;
    }

    public function getCourseQuizHistory ($userId) {
        $courses = Course::get();
        $courseList = [];
        foreach ($courses as $course) {
            $total = $this->participatedQuizCount($course->id, $userId);
            if ($total) {
                $course->participation_count =  $this->participatedQuizCount($course->id, $userId);
                $course->participation_history =  $this->participatedIndividualQuizCount($course->id, $userId);
                $courseList[] = $course;
            }
        }
        return $courseList;
    }


    public function getCourseQuizHistoryForChart ($userId) {
        $courses = Course::get();
        $courseList = [];
        foreach ($courses as $course) {
            $total = $this->participatedQuizCount($course->id, $userId);
            if ($total) {

                $subject_list = CourseSubject::join('subjects', 'course_subjects.subject_id', 'subjects.id')
                ->where('course_subjects.course_id',$course->id)->select('subjects.id','subjects.name')->get();

                    $subject_array = [];
                    foreach ($subject_list as $subject) {
                        $subject_total = $this->participatedQuizCountSubject($course->id,$subject->id, $userId);
                        if($subject_total){
                            $subject->participation_count = $this->participatedQuizCountSubject($course->id,$subject->id, $userId);
                            $subject->result_percentage = $this->participatedQuizResultPercentageSubject($course->id, $subject->id, $userId) /  $this->participatedQuizCountSubject($course->id,$subject->id, $userId);
                            $subject->participation_history =  $this->participatedQuizParticipationDayWiseSubject($course->id, $subject->id, $userId);
                            $subject_array[] = $subject;
                        }
                    }
                $course->subject_wise = $subject_array;
                $course->participation_count =  $this->participatedQuizCount($course->id, $userId);
                $course->result_percentage =  $this->participatedQuizResultPercentage($course->id, $userId) /  $this->participatedQuizCount($course->id, $userId);
                $course->participation_history =  $this->participatedQuizParticipationDayWise($course->id, $userId);
                $courseList[] = $course;
            }
        }
        return $courseList;
    }

    public function resultDetails ($examId, $userId) {
        $result = ResultLecture::where('lecture_exam_id', $examId)->where('user_id', $userId)->get()->last();
        $answers =  ResultLectureAnswer::where('result_lecture_id', $result->id)->get();

        foreach ($answers as $answer) {
            $answer['question'] =  LectureQuestion::where('id', $answer->question_id)->get();
        }

        $result->answers =  $answers;

         return FacadeResponse::json($result);
    }

}
