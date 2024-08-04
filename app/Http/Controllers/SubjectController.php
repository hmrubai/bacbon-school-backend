<?php

namespace App\Http\Controllers;

use App\Chapter;
use App\ChapterExam;
use App\ChapterScript;
use App\Course;
use App\CourseSubject;
use App\LectureVideoParticipant;
use App\Custom\Common;
use App\Http\Controllers\Controller;
use App\Http\Helper\ResponseObject;
use App\LectureExam;
use App\LectureFavorite;
use App\LectureScript;
use App\LectureVideo;
use App\LogLectureWatchComplete;
use App\LogScript;
use App\Payment;
use App\PaymentChapter;
use App\PaymentChapterScript;
use App\PaymentCourse;
use App\PaymentLecture;
use App\PaymentLectureScript;
use App\PaymentSubject;
use App\ResultChapter;
use App\ResultLecture;
use App\ReviewExam;
use App\ReviewExamDetail;
use App\Subject;
use App\SubjectExam;
use App\SubjectExamQuestion;
use App\SubjectQuestion;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use stdClass;
use Validator;

use App\UserAllPayment;
use App\UserAllPaymentDetails;

use \Illuminate\Support\Facades\Response as FacadeResponse;

class SubjectController extends Controller
{
    public function getSubjectDetailsById($subId)
    {
        return Subject::where('id', $subId)->first();
    }

    public function getSubjectExamDetailById($id)
    {
        return SubjectExam::where('id', $id)->select('id', 'exam_name', 'question_number', 'total_mark', 'positive_mark', 'negative_mark')->withCount('questions')->first();
    }

    public function searchCourseSubjectList(Request $request)
    {
        $courseSubjectList = CourseSubject::join('courses', 'course_subjects.course_id', 'courses.id')
            ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
            ->select(
                'courses.id as course_id',
                'courses.name as course_name',
                'courses.name_bn as course_name_bn',
                'subjects.id as subject_id',
                'subjects.name as subject_name',
                'subjects.name_bn as subject_name_bn'
            )
            ->whereIn('courses.id', [26, 1, 2, 3, 5, 12, 13, 15])
            ->where(function ($q) use ($request) {
                $q->where('courses.name', 'like', '%' . $request->item . '%')
                    ->orWhere('courses.name_bn', 'like', '%' . $request->item . '%')
                    ->orWhere('subjects.name', 'like', '%' . $request->item . '%')
                    ->orWhere('subjects.name_bn', 'like', '%' . $request->item . '%')
                    ->orWhere('course_subjects.keywords', 'like', '%' . $request->item . '%');
            })
            ->orderBy('courses.name', 'asc')
            ->get();
        return $courseSubjectList;
    }
    public function getCourseSubjectList()
    {
        $courseSubjectList = CourseSubject::join('courses', 'course_subjects.course_id', 'courses.id')
            ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
            ->select(
                'courses.id as course_id',
                'courses.name as course_name',
                'courses.name_bn as course_name_bn',
                'subjects.id as subject_id',
                'subjects.name as subject_name',
                'subjects.name_bn as subject_name_bn'
            )

            ->orderBy('courses.name', 'asc')
            ->get();
        return $courseSubjectList;
        // course_subjects
    }

    public function GetSubjectList()
    {
        $subjects = Subject::all();
        return FacadeResponse::json($subjects);
    }

    public function storeSubjectExam(Request $request)
    {

        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'subject_id' => 'required',
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

        $exam = SubjectExam::create($data);
        $response->status = $response::status_ok;
        $response->messages = "Exam has been created";
        $response->result = $exam;
        return FacadeResponse::json($response);

    }

    public function getExamListBySubject($course_id, $subject_id)
    {

        $examList = SubjectExam::where('course_id', $course_id)->where('subject_id', $subject_id)->get();
        foreach ($examList as $exam) {
            $exam->details_url = "api/question/getSubjectExamQuestionsById/" .$exam->id .'/'.$exam->question_number;
          }
        return FacadeResponse::json($examList);
    }

