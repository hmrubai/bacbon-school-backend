<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use App\RevisionExam;
use App\ReviewExamQuestion;
use App\Chapter;
use App\ChapterExam;
use App\ChapterQuestion;
use App\CourseSubject;
use App\RevisionSubjectQuestionQuantilty;
use App\User;
use Illuminate\Http\Request;

class RevisionExamController extends Controller
{
    public function editRevisionQuestion () {
        $revisionQuestions = ReviewExamQuestion::limit(1000)->skip(6000)->get();
        $count = 0;
        foreach ($revisionQuestions as $rq) {
            $chQ = ChapterQuestion::where('question', 'LIKE' ,$rq->question)->first();
            if (!is_null($chQ)) {
                $rq->update([
                    "correct_answer" => $chQ->correct_answer,
                    "option1" => $chQ->option1,
                    "option2" => $chQ->option2,
                    "option3" => $chQ->option3,
                    "option4" => $chQ->option4,
                    "explanation_text" => $chQ->explanation_text,
                    ]);
                $count++;
            }
        }
        return FacadeResponse::json($count);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function totalQuestionNumber () {
        $chapters = Chapter::where('course_id', 27)->where('subject_id', 5)->with('exam', 'exam.questions')->get();
        $count = 0;
        foreach ($chapters as $chapter) {
            foreach ($chapter->exam as $ex) {
                // if (count($ex->questions) < 15) {

                // }
                foreach ($ex->questions as $question) {

                 $count++;
                }
            }
        }

        return FacadeResponse::json(" Total Questions: ". $count);
    }

    public function createRevisionTestLectureSheetWise(Request $request)
    {
        $response = new ResponseObject;
        $count = 0;
        // $chapters = Chapter::where('course_id', 27)
        // ->when($request->name, function ($query) use ($request){
        //     return $query->where('name', $request->name);
        // })
        // ->when($request->ids, function ($query) use ($request){
        //     return $query->whereIn('id', $request->ids);
        // })
        // ->with('exam', 'exam.questions')->get();

        // $revisionExam = RevisionExam::create([
        //     'course_id' => $request->course_id,
        //     'exam_name' => $request->revision_test_name,
        //     'exam_name_bn' => $request->revision_test_name_bn ? $request->revision_test_name_bn :$request->revision_test_name,
        //     'duration' => $request->duration,
        //     'positive_mark' => $request->positive_mark ? $request->positive_mark: 1,
        //     'negative_mark' => $request->negative_mark ? $request->negative_mark: 0,
        //     'total_mark' => $request->total_mark,
        //     'question_number' => $request->question_number,
        //     'question_number_per_subject' => $request->question_number_per_subject,
        //     'status' => $request->status,
        //     'week_number' => $request->week_number,
        //     'month_number' => $request->month_number,
        //     'type' => $request->type,
        //     'unit' => $request->unit,
        // ]);


        // foreach ($chapters as $chapter) {
        //     foreach ($chapter->exam as $ex) {
        //         foreach ($ex->questions as $question) {
        //             $count++;
        //             ReviewExamQuestion::create([
        //                 'revision_exam_id' => $revisionExam->id,
        //                 'subject_id' => $chapter->subject_id,
        //                 'question' => $question->question,
        //                 'option1' => $question->option1,
        //                 'option2' => $question->option2,
        //                 'option3' => $question->option3,
        //                 'option4' => $question->option4,
        //                 'correct_answer' => $question->correct_answer,
        //                 'explanation' => $question->explanation,
        //                 'explanation_text' => $question->explanation_text,
        //                 'status' => $question->status
        //             ]);
        //         }
        //     }
        // }
        if ($request->examIds) {
            $chapExams = ChapterExam::whereIn('id', $request->examIds)->with('questions')->get();
            foreach ($chapExams as $ex) {
                foreach ($ex->questions as $question) {
                    ReviewExamQuestion::create([
                        'revision_exam_id' => 114,
                        'subject_id' => $ex->subject_id,
                        'question' => $question->question,
                        'option1' => $question->option1,
                        'option2' => $question->option2,
                        'option3' => $question->option3,
                        'option4' => $question->option4,
                        'correct_answer' => $question->correct_answer,
                        'explanation' => $question->explanation,
                        'explanation_text' => $question->explanation_text,
                        'status' => $question->status
                    ]);

                    ReviewExamQuestion::create([
                        'revision_exam_id' => 115,
                        'subject_id' => $ex->subject_id,
                        'question' => $question->question,
                        'option1' => $question->option1,
                        'option2' => $question->option2,
                        'option3' => $question->option3,
                        'option4' => $question->option4,
                        'correct_answer' => $question->correct_answer,
                        'explanation' => $question->explanation,
                        'explanation_text' => $question->explanation_text,
                        'status' => $question->status
                    ]);
                }
            }
        }

        $response->status = $response::status_ok;
        $response->messages = "Review test has been created == " . $count;
        return FacadeResponse::json($response);
    }





    public function getWeeklyTestList(Request $request)
    {
        $unit = $request->unit;
        if ($request->userId) {
            $userId = $request->userId;
            $unit = $request->unit;
            $user = User::where('id', $userId)->select('id', 'is_e_edu_c_unit', 'c_unit_start_date', 'b_unit_start_date', 'd_unit_start_date', 'is_e_edu_5')->first();
            if (!is_null($user)) {
                $startDate = null;
                $todate = date('Y-m-d');
                $today = date_create($todate);
                if ($user->is_e_edu_5) {
                    $startDate= date_create("2020-11-14");
                } else {
                    if ($request->unit == "c")
                        $startDate= date_create($user->c_unit_start_date);
                    else if ($request->unit == "b")
                        $startDate= date_create($user->b_unit_start_date);
                    else
                        $startDate= date_create($user->d_unit_start_date);
                }
                $dayDiffers = date_diff($today, $startDate);
                $joinedWeek = (int)($dayDiffers->days / 7);
                $examList = RevisionExam::where('type', 'Weekly')->where('unit', $unit)->where('week_number', "<=" , $joinedWeek)->get();
                return FacadeResponse::json($examList);
            } else {
                $examList = [];
                return FacadeResponse::json($examList);
            }
        }
        $examList = RevisionExam::where('type', 'Weekly')->where('unit', $unit)->get();
        return FacadeResponse::json($examList);
    }


    public function getMonthlyTestList(Request $request)
    {
        $unit = $request->unit;
        if ($request->userId) {
            $userId = $request->userId;
            $user = User::where('id', $userId)->select('id', 'is_e_edu_c_unit', 'c_unit_start_date', 'b_unit_start_date', 'd_unit_start_date')->first();
            if (!is_null($user)) {
                $startDate = null;
                $todate = date('Y-m-d');
                $today = date_create($todate);
                if ($request->unit == "c")
                    $startDate= date_create($user->c_unit_start_date);
                else if ($request->unit == "b")
                    $startDate= date_create($user->b_unit_start_date);
                else
                    $startDate= date_create($user->d_unit_start_date);
                $dayDiffers = date_diff($today, $startDate);
                $joinedMonth = (int)($dayDiffers->m);
                $examList = RevisionExam::where('type', 'Monthly')->where('unit', $unit)->where('month_number', "<=" , $joinedMonth)->get();
                return FacadeResponse::json($examList);
            } else {
                $examList = [];
                return FacadeResponse::json($examList);
            }
        }
        $examList = RevisionExam::where('type', 'Monthly')->where('unit', $unit)->get();
        return FacadeResponse::json($examList);
    }

    /**
     * Show the form for revision test.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRevisionTestList(Request $request)
    {
        $unit = $request->unit ? $request->unit : 'c';
        if ($request->userId) {
            $userId = $request->userId;
            $user = User::where('id', $userId)->select('id', 'is_e_edu_c_unit', 'c_unit_start_date')->first();
            if (!is_null($user)) {
                $examList = RevisionExam::where('type', 'Revision')->where('unit', $unit)->get();
                return FacadeResponse::json($examList);
            } else {
                $examList = [];
                return FacadeResponse::json($examList);
            }
        }
        $examList = RevisionExam::where('type', 'Revision')->where('unit', $unit)->get();
        return FacadeResponse::json($examList);
    }

    /**
     * Get exam list by type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function getTestListByType(Request $request)
    {
        $notice = "";
        $unit = 'c';
        if ($request->unit) {
            $unit = $request->unit;
        }
        if ($request->userId) {
            $userId = $request->userId;
            $user = User::where('id', $userId)->select('id', 'is_e_edu_c_unit', 'c_unit_start_date', 'b_unit_start_date', 'd_unit_start_date')->first();

            $examList = [];
            $todate = date('Y-m-d');
            $today = date_create($todate);

            $startDate = date_create($user->c_unit_start_date);
            if ($request->unit == 'b') {
                $startDate = date_create($user->b_unit_start_date);
            } else if ($request->unit == 'd') {
                $startDate = date_create($user->d_unit_start_date);
            }

            $dayDiffers = date_diff($today, $startDate);
            if (!is_null($user)) {
                if ($request->type == "Revision") {
                    $examList = RevisionExam::where('type', $request->type)->where('unit', $unit)->get();
                    $notice = "Revision test is available for you.";
                    foreach ($examList as $exam) {
                        $exam->details_url = "api/getReviewExamQuestionsById/". $exam->id.'/'.$userId;
                    }
                }
                else if ($request->type == "Weekly") {
                    // $joinedWeek = (int)($dayDiffers->days / 7);
                    $new_time = date("Y-m-d H:i:s", strtotime('+6 hours'));
                    // $new_time = date("Y-m-d H:i:s");
                    $examList = RevisionExam::where('type', $request->type)->where('unit', $unit)->where('appeared_from', "<=" ,$new_time)->get();
                    $notice = "Weekly test will be appeared 20 Nov, 2020";
                    foreach ($examList as $exam) {
                        $exam->details_url = "api/getReviewExamQuestionsById/". $exam->id.'/'.$userId;
                    }
                }
                else if ($request->type == "Monthly") {
                    $joinedMonth = (int)($dayDiffers->m);
                    $examList = RevisionExam::where('type', $request->type)->where('unit', $unit)->where('month_number', "<=" ,$joinedMonth)->get();
                    $notice = "Monthly test will be appeared 12 Dec, 2020";
                    foreach ($examList as $exam) {
                        $exam->details_url = "api/getReviewExamQuestionsById/". $exam->id.'/'.$userId;
                    }
                } else if ($request->type == "Model") {
                    $modelTestController = new ModelTestController();
                    $examList = $modelTestController->getModelTestListByType($userId, $unit);
                    $notice = "Model test will be appeared next week";
                }

            }
        } else {
            $examList = RevisionExam::where('type', $request->type)->get();
        }

        $obj = (Object) [
            "notice" => $notice,
            "records" => $examList
        ];
        return FacadeResponse::json($obj);

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getReviewExamQuestionsById($examId, $userId)
    {
        // $user = User::where('id', $userId)->select('id', 'c_unit_optional_subject_id')->first();
        // $exam = RevisionExam::where('id', $examId)->first();
        // $subjectIdList = CourseSubject::where('subject_id', '!=',$user->c_unit_optional_subject_id)->where('course_id', 27)->select('subject_id as id')->get();
        // $questions = [];
        // foreach ($subjectIdList as $subject) {
        //     $list = ReviewExamQuestion::where('revision_exam_id', $examId)
        //     ->where('subject_id', $subject->id)
        //     ->inRandomOrder(time())
        //     ->limit($exam->question_number_per_subject)
        //     ->get();
        //     foreach ($list as $item) {
        //         $questions[] = $item;
        //     }
        // }
        // shuffle($questions);

        // $questions = ReviewExamQuestion::where('revision_exam_id', $examId)
        // ->inRandomOrder(time())
        // ->limit($exam->question_number)
        // ->get();

        $user = User::where('id', $userId)->select('id', 'c_unit_optional_subject_id')->first();
        $exam = RevisionExam::where('id', $examId)->first();
        $courseId = 27;
        if ($exam->unit == "b")
            $courseId = 13;
        if ($exam->unit == "d")
            $courseId = 15;

        $questions = [];
        if ($exam->type != "Revision") {
            $subjectIdList = [];
            if ($courseId == 27) {
                $excluded = null;
                if ($user->c_unit_optional_subject_id == 18) {
                    $excluded = 39;
                }
                else if ($user->c_unit_optional_subject_id == 39) {
                    $excluded = 18;
                }
                $subjectIdList = CourseSubject::where('subject_id', '!=', $excluded)->where('course_id', $courseId)->select('subject_id as id')->get();
            }
            else
                $subjectIdList = CourseSubject::where('course_id', $courseId)->select('subject_id as id')->get();

            foreach ($subjectIdList as $subject) {
                // $list = ReviewExamQuestion::where('revision_exam_id', $examId)
                // ->where('subject_id', $subject->id)
                // ->inRandomOrder(time())
                // ->limit($exam->question_number_per_subject)
                // ->get();

                $quantity = RevisionSubjectQuestionQuantilty::where('subject_id', $subject->id)->where('revision_exam_id', $examId)->first();
                $questionNumber = is_null($quantity) ? $exam->question_number_per_subject : $quantity->question_number;
                $list = ReviewExamQuestion::where('revision_exam_id', $examId)
                ->where('subject_id', $subject->id)
                ->inRandomOrder(time())
                ->limit($questionNumber)
                ->get();
                foreach ($list as $item) {
                    $questions[] = $item;
                }
            }
        } else {
            $questions = ReviewExamQuestion::where('revision_exam_id', $examId)
            ->inRandomOrder(time())
            ->limit($exam->question_number)
            ->get();
        }
        $obj = (Object) [
            "data" => $questions,
            "submission_url" => "api/submitRevisionExamResult"
        ];
        return FacadeResponse::json($obj);
    }

    public function getReviewExamQuestionsByIdWeb($examId, $userId)
    {
        $user = User::where('id', $userId)->select('id', 'c_unit_optional_subject_id')->first();
        $exam = RevisionExam::where('id', $examId)->first();
        $courseId = 27;
        if ($exam->unit == "b")
            $courseId = 13;
        if ($exam->unit == "d")
            $courseId = 15;
        $questions = [];
        if ($exam->type != "Revision") {
            $subjectIdList = [];
            if ($courseId == 27) {
                $excluded = null;
                if ($user->c_unit_optional_subject_id == 18) {
                    $excluded = 39;
                }
                else if ($user->c_unit_optional_subject_id == 39) {
                    $excluded = 18;
                }
                $subjectIdList = CourseSubject::where('subject_id', '!=', $excluded)->where('course_id', $courseId)->select('subject_id as id')->get();
            }
            else
                $subjectIdList = CourseSubject::where('course_id', $courseId)->select('subject_id as id')->get();

            foreach ($subjectIdList as $subject) {
                // $list = ReviewExamQuestion::where('revision_exam_id', $examId)
                // ->where('subject_id', $subject->id)
                // ->inRandomOrder(time())
                // ->limit($exam->question_number_per_subject)
                // ->get();

                $quantity = RevisionSubjectQuestionQuantilty::where('subject_id', $subject->id)->where('revision_exam_id', $examId)->first();
                $questionNumber = is_null($quantity) ? $exam->question_number_per_subject : $quantity->question_number;
                $list = ReviewExamQuestion::where('revision_exam_id', $examId)
                ->where('subject_id', $subject->id)
                ->inRandomOrder(time())
                ->limit($questionNumber)
                ->get();
                foreach ($list as $item) {
                    $questions[] = $item;
                }
            }
        } else {
            $questions = ReviewExamQuestion::where('revision_exam_id', $examId)
            ->inRandomOrder(time())
            ->limit($exam->question_number)
            ->get();
        }
        $obj = (Object) [
            "data" => $questions,
            "exam_name" => $exam->exam_name,
            "duration" => $exam->duration,
            "question_number" => $exam->question_number,
            "total_mark" => $exam->total_mark,
            "submission_url" => "api/submitRevisionExamResult"
        ];
        return FacadeResponse::json($obj);
    }

    // public function getReviewExamQuestionsByIdWeb($examId, $userId)
    // {
    //     $exam = RevisionExam::where('id', $examId)->first();
    //     $questions = ReviewExamQuestion::where('revision_exam_id', $examId)
    //         ->inRandomOrder(time())
    //         ->limit($exam->question_number)
    //         ->get();
    //     $obj = (Object) [
    //         "data" => $questions,
    //         "exam_name" => $exam->exam_name,
    //         "duration" => $exam->duration,
    //         "question_number" => $exam->question_number,
    //         "total_mark" => $exam->total_mark,
    //         "submission_url" => "api/submitRevisionExamResult"
    //     ];
    //     return FacadeResponse::json($obj);
    // }


    public function show(RevisionExam $revisionExam)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RevisionExam  $revisionExam
     * @return \Illuminate\Http\Response
     */
    public function edit(RevisionExam $revisionExam)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RevisionExam  $revisionExam
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RevisionExam $revisionExam)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RevisionExam  $revisionExam
     * @return \Illuminate\Http\Response
     */
    public function destroy(RevisionExam $revisionExam)
    {
        //
    }
}
