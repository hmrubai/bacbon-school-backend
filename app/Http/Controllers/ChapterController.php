<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

use App\Custom\Common;

use Session;
use File;
use App\Chapter;
use App\ChapterExam;
use App\ChapterScript;
use App\LectureScript;
use App\ChapterExamQuestion;
use App\LectureVideo;
use App\PaymentLecture;
use App\Payment;
use App\ChapterQuestion;
use Carbon\Carbon;

use App\SubjectExamQuestion;
use App\SubjectQuestion;

use App\LectureQuestion;
use App\LectureExamQuestion;

class ChapterController extends Controller
{
    public function makeExamSequence (Request $request) {
        $exams = ChapterExam::where('course_id', $request->courseId)->where('subject_id', $request->subjectId)->orderBy('exam_name', 'asc')->get();
        $count = 0;
        foreach ($exams as $exam) {
            $exam->update([
                "sequence" => $count++
                ]);
        }
        return FacadeResponse::json($count);
    }

    public function getEnglishAdmissionQuestions () {
        $questions = ChapterQuestion::where('chapter_id', '>', 1100)->where('subject_id', 2)->get();
        return FacadeResponse::json($questions);
    }
    public function getChapterBySubjectCourse (Request $request) {
        $chapters = Chapter::where('course_id', $request->courseId)->where('subject_id', $request->subjectId)->with('scripts')->get();
        foreach ($chapters as $chapter) {
            foreach ($chapter->scripts  as $script) {
                ChapterScript::where('id', $script->id)->update([
                    "course_id" => $request->courseId
                    ]
                );
            }
        }

        return FacadeResponse::json($chapters);
    }
    public function getChapterExamIds (Request $request) {

        $chapterExamIds = Chapter::where('chapters.name', 'Lecture Sheet 1')
        ->where('chapters.course_id', 13)
        ->join('chapter_exams', 'chapters.id', 'chapter_exams.chapter_id')
        ->pluck('chapter_exams.id')
        ->toArray();

        return FacadeResponse::json($chapterExamIds);
    }
    public function makeSequence (Request $request) {
        $totalChapter = 0;
        $totalLecture = 0;
        $chapters = Chapter::where('course_id', $request->course_id)->where('subject_id', $request->subject_id)->with('videosNameAsc')->orderBy('name', 'asc')->get();
        foreach ($chapters as $chapter) {
            // $totalChapter++;
            // $chapter->update([
            //     'sequence' => $totalChapter
            // ]);
            $count = 0;
            foreach($chapter->videosNameAsc as $video) {
                $count++;
                $totalLecture++;
                LectureVideo::where('id', $video->id)->update([
                    'sequence' => $count
                ]);
            }
        }

        $response = new ResponseObject;

        $response->status = $response::status_ok;
        $response->messages = "Success";
        $response->result = $totalChapter." Chapters & Lectures ". $totalLecture;
        return FacadeResponse::json($response);
    }


    public function copyChapterWithLecture (Request $request) {
        $chapters = Chapter::where('course_id', $request->from_course_id)->where('subject_id', $request->from_subject_id)->with('videos', 'scripts', 'exam', 'exam.questionIds')->get();
        $count = 0;
        $totalLecture = 0;


        foreach ($chapters  as $chapter) {

            $count++;
            $code_number = str_pad( $count, 4, "0", STR_PAD_LEFT );
            $newChapter = Chapter::create([
                "name" => $chapter->name,
                "name_bn" => $chapter->name,
                "course_id" => $request->to_course_id,
                "subject_id" => $request->to_subject_id,
                "status" => "Available",
                "code" => 'CC'.$request->to_course_id.$request->to_subject_id.'0'.$code_number,
                "price" => 0,
                "sequence" => $chapter->sequence
            ]);
            $lectureCount = 0;
            foreach ($chapter->videos as $video) {


                $totalLecture++;

                $code_number = str_pad( $lectureCount++, 4, "0", STR_PAD_LEFT );
                $lecture = (array)[
                    "course_id" => $request->to_course_id,
                    "subject_id" => $request->to_subject_id,
                    "chapter_id" => $newChapter->id,
                    "title" => $video->title,
                    "title_bn" => $video->title_bn,
                    "description" => $video->description,
                    "price" => $video->price,
                    "url" =>  $video->url,
                    "full_url" => $video->full_url,
                    "thumbnail" => $video->thumbnail,
                    "status" =>  $video->status,
                    "code" =>  'LC'.$newChapter->id.'0'.$code_number ,
                    "isFree" => true,
                    "duration" => $video->duration,
                    "sequence" => $video->sequence
                ];
                $lecture = LectureVideo::create($lecture);
            }

            foreach ($chapter->scripts as $script) {
                $ch = (array) [
                    "subject_id" => $request->to_subject_id,
                    "chapter_id" => $newChapter->id,
                    "title" => $script->title,
                    "title_bn" => $script->title_bn,
                    "title_jp" => $script->title_jp,
                    "url" => $script->url,
                    "status" => "Available",
                    "is_premium" => false,
                    "price" => 0,
                    "price_text" => ''
                    ];

                $scrpt = ChapterScript::create($ch);
            }

            foreach ($chapter->examArray as $ex) {
                $examData = (array) [
                        "course_id" => $request->to_course_id,
                        "subject_id" => $request->to_subject_id,
                        "chapter_id" => $newChapter->id,
                        "exam_name" => $ex->exam_name,
                        "exam_name_bn" => $ex->exam_name_bn,
                        "exam_name_jp" => $ex->exam_name_jp,
                        "duration" => $ex->duration,
                        "positive_mark" => $ex->positive_mark,
                        "negative_mark" => $ex->negative_mark,
                        "total_mark" => $ex->total_mark,
                        "question_number" => $ex->question_number,
                        "status" => $ex->status
                    ];


                $exam = ChapterExam::create($examData);
                    foreach ($ex->questionIds as $question) {
                          ChapterExamQuestion::create([
                            "subject_id" => $request->to_subject_id,
                            "chapter_id" => $newChapter->id,
                            "exam_id" => $exam->id,
                            "question_id" => $question->question_id,
                            "status" => "Available",
                            ]);
                    }

            }
        }

        $response = new ResponseObject;

        $response->status = $response::status_ok;
        $response->messages = "Chapter has been created ". $count. " and lecture ". $totalLecture;
        return FacadeResponse::json($response);
    }

