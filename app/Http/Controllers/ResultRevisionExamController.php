<?php

namespace App\Http\Controllers;


use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\ResultModelTest;

use App\ResultRevisionExam;
use App\ResultRevisionExamAnswer;
use App\RevisionExam;
use App\User;
use App\Subject;
use App\ReviewExamQuestion;
use Illuminate\Http\Request;

class ResultRevisionExamController extends Controller
{
    public function getEEducationUserResults(Request $request)
    {
        $list = [];
        $users = User::where('is_e_edu_5', true)->select('id', 'name', 'e_edu_id', 'mobile_number')->whereNotNull('e_edu_id')->get();
        foreach ($users as $user) {
            $user->result = $this->revisionExamResult($user->id, $request->examId);
            //   if ($user->result != null) {
            //       $list[] = $user;
            //   }
        }
        return FacadeResponse::json($users);
    }

    private function revisionExamResult($userId, $examId)
    {
        $exam = RevisionExam::where('id', $examId)->first();
        $user = User::where('users.id', $userId)
            ->leftJoin('subjects', 'users.c_unit_optional_subject_id', 'subjects.id')
            ->select('users.id', 'users.name', 'users.c_unit_optional_subject_id', 'subjects.name as subject_name')
            ->first();
        $subjects = ReviewExamQuestion::where('revision_exam_questions.revision_exam_id', $exam->id)
            // ->where('revision_exam_questions.subject_id', '!=', $user->c_unit_optional_subject_id)
            ->join('subjects', 'revision_exam_questions.subject_id', 'subjects.id')
            ->groupBy('revision_exam_questions.subject_id', 'subjects.name')
            ->select('revision_exam_questions.subject_id', 'subjects.name')
            ->get();
        // return FacadeResponse::json($subjects);
        $rs = ResultRevisionExam::where('result_revision_exams.user_id', $userId)->where('result_revision_exams.revision_exam_id', $examId)
            ->join('revision_exams', 'result_revision_exams.revision_exam_id', 'revision_exams.id')
            ->join('courses', 'revision_exams.course_id', 'courses.id')
            ->select(
                'result_revision_exams.id',
                'result_revision_exams.mark',
                'courses.name as course_name',
                'revision_exams.total_mark',
                'revision_exams.positive_mark',
                'revision_exams.negative_mark'
            )->orderBy('result_revision_exams.id', 'desc')->first();
        if (!is_null($rs)) {
            $gainMark = 0;
            $list = [];
            foreach ($subjects as $subject) {
                $answers = ResultRevisionExamAnswer::where('result_revision_exam_answers.result_revision_exam_id', $rs->id)
                    ->join('revision_exam_questions', 'result_revision_exam_answers.question_id', 'revision_exam_questions.id')
                    ->where('revision_exam_questions.subject_id', $subject->subject_id)
                    ->select(
                        'revision_exam_questions.id',
                        'revision_exam_questions.correct_answer',
                        'revision_exam_questions.correct_answer2',
                        'revision_exam_questions.correct_answer3',
                        'revision_exam_questions.correct_answer4',
                        'revision_exam_questions.correct_answer5',
                        'revision_exam_questions.correct_answer6',
                        'result_revision_exam_answers.answer',
                        'result_revision_exam_answers.answer2',
                        'result_revision_exam_answers.answer3',
                        'result_revision_exam_answers.answer4',
                        'result_revision_exam_answers.answer5',
                        'result_revision_exam_answers.answer6'
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRevisionExamResult($userId, $examId)
    {
        $exam = RevisionExam::where('id', $examId)->first();
        $user = User::where('users.id', $userId)
            ->leftJoin('subjects', 'users.c_unit_optional_subject_id', 'subjects.id')
            ->select('users.id', 'users.name', 'users.c_unit_optional_subject_id', 'subjects.name as subject_name')
            ->first();
        $subjects = ReviewExamQuestion::where('revision_exam_questions.revision_exam_id', $exam->id)
            // ->where('revision_exam_questions.subject_id', '!=', $user->c_unit_optional_subject_id)
            ->join('subjects', 'revision_exam_questions.subject_id', 'subjects.id')
            ->groupBy('revision_exam_questions.subject_id', 'subjects.name')
            ->select('revision_exam_questions.subject_id', 'subjects.name')
            ->get();
        // return FacadeResponse::json($subjects);
        $result = ResultRevisionExam::where('result_revision_exams.user_id', $userId)->where('result_revision_exams.revision_exam_id', $examId)
            ->join('revision_exams', 'result_revision_exams.revision_exam_id', 'revision_exams.id')
            ->join('courses', 'revision_exams.course_id', 'courses.id')
            ->select(
                'result_revision_exams.*',
                'courses.name as course_name',
                'revision_exams.exam_name',
                'revision_exams.exam_name_bn',
                'revision_exams.duration',
                'revision_exams.total_mark',
                'revision_exams.positive_mark',
                'revision_exams.negative_mark',
                'revision_exams.question_number_per_subject',
                'revision_exams.question_number'
            )
            ->with('questions')->get();
        foreach ($result as $rs) {
            $gainMark = 0;
            $list = [];
            foreach ($subjects as $subject) {
                $answers = ResultRevisionExamAnswer::where('result_revision_exam_answers.result_revision_exam_id', $rs->id)
                    ->join('revision_exam_questions', 'result_revision_exam_answers.question_id', 'revision_exam_questions.id')
                    ->where('revision_exam_questions.subject_id', $subject->subject_id)
                    ->select(
                        'revision_exam_questions.*',
                        'revision_exam_questions.correct_answer',
                        'revision_exam_questions.correct_answer2',
                        'revision_exam_questions.correct_answer3',
                        'revision_exam_questions.correct_answer4',
                        'revision_exam_questions.correct_answer5',
                        'revision_exam_questions.correct_answer6',
                        'result_revision_exam_answers.answer as given_answer',
                        'result_revision_exam_answers.answer2 as given_answer2',
                        'result_revision_exam_answers.answer3 as given_answer3',
                        'result_revision_exam_answers.answer4 as given_answer4',
                        'result_revision_exam_answers.answer5 as given_answer5',
                        'result_revision_exam_answers.answer6 as given_answer6'
                        )
                    ->get();
                $gainMarks = 0;
                $negativeMarks = 0;
                $totalMarks = 0;
                $totalQuestion = 0;
                foreach ($answers as $answer) {
                    $totalMarks += $rs->positive_mark;

                    // if ($answer->given_answer != -1) {
                    //     if ($answer->given_answer == $answer->correct_answer) {
                    //         $gainMarks += $rs->positive_mark;
                    //     } else {
                    //         $negativeMarks += $rs->negative_mark;
                    //     }
                    // }


                    if ($answer->given_answer != -1) {

                        $given_answer_array = [];
                        if ($answer->given_answer) {
                            array_push($given_answer_array, $answer->given_answer);
                        }
                        if ($answer->given_answer2) {
                            array_push($given_answer_array, $answer->given_answer2);
                        }
                        if ($answer->given_answer3) {
                            array_push($given_answer_array, $answer->given_answer3);
                        }
                        if ($answer->given_answer4) {
                            array_push($given_answer_array, $answer->given_answer4);
                        }
                        if ($answer->given_answer5) {
                            array_push($given_answer_array, $answer->given_answer5);
                        }
                        if ($answer->given_answer6) {
                            array_push($given_answer_array, $answer->given_answer6);
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
                    "answers" => $answers,
                    "subject_name" => $subject->name,
                    "total_postive" => $gainMarks,
                    "total_negative" => $negativeMarks,
                    "mark" =>  $gainMarks - $negativeMarks,
                    "total_marks" => $totalMarks // $rs->question_number_per_subject * $rs->positive_mark
                ];
                $gainMark += ($gainMarks - $negativeMarks);
                $list[] = $subjectObj;
            }
            $rs->mark = $gainMark;
            $rs->optional_subject = $user->subject_name;
            $rs->subject_results = $list;
        }
        return FacadeResponse::json($result);
    }


    public function submitRevisionExamResult(Request $request)
    {
        $response = new ResponseObject;
        $exam = RevisionExam::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;

        $resultRevisionExam = ResultRevisionExam::create([
            "user_id" => $request->user_id,
            "revision_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);

        // foreach($request->answers as $ans) {
        //     ResultRevisionExamAnswer::insert([
        //         "question_id" => $ans['question_id'],
        //         "result_revision_exam_id" => $resultRevisionExam->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = ReviewExamQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }

        foreach ($request->answers as $ans) {
            ResultRevisionExamAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_revision_exam_id" => $resultRevisionExam->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = ReviewExamQuestion::where('id', $ans['question_id'])->select(
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
        ResultRevisionExam::where('id', $resultRevisionExam->id)->update([
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


    public function submitRevisionExamResultWeb(Request $request)
    {
        $response = new ResponseObject;
        $exam = RevisionExam::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;

        $resultRevisionExam = ResultRevisionExam::create([
            "user_id" => $request->user_id,
            "revision_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);

        // foreach($request->answers as $ans) {
        //     ResultRevisionExamAnswer::insert([
        //         "question_id" => $ans['question_id'],
        //         "result_revision_exam_id" => $resultRevisionExam->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = ReviewExamQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }


        foreach ($request->answers as $ans) {
            ResultRevisionExamAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_revision_exam_id" => $resultRevisionExam->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = ReviewExamQuestion::where('id', $ans['question_id'])->select(
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

        ResultRevisionExam::where('id', $resultRevisionExam->id)->update([
            "mark" => $mark
        ]);

        $user = User::where('id', $request->user_id)->first();
        $points = $mark + $user->points;
        User::where('id', $request->user_id)->update(['points' => $points]);


        $exam = RevisionExam::where('id', $request->exam_id)->first();

        $userResult = ResultRevisionExam::where('result_revision_exams.id', $resultRevisionExam->id)
            ->join('revision_exams', 'result_revision_exams.revision_exam_id', 'revision_exams.id')
            ->select('result_revision_exams.*', 'revision_exams.exam_name', 'revision_exams.positive_mark', 'revision_exams.negative_mark', 'revision_exams.question_number')
            ->first();

        $final_result = ReviewExamQuestion::leftJoin('result_revision_exam_answers', 'revision_exam_questions.id', 'result_revision_exam_answers.question_id')
            ->where('revision_exam_questions.revision_exam_id', $request->exam_id)
            ->where('result_revision_exam_answers.result_revision_exam_id', $userResult->id)
            ->select(
                'revision_exam_questions.*',
                'result_revision_exam_answers.answer as given_answer',
                'result_revision_exam_answers.answer2 as given_answer2',
                'result_revision_exam_answers.answer3 as given_answer3',
                'result_revision_exam_answers.answer4 as given_answer4',
                'result_revision_exam_answers.answer5 as given_answer5',
                'result_revision_exam_answers.answer6 as given_answer6'
            )
            ->get();
        $userResult->correct_answer_number = $count;
        $userResult->wrong_answer_number = $negCount;
        $userResult->questions = $final_result;


        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";
        $response->result = $userResult;

        return FacadeResponse::json($response);
    }

    public function getParticipatedUserNumber()
    {
        $list = ResultRevisionExam::join('users', 'result_revision_exams.user_id', 'users.id')
            // ->where('users.is_e_edu_5', true)
            // ->where('users.is_chandpur', true)
            ->whereDate('result_revision_exams.created_at', date("2020-11-22"))
            ->whereIn('result_revision_exams.revision_exam_id', [7, 13, 14])
            ->select('users.id', 'users.name', 'users.e_edu_id')
            ->groupBy('users.id', 'users.name', 'users.e_edu_id')
            ->get();
        return FacadeResponse::json($list);
    }


    public function getRevisionExamResultTest($userId, $examId)
    {
        $exam = RevisionExam::where('id', $examId)->first();
        $user = User::where('users.id', $userId)
            ->leftJoin('subjects', 'users.c_unit_optional_subject_id', 'subjects.id')
            ->select('users.id', 'users.name', 'users.c_unit_optional_subject_id', 'subjects.name as subject_name')
            ->first();
        $subjects = ReviewExamQuestion::where('revision_exam_questions.revision_exam_id', $exam->id)
            // ->where('revision_exam_questions.subject_id', '!=', $user->c_unit_optional_subject_id)
            ->join('subjects', 'revision_exam_questions.subject_id', 'subjects.id')
            ->groupBy('revision_exam_questions.subject_id', 'subjects.name')
            ->select('revision_exam_questions.subject_id', 'subjects.name')
            ->get();
        return FacadeResponse::json($subjects);
        $result = ResultRevisionExam::where('result_revision_exams.user_id', $userId)->where('result_revision_exams.revision_exam_id', $examId)
            ->join('revision_exams', 'result_revision_exams.revision_exam_id', 'revision_exams.id')
            ->join('courses', 'revision_exams.course_id', 'courses.id')
            ->select(
                'result_revision_exams.*',
                'courses.name as course_name',
                'revision_exams.exam_name',
                'revision_exams.exam_name_bn',
                'revision_exams.duration',
                'revision_exams.total_mark',
                'revision_exams.positive_mark',
                'revision_exams.negative_mark',
                'revision_exams.question_number_per_subject',
                'revision_exams.question_number'
            )
            ->with('questions')->get();
        foreach ($result as $rs) {
            $gainMark = 0;
            $list = [];
            foreach ($subjects as $subject) {
                $answers = ResultRevisionExamAnswer::where('result_revision_exam_answers.result_revision_exam_id', $rs->id)
                    ->join('revision_exam_questions', 'result_revision_exam_answers.question_id', 'revision_exam_questions.id')
                    ->where('revision_exam_questions.subject_id', $subject->subject_id)
                    ->select(
                        'revision_exam_questions.id',
                        'revision_exam_questions.correct_answer',
                        'revision_exam_questions.correct_answer2',
                        'revision_exam_questions.correct_answer3',
                        'revision_exam_questions.correct_answer4',
                        'revision_exam_questions.correct_answer5',
                        'revision_exam_questions.correct_answer6',
                        'result_revision_exam_answers.answer',
                        'result_revision_exam_answers.answer2',
                        'result_revision_exam_answers.answer3',
                        'result_revision_exam_answers.answer4',
                        'result_revision_exam_answers.answer5',
                        'result_revision_exam_answers.answer6'
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
                    "answers" => $answers,
                    "subject_name" => $subject->name,
                    "total_postive" => $gainMarks,
                    "total_negative" => $negativeMarks,
                    "mark" =>  $gainMarks - $negativeMarks,
                    "total_marks" => $totalMarks // $rs->question_number_per_subject * $rs->positive_mark
                ];
                $gainMark += ($gainMarks - $negativeMarks);
                $list[] = $subjectObj;
            }
            $rs->mark = $gainMark;
            $rs->optional_subject = $user->subject_name;
            $rs->subject_results = $list;
        }
        return FacadeResponse::json($result);
    }

    public function getParticipatedUserIds(Request $request)
    {
        $userIds = ResultRevisionExam::where('revision_exam_id', $request->examId)->distinct('user_id')->pluck('user_id')->toArray();
        $results = [];
        foreach ($userIds as $userId) {
            $results[] = ResultRevisionExam::where('user_id', $userId)->where('revision_exam_id', $request->examId)->orderBy('result_revision_exams.id', 'desc')
                ->join('users', 'result_revision_exams.user_id', 'users.id')
                ->select(
                    'result_revision_exams.id',
                    'result_revision_exams.mark',
                    'result_revision_exams.total_mark',
                    'users.name',
                    'users.image'
                )
                ->first();
        }

        $keys = array_column($results, 'mark');

        array_multisort($keys, SORT_DESC, $results);

        return FacadeResponse::json($results);
    }

    //     public function getParticipatedUserIds (Request $request) {
    //     $userIds = ResultModelTest::where('model_test_id', $request->examId)->distinct('user_id')->pluck('user_id')->toArray();
    //     $results = [];
    //     foreach ($userIds as $userId) {
    //         $results[] = ResultModelTest::where('user_id', $userId)->where('model_test_id', $request->examId)->orderBy('result_model_tests.id', 'desc')
    //         ->join('users','result_model_tests.user_id' ,'users.id')
    //         ->select(
    //             'result_model_tests.id',
    //             'result_model_tests.mark',
    //             'result_model_tests.total_mark',
    //             'users.name',
    //             'users.image'
    //         )
    //         ->first();
    //     }

    //     $keys = array_column($results, 'mark');

    //     array_multisort($keys, SORT_DESC, $results);

    //     return FacadeResponse::json($results);
    // }

}