    public function createSubject(Request $request)
    {
        $response = new ResponseObject;

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }
        $courseCount = Subject::where('name', $data['name'])->count();
        if (!$courseCount) {
            try {

                $subject = Subject::create($data);
                $response->status = $response::status_ok;
                $response->messages = "Successfully inserted";
                $response->result = $subject;
                return FacadeResponse::json($response);
            } catch (Exception $e) {
                $response->status = $response::status_fail;
                $response->messages = $e->getMessage();
                return FacadeResponse::json($response);
            }
        } else {
            $response->status = $response::status_fail;
            $response->messages = $data['name'] . " has been already created";
            return FacadeResponse::json($response);
        }
    }
    
    public function GetCourseWiseSubjectList(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();
        // validating the request
        $validator = Validator::make($data, [
            'course_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }

        try {
            $course_id = $request->get('course_id');

            $result_data = DB::table('subjects')
                ->join('subject_courses', 'subjects.id', '=', 'subject_courses.subject_id')
                ->where('subject_courses.course_id', $course_id)
                ->select('subjects.id', 'subjects.name', 'subjects.name_bn')
                ->get();

            $response->status = $response::status_ok;
            $response->result = $result_data;
            return FacadeResponse::json($response);

        } catch (Exception $e) {
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);
        }
    }

    public function getChapterNameListBySubjectId($courseId, $subId)
    {
        $subject = Subject::where('id', $subId)->first();
        $exams = SubjectExam::where('subject_id', $subId)->withCount('questions')->where('course_id', $courseId)->get();
        $chapters = Chapter::where('course_id', $courseId)->where('subject_id', $subId)
            ->select('name', 'name_bn', 'price', 'id', 'course_id', 'subject_id')
            ->get();
        $data = (object) [
            "subject" => $subject,
            "chapters" => $chapters,
            "exams" => $exams,
        ];
        return FacadeResponse::json($data);
    }

    public function getNestedCourseStructureList(Request $request)
    {
        $course_list = Course::select('id','name')->where('status', 'active')->get();
        foreach ($course_list as $course) {
            $courseSubjectList = CourseSubject::select(
                'course_subjects.id as id',
                'subjects.id as main_subject_id',
                'subjects.name'
            )
            ->where('course_subjects.course_id', $course->id)
            ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
            ->orderBy('subjects.name', 'asc')
            ->get();

            foreach ($courseSubjectList as $subject) {
                $chapters = Chapter::where('course_id', $course->id)
                ->where('subject_id', $subject->main_subject_id)
                ->select('id', 'name')
                ->get();
                $subject->chapters = $chapters;
            }
            $course->subjects = $courseSubjectList;
        }

        return FacadeResponse::json($course_list);
    }

    public function getChapterListBySubjectIdAndUserIdLatest($courseId, $subId, $userId)
    {

        $chapters = Chapter::where('course_id', $courseId)
            ->where('subject_id', $subId)
            ->orderBy('sequence', 'asc')
            ->with(['lectureVideos'])
            ->get();

        for ($i = 0; $i < count($chapters); $i++) {
            for ($j = 0; $j < count($chapters[$i]->lectureVideos); $j++) {

                $isFavorite = LectureFavorite::where('user_id', $userId)->where('lecture_id', $chapters[$i]->lectureVideos[$j]->id)->count();
                $isFavorite ? $chapters[$i]->lectureVideos[$j]['isFavorite'] = true : $chapters[$i]->lectureVideos[$j]['isFavorite'] = false;
                if (!$chapters[$i]->lectureVideos[$j]->isFree) {
                    $paidCourse = PaymentCourse::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->first();
                    if (!$paidCourse) {
                        $paidSubject = PaymentSubject::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->where('subject_id', $chapters[$i]->subject_id)->first();
                        if (!$paidSubject) {
                            $paidChapter = PaymentChapter::where('user_id', $userId)->where('chapter_id', $chapters[$i]->id)->first();
                            if (!$paidChapter) {
                                $chapters[$i]['isBought'] = false;
                                $paidLecture = PaymentLecture::where('user_id', $userId)->where('lecture_id', $chapters[$i]->lectureVideos[$j]->id)->first();
                                $chapters[$i]->lectureVideos[$j]->url = $paidLecture ? $chapters[$i]->lectureVideos[$j]->url : null;
                                $chapters[$i]->lectureVideos[$j]->full_url = $paidLecture ? $chapters[$i]->lectureVideos[$j]->full_url : null;
                                $chapters[$i]->lectureVideos[$j]['isBought'] = $paidLecture ? true : false;
                                $chapters[$i]->lectureVideos[$j]['isFree'] = $paidLecture ? false : $chapters[$i]->lectureVideos[$j]['isFree'];
                            } else {
                                $chapters[$i]['isBought'] = true;
                                $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                            }
                        } else {
                            $chapters[$i]['isBought'] = true;
                            $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                        }
                    } else {
                        $chapters[$i]['isBought'] = true;
                        $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                    }

                } else {

                    $paidCourse = PaymentCourse::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->first();
                    if (!$paidCourse) {
                        $paidSubject = PaymentSubject::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->where('subject_id', $chapters[$i]->subject_id)->first();
                        if (!$paidSubject) {
                            $paidChapter = PaymentChapter::where('user_id', $userId)->where('chapter_id', $chapters[$i]->id)->first();
                            if (!$paidChapter) {
                                $chapters[$i]['isBought'] = false;
                            } else {
                                $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                            }
                        } else {
                            $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                        }
                    } else {
                        $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                    }

                }
            }
        }

        $lectureCount = LectureVideo::where('course_id', $courseId)->where('subject_id', $subId)->count();
        $this->createReviewExam($courseId, $subId, $userId);
        $subject = $this->getSubjectDetails($courseId, $subId, $userId);

        $subjectObj = (object) [
            "id" => $subject->id,
            "name" => $subject->name,
            "name_bn" => $subject->name_bn,
            "code" => $subject->code,
            "has_lecture" => $lectureCount ? true : false,
            "color_name" => $subject->color_name,
            "exams" => $subject->exams,
            "chapter_exams" => $subject->chapterExams,
            "lecture_exams" => $subject->lectureExams,
            "chapter_scripts" => $subject->chapterScripts,
            "lecture_scripts" => $subject->lectureScripts,
            "chapters" => $chapters,
        ];
        return FacadeResponse::json($subjectObj);

    }

    public function createReviewExam($courseId, $subId, $userId)
    {
        $user = User::where('id', $userId)->select('id', 'is_e_edu_c_unit')->first();
        if ($user->is_e_edu_c_unit) {
            $subject = Subject::where('id', $subId)->first();
            $createdReviewExams = ReviewExam::where('review_exams.user_id', $userId)
                ->join('review_exam_details', 'review_exam_details.review_exam_id', 'review_exams.id')
                ->select('review_exam_details.lecture_exam_id as id')
                ->get();
            $examIds = [];

            foreach ($createdReviewExams as $exam) {
                $examIds[] = $exam->id;
            }

            $results = ResultLecture::where('result_lectures.user_id', $userId)
                ->join('lecture_exams', 'result_lectures.lecture_exam_id', 'lecture_exams.id')
                ->where('lecture_exams.course_id', $courseId)
                ->where('lecture_exams.subject_id', $subId)
                ->whereNotIn('lecture_exams.id', $examIds)
                ->select('lecture_exams.id')
                ->groupBy('id')
                ->get();
            if (count($results) > 2) {

                $countReviewExam = ReviewExam::where('course_id', $courseId)->where('user_id', $userId)->where('subject_id', $subId)->count();
                $reviewExam = ReviewExam::create([
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'subject_id' => $subId,
                    'exam_name' => $subject->name . ' Review 0' . ($countReviewExam + 1),
                    'exam_name_bn' => $subject->name . ' Review 0' . ($countReviewExam + 1),
                    'duration' => 5,
                    'positive_mark' => 1,
                    'negative_mark' => 0,
                    'total_mark' => 10,
                    'question_number' => 10,
                ]);

                foreach ($results as $result) {
                    ReviewExamDetail::create([
                        'review_exam_id' => $reviewExam->id,
                        'lecture_exam_id' => $result->id,
                    ]);
                }
            }
            return $results;
        }
        return true;

    }

    private function getSubjectDetails($courseId, $subId, $userId)
    {
        $subject = Subject::where('course_subjects.course_id', $courseId)->where('subjects.id', $subId)
            ->join('course_subjects', 'subjects.id', 'course_subjects.subject_id')
            ->select('subjects.id as id', 'course_subjects.code as code', 'subjects.name as name', 'subjects.name_bn as name_bn', 'subjects.color_name as color_name')
            ->with(['exams', 'chapterScripts', 'chapterExams' => function ($query) use ($courseId, $subId) {
                return $query->where('course_id', $courseId);
            }, 'lectureScripts', 'lectureExams' => function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            },
            ])
            ->first();
        $reviewExams = ReviewExam::where('user_id', $userId)->where('course_id', $courseId)->where('subject_id', $subId)->get();
        $array = (array) $subject->lectureExams;

        foreach ($reviewExams as $exam) {
            $exam->details_url = "api/question/getLectureExamQuestionsById/" . $exam->id . '/' . $exam->question_number . '?type=review';

        }
        foreach ($subject->chapterExams as $exam) {
            $exam->details_url = "api/question/getChapterExamQuestionsById/" . $exam->id . '/' . $exam->question_number;
            // $reviewExams[] = $exam;
        }
        foreach ($subject->lectureExams as $exam) {
            $exam->details_url = "api/question/getLectureExamQuestionsById/" . $exam->id . '/' . $exam->question_number;
            $reviewExams[] = $exam;
        }
        if (count($reviewExams)) {
            array_unshift($array, $reviewExams);
        }
        $subject->lectureExams = $reviewExams;
        return $subject;
    }

    public function getChapterListBySubjectIdAndUserIdV2($courseId, $subId, $userId)
    {
        $lecture_scripts = [];
        $chapters = Chapter::where('course_id', $courseId)
            ->where('subject_id', $subId)
            ->orderBy('sequence', 'asc')
            ->with(['lectureVideos'])
            ->get();

        for ($i = 0; $i < count($chapters); $i++) {
            for ($j = 0; $j < count($chapters[$i]->lectureVideos); $j++) {

                $isFavorite = LectureFavorite::where('user_id', $userId)->where('lecture_id', $chapters[$i]->lectureVideos[$j]->id)->count();
                $isFavorite ? $chapters[$i]->lectureVideos[$j]['isFavorite'] = true : $chapters[$i]->lectureVideos[$j]['isFavorite'] = false;
                if (!$chapters[$i]->lectureVideos[$j]->isFree) {
                    $paidCourse = PaymentCourse::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->first();
                    if (!$paidCourse) {
                        $paidSubject = PaymentSubject::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->where('subject_id', $chapters[$i]->subject_id)->first();
                        if (!$paidSubject) {
                            $paidChapter = PaymentChapter::where('user_id', $userId)->where('chapter_id', $chapters[$i]->id)->first();
                            if (!$paidChapter) {
                                $chapters[$i]['isBought'] = false;
                                $paidLecture = PaymentLecture::where('user_id', $userId)->where('lecture_id', $chapters[$i]->lectureVideos[$j]->id)->first();
                                $chapters[$i]->lectureVideos[$j]->url = $paidLecture ? $chapters[$i]->lectureVideos[$j]->url : null;
                                $chapters[$i]->lectureVideos[$j]->full_url = $paidLecture ? $chapters[$i]->lectureVideos[$j]->full_url : null;
                                $chapters[$i]->lectureVideos[$j]->download_url = $paidLecture ? $chapters[$i]->lectureVideos[$j]->download_url : null;
                                $chapters[$i]->lectureVideos[$j]['isBought'] = $paidLecture ? true : false;
                                $chapters[$i]->lectureVideos[$j]['isFree'] = $paidLecture ? false : $chapters[$i]->lectureVideos[$j]['isFree'];
                            } else {
                                $chapters[$i]['isBought'] = true;
                                $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                            }
                        } else {
                            $chapters[$i]['isBought'] = true;
                            $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                        }
                    } else {
                        $chapters[$i]['isBought'] = true;
                        $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                    }

                } else {

                    $paidCourse = PaymentCourse::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->first();
                    if (!$paidCourse) {
                        $paidSubject = PaymentSubject::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->where('subject_id', $chapters[$i]->subject_id)->first();
                        if (!$paidSubject) {
                            $paidChapter = PaymentChapter::where('user_id', $userId)->where('chapter_id', $chapters[$i]->id)->first();
                            if (!$paidChapter) {
                                $chapters[$i]['isBought'] = false;
                            } else {
                                $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                            }
                        } else {
                            $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                        }
                    } else {
                        $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                    }

                }
            }

            // lecture scripts
            $ls = LectureScript::where('subject_id', $subId)->where('chapter_id', $chapters[$i]->id)->get();
            foreach ($ls as $lScript) {
                $isBought = PaymentLectureScript::where('user_id', $userId)->where('lecture_script_id', $lScript->id)->where('is_complete', true)->count();
                $lScript->is_bought = $isBought ? true : false;

                $lecture_scripts[] = $lScript;
            }

            // chapter  scripts
            $cs = ChapterScript::where('subject_id', $subId)->where('chapter_id', $chapters[$i]->id)->get();
            foreach ($cs as $cScript) {
                $isBought = PaymentChapterScript::where('user_id', $userId)->where('chapter_script_id', $cScript->id)->where('is_complete', true)->count();
                $cScript->is_bought = $isBought ? true : false;

                $lecture_scripts[] = $cScript;
            }

            //  array_push($lecture_scripts,$ls);

        }

        $lectureCount = LectureVideo::where('course_id', $courseId)->where('subject_id', $subId)->count();
        $this->createReviewExam($courseId, $subId, $userId);
        $subject = $this->getSubjectDetailsV2($courseId, $subId, $userId);

        $subject_status = CourseSubject::where('course_id', $courseId)->where('subject_id', $subId)->first();

        $is_purchased_video = LectureVideoParticipant::where('user_id', $userId)
                            ->where('course_id', $courseId)
                            ->where('subject_id', $subId)
                            ->where('payment_status', 'completed')
                            ->first();

        $is_purchased = false;
        if(!empty($is_purchased_video)){
            $is_purchased = true;
        }

        $subjectObj = (object) [
            "id" => $subject->id,
            "name" => $subject->name,
            "name_bn" => $subject->name_bn,
            "code" => $subject->code,
            "color_name" => $subject->color_name,
            "is_free" => $subject_status->is_free,
            "is_purchased" => $is_purchased,
            "price" => $subject_status->price,
            "gp_product_id" => $subject_status->gp_product_id,
            "has_lecture" => $lectureCount ? true : false,
            "exams" => $subject->exams,
            "lecture_exams" => $subject->lectureExams,
            "chapter_scripts" => $subject->chapterScripts,
            "lecture_scripts" => $lecture_scripts, //$subject->lectureScripts,
            "chapters" => $chapters,
        ];
        return FacadeResponse::json($subjectObj);
    }

    public function createUserLectureVideoPaymentMobile(Request $request){
        $response = new ResponseObject;

        if(!$request->user_id || !$request->course_id || !$request->subject_id || !$request->currency || !$request->card_type || !$request->transaction_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, check the details!";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $user =  User::where('id', $request->user_id)->first();

        $course_subject = CourseSubject::select('course_subjects.*', 'courses.name as course_name', 'subjects.name as subject_name')
                    ->leftJoin('courses', 'courses.id', 'course_subjects.course_id')
                    ->leftJoin('subjects', 'subjects.id', 'course_subjects.subject_id')
                    ->where('course_subjects.course_id', $request->course_id)
                    ->where('course_subjects.subject_id', $request->subject_id)
                    ->first();

        $already_purchased = LectureVideoParticipant::where('user_id', $request->user_id)
            ->where('course_subject_id', $course_subject->id)
            ->where('payment_status', 'completed')
            ->first();

        if(!empty($already_purchased)){
            $response->status = $response::status_fail;
            $response->messages = " You have already purchased this lecture videos!";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $is_exist = LectureVideoParticipant::where('user_id', $request->user_id)
            ->where('course_subject_id', $course_subject->id)
            ->where('payment_status', 'pending')
            ->first();

        if(empty($is_exist)){
            LectureVideoParticipant::create([
                'user_id'           => $request->user_id,
                'course_id'         => $request->course_id,
                'subject_id'        => $request->subject_id,
                'course_subject_id' => $course_subject->id,
                'total_amount'      => $course_subject->price,
                'paid_amount'       => $course_subject->price,
                'is_fully_paid'     => 1,
                'is_trial_taken'    => 0,
                'is_active'         => 1,
                'payment_status'    => "completed"
            ]);

            $payment = UserAllPayment::updateOrCreate([
                'user_id' => $request->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->mobile_number,
                'address' => $user->address,
                'currency' => $request->currency,
                'item_id' => $course_subject->id,
                'item_name' => $course_subject->course_name . ' - ' . $course_subject->subject_name,
                'item_type'=> "Lecture Videos",
                'payable_amount' => $course_subject->price,
                'paid_amount' => $course_subject->price,
                'card_type'  => $request->card_type,
                'discount' => 0,
                'transaction_id' => $request->transaction_id,
                'transaction_status' => 'Complete',
                'payment_status' => "Full Paid",
                'status' => 'Enrolled'
            ]);

            $user_payment_details = UserAllPaymentDetails::updateOrCreate([
                'payment_id' => $payment->id,
                'amount' => $payment->paid_amount,
            ]);

        }
        else{
            LectureVideoParticipant::where('id', $is_exist->id)->update([
                'payment_status'    => "completed"
            ]);

            $is_exist_pending = UserAllPayment::where('user_id', $request->user_id)
                ->where('item_id', $course_subject->id)
                ->where('item_type', 'Lecture Videos')
                ->where('status', 'Pending')
                ->get();

            foreach ($is_exist_pending as $item) {
                UserAllPayment::where("id", $item->id)->delete();
            }

            $payment = UserAllPayment::updateOrCreate([
                'user_id' => $request->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->mobile_number,
                'address' => $user->address,
                'currency' => $request->currency,
                'item_id' => $course_subject->id,
                'item_name' => $course_subject->course_name . ' - ' . $course_subject->subject_name,
                'item_type'=> "Lecture Videos",
                'payable_amount' => $course_subject->price,
                'paid_amount' => $course_subject->price,
                'card_type'  => $request->card_type,
                'discount' => 0,
                'transaction_id' => $request->transaction_id,
                'transaction_status' => 'Complete',
                'payment_status' => "Full Paid",
                'status' => 'Enrolled'
            ]);

            $user_payment_details = UserAllPaymentDetails::updateOrCreate([
                'payment_id' => $payment->id,
                'amount' => $payment->paid_amount,
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = " You have successfully enrolled this course";
        $response->result = "";
        return FacadeResponse::json($response);
    }

    private function getSubjectDetailsV2($courseId, $subId, $userId)
    {
        $subject = Subject::where('course_subjects.course_id', $courseId)->where('subjects.id', $subId)
            ->join('course_subjects', 'subjects.id', 'course_subjects.subject_id')
            ->select('subjects.id as id', 'course_subjects.code as code', 'subjects.name as name', 'subjects.name_bn as name_bn', 'subjects.color_name as color_name')
            ->with(['exams',
                'chapterScripts' => function ($query) use ($courseId) {
                    return $query->where('course_id', $courseId);
                }, 'chapterExams' => function ($query) use ($courseId, $subId) {
                    return $query->where('course_id', $courseId);
                }, 'lectureScripts', 'lectureExams' => function ($query) use ($courseId) {
                    return $query->where('course_id', $courseId);
                },
            ])
            ->first();
        $reviewExams = ReviewExam::where('user_id', $userId)->where('course_id', $courseId)->where('subject_id', $subId)->get();
        $array = (array) $subject->lectureExams;

        foreach ($reviewExams as $exam) {
            $exam->details_url = "api/question/getLectureExamQuestionsById/" . $exam->id . '/' . $exam->question_number . '?type=review';
        }
        foreach ($subject->chapterExams as $exam) {
            $exam->details_url = "api/question/getChapterExamQuestionsById/" . $exam->id . '/' . $exam->question_number;
            $reviewExams[] = $exam;
        }
        foreach ($subject->lectureExams as $exam) {
            $exam->details_url = "api/question/getLectureExamQuestionsById/" . $exam->id . '/' . $exam->question_number;
            $reviewExams[] = $exam;
        }
        if (count($reviewExams)) {
            array_unshift($array, $reviewExams);
        }
        foreach ($subject->lectureScripts as $lScript) {
            $isBought = PaymentLectureScript::where('user_id', $userId)->where('lecture_script_id', $lScript->id)->where('is_complete', true)->count();
            $lScript->is_bought = $isBought ? true : false;
        }
        foreach ($subject->chapterScripts as $chScript) {
            $isBought = PaymentChapterScript::where('user_id', $userId)->where('chapter_script_id', $chScript->id)->where('is_complete', true)->count();
            $chScript->is_bought = $isBought ? true : false;
            $subject->lectureScripts[] = $chScript;
        }
        $subject->lectureExams = $reviewExams;
        return $subject;
    }

    public function getChapterListBySubjectIdAndUserId($courseId, $subId, $userId)
    {

        $chapters = Chapter::where('course_id', $courseId)
            ->where('subject_id', $subId)
            ->orderBy('sequence', 'asc')
            ->with(['scripts', 'lectureVideos', 'lectureVideos.lectureScripts', 'exams', 'lectureVideos.exams', 'lectureVideos.lectureRating'])
        // ->has('lectureVideos')
            ->get();

        for ($i = 0; $i < count($chapters); $i++) {
            for ($j = 0; $j < count($chapters[$i]->lectureVideos); $j++) {

                $isFavorite = LectureFavorite::where('user_id', $userId)->where('lecture_id', $chapters[$i]->lectureVideos[$j]->id)->count();
                $isFavorite ? $chapters[$i]->lectureVideos[$j]['isFavorite'] = true : $chapters[$i]->lectureVideos[$j]['isFavorite'] = false;
                if (!$chapters[$i]->lectureVideos[$j]->isFree) {
                    $paidCourse = PaymentCourse::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->first();
                    if (!$paidCourse) {
                        $paidSubject = PaymentSubject::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->where('subject_id', $chapters[$i]->subject_id)->first();
                        if (!$paidSubject) {
                            $paidChapter = PaymentChapter::where('user_id', $userId)->where('chapter_id', $chapters[$i]->id)->first();
                            if (!$paidChapter) {
                                $chapters[$i]['isBought'] = false;
                                $paidLecture = PaymentLecture::where('user_id', $userId)->where('lecture_id', $chapters[$i]->lectureVideos[$j]->id)->first();
                                $chapters[$i]->lectureVideos[$j]->url = $paidLecture ? $chapters[$i]->lectureVideos[$j]->url : null;
                                $chapters[$i]->lectureVideos[$j]->full_url = $paidLecture ? $chapters[$i]->lectureVideos[$j]->full_url : null;
                                $chapters[$i]->lectureVideos[$j]->download_url = $paidLecture ? $chapters[$i]->lectureVideos[$j]->download_url : null;
                                $chapters[$i]->lectureVideos[$j]['isBought'] = $paidLecture ? true : false;
                                $chapters[$i]->lectureVideos[$j]['isFree'] = $paidLecture ? false : $chapters[$i]->lectureVideos[$j]['isFree'];
                            } else {
                                $chapters[$i]['isBought'] = true;
                                $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                            }
                        } else {
                            $chapters[$i]['isBought'] = true;
                            $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                        }
                    } else {
                        $chapters[$i]['isBought'] = true;
                        $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                    }

                } else {

                    $paidCourse = PaymentCourse::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->first();
                    if (!$paidCourse) {
                        $paidSubject = PaymentSubject::where('user_id', $userId)->where('course_id', $chapters[$i]->course_id)->where('subject_id', $chapters[$i]->subject_id)->first();
                        if (!$paidSubject) {
                            $paidChapter = PaymentChapter::where('user_id', $userId)->where('chapter_id', $chapters[$i]->id)->first();
                            if (!$paidChapter) {
                                $chapters[$i]['isBought'] = false;
                            } else {
                                $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                            }
                        } else {
                            $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                        }
                    } else {
                        $chapters[$i]->lectureVideos[$j]['isFree'] = false;
                    }

                }
            }
        }

        $subject = CourseSubject::where('course_id', $courseId)->where('subject_id', $subId)
            ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
            ->select('subjects.id as id', 'course_subjects.code as code', 'subjects.name as name', 'subjects.name_bn as name_bn', 'subjects.color_name as color_name')
            ->first();

        $subjectObj = (object) [
            "id" => $subject->id,
            "name" => $subject->name,
            "name_bn" => $subject->name_bn,
            "code" => $subject->code,
            "color_name" => $subject->color_name,
            "exams" => $subject->exams,
            "chapters" => $chapters,
        ];
        return FacadeResponse::json($subjectObj);

    }
    public function getChapterListBySubjectId($courseId, $subId)
    {

        $chapters = Chapter::where('course_id', $courseId)
            ->with(['scripts', 'lectureVideos', 'lectureVideos.lectureScripts', 'exams', 'lectureVideos.exams'])
            ->has('lectureVideos')
            ->where('subject_id', $subId)->get();

        $isAvailableInCourse = CourseSubject::where('course_id', $courseId)->where('subject_id', $subId)->count();
        if ($isAvailableInCourse > 0) {
            $subject = Subject::select('id', 'name', 'color_name')->where('id', $subId)
                ->with(['exams' => function ($q) use ($courseId) {
                    $q->where('course_id', $courseId);
                }])
                ->first();

            $subjectObj = (object) [
                "id" => $subject->id,
                "name" => $subject->name,
                "color_name" => $subject->color_name,
                "exams" => $subject->exams,
                "chapters" => $chapters,
            ];
            return FacadeResponse::json($subjectObj);
        } else {
            return FacadeResponse::json([]);
        }
    }

    public function getChapterListBySubjectIdForWeb($courseId, $subId)
    {
        $lecture_scripts = [];
        $chapters = Chapter::where('course_id', $courseId)
            ->with(['lectureVideos'])
            ->has('lectureVideos')
            ->orderBy('sequence', 'asc')
            ->where('subject_id', $subId)->get();
        for ($j = 0; $j < count($chapters); $j++) {

            // lecture scripts
            $ls = LectureScript::where('subject_id', $subId)->where('chapter_id', $chapters[$j]->id)->get();
            foreach ($ls as $lScript) {
                $lecture_scripts[] = $lScript;
            }

            // chapter  scripts
            $cs = ChapterScript::where('subject_id', $subId)->where('chapter_id', $chapters[$j]->id)->get();
            foreach ($cs as $cScript) {
                $lecture_scripts[] = $cScript;
            }

            for ($i = 0; $i < count($chapters[$j]->lectureVideos); $i++) {
                (string) $duration = floor($chapters[$j]->lectureVideos[$i]->duration / 60) . ':' . $chapters[$j]->lectureVideos[$i]->duration % 60;
                // return $duration;
                $chapters[$j]->lectureVideos[$i]->url = $chapters[$j]->lectureVideos[$i]->isFree ? $chapters[$j]->lectureVideos[$i]->url : null;
                $chapters[$j]->lectureVideos[$i]->durations = $duration;
            }

        }
        $course = Course::where('id', $courseId)->first();
        $subject_status = CourseSubject::where('course_id', $courseId)->where('subject_id', $subId)->first();
        $isAvailableInCourse = CourseSubject::where('course_id', $courseId)->where('subject_id', $subId)->count();
        //return FacadeResponse::json($subject_list);

        if ($isAvailableInCourse > 0) {

            $subject = Subject::select('id', 'name', 'name_bn', 'color_name')
                ->where('id', $subId)
                ->with(['exams' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }, 'chapterScripts' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }, 'chapterExams' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }, 'lectureScripts', 'lectureExams' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }])
                ->first();

            foreach ($subject->chapterScripts as $scrpt) {
                $subject->lectureScripts[] = $scrpt;
            }
            $subjectObj = (object) [
                "id" => $subject->id,
                "name" => $subject->name,
                "course_name" => $course->name,
                "course_name_bn" => $course->name_bn,
                "gp_product_id" => $subject_status->gp_product_id,
                "is_free" => $subject_status->is_free,
                "is_purchased" => false,
                "price" => $subject_status->price,
                "color_name" => $subject->color_name,
                "exams" => $subject->exams,
                "chapterScripts" => $subject->chapterScripts,
                "chapterExams" => $subject->chapterExams,
                "lectureScripts" => $lecture_scripts, //$subject->lectureScripts,
                "lectureExams" => $subject->lectureExams,
                "chapters" => $chapters,
            ];
            return FacadeResponse::json($subjectObj);
        } else {
            return FacadeResponse::json([]);
        }
    }

    public function UpdateGPIDCourseSubject(Request $request)
    {
        $course_subjects = CourseSubject::all();
        
        foreach ($course_subjects as $item) {
            $gpid = "gp_cs_" . str_pad($item->id, 2, '0', STR_PAD_LEFT); 

            CourseSubject::where('id', $item->id)->update([
                "gp_product_id" => $gpid
            ]);
        }

        $list = CourseSubject::all();
        return FacadeResponse::json($list);
    }

    public function getChapterListBySubjectIdUserIDForWeb(Request $request)
    {
        $courseId = $request->CourseId ? $request->CourseId : 0;
        $subId = $request->SubId ? $request->SubId : 0;
        $UserId = $request->UserId ? $request->UserId : 0;

        $lecture_scripts = [];
        $chapters = Chapter::where('course_id', $courseId)
            ->with(['lectureVideos'])
            ->has('lectureVideos')
            ->orderBy('sequence', 'asc')
            ->where('subject_id', $subId)->get();
        for ($j = 0; $j < count($chapters); $j++) {
            // lecture scripts
            $ls = LectureScript::where('subject_id', $subId)->where('chapter_id', $chapters[$j]->id)->get();
            foreach ($ls as $lScript) {
                $lecture_scripts[] = $lScript;
            }

            // chapter  scripts
            $cs = ChapterScript::where('subject_id', $subId)->where('chapter_id', $chapters[$j]->id)->get();
            foreach ($cs as $cScript) {
                $lecture_scripts[] = $cScript;
            }

            for ($i = 0; $i < count($chapters[$j]->lectureVideos); $i++) {
                (string) $duration = floor($chapters[$j]->lectureVideos[$i]->duration / 60) . ':' . $chapters[$j]->lectureVideos[$i]->duration % 60;
                // return $duration;
                $chapters[$j]->lectureVideos[$i]->url = $chapters[$j]->lectureVideos[$i]->isFree ? $chapters[$j]->lectureVideos[$i]->url : null;
                $chapters[$j]->lectureVideos[$i]->durations = $duration;
            }

        }
        $course = Course::where('id', $courseId)->first();
        $subject_status = CourseSubject::where('course_id', $courseId)->where('subject_id', $subId)->first();
        $isAvailableInCourse = CourseSubject::where('course_id', $courseId)->where('subject_id', $subId)->count();
        //return FacadeResponse::json($subject_list);

        if ($isAvailableInCourse > 0) {
            $is_purchased = false;
            $is_purchased_video = LectureVideoParticipant::where('user_id', $UserId)
                            ->where('course_id', $courseId)
                            ->where('subject_id', $subId)
                            ->where('payment_status', 'completed')
                            ->first();

            if(!empty($is_purchased_video)){
                $is_purchased = true;
            }

            $subject = Subject::select('id', 'name', 'name_bn', 'color_name')
                ->where('id', $subId)
                ->with(['exams' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }, 'chapterScripts' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }, 'chapterExams' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }, 'lectureScripts', 'lectureExams' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }])
                ->first();

            foreach ($subject->chapterScripts as $scrpt) {
                $subject->lectureScripts[] = $scrpt;
            }
            $subjectObj = (object) [
                "id" => $subject->id,
                "name" => $subject->name,
                "course_name" => $course->name,
                "course_name_bn" => $course->name_bn,
                "is_free" => $subject_status->is_free,
                "is_purchased" => $is_purchased,
                "price" => $subject_status->price,
                "color_name" => $subject->color_name,
                "exams" => $subject->exams,
                "chapterScripts" => $subject->chapterScripts,
                "chapterExams" => $subject->chapterExams,
                "lectureScripts" => $lecture_scripts, //$subject->lectureScripts,
                "lectureExams" => $subject->lectureExams,
                "chapters" => $chapters,
            ];
            return FacadeResponse::json($subjectObj);
        } else {
            return FacadeResponse::json([]);
        }
    }

    public function storeSubjectQuestions(Request $request)
    {

        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'subject_id' => 'required',
            'exam_id' => 'required',
            'question' => 'required|string',
            'option1' => 'required|string',
            'option2' => 'required|string',
            'option3' => 'required|string',
            'option4' => 'required|string',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            // $response->messages = "Please fill up all required fields";
            return FacadeResponse::json($response);
        }
        $data['status'] = "Available";
        $question = (array) [
            "subject_id" => $data['subject_id'],
            "exam_id" => $data['exam_id'],
            "question" => $data['question'],
            "option1" => $data['option1'],
            "option2" => $data['option2'],
            "option3" => $data['option3'],
            "option4" => $data['option4'],
            "option5" => isset($data['option5']) ? $data['option5'] : null,
            "option6" => isset($data['option6']) ? $data['option6'] : null,
            "correct_answer" => isset($data['correct_answer']) ? $data['correct_answer'] : null,
            "correct_answer2" => isset($data['correct_answer2']) ? $data['correct_answer2'] : null,
            "correct_answer3" => isset($data['correct_answer3']) ? $data['correct_answer3'] : null,
            "correct_answer4" => isset($data['correct_answer4']) ? $data['correct_answer4'] : null,
            "correct_answer5" => isset($data['correct_answer5']) ? $data['correct_answer5'] : null,
            "correct_answer6" => isset($data['correct_answer6']) ? $data['correct_answer6'] : null,
            "status" => $data['status'],
        ];

        $subQue = SubjectQuestion::create($question);

        $examQuestion = (array) [
            "subject_id" => $data['subject_id'],
            "exam_id" => $data['exam_id'],
            "question_id" => $subQue->id,
            "status" => $data['status'],
        ];
        $this->storeSubjectExamQuestions($examQuestion);

        $response->status = $response::status_ok;
        $response->messages = "Question has been inserted";
        $response->result = $subQue;

        return FacadeResponse::json($response);

    }

    public function storeSubjectExamQuestions($data)
    {
        return SubjectExamQuestion::create($data);
    }

    public function getSubjectExamQuestionsById(Request $request, $examId, $pageSize)
    {
        if (Session::get('session_rand')) {
            if ((time() - Session::get('session_rand') > 3600)) {
                Session::put('session_rand', time());
            }
        } else {
            Session::put('session_rand', time());
        }
        $questions = SubjectExamQuestion::where('exam_id', $examId)
            ->join('subject_questions', 'subject_exam_questions.question_id', '=', 'subject_questions.id')
        // ->join('courses','users.current_course_id','=','courses.id')
            ->select(
                'subject_questions.id as id',
                'subject_questions.question as question',
                'subject_questions.option1 as option1',
                'subject_questions.option2 as option2',
                'subject_questions.option3 as option3',
                'subject_questions.option4 as option4',
                'subject_questions.option5 as option5',
                'subject_questions.option6 as option6',
                'subject_questions.correct_answer as correct_answer',
                'subject_questions.correct_answer2 as correct_answer2',
                'subject_questions.correct_answer3 as correct_answer3',
                'subject_questions.correct_answer4 as correct_answer4',
                'subject_questions.correct_answer5 as correct_answer5',
                'subject_questions.correct_answer6 as correct_answer6',
                'subject_questions.explanation as explanation',
                'subject_questions.explanation_text as explanation_text')
            ->inRandomOrder(Session::get('session_rand'))
            ->limit($pageSize)
            ->get();
        $obj = (Object) [
            "data" => $questions,
            "submission_url" => "api/submitSubjectExamResult",
        ];

        return FacadeResponse::json($obj);
    }

    public function buySubject(Request $request, Common $common)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'subject_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }
        $subjectPrice = $this->getSubjectPrice($common, $data['subject_id'], $data['user_id']);

        $current_date_time = Carbon::now()->toDateTimeString();

        $amountForPayment = $data['amount'] + $data['discount'];

        $previousPayment = Payment::where('user_id', $data['user_id'])
            ->orderBy('id', 'DESC')
            ->first();
        if ($previousPayment) {
            $lastDue = $previousPayment->due;
            $lastBalance = $previousPayment->balance;
            Payment::where('id', $previousPayment->id)->update([
                'due' => 0,
                'balance' => 0,
            ]);
            $due = $lastDue + ($subjectPrice - $amountForPayment) - $lastBalance;
            $balance = $lastBalance + ($amountForPayment - $subjectPrice);
        } else {
            $due = $subjectPrice - $amountForPayment;
            $balance = $amountForPayment - $subjectPrice;
        }

        // $paymentAmount += $balance;
        $paymentObj = (array) [
            "user_id" => $data['user_id'],
            "amount" => $data['amount'],
            "payment_method" => $data['payment_method'],
            "payment_date" => $current_date_time,
            "due" => $due > 0 ? $due : 0,
            "discount" => $data['discount'],
            "balance" => $balance > 0 ? $balance : 0,
        ];

        $payment = Payment::create($paymentObj);
        $chapterList = Chapter::where('subject_id', $data['subject_id'])->get();

        foreach ($chapterList as $chapter) {
            $chapterArray = (array) [
                "user_id" => $data['user_id'],
                "amount" => $amountForPayment,
                "chapter_id" => $chapter->id,
            ];
            $this->buyChapterPrivate($payment, $chapterArray);
        }
        $response->status = $response::status_ok;
        $response->messages = "Successfully bought";
        return FacadeResponse::json($response);
    }

    public function buyChapterPrivate($payment, $data)
    {

        $paymentAmount = $data['amountForPayment'];
        $chapterPrice = $this->getChapterPrice($data['chapter_id'], $data['user_id']);

        $paid = PaymentLecture::where('user_id', $data['user_id'])
            ->where('isPaid', true)->select('lecture_id')->get();
        $lectureListToPay = LectureVideo::where('chapter_id', $data['chapter_id'])
            ->whereNotIn('id', $paid)->get();

        foreach ($lectureListToPay as $lecture) {
            if ($paymentAmount) {
                $paymentAmountForLecture = 0;
                $pl = PaymentLecture::where('user_id', $data['user_id'])
                    ->where('lecture_id', $lecture->id)
                    ->first();
                if ($pl) {
                    if ($paymentAmount >= ($pl->actual_price - $pl->amount)) {
                        PaymentLecture::where('id', $pl->id)->update([
                            "isPaid" => true,
                        ]);
                    }

                } else {

                    $paymentAmountForLecture = $paymentAmount > $lecture->price ? $lecture->price : $paymentAmount;
                    $paymentLectureObj = (array) [
                        "user_id" => $data['user_id'],
                        "lecture_id" => $lecture->id,
                        "payment_id" => $payment->id,
                        "amount" => $paymentAmountForLecture,
                        "actual_price" => $lecture->price,
                        "isPaid" => true,
                    ];
                    PaymentLecture::create($paymentLectureObj);
                }
            }
        }
        return true;
    }

    public function getSubjectPrice($common, $subjectId, $userId)
    {
        $sum = 0;
        $chapterList = Chapter::where('subject_id', $subjectId)->get();
        foreach ($chapterList as $chapter) {
            $sum += $common->getChapterPrice($chapter->id, $userId);
        }
        return $sum;
    }

    public function getCoursePriceByUserId($courseId, $userId)
    {
        $object = new stdClass();
        $sum = 0;
        $subjectList = CourseSubject::where('course_id', $courseId)->get();
        foreach ($subjectList as $subject) {
            $subjectObj = $this->getSubjectPriceWithChapterByUserId($courseId, $subject->subject_id, $userId);

            $subjectArray[] = (object) array(
                'id' => $subject->subject_id,
                'price' => $subjectObj->price, //   $this->getSubjectPriceByUserId($courseId, $subject->subject_id, $userId)
                'chapters' => $subjectObj->chapters,
            );

            $sum += $subjectObj->price; // $this->getSubjectPriceByUserId($courseId, $subject->subject_id, $userId);
            // return FacadeResponse::json($subjectObj);
            // return FacadeResponse::json($this->getSubjectPriceWithChapterByUserId($courseId, $subject->subject_id, $userId));
        }

        $coursePrice = (object) [
            "id" => (int) $courseId,
            "price" => $sum,
            "subjects" => $subjectArray,
        ];
        // return $coursePrice;
        return FacadeResponse::json($coursePrice);
    }

    public function getSubjectPriceWithChapterByUserId($courseId, $subjectId, $userId)
    {
        $sum = 0;
        $chapterArray = [];
        $chapterList = Chapter::where('course_id', $courseId)->where('subject_id', $subjectId)->get();
        foreach ($chapterList as $chapter) {
            $price = $this->getChapterPrice($chapter->id, $userId);
            $chapterArray[] = (object) array(
                'id' => $chapter->id,
                'price' => $price,
            );
            $sum += $price;
        }
        $coursePrice = (object) [
            "id" => (int) $courseId,
            "price" => $sum,
            "chapters" => $chapterArray,
        ];
        return $coursePrice;
    }

    public function getSubjectPriceByUserId($courseId, $subjectId, $userId)
    {
        $sum = 0;
        $chapterList = Chapter::where('course_id', $courseId)->where('subject_id', $subjectId)->get();
        foreach ($chapterList as $chapter) {
            $sum += $this->getChapterPrice($chapter->id, $userId);
        }
        return $sum;
    }

    public function getChapterPrice($chapterId, $userId)
    {
        $sum = 0;
        $paid = PaymentLecture::where('user_id', $userId)
            ->where('isPaid', true)->select('lecture_id')->get();

        $lectureListToPay = LectureVideo::where('chapter_id', $chapterId)
            ->whereNotIn('id', $paid)->get();
        $unPaid = 0;
        $partiallyPaid = 0;
        foreach ($lectureListToPay as $lecture) {
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

    public function searchByCode($search)
    {
        $item = Course::where('code', $search)->first();
        if ($item) {
            $item['type'] = "Course";
        }
        if (!$item) {
            $item = CourseSubject::where('code', $search)
                ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
                ->select('subjects.id as id', 'subjects.name as name', 'subjects.name_bn as name_bn', 'course_subjects.code as code', 'course_subjects.course_id as course_id')
                ->with('course')
                ->first();
            if ($item) {
                $item['type'] = "Subject";
            }
        }
        if (!$item) {
            $item = Chapter::where('code', $search)
                ->with('course', 'subject')
                ->first();
            if ($item) {
                $item['type'] = "Chapter";
            }
        }

        if (!$item) {
            $item = LectureVideo::where('code', $search)
                ->select('id', 'title as name', 'code', 'course_id', 'subject_id', 'chapter_id')
                ->with('course', 'subject', 'chapter')
                ->first();
            if ($item) {
                $item['type'] = "Lecture";
            }
        }
        return $item;
    }

    public function getExamListByCourse($courseId)
    {
        $exams = SubjectExam::where('course_id', $courseId)->get();
        return $exams;
    }

    public function getDetailsByExamId($id)
    {
        $exam = SubjectExam::where('id', $id)->first();
        $questions = SubjectExamQuestion::where('exam_id', $id)
            ->join('subject_questions', 'subject_exam_questions.question_id', 'subject_questions.id')
            ->select('subject_questions.*')
            ->limit($exam->question_number)
            ->inRandomOrder()

            ->get();
        $exam->questions = $questions;
        return $exam;
    }

    public function makeSequence()
    {
        $courseId = 27;
        $subjectList = CourseSubject::where('course_id', $courseId)->get();
        $total = 0;
        $totalExam = 0;
        foreach ($subjectList as $sub) {
            $subject = Subject::select('id', 'name', 'name_bn', 'color_name')
                ->where('id', $sub->subject_id)
                ->with(['exams' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }, 'chapterScripts' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }, 'chapterExams' => function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                }])
                ->first();

            // return FacadeResponse::json($subject);
            $count = 0;
            foreach ($subject->chapterScripts as $cs) {
                ChapterScript::where('id', $cs->id)->update(['sequence' => $count++]);
                $total++;
            }
            $countEx = 0;
            foreach ($subject->chapterExams as $ce) {
                ChapterExam::where('id', $ce->id)->update(['sequence' => $countEx++]);
                $totalExam++;
            }
        }
        return FacadeResponse::json($total . " ==" . $totalExam);
    }

    public function getChapterListWithDetails($courseId, $subId, $userId)
    {

        $chapters = Chapter::where('course_id', $courseId)
            ->where('subject_id', $subId)
            ->orderBy('sequence', 'asc')
            ->with(['lectureVideos'])
            ->get();

        foreach ($chapters as $chapter) {
            $scripts = [];
            $exam = [];

            $total_exam_count = 0;
            $total_script_count = 0;

            $total_video_complete_count = 0;
            $total_exam_complete_count = 0;
            $total_script_complete_count = 0;

            $chapter_scripts = Chapter::where('chapters.id', $chapter->id)
                ->join('chapter_scripts', 'chapters.id', 'chapter_scripts.chapter_id')
                ->select('chapter_scripts.*')
                ->get();

            $lecture_scripts = Chapter::where('chapters.id', $chapter->id)
                ->join('lecture_scripts', 'chapters.id', 'lecture_scripts.chapter_id')
                ->select('lecture_scripts.*')
                ->get();

            foreach ($chapter_scripts as $cScript) {
                $cScript->type = 'chapter';
                $scripts[] = $cScript;
                $total_script_count++;
            }
            foreach ($lecture_scripts as $lScript) {
                $lScript->type = 'lecture';
                $scripts[] = $lScript;
                $total_script_count++;
            }
            $chapter->lecture_scripts = $scripts;

            $chapterExams = ChapterExam::where('chapter_id', $chapter->id)->get();
            $lectureExams = LectureExam::where('chapter_id', $chapter->id)->get();

            foreach ($lectureExams as $lExam) {
                $lExam->details_url = "api/question/getLectureExamQuestionsById/" . $lExam->id . '/' . $lExam->question_number;
                $lExam->type = 'lecture';
                $lExam->is_completed = $this->getIsLectureExamPassed($lExam->id, $userId);
                if ($lExam->is_completed) {
                    $total_exam_complete_count++;
                }

                $exam[] = $lExam;
                $total_exam_count++;

            }

            foreach ($chapterExams as $cExam) {
                $cExam->details_url = "api/question/getChapterExamQuestionsById/" . $cExam->id . '/' .
                $cExam->question_number;
                $cExam->type = 'chapter';
                $cExam->is_completed = $this->getIsChapterExamPassed($cExam->id, $userId);
                if ($cExam->is_completed) {
                    $total_exam_complete_count++;
                }

                $exam[] = $cExam;
                $total_exam_count++;
            }

            $chapter->lecture_exams = $exam;

            foreach ($chapter->lectureVideos as $video) {
                $video->is_completed = $this->getIsfullWatched($video->id, $userId);
                if ($video->is_completed) {
                    $total_video_complete_count++;
                }

            }
            foreach ($chapter->lecture_scripts as $script) {
                $script->is_completed = $this->getIsScriptDownloaded($script->id, $userId);

                if ($script->is_completed) {
                    $total_script_complete_count++;
                }

            }

            $chapter->total_video_count = $chapter->lectureVideos->count();
            $chapter->total_script_count = $total_script_count;
            $chapter->total_exam_count = $total_exam_count;

            $chapter->total_video_complete_count = $total_video_complete_count;
            $chapter->total_script_complete_count = $total_script_complete_count;
            $chapter->total_exam_complete_count = $total_exam_complete_count;

        }

        return FacadeResponse::json($chapters);
    }

    private function getIsfullWatched($lectureId, $userId)
    {
        $watchTime = LogLectureWatchComplete::where('lecture_id', $lectureId)->where('user_id', $userId)->where('is_full_watched', true)->count();
        return $watchTime ? true : false;
    }

    private function getIsScriptDownloaded($scriptId, $userId)
    {
        $countLogScript = LogScript::where('script_id', $scriptId)->where('user_id', $userId)->count();
        return $countLogScript ? true : false;
    }

    private function getIsChapterExamPassed($examId, $userId)
    {
        $isPassed = false;
        $result = ResultChapter::where('user_id', $userId)->where('chapter_exam_id', $examId)->get();
        if (empty($result)) {
            return $isPassed;
        }
        foreach ($result as $item) {
            if (($item->mark * 100) / $item->total_mark > 40) {
                $isPassed = true;
            } else {
                $isPassed = false;
            }
        }
        return $isPassed;
    }

    private function getIsLectureExamPassed($examId, $userId)
    {
        $isPassed = false;
        $result = ResultLecture::where('user_id', $userId)->where('lecture_exam_id', $examId)->get();
        if (empty($result)) {
            return $isPassed;
        }
        foreach ($result as $item) {
            if (($item->mark * 100) / $item->total_mark > 40) {
                $isPassed = true;
            } else {
                $isPassed = false;
            }
        }
        return $isPassed;
    }

}