    public function getExamDetailById ($id) {

        return ChapterExam::where('id', $id)->with('questions')->select('id','exam_name', 'question_number', 'total_mark', 'positive_mark', 'negative_mark')->withCount('questions')->first();

    }
    public function getChapterDetailsChapterId ($id) {
        return Chapter::where('id', $id)->with('courses', 'subjects')->first();
    }

    public function getList () {
        return $this->returnThis();
        // $chapters = Chapter::with('lectureVideos')->get();
        // return $chapters;
    }
    public function returnThis () {
        return "Somthing ";
    }
    public function getExamListWithChapterDetailsByChapter($id) {
        $chapter = Chapter::where('id', $id)->with('examList', 'lectureVideos', 'scripts')->first();
        return $chapter;
    }
    public function getExamListByChapter($id) {
        $chapters = ChapterExam::where('chapter_id', $id)->get();
        return FacadeResponse::json($chapters);

    }

    public function updateChapter (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'name' => 'required|string|max:250'
        ]);

        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            // $response->messages = "Please fill up all required fields";
            return FacadeResponse::json($response);
        }
        $chapter = Chapter::where('id', $request->id)->first();
        $chapter->update([
            "name" => $request->name,
            "name_bn" => $request->name_bn,
            "price" => $request->price
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Chapter has been updated";
        return FacadeResponse::json($response);

    }
    public function storeChapter (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'course_id' => 'required',
            'subject_id' => 'required',
            'name' => 'required|string|max:250'
        ]);

        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            // $response->messages = "Please fill up all required fields";
            return FacadeResponse::json($response);
        }
        try {

            $courseController = new CourseController();
            $course = $courseController->courseDetail($data['course_id']);
            $courseName = str_replace(' ', '_', $course->name);
            $subjectController = new SubjectController();
            $subjectDetails = $subjectController->getSubjectDetailsById($data['subject_id']);

            $subjectName = str_replace(' ', '_', $subjectDetails->name);
            $chapterName = str_replace(' ', '_', $request->name);

            $path = 'uploads/' . $courseName . '/' . $subjectName . '/' . $chapterName;
            File::makeDirectory($path, $mode = 0777, true, true);


            $chapterSequence = 1;
            $lastChapter = Chapter::where('subject_id', $request->subject_id)->orderBy('id', 'desc')->first();
            if ($lastChapter) {
                $ar = explode( '0', $lastChapter->code);
                if (end($ar) == "") {
                    $chapterSequence = $ar[count($ar) -2] * 10 + 1;
                } else
                $chapterSequence = end($ar) + 1;
            }
            $code_number = str_pad( $chapterSequence, 4, "0", STR_PAD_LEFT );
            $data['status'] = "Available";
            $data['code'] = 'CC'.$request->course_id.$request->subject_id.'0'.$code_number ;
            $chapter = Chapter::create($data);
            $response->status = $response::status_ok;
            $response->messages = "Chapter has been saved";
            $response->result = $chapter;
            return FacadeResponse::json($response);

        } catch(Exception $e) {
            $response->status = $response::status_ok;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);
        }

    }

    public function storeChapterExam (Request $request) {

        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'subject_id' => 'required',
            'chapter_id' => 'required',
            'exam_name' => 'required|string|max:250|min:5',
            'duration' => 'required|numeric',
            'positive_mark' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'negative_mark' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'total_mark' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'question_number' => 'required|numeric',
            'status' => 'required|string',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            // $response->messages = "Please fill up all required fields";
            return FacadeResponse::json($response);
        }

        $exam = ChapterExam::create($data);



        $response->status = $response::status_ok;
        $response->messages = "Exam has been created";
        $response->result = $exam;
        return FacadeResponse::json($response);

    }


    public function storeChapterQuestions (Request $request) {

        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'subject_id' => 'required',
            'chapter_id' => 'required',
            'question' => 'required|string',
            'option1' => 'required|string',
            'option2' => 'required|string',
            'option2' => 'required|string',
            'option3' => 'required|string',
            'option4' => 'required|string'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            // $response->messages = "Please fill up all required fields";
            return FacadeResponse::json($response);
        }
        $data['status'] = "Available";

        $question = (array)[
            "subject_id" => $data['subject_id'],
            "chapter_id" => $data['chapter_id'],
            "exam_id" => $data['exam_id'],
            "question" => $data['question'],
            "option1" => $data['option1'],
            "option2" => $data['option2'],
            "option3" => $data['option3'],
            "option4" => $data['option4'],
            "option5" => $data['option5'] ?  $data['option5'] : null,
            "option6" => $data['option6'] ?  $data['option6'] : null,
            "correct_answer" => $data['correct_answer'] ? $data['correct_answer'] : null,
            "correct_answer2" => $data['correct_answer2'] ? $data['correct_answer2'] : null,
            "correct_answer3" => $data['correct_answer3'] ? $data['correct_answer3'] : null,
            "correct_answer4" => $data['correct_answer4'] ? $data['correct_answer4'] : null,
            "correct_answer5" => $data['correct_answer5'] ? $data['correct_answer5'] : null,
            "correct_answer6" => $data['correct_answer6'] ? $data['correct_answer6'] : null,
            "status" => $data['status'],
         ];

        $chapterQue = ChapterQuestion::create($question);


        $examQuestion = (array)[
            "subject_id" => $data['subject_id'],
            "chapter_id" => $data['chapter_id'],
            "exam_id" => $data['exam_id'],
            "question_id" => $chapterQue->id,
            "status" => $data['status']
         ];
        $this->storeChapterExamQuestions($examQuestion);


        $response->status = $response::status_ok;
        $response->messages = "Chapter question has been inserted";
        $response->result = $chapterQue;

        return FacadeResponse::json($response);
    }


    public function storeChapterExamQuestions ($data) {
        return ChapterExamQuestion::create($data);
    }


    public function getChapterExamDetailsByExamId ($id) {
        $exam = ChapterExam::where('id', $id)->first();
         $questions = ChapterExamQuestion::where('exam_id', $id)
         ->join('chapter_questions', 'chapter_exam_questions.question_id' ,'chapter_questions.id')
         ->select('chapter_questions.*')
         ->limit($exam->question_number)
         ->inRandomOrder()
         ->get();
         $exam->questions = $questions;
        return $exam;
    }


    public function getChapterExamQuestionsById(Request $request, $examId, $pageSize) {
        if (Session::get('session_rand')) {
            if((time() - Session::get('session_rand') > 3600)) {
                Session::put('session_rand', time());
            }
        }else{
            Session::put('session_rand', time());
        }

        $questions = ChapterExamQuestion::where('exam_id', $examId)
        ->join('chapter_questions','chapter_exam_questions.question_id','=', 'chapter_questions.id')
        ->select(
            'chapter_questions.id as id',
            'chapter_questions.question as question',
            'chapter_questions.option1 as option1',
            'chapter_questions.option2 as option2',
            'chapter_questions.option3 as option3',
            'chapter_questions.option4 as option4',
            'chapter_questions.option5 as option5',
            'chapter_questions.option6 as option6',
            'chapter_questions.explanation as explanation',
            'chapter_questions.explanation_text as explanation_text',
            'chapter_questions.correct_answer as correct_answer',
            'chapter_questions.correct_answer2 as correct_answer2',
            'chapter_questions.correct_answer3 as correct_answer3',
            'chapter_questions.correct_answer4 as correct_answer4',
            'chapter_questions.correct_answer5 as correct_answer5',
            'chapter_questions.correct_answer6 as correct_answer6'
            )

        ->inRandomOrder(Session::get('session_rand'))
        ->limit($pageSize)
        ->get();
        $obj = (Object) [
            "data" => $questions,
            "submission_url" => "api/submitChapterExamResult"
        ];

        return FacadeResponse::json($obj);
    }



    public function buyChapter (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'chapter_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }
        $result = $this->buyChapterPrivate($data);
        $response->status = $response::status_ok;
        $response->messages = $result? "Successfully bought" : "Something wrong";
        return FacadeResponse::json($response);
    }



    public function buyChapterPrivate ($data) {

        $paymentAmount = $data['amount'] + $data['discount'];
        $chapterPrice = $this->getChapterPrice($data['chapter_id'], $data['user_id']);


        $current_date_time = Carbon::now()->toDateTimeString();


        $previosPayment = Payment::where('user_id', $data['user_id'])
        ->orderBy('id', 'DESC')
        ->first();
        if ($previosPayment) {
            $lastDue = $previosPayment->due;
            $lastBalance = $previosPayment->balance;
            Payment::where('id', $previosPayment->id)->update([
                'due' => 0,
                'balance' => 0,
            ]);
        $due = $lastDue + ($chapterPrice - $paymentAmount) - $lastBalance;
        $balance = $lastBalance + ($paymentAmount - $chapterPrice);
        } else {
            $due = $chapterPrice - $paymentAmount;
            $balance = $paymentAmount - $chapterPrice;
        }

        // $paymentAmount += $balance;
        $paymentObj = (array)[
            "user_id" => $data['user_id'],
            "amount" => $data['amount'],
            "payment_method" => $data['payment_method'],
            "payment_date" => $current_date_time,
            "due" => $due > 0 ? $due : 0,
            "discount" => $data['discount'],
            "balance" => $balance > 0 ? $balance : 0,
         ];
        $payment = Payment::create($paymentObj);


        // Common Method ........... Here

        $paid = PaymentLecture::where('user_id', $data['user_id'])
        ->where('isPaid', true)->select('lecture_id')->get();
        $lectureListToPay = LectureVideo::where('chapter_id',  $data['chapter_id'])
        ->whereNotIn('id', $paid)->get();

        foreach($lectureListToPay as $lecture) {
            if ($paymentAmount) {
                $paymentAmountForLecture = 0;
                $pl = PaymentLecture::where('user_id', $data['user_id'])
                ->where('lecture_id', $lecture->id)
                ->first();
                if ($pl) {
                    if ($paymentAmount >= ($pl->actual_price - $pl->amount)) {
                        // $paymentAmountForLecture = $pl->actual_price;
                        PaymentLecture::where('id', $pl->id)->update([
                            // "amount" => $paymentAmountForLecture,
                            "isPaid" => true
                        ]);
                    }
                } else {

                    $paymentAmountForLecture = $paymentAmount > $lecture->price? $lecture->price : $paymentAmount;
                    $paymentLectureObj = (array)[
                        "user_id" => $data['user_id'],
                        "lecture_id" => $lecture->id,
                        "payment_id" => $payment->id,
                        "amount" => $paymentAmountForLecture,
                        "actual_price" =>$lecture->price,
                        "isPaid" => true,
                     ];
                     PaymentLecture::create($paymentLectureObj);
                }
            }
        }

        return true;
    }

    public function getChapterPrice($chapterId, $userId) {
        $sum = 0;
        $paid = PaymentLecture::where('user_id', $userId)
        ->where('isPaid', true)->select('lecture_id')->get();

        $lectureListToPay = LectureVideo::where('chapter_id',  $chapterId)
        ->whereNotIn('id', $paid)->get();
        $unPaid = 0;
        $partiallyPaid = 0;
        foreach($lectureListToPay as $lecture){
            $pl = PaymentLecture::where('user_id', $userId)
            ->where('lecture_id', $lecture->id)
            ->first();
            if ($pl) {
                $unPaid += $pl->actual_price - $pl->amount;
                $partiallyPaid += $pl->amount;
            }
        }

        $sum = $lectureListToPay->sum('price');
        return $sum - $partiallyPaid;
    }

    public function updateQuestions (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'id' => 'required',
            'subject_id' => 'required',
            'question' => 'required|string',
            'option1' => 'required|string',
            'option2' => 'required|string',
            'option3' => 'required|string',
            'option4' => 'required|string'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }


        $examQuestion = (array)[
            "question" => $data['question'],
            "option1" => $data['option1'],
            "option2" => $data['option2'],
            "option3" => $data['option3'],
            "option4" => $data['option4'],
            "option5" => $data['option5'] ?  $data['option5'] : null,
            "option6" => $data['option6'] ?  $data['option6'] : null,
            "correct_answer" => $data['correct_answer'] ? $data['correct_answer'] : null,
            "correct_answer2" => $data['correct_answer2'] ? $data['correct_answer2'] : null,
            "correct_answer3" => $data['correct_answer3'] ? $data['correct_answer3'] : null,
            "correct_answer4" => $data['correct_answer4'] ? $data['correct_answer4'] : null,
            "correct_answer5" => $data['correct_answer5'] ? $data['correct_answer5'] : null,
            "correct_answer6" => $data['correct_answer6'] ? $data['correct_answer6'] : null,
            "status" => $data['status'],
            "subject_id" => $data['subject_id'],
            "explanation_text" => $data['explanation_text'],
            "status" => $data['status']
        ];


        if ($data['chapter_id'] != null) {
            $examQuestion['chapter_id'] = $data['chapter_id'];
            if ($data['lecture_id']) {
                if ($request->file) {
                    $file = base64_decode($request->file);
                    $savedName = time().str_random(10).'.'.$request->ext;
                    $success = file_put_contents('uploads/question_explanation/'.$savedName, $file);
                    $examQuestion['explanation'] = $savedName;
                    $question = LectureQuestion::where('id', $request->id)->first();
                    if($question->explanation) {
                        unlink('uploads/question_explanation/'.$question->explanation);
                    }
                }

                $examQuestion['lecture_id'] = $data['lecture_id'];
                LectureQuestion::where('id', $request->id)->update($examQuestion);
            } else {

                if ($request->file) {
                    $file = base64_decode($request->file);
                    $savedName = time().str_random(10).'.'.$request->ext;
                    $success = file_put_contents('uploads/question_explanation/'.$savedName, $file);
                    $examQuestion['explanation'] = $savedName;
                    $question = ChapterQuestion::where('id', $request->id)->first();
                    if($question->explanation) {
                        unlink('uploads/question_explanation/'.$question->explanation);
                    }
                }

                ChapterQuestion::where('id', $request->id)->update($examQuestion);
            }
        } else {

            if ($request->file) {
                $file = base64_decode($request->file);
                $savedName = time().str_random(10).'.'.$request->ext;
                $success = file_put_contents('uploads/question_explanation/'.$savedName, $file);
                $examQuestion['explanation'] = $savedName;
                $question = SubjectQuestion::where('id', $request->id)->first();
                if($question->explanation) {
                    unlink('uploads/question_explanation/'.$question->explanation);
                }
            }
            SubjectQuestion::where('id', $request->id)->update($examQuestion);
        }


        $response->status = $response::status_ok;
        $response->messages = "Question has been updated";

        return FacadeResponse::json($response);
    }
    public function storeQuestions (Request $request) {

        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'subject_id' => 'required',
            'question' => 'required|string',
            'option1' => 'required|string',
            'option2' => 'required|string',
            'option3' => 'required|string',
            'option4' => 'required|string'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        $savedName = '';
        if ($request->file) {
            $file = base64_decode($request->file);
            $savedName = time().str_random(10).'.'.$request->ext;
            $success = file_put_contents('uploads/question_explanation/'.$savedName, $file);
        }


        $data['explanation'] = $savedName;

        $data['status'] = "Available";
        $examQuestion = (array)[
            "subject_id" => $data['subject_id'],
            "exam_id" => $data['exam_id'],
            "status" => $data['status']
        ];

        if ($data['chapter_id'] != null) {
            $examQuestion['chapter_id'] = $data['chapter_id'];
            if ($data['lecture_id']) {
                $examQuestion['lecture_id'] = $data['lecture_id'];
                $que = $this->saveLectureQuestion($data);
                $examQuestion['question_id'] = $que->id;
                $this->storeLectureExamQuestions($examQuestion);
            } else {
                $que = $this->saveChapterQuestion($data);
                $examQuestion['question_id'] = $que->id;
                $this->storeChapterExamQuestions($examQuestion);
            }
        } else {
            $que = $this->saveSubjectQuestion($data);
            $examQuestion['question_id'] = $que->id;
            $this->storeSubjectExamQuestions($examQuestion);
        }


        $response->status = $response::status_ok;
        $response->messages = "Question has been inserted";
        $response->result = $que;

        return FacadeResponse::json($response);
    }

    public function storeLectureExamQuestions ($data) {
        return LectureExamQuestion::create($data);
    }

    public function storeSubjectExamQuestions ($data) {
        return SubjectExamQuestion::create($data);
    }
    public function saveChapterQuestion ($data) {

        $question = (array)[
            "subject_id" => $data['subject_id'],
            "chapter_id" => $data['chapter_id'],
            "exam_id" => $data['exam_id'],
            "question" => $data['question'],
            "option1" => $data['option1'],
            "option2" => $data['option2'],
            "option3" => $data['option3'],
            "option4" => $data['option4'],
            "option5" => isset($data['option5']) ?  $data['option5'] : null,
            "option6" => isset($data['option6']) ?  $data['option6'] : null,
            "correct_answer" => isset($data['correct_answer']) ? $data['correct_answer'] : null,
            "correct_answer2" => isset($data['correct_answer2']) ? $data['correct_answer2'] : null,
            "correct_answer3" => isset($data['correct_answer3']) ? $data['correct_answer3'] : null,
            "correct_answer4" => isset($data['correct_answer4']) ? $data['correct_answer4'] : null,
            "correct_answer5" => isset($data['correct_answer5']) ? $data['correct_answer5'] : null,
            "correct_answer6" => isset($data['correct_answer6']) ? $data['correct_answer6'] : null,
            "explanation" => $data['explanation'],
            "explanation_text" => array_key_exists("explanation_text", $data) ? $data['explanation_text'] : null,
            "status" => $data['status'],
         ];

        return ChapterQuestion::create($question);
    }

    public function saveSubjectQuestion ($data) {
        $question = (array)[
            "subject_id" => $data['subject_id'],
            "exam_id" => $data['exam_id'],
            "question" => $data['question'],
            "option1" => $data['option1'],
            "option2" => $data['option2'],
            "option3" => $data['option3'],
            "option4" => $data['option4'],
            "option5" => isset($data['option5']) ?  $data['option5'] : null,
            "option6" => isset($data['option6']) ?  $data['option6'] : null,
            "correct_answer" => isset($data['correct_answer']) ? $data['correct_answer'] : null,
            "correct_answer2" => isset($data['correct_answer2']) ? $data['correct_answer2'] : null,
            "correct_answer3" => isset($data['correct_answer3']) ? $data['correct_answer3'] : null,
            "correct_answer4" => isset($data['correct_answer4']) ? $data['correct_answer4'] : null,
            "correct_answer5" => isset($data['correct_answer5']) ? $data['correct_answer5'] : null,
            "correct_answer6" => isset($data['correct_answer6']) ? $data['correct_answer6'] : null,
            "explanation" => $data['explanation'],
            "explanation_text" => $data['explanation_text'],
            "status" => $data['status'],
         ];

        return SubjectQuestion::create($question);
    }

    public function saveLectureQuestion ($data) {

        $question = (array)[
            "subject_id" => $data['subject_id'],
            "chapter_id" => $data['chapter_id'],
            "lecture_id" => $data['lecture_id'],
            "exam_id" => $data['exam_id'],
            "question" => $data['question'],
            "option1" => $data['option1'],
            "option2" => $data['option2'],
            "option3" => $data['option3'],
            "option4" => $data['option4'],
            "option5" => isset($data['option5']) ?  $data['option5'] : null,
            "option6" => isset($data['option6']) ?  $data['option6'] : null,
            "correct_answer" => isset($data['correct_answer']) ? $data['correct_answer'] : null,
            "correct_answer2" => isset($data['correct_answer2']) ? $data['correct_answer2'] : null,
            "correct_answer3" => isset($data['correct_answer3']) ? $data['correct_answer3'] : null,
            "correct_answer4" => isset($data['correct_answer4']) ? $data['correct_answer4'] : null,
            "correct_answer5" => isset($data['correct_answer5']) ? $data['correct_answer5'] : null,
            "correct_answer6" => isset($data['correct_answer6']) ? $data['correct_answer6'] : null,
            "explanation" => $data['explanation'],
            "explanation_text" => array_key_exists('explanation_text', $data)? $data['explanation_text'] : '',
            "status" => $data['status'],
         ];

        return LectureQuestion::create($question);
    }


    public function storeQuestionsMultiple (Request $request) {

        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'subject_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        foreach ($request->items as $item) {

            $savedName = '';
            if ($request->file) {
                $file = base64_decode($request->file);
                $savedName = time().str_random(10).'.'.$request->ext;
                $success = file_put_contents('uploads/question_explanation/'.$savedName, $file);
            }


            $item['explanation'] = $savedName;

            $item['status'] = "Available";
            $item['subject_id'] = $data['subject_id'];
            $item['exam_id'] = $data['exam_id'];
            $examQuestion = (array)[
                "subject_id" => $data['subject_id'],
                "exam_id" => $data['exam_id'],
                "status" => $item['status']
            ];

            if ($data['chapter_id'] != null) {
                $examQuestion['chapter_id'] = $data['chapter_id'];
                $item['chapter_id'] = $data['chapter_id'];
                if ($data['lecture_id']) {
                    $item['lecture_id'] = $data['lecture_id'];
                    $examQuestion['lecture_id'] = $data['lecture_id'];
                    $que = $this->saveLectureQuestion($item);
                    $examQuestion['question_id'] = $que->id;
                    $this->storeLectureExamQuestions($examQuestion);
                } else {

                    $que = $this->saveChapterQuestion($item);
                    $examQuestion['question_id'] = $que->id;
                    $this->storeChapterExamQuestions($examQuestion);
                }
            } else {
                $que = $this->saveSubjectQuestion($item);
                $examQuestion['question_id'] = $que->id;
                $this->storeSubjectExamQuestions($examQuestion);
            }

        }






        $response->status = $response::status_ok;
        $response->messages = "Question has been inserted";
        $response->result = $que;

        return FacadeResponse::json($response);
    }




    public function getChapterNumberByCourse () {
        $chapters = [];
        $chapterCount = Chapter::join('courses', 'chapters.course_id', 'courses.id')
                        ->select('courses.id',
                            DB::raw('count(*) as total')
                            )
                            ->groupBy('courses.id')
                        ->get();
        foreach ($chapterCount as $key=>$element) {
            switch ($element->id) {
                case 1:
                    $chapters[] = (Object) [
                        'name' => 'SSC',
                        'total' => $element->total
                    ];
                break;
                case 2:
                    $chapters[] = (Object) [
                        'name' => 'HSC',
                        'total' => $element->total
                    ];
                break;
                case 3:
                    $chapters[] = (Object) [
                        'name' => 'JSC',
                        'total' => $element->total
                    ];
                break;
                case 5:
                    $chapters[] = (Object) [
                        'name' => 'Medical',
                        'total' => $element->total
                    ];
                break;
                case 12:
                    $chapters[] = (Object) [
                        'name' => 'University Unit A',
                        'total' => $element->total
                    ];
                break;
                case 13:
                    $chapters[] = (Object) [
                        'name' => 'University Unit B',
                        'total' => $element->total
                    ];
                break;
                Case 27:
                    $chapters[] = (Object) [
                        'name' => 'University Unit C',
                        'total' => $element->total
                    ];
                break;
                case 15:
                    $chapters[] = (Object) [
                        'name' => 'University Unit D',
                        'total' => $element->total
                    ];
                break;
            }
        }
        return FacadeResponse::json($chapters);
    }



    public function copyChapterExam (Request $request) {

        try{
           return $chapters = Chapter::where(['id'=>$request->from_chapter_id,'course_id'=> $request->from_course_id])->where('subject_id', $request->from_subject_id)->with('examArray', 'examArray.questionIds')->get();
            $count = 0;
            $totalLecture = 0;


            foreach ($chapters  as $chapter) {

                $count++;
                $code_number = str_pad( $count, 4, "0", STR_PAD_LEFT );
                // $newChapter = Chapter::create([
                //     "name" => $chapter->name,
                //     "name_bn" => $chapter->name,
                //     "course_id" => $request->to_course_id,
                //     "subject_id" => $request->to_subject_id,
                //     "status" => "Available",
                //     "code" => 'CC'.$request->to_course_id.$request->to_subject_id.'0'.$code_number,
                //     "price" => 0,
                //     "sequence" => $chapter->sequence
                // ]);
                $lectureCount = 0;
                // foreach ($chapter->videos as $video) {


                //     $totalLecture++;

                //     $code_number = str_pad( $lectureCount++, 4, "0", STR_PAD_LEFT );
                //     $lecture = (array)[
                //         "course_id" => $request->to_course_id,
                //         "subject_id" => $request->to_subject_id,
                //         "chapter_id" => $newChapter->id,
                //         "title" => $video->title,
                //         "title_bn" => $video->title_bn,
                //         "description" => $video->description,
                //         "price" => $video->price,
                //         "url" =>  $video->url,
                //         "full_url" => $video->full_url,
                //         "thumbnail" => $video->thumbnail,
                //         "status" =>  $video->status,
                //         "code" =>  'LC'.$newChapter->id.'0'.$code_number ,
                //         "isFree" => true,
                //         "duration" => $video->duration,
                //         "sequence" => $video->sequence
                //     ];
                //     $lecture = LectureVideo::create($lecture);
                // }

                // foreach ($chapter->scripts as $script) {
                //     $ch = (array) [
                //         "subject_id" => $request->to_subject_id,
                //         "chapter_id" => $newChapter->id,
                //         "title" => $script->title,
                //         "title_bn" => $script->title_bn,
                //         "title_jp" => $script->title_jp,
                //         "url" => $script->url,
                //         "status" => "Available",
                //         "is_premium" => false,
                //         "price" => 0,
                //         "price_text" => ''
                //         ];

                //     $scrpt = ChapterScript::create($ch);
                // }

                foreach ($chapter->examArray as $ex) {
                    $examData = (array) [
                        "course_id" => $request->to_course_id,
                        "subject_id" => $request->to_subject_id,
                        "chapter_id" => $request->to_chapter_id,
                        "exam_name" => $ex->exam_name,
                        "exam_name_bn" => $ex->exam_name_bn,
                        "exam_name_jp" => $ex->exam_name_jp,
                        "duration" => $ex->duration,
                        "positive_mark" => $ex->positive_mark,
                        "negative_mark" => $ex->negative_mark,
                        "total_mark" => $ex->total_mark,
                        "question_number" => $ex->question_number,
                        "status" => $ex->status
                    ];


                    $exam = ChapterExam::create($examData);
                    foreach ($ex->questionIds as $question) {
                        ChapterExamQuestion::create([
                            "subject_id" => $request->to_subject_id,
                            "chapter_id" => $request->to_chapter_id,
                            "exam_id" => $exam->id,
                            "question_id" => $question->question_id,
                            "status" => "Available",
                        ]);
                    }

                }
            }

            $response = new ResponseObject;

            $response->status = $response::status_ok;
            $response->messages = "Chapter has been created ". $count. " and lecture ". $totalLecture;

            DB::commit();
            return FacadeResponse::json($response);

        }catch (\Exception $e){

            DB::rollback();
            $response = new ResponseObject;
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);

        }


    }


    public function copyChapterExamOnlyOne (Request $request) {

        try{
            $chapterExam = ChapterExam::where(['id'=>$request->chapter_exam_id])->with('questionIds')->first();

            if (empty($chapterExam)){ // check chapter exam empty
                $response = new ResponseObject;
                $response->status = $response::status_fail;
                $response->messages = 'There is no chapter exam data';
                return FacadeResponse::json($response);
            }


            $chapterExamOld = ChapterExam::where([
                'course_id'=>$request->to_course_id,
                'subject_id'=>$request->to_subject_id,
                'chapter_id'=>$request->to_chapter_id,
                'exam_name'=>$chapterExam->exam_name,
            ])->first();


            if ($chapterExamOld){ // check chapter exam existent
                $response = new ResponseObject;
                $response->status = $response::status_fail;
                $response->messages = 'Chapter Exam Already Exists';
                return FacadeResponse::json($response);
            }



            $examData = (array) [
                "course_id" => $request->to_course_id,
                "subject_id" => $request->to_subject_id,
                "chapter_id" => $request->to_chapter_id,
                "exam_name" => $chapterExam->exam_name,
                "exam_name_bn" => $chapterExam->exam_name_bn,
                "exam_name_jp" => $chapterExam->exam_name_jp,
                "duration" => $chapterExam->duration,
                "positive_mark" => $chapterExam->positive_mark,
                "negative_mark" => $chapterExam->negative_mark,
                "total_mark" => $chapterExam->total_mark,
                "question_number" => $chapterExam->question_number,
                "status" => $chapterExam->status
            ];


            $exam = ChapterExam::create($examData);
            foreach ($chapterExam->questionIds as $question) {
                ChapterExamQuestion::create([
                    "subject_id" => $request->to_subject_id,
                    "chapter_id" => $request->to_chapter_id,
                    "exam_id" => $exam->id,
                    "question_id" => $question->question_id,
                    "status" => "Available",
                ]);
            }


            $response = new ResponseObject;

            $response->status = $response::status_ok;
            $response->messages = "Chapter exam has been created  ";

            DB::commit();
            return FacadeResponse::json($response);

        }catch (\Exception $e){

            DB::rollback();
            $response = new ResponseObject;
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);

        }


    }

    //     public function makeSequenceLectureScript (Request $request) {
    //     $totalChapter = 0;
    //     $totalLecture = 0;
    //     $chapters = Chapter::where('course_id', $request->course_id)->where('subject_id', $request->subject_id)->orderBy('sequence', 'asc')->get();
    //     foreach ($chapters as $chapter) {
    //         $count = 0;

    //       $lecture_scripts =  LectureScript::where('chapter_id',$chapter->id)->orderBy('title', 'asc')->get();

    //         foreach($lecture_scripts as $script) {
    //             $count++;
    //             $totalLecture++;
    //             LectureScript::where('id', $script->id)->update([
    //                 'sequence' => $count
    //             ]);
    //         }
    //     }

    //     $response = new ResponseObject;

    //     $response->status = $response::status_ok;
    //     $response->messages = "Success";
    //     $response->result = $totalChapter." Chapters & Lectures ". $totalLecture;
    //     return FacadeResponse::json($response);
    // }


    // public function getChapterDetails (Request $request) {

    //     // Chapter::where('course_id', $request->courseId)->where('subject_id', $request->subjectId)->where('chapters.is_active', 1)
    //     //     ->where('chapters.subject_id', $subject_id)
    //     //     ->leftjoin('courses', 'courses.id', '=', 'chapters.course_id')
    //     //     ->leftjoin('subjects', 'subjects.id', '=', 'chapters.subject_id')
    //     //     ->orderBy('chapters.name_en','asc')
    //     //     ->get();



    //     // $chapters = Chapter::where('course_id', $request->courseId)->where('subject_id', $request->subjectId)->with('scripts')->get();
    //     // foreach ($chapters as $chapter) {
    //     //     foreach ($chapter->scripts  as $script) {
    //     //         ChapterScript::where('id', $script->id)->update([
    //     //             "course_id" => $request->courseId
    //     //             ]
    //     //         );
    //     //     }
    //     // }

    //     return FacadeResponse::json($chapters);
    // }


    public function getChapterExamForVAB ($id) {
        $exam = ChapterExam::where('id', $id)->select('exam_name as chapter_name')->first();
         $questions = ChapterExamQuestion::where('exam_id', $id)
         ->join('chapter_questions', 'chapter_exam_questions.question_id' ,'chapter_questions.id')
         ->select('chapter_questions.question','chapter_questions.option1','chapter_questions.option2','chapter_questions.option3','chapter_questions.option4','chapter_questions.correct_answer','chapter_questions.correct_answer2','chapter_questions.correct_answer3','chapter_questions.correct_answer4')
         ->get();
         $exam->questions = $questions;
        return $exam;
    }



}
