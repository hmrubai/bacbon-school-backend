<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\ResultSubject;
use App\ResultChapter;
use App\ResultLecture;
use App\ResultSubjectAnswer;
use App\SubjectQuestion;
use App\SubjectExamQuestion;
use App\User;
use App\SubjectExam;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Excel;

class ResultSubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getExamHistoryByUserId($user_id)
    {
        $result = (object) [
            "subjects" => $this->getSubjectHistoryByUserId($user_id),
            "chapters" => $this->getChapterHistoryByUserId($user_id),
            "lecture" => $this->getLectureHistoryByUserId($user_id),
        ];
        return FacadeResponse::json($result);
    }
    public function getSubjectHistoryByUserId($user_id)
    {
        $resultHistory = ResultSubject::where('user_id', $user_id)
            ->join('subject_exams', 'result_subjects.subject_exam_id', 'subject_exams.id')
            ->join('courses', 'subject_exams.course_id', 'courses.id')
            ->join('subjects', 'subject_exams.subject_id', 'subjects.id')
            ->select(
                'result_subjects.id as id',
                'result_subjects.mark as mark',
                'result_subjects.total_mark as total_mark',
                'result_subjects.created_at as date',
                'courses.name as course_name',
                'courses.name_bn as course_name_bn',
                'subjects.name as subject_name',
                'subjects.name_bn as subject_name_bn',
                'subject_exams.exam_name as exam_name'
            )
            ->orderBy('subject_exams.course_id', 'asc')
            ->get();
        return $resultHistory;
    }
    public function getChapterHistoryByUserId($user_id)
    {
        $resultHistory = ResultChapter::where('user_id', $user_id)
            ->join('chapter_exams', 'result_chapters.chapter_exam_id', 'chapter_exams.id')
            ->join('courses', 'chapter_exams.course_id', 'courses.id')
            ->join('subjects', 'chapter_exams.subject_id', 'subjects.id')
            ->join('chapters', 'chapter_exams.chapter_id', 'chapters.id')
            ->select(
                'result_chapters.id as id',
                'result_chapters.mark as mark',
                'result_chapters.total_mark as total_mark',
                'result_chapters.created_at as date',
                'courses.name as course_name',
                'courses.name_bn as course_name_bn',
                'subjects.name as subject_name',
                'subjects.name_bn as subject_name_bn',
                'chapters.name as chapter_name',
                'chapters.name_bn as chapter_name_bn',
                'chapter_exams.exam_name as exam_name'
            )
            ->orderBy('chapter_exams.course_id', 'asc')
            ->get();
        return $resultHistory;
    }
    public function getLectureHistoryByUserId($user_id)
    {
        $resultHistory = ResultLecture::where('user_id', $user_id)
            ->join('lecture_exams', 'result_lectures.lecture_exam_id', 'lecture_exams.id')
            ->join('courses', 'lecture_exams.course_id', 'courses.id')
            ->join('subjects', 'lecture_exams.subject_id', 'subjects.id')
            ->join('chapters', 'lecture_exams.chapter_id', 'chapters.id')
            ->join('lecture_videos', 'lecture_exams.lecture_id', 'lecture_videos.id')
            ->select(
                'result_lectures.id as id',
                'result_lectures.mark as mark',
                'result_lectures.total_mark as total_mark',
                'result_lectures.created_at as date',
                'courses.name as course_name',
                'courses.name_bn as course_name_bn',
                'subjects.name as subject_name',
                'subjects.name_bn as subject_name_bn',
                'chapters.name as chapter_name',
                'chapters.name_bn as chapter_name_bn',
                'lecture_videos.title as lecture_name',
                'lecture_videos.title_bn as lecture_name_bn',
                'lecture_exams.exam_name as exam_name'
            )
            ->orderBy('lecture_exams.course_id', 'asc')
            ->get();
        return $resultHistory;
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
    public function submitSubjectExamResult(Request $request)
    {
        $response = new ResponseObject;
        $exam = SubjectExam::where('id', $request->exam_id)->first();
        $count = 0;
        $negCount = 0;

        $resultSubject = ResultSubject::create([
            "user_id" => $request->user_id,
            "subject_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);


        // foreach($request->answers as $ans) {
        //     ResultSubjectAnswer::insert([
        //         "question_id" => $ans['question_id'],
        //         "result_subject_id" => $resultSubject->id,
        //         "user_id" =>  $request->user_id,
        //         "answer" =>  $ans['answer']
        //     ]);
        //     $question = SubjectQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //     if ($ans['answer'] == $question->correct_answer )
        //         $count++;
        //     else
        //         $negCount++;
        // }


        foreach ($request->answers as $ans) {
            ResultSubjectAnswer::insert([
                "question_id" => $ans['question_id'],
                "result_subject_id" => $resultSubject->id,
                "user_id" =>  $request->user_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);
            $question = SubjectQuestion::where('id', $ans['question_id'])->select(
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



        ResultSubject::where('id', $resultSubject->id)->update(["mark" => $count * $exam->positive_mark - $negCount * $exam->negative_mark]);
        $obj = (object) [
            "user_id" => $request->user_id,
            "exam_id" => $request->exam_id,
            "points" => $count * $exam->positive_mark - $negCount * $exam->negative_mark
        ];

        $exam = SubjectExam::where('id', $request->exam_id)->first();
        $userResult = ResultSubject::where('id', $resultSubject->id)->first();
        // $this->saveSLCResult($obj);

        $verifyController = new VerifyCodeController();
        if ($exam->course_id == 9) {
            $this->sendResultInMail($userResult, $exam);
        }
        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";
        $response->result = $verifyController->getLoginData($request->user_id);

        return FacadeResponse::json($response);
    }




    public function saveSubjectExamResultWeb(Request $request)
    {
        $response = new ResponseObject;
        $exam = SubjectExam::where('id', $request->exam_id)->first();

        $count = 0;
        $negCount = 0;

        $resultSubject = ResultSubject::create([
            "user_id" => $request->user_id,
            "subject_exam_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->total_mark,
        ]);


        // foreach($request->answers as $ans) {
        //     if ($ans['answer']) {

        //         ResultSubjectAnswer::insert([
        //             "question_id" => $ans['question_id'],
        //             "result_subject_id" => $resultSubject->id,
        //             "user_id" =>  $request->user_id,
        //             "answer" =>  $ans['answer']
        //         ]);
        //         $question = SubjectQuestion::where('id', $ans['question_id'])->select('id', 'correct_answer')->first();
        //         if ($ans['answer'] == $question->correct_answer )
        //             $count++;
        //         else
        //             $negCount++;
        //     }
        // }



        foreach ($request->answers as $ans) {
            if ($ans['answer'] || $ans['answer2'] || $ans['answer3'] || $ans['answer4'] || $ans['answer5'] || $ans['answer6']) {
                ResultSubjectAnswer::insert([
                    "question_id" => $ans['question_id'],
                    "result_subject_id" => $resultSubject->id,
                    "user_id" =>  $request->user_id,
                    "answer" =>  $ans['answer'],
                    "answer2" =>  $ans['answer2'],
                    "answer3" =>  $ans['answer3'],
                    "answer4" =>  $ans['answer4'],
                    "answer5" =>  $ans['answer5'],
                    "answer6" =>  $ans['answer6']
                ]);
                $question = SubjectQuestion::where('id', $ans['question_id'])->select(
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
        }



        ResultSubject::where('id', $resultSubject->id)->update(["mark" => $count * $exam->positive_mark - $negCount * $exam->negative_mark]);
        $obj = (object) [
            "user_id" => $request->user_id,
            "exam_id" => $request->exam_id,
            "points" => $count * $exam->positive_mark - $negCount * $exam->negative_mark
        ];




        $exam = SubjectExam::where('id', $request->exam_id)->first();
        $userResult = ResultSubject::where('result_subjects.id', $resultSubject->id)
            ->join('subject_exams', 'result_subjects.subject_exam_id', 'subject_exams.id')
            ->select('result_subjects.*', 'subject_exams.exam_name', 'subject_exams.positive_mark', 'subject_exams.negative_mark', 'subject_exams.question_number')
            ->first();
        // $this->saveSLCResult($obj);

        if ($exam->course_id == 9) {
            $this->sendResultInMail($userResult, $exam);
        }
        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";

        $final_result = SubjectExamQuestion::join('subject_questions', 'subject_exam_questions.question_id', 'subject_questions.id')
            ->leftJoin('result_subject_answers', 'subject_questions.id', 'result_subject_answers.question_id')
            ->where('subject_exam_questions.exam_id', $request->exam_id)
            ->where('result_subject_answers.result_subject_id', $userResult->id)
            ->select(
                'subject_questions.*',
                'result_subject_answers.answer as given_answer',
                'result_subject_answers.answer2 as given_answer2',
                'result_subject_answers.answer3 as given_answer3',
                'result_subject_answers.answer4 as given_answer4',
                'result_subject_answers.answer5 as given_answer5',
                'result_subject_answers.answer6 as given_answer6'
                )
            ->get();
        $userResult->correct_answer_number = $count;
        $userResult->wrong_answer_number = $negCount;
        $userResult->questions = $final_result;


        $response->result = $userResult;
        // $response->result = $count * $exam->positive_mark - $negCount * $exam->negative_mark;

        return FacadeResponse::json($response);
    }







    public function saveSLCResult($data)
    {


        $exam = SubjectExam::where('id', $data->exam_id)->first();
        $resultSubject = ResultSubject::create([
            "user_id" => $data->user_id,
            "subject_exam_id" => $data->exam_id,
            "mark" => $data->points,
            "total_mark" => $exam->total_mark,
        ]);
        $this->sendResultInMail($resultSubject, $exam);
        return $resultSubject;
    }


    public function sendResultInMail($data, $exam)
    {
        $user = User::where('id', $data->user_id)->first();
        // Recipient
        $toEmail = 'hr@bacbonltd.com';

        // Sender
        $from = $user->email ? $user->email : 'slc@bacbonltd.com';
        $fromName = 'Bacbon School | SLC';

        // Subject
        $emailSubject = 'SLC Result of ' . $user->name;

        $htmlContent = '<html><body>';
        $htmlContent .= '<h2 style="background: #1d72ba; color: #fff; padding: 5px;">Result of SLC Quiz</h2>';
        $htmlContent .= '<p><b>Name:</b> ' . $user->name . '</p>';
        $htmlContent .= '<p><b>Phone number:</b> ' .  $user->mobile_number . '</p>';
        $htmlContent .= '<p><b>Email:</b> ' . $user->email . '</p>';
        $htmlContent .= '<p><b>Gender:</b> ' .  $user->gender . '</p>';
        $htmlContent .= '<p><b>Exam:</b> ' .  $exam->exam_name . '</p>';
        $htmlContent .= '<p><b>Total mark:</b> ' .  $data->total_mark . '</p>';
        $htmlContent .= '<p><b>Gained mark:</b> ' .  $data->mark . '</p>';

        $htmlContent .= '</body></html>';


        $headers = "From: $fromName" . " <" . $from . ">";
        $headers .= "\r\n" . "MIME-Version: 1.0";
        $headers .= "\r\n" . "Content-Type: text/html; charset=ISO-8859-1";
        $headers .= "Reply-To: " . $from . "\r\n";
        $headers .= "Return-Path: " . $from . "\r\n";

        return mail($toEmail, $emailSubject, $htmlContent, $headers);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ResultSubject  $resultSubject
     * @return \Illuminate\Http\Response
     */
    public function show(ResultSubject $resultSubject)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ResultSubject  $resultSubject
     * @return \Illuminate\Http\Response
     */
    public function edit(ResultSubject $resultSubject)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ResultSubject  $resultSubject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ResultSubject $resultSubject)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ResultSubject  $resultSubject
     * @return \Illuminate\Http\Response
     */
    public function getSLCStudents()
    {

        $students = ResultSubject::join('users', 'users.id', 'result_subjects.user_id')
            ->select('result_subjects.user_id', 'users.name', 'users.mobile_number', 'users.gender', 'users.email', 'result_subjects.mark', 'result_subjects.total_mark', 'result_subjects.created_at')
            ->get();

        $user_array = array(
            ["Name", "Phone", "Email", "Gender", "Mark", "Total Mark", "Date"],
            ["", "", "", "", "", "", ""]
        );

        foreach ($students as $student) {
            $user_array[] = array(
                $student->name,
                $student->mobile_number,
                $student->email,
                $student->gender,
                $student->mark,
                $student->total_mark,
                $student->created_at
            );
        }

        $export = new UserExport($user_array);
        $time = date("Y-m-d h:i:s");
        return Excel::download($export, 'SLC' . $time . 'users.xlsx');

        // return FacadeResponse::json($students);
    }
    public function getExcelLampByUniversity($shortName)
    {
        $universityController = new UniversityController();

        $university = $universityController->getUniversityByShortName($shortName);
        // return $university;
        $data = User::where('users.isLampFormSubmitted', true)
            ->where('users.university_id', $university->id)
            ->join('lamps', 'users.id', 'lamps.user_id')
            ->select(
                'users.name',
                'users.mobile_number',
                'users.gender',
                'users.email',
                'lamps.age',
                'lamps.passport',
                'lamps.organization',
                'lamps.reason',
                'lamps.background',
                'lamps.contributionProcess',
                'lamps.remark'
            )
            ->get();


        $user_array = array(
            ["Name", "Phone", "Email", "Gender", "Age", "Passport", "Organization", "Reason", "Background", "Contribution", "Remark"],
            ["", "", "", "", "", "", "", "", "", "", ""]
        );

        foreach ($data as $datum) {
            $user_array[] = array(
                $datum->name,
                $datum->mobile_number,
                $datum->email,
                $datum->gender,
                $datum->age,
                $datum->passport ? "Yes" : "No",
                $datum->organization,
                $datum->reason,
                $datum->background,
                $datum->contributionProcess,
                $datum->remark
            );
        }
        $export = new UserExport($user_array);
        $time = date("Y-m-d h:i:s");
        return Excel::download($export, $shortName . $time . 'users.xlsx');
    }
    public function destroy(ResultSubject $resultSubject)
    {
        //
    }

    public function uploadFromExcel(Request $request)
    {

        $path = $request->file('file')->getRealPath();

        // $data = Excel::import($path)->get();
        $data = Excel::import(new UsersImport, $request->file('file'));
        return FacadeResponse::json($data);
    }
}
