<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use Validator;
use DB;
use App\User;
use App\Course;
use App\CourseType;
use App\UserCourse;
use App\CourseSubject;
use App\LectureVideoParticipant;
use App\BmoocCorner;


use App\Custom\Common;
use Carbon\Carbon;
use App\LectureVideo;
use App\SubjectExam;
use App\ResultSubject;

use App\Subject;
use App\Chapter;
use App\eBook;
use App\PaymentLecture;
use App\Payment;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use File;

class CourseController extends Controller
{
    public function getCourseSubjectList () {
        $response = new ResponseObject;
        $courseList = Course::join('course_types', 'courses.course_type_id', 'course_types.id')
        ->select('courses.*',
        'course_types.name as type_name',
        'course_types.name_bn as type_name_bn',
        'course_types.name_jp as type_name_jp'
        )
            ->with('subjects')->whereIn('courses.id', [26, 1,2,3,5,12,13,15,27,35,36])
            ->orderBy('courses.sequence', 'asc')->get();

        $course = (Object) [
                "list" => $courseList
            ];
        return FacadeResponse::json($course);
    }
    public function getCourseTypeList () {
        return CourseType::all();
    }

    public function getCourseListWithTypeV2 (Request $request) {
        // $user = User::where('id', $request->userId)->first();
        // $list = CourseType::where('status', 'Active')->with(['courses'=> function ($query) use ($user) {
        //     return $query->where('id', $user->current_course_id);
        // }])->whereNotIn('id', [7,10])->select('id','name', 'name_bn', 'name_jp')->orderBy('sequence', 'asc')->get();
        $list = CourseType::where('status', 'active')->with('courses')->whereNotIn('id', [7,10])->select('id','name', 'name_bn', 'name_jp')->orderBy('sequence', 'asc')->get();
        return $list ;
    }

    public function getCourseListWithType () {
        $list = CourseType::where('status', 'active')->with('courses')->whereNotIn('id', [7,10])->select('id','name', 'name_bn', 'name_jp')->orderBy('sequence', 'asc')->get();
        return $list ;
    }
    public function getScholarShipCourseListWithType () {
        // dd(CourseType::get());
        $list = CourseType::where('status', 'inactive')->with('scholarships')->select('id','name', 'name_bn', 'name_jp')->get();
        return $list ;
    }
    public function updateCourse (Request $request) {
        $response = new ResponseObject;

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        $course = Course::where('id', $request->id)->first();
        $course->update([
            "name" => $request->name,
            "name_bn" => $request->name_bn,
            "price" => $request->price,
            "status" => $request->status
        ]);
        $response->status = $response::status_ok;
        $response->messages = "Update Successful";
        return FacadeResponse::json($response);

    }
    public function createCourse (Request $request) {
        $response = new ResponseObject;

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'name' => 'required|unique:courses'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        $courseName = str_replace(' ', '_', $request->name);

        $path = 'uploads/' . $courseName;
        File::makeDirectory($path, $mode = 0777, true, true);
        $courseCount = Course::where('name', $data['name'])->count();
        $lastCourse = Course::orderBy('id', 'desc')->first();
        if ($lastCourse) {

            $code = $lastCourse->id + 1;
        } else {
            $code = 1;
        }
        if(!$courseCount) {
            try {
                $data['code'] = "C0000". $code;
                $course = Course::create($data);
                $response->status = $response::status_ok;
                $response->messages = "Successfully inserted";
                $response->result = $course;
                return FacadeResponse::json($response);
            }  catch (Exception $e) {
                $response->status = $response::status_fail;
                $response->messages = $e->getMessage();
                return FacadeResponse::json($response);
            }
        } else {
            $response->status = $response::status_fail;
            $response->messages = $data['name']." has been already created";
            return FacadeResponse::json($response);
        }

    }


    public function addSubjectToCourse (Request $request) {
        $response = new ResponseObject;

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'course_id' => 'required',
            'subject_id' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }
        $course = $this->courseDetail($request->course_id);
        $subjectController = new SubjectController();
        $subjectDetails = $subjectController->getSubjectDetailsById($request->subject_id);

        $path = 'uploads/' . $course->name . '/' . $subjectDetails->name;

        $path = str_replace(' ', '_', $path);

        File::makeDirectory($path, $mode = 0777, true, true);

        $courseCount = CourseSubject::where('course_id', $data['course_id'])->where('subject_id', $data['subject_id'])->count();
        if(!$courseCount) {
            try {

                $subjectSequence = 1;
                $lastSubject = CourseSubject::where('course_id', $data['course_id'])->orderBy('id', 'desc')->first();
                if ($lastSubject) {
                    if ($lastSubject->code) {
                        // return $lastSubject->code;
                        $ar = explode( '0', $lastSubject->code);
                        $subjectSequence = end($ar) + 1;
                    } else {
                        $subjectSequence = 1;
                    }
                }
                $code_number = str_pad( $subjectSequence, 4, "0", STR_PAD_LEFT );

                $data['code'] = 'SC'.$request->course_id.$request->subject_id.'0'.$code_number ;


                $data['status'] = "Active";
                $subject = CourseSubject::create($data);
                $response->status = $response::status_ok;
                $response->messages = "Successfully added";
                $response->result = $subject;
                return FacadeResponse::json($response);
            }  catch (Exception $e) {
                $response->status = $response::status_fail;
                $response->messages = $e->getMessage();
                return FacadeResponse::json($response);
            }
        } else {
            $response->status = $response::status_fail;
            $response->messages = "Course can be added only once";
            return FacadeResponse::json($response);
        }
    }



    public function courseDetail ($id) {
        return Course::where('id', $id)->first();
    }

    public function getSubjectsByCourseId ($id) {
        return Course::where('id', $id)->with('subjects')->first();
    }

    public function getSubjectlistWithoutChapterByCourseId ($id) {
        $subjectList = CourseSubject::where('course_id', $id)
            // ->has('chapters')
            ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
            ->select('course_subjects.id as id', 'course_subjects.code as code',
             'subjects.id as subject_id', 'subjects.name as name',
             'subjects.name_bn as name_bn',
             'subjects.color_name as color_name',
             'course_subjects.e_book_url',
             'course_subjects.is_free',
             'course_subjects.e_book_url_aws')
            ->orderBy('course_subjects.sequence', 'asc')
            ->get();

        foreach ($subjectList as $item)
        {
            $item->is_purchased = false;
        }

        return $subjectList;
    }

    public function getSubjectlistByCourseId ($id) {
        $subjectList = CourseSubject::where('course_id', $id)
            ->has('chapters')
            ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
            ->select('course_subjects.id as id', 'course_subjects.code as code',
             'subjects.id as subject_id', 'subjects.name as name',
             'subjects.name_bn as name_bn',
             'subjects.color_name as color_name',
             'course_subjects.e_book_url',
             'course_subjects.is_free',
             'course_subjects.e_book_url_aws')
            ->orderBy('course_subjects.sequence', 'asc')
            ->get();

        foreach ($subjectList as $item)
        {
            $item->is_purchased = false;
        }

        return $subjectList;
    }

    public function getSubjectlistByUserIdCourseId (Request $request)
    {
        $course_id = $request->course_id ? $request->course_id : 0;
        $user_id = $request->user_id ? $request->user_id : 0;

        $subjectList = CourseSubject::where('course_id', $course_id)
            ->has('chapters')
            ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
            ->select('course_subjects.id as id', 'course_subjects.code as code',
             'subjects.id as subject_id', 'subjects.name as name',
             'subjects.name_bn as name_bn',
             'subjects.color_name as color_name',
             'course_subjects.e_book_url',
             'course_subjects.is_free',
             'course_subjects.e_book_url_aws')
            ->orderBy('course_subjects.sequence', 'asc')
            ->get();

        foreach ($subjectList as $item)
        {
            $is_purchased_video = LectureVideoParticipant::where('user_id', $user_id)
                            ->where('course_subject_id', $item->id)
                            ->where('payment_status', 'completed')
                            ->first();

            if(!empty($is_purchased_video)){
                $item->is_purchased = true;
            }else{
                $item->is_purchased = false;
            }
        }

        return $subjectList;
    }

    public function geteBooklistByCourseId ($id) {
        $subjectList = CourseSubject::where('course_id', $id)
            ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
            ->whereNotNull('course_subjects.e_book_url')
            ->orWhereNotNull('course_subjects.e_book_url_aws')
            ->select('course_subjects.id as id',
            'course_subjects.code as code',
            'subjects.id as subject_id',
            'subjects.name as name',
            'subjects.name_bn as name_bn',
            'subjects.color_name as color_name',
            'course_subjects.e_book_url',
            'course_subjects.is_free',
            'course_subjects.e_book_url_aws')
            ->orderBy('subjects.sequence', 'asc')
            ->get();

        return $subjectList;

    }
    public function getSubjectListNotInCourse ($id) {
        $courseSubjectList = CourseSubject::where('course_id', $id)
        ->select('subject_id as id')->get();
        $subjects = Subject::whereNotIn('id', $courseSubjectList)->get();
        return $subjects;
    }
    public function GetCourseListAll(){
        return Course::sAll();
    }
    public function GetCourseList(){
       return Course::where('status', 'active')->get();
    }

    public function CouresSelectByUser( Request $request ){
        $subjects = [];
        $response = new ResponseObject;

        $data = $request->json()->all();
        // validating the request
        $validator = Validator::make($data, [
            'user_id' => 'required',
            'course_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }

        try {
        $user_id = $request->user_id;
        $course_id = $request->course_id;

        $skip_course_ids = array(12,13,14,15,21,22,23,24,27,29,32,33,35,36);

        if (!in_array($request->course_id, $skip_course_ids))
          {
                  $user_course = UserCourse::where('user_id', $user_id)
                                                ->where('course_id', $course_id)
                                                ->first();
                    if(!$user_course){
                        UserCourse::create([
                            'user_id' => $user_id,
                            'course_id' => $course_id
                        ]);
                    }

                    User::where('id', $user_id)
                    ->update(['current_course_id' => $course_id]);
                    $response->status = $response::status_ok;
                    $response->messages = "Course selected";
          }
        else
          {
              $response->status = $response::status_ok;
              $response->messages = "Subjects of courses";
          }


        // if ($request->course_id < 12 || $request->course_id > 15) {
        //     if ($request->course_id < 21 || $request->course_id > 24) {
        //         if ($request->course_id != 27) {
        //             $user_course = UserCourse::where('user_id', $user_id)
        //                                         ->where('course_id', $course_id)
        //                                         ->first();
        //             if(!$user_course){
        //                 UserCourse::create([
        //                     'user_id' => $user_id,
        //                     'course_id' => $course_id
        //                 ]);
        //             }

        //             User::where('id', $user_id)
        //             ->update(['current_course_id' => $course_id]);
        //             $response->status = $response::status_ok;
        //             $response->messages = "Course selected";
        //         } else {
        //             $response->status = $response::status_ok;
        //             $response->messages = "Subjects of courses";
        //         }
        //     }
        // }  else {

        //     $response->status = $response::status_ok;
        //     $response->messages = "Subjects of courses";
        // }


        $response->result = $this->getCourseResult($user_id, $course_id);
        // $verifyController = new VerifyCodeController();
        // $response->result = $verifyController->getLoginData($user_id);
          return FacadeResponse::json($response);

        } catch (Exception $e) {
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);
        }
    }



    public function getCourseResult ($user_id, $course_id) {

        $user_data = User::where('users.id', $user_id)
        ->join('courses','users.current_course_id','=','courses.id')
        ->select('users.id as id', 'users.name as name', 'users.email as email', 'users.address as address', 'users.gender as gender', 'users.points as points', 'users.isBangladeshi as isBangladeshi',
        'users.mobile_number as mobile_number', 'courses.name as courseName', 'courses.name_bn as course_name_bn', 'courses.name_jp as course_name_jp',  'users.current_course_id as current_course_id',
        'users.user_code as refferal_code', 'users.isLampFormSubmitted as isLampFormSubmitted', 'users.is_applied_scholarship as is_applied_scholarship',
        'users.isCompleteRegistration', 'users.isSetPassword', 'users.is_staff', 'users.is_e_edu_3', 'users.is_e_edu_c_unit',
        'users.c_unit_start_date',
        'users.b_unit_start_date',
        'users.d_unit_start_date',
        'users.c_unit_optional_subject_id'
         )
        ->first();
        $refferenceCount = User::where('refference_id', $user_id)->count();

       $favorite = new LectureFavoriteController();

       $user = (object)[
          "id" => $user_data->id,
          "name" =>$user_data->name,
          "email" =>$user_data->email,
          "mobile_number" =>$user_data->mobile_number,
          "gender" => $user_data->gender,
          "address" => $user_data->address,
          "points" => $user_data->points,
          "isBangladeshi" => $user_data->isBangladeshi,
          "courseName" => $user_data->courseName,
          "course_name_bn" => $user_data->course_name_bn,
          "course_name_jp" => $user_data->course_name_jp,
          "current_course_id" => $user_data->current_course_id,
          "favorite_courses" => $favorite->getFavoriteLectureByUserId($user_id),
          "refferal_code" => $user_data->refferal_code,
          "refferenceCount" => $refferenceCount,
          "isLampFormSubmitted" => $user_data->isLampFormSubmitted,
          "is_applied_scholarship" => $user_data->is_applied_scholarship,
          "is_e_edu_3" => $user_data->is_e_edu_3,
          "is_e_edu_c_unit" => $user_data->is_e_edu_c_unit,
          "c_unit_start_date" => $user_data->c_unit_start_date,
          "b_unit_start_date" => $user_data->b_unit_start_date,
          "d_unit_start_date" => $user_data->d_unit_start_date,
          "c_unit_optional_subject_id" => $user_data->c_unit_optional_subject_id,
          'qr-code-url' => 'api/getQRCodeByUserId/'.$user_data->id
       ];

       if ($course_id == 9) {

          $subjects = CourseSubject::where('course_id', 9)
          ->join('subjects','course_subjects.subject_id','=','subjects.id')
          ->select('subjects.name as name', 'subjects.name_bn as name_bn', 'subjects.name_jp as name_jp', 'subjects.id as id', 'subjects.color_name as color_name', 'course_subjects.subject_id as subject_id')
          ->orderBy('course_subjects.sequence', 'asc')
          ->get();
          foreach ($subjects as $subject ) {
              $exams = SubjectExam::where('subject_id', $subject->id)->get();

            foreach ($exams as $exam) {
              $result = ResultSubject::where('user_id', $user_id)->where('subject_exam_id', $exam->id)->first();
              $exam->mark = $result ?  $result->mark : null;
              $exam->subject_exam_id = $result ?   $result->subject_exam_id : null;
              $exam->user_id =  $result ?  $result->user_id : null;
            }
           $subject->exams = $exams;
          }

        } else if ($course_id == 4) {
          $subjects = Course::where('isSubCourse', true)->where('parent_course_id', 4)->select('id', 'name', 'name_bn')->get();

          foreach($subjects as $subject) {
              $subject->color_name = "blue";
              $subject->exams = [];
          }
        } else if ($course_id == 28) {
          $subjects = Course::where('isSubCourse', true)->where('parent_course_id', 28)->select('id', 'name', 'name_bn')->get();

          foreach($subjects as $subject) {
              $subject->color_name = "blue";
              $subject->exams = [];
          }
        } else if ($course_id == 34) {
            $subjects = Course::where('isSubCourse', true)->where('parent_course_id', 34)->select('id', 'name', 'name_bn')->get();

            foreach($subjects as $subject) {
                $subject->color_name = "blue";
                $subject->exams = [];
            }
          } else {
          $subjects = CourseSubject::where('course_id', $course_id)
          ->join('subjects','course_subjects.subject_id','=','subjects.id')
          ->select('subjects.name as name', 'subjects.name_bn as name_bn', 'subjects.name_jp as name_jp', 'subjects.id as id', 'subjects.color_name as color_name', 'course_subjects.subject_id as subject_id')
          ->orderBy('course_subjects.sequence', 'asc')
          ->with('exams')
          ->get();

       foreach ($subjects as $subject) {
          foreach ($subject->exams as $exam) {
            $exam->details_url = "api/question/getSubjectExamQuestionsById/" .$exam->id .'/'.$exam->question_number;
          }
        }
        }
        if (($course_id >= 12 || $course_id <= 15) || $course_id == 27 ) {
        $user->current_course_id =  $course_id;
      }

       if ( $course_id == 29 || $course_id == 32 || $course_id == 33) {
        $user->current_course_id =  $course_id;
      }

      if ( $course_id == 35 || $course_id == 36 ) {
        $user->current_course_id =  $course_id;
      }

       $result_data =  (object)[
           'user' => $user,
           'subjects' => $subjects,
           'bmooc_corner' => []
       ];
       return $result_data;
    }

    public function buyCourse (Request $request, Common $common) {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'course_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }

        $amountForPayment = $data['amount'] + $data['discount'];

        $subjectList = CourseSubject::where('course_id', $data['course_id'])
                ->join('subjects', 'course_subjects.subject_id', '=' , 'subjects.id')
                ->select('subjects.id as id')
                ->get();
        $coursePrice = $this->getCoursePrice($common, $subjectList, $data['user_id']);
        // $subjectPrice = $this->getSubjectPrice($common, $data['subject_id'], $data['user_id']);

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
        $due = $lastDue + ($coursePrice - $amountForPayment) - $lastBalance;
        $balance = $lastBalance + ($amountForPayment - $coursePrice);
        } else {
            $due = $coursePrice - $amountForPayment;
            $balance = $amountForPayment - $coursePrice;
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

        foreach($subjectList as $sub) {

            $chapterList = Chapter::where('subject_id', $sub->id)->get();

            foreach ($chapterList as $chapter) {
                $chapterArray = (array)[
                    "user_id" => $data['user_id'],
                    "amount" => $amountForPayment,
                    "chapter_id" => $chapter->id
                 ];
                 $this->buyChapterPrivate($payment, $chapterArray, $amountForPayment);
            }

        }



        $response->status = $response::status_ok;
        $response->messages = "Successfully bought";
        return FacadeResponse::json($response);
    }



    public function buyChapterPrivate ($payment, $data, $amountForPayment) {

        $paymentAmount = $amountForPayment;
        $chapterPrice = $this->getChapterPrice($data['chapter_id'], $data['user_id']);


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

    public function getCoursePrice($common, $subjectList, $userId) {
        $sum = 0;
        foreach($subjectList as $subject) {
            $sum += $this->getSubjectPrice($common, $subject->id, $userId) ;
        }
        return $sum;
    }

    public function getSubjectPrice ($common, $subjectId, $userId) {
        $sum = 0;
        $chapterList = Chapter::where('subject_id', $subjectId)->get();
        foreach($chapterList as $chapter) {
            $sum += $common->getChapterPrice($chapter->id, $userId);
        }
        return $sum;
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


    public function sendFCMNotification ($token, $obj) {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder($obj->title);
        $notificationBuilder->setBody($obj->body)
                            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();


        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        //return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();

        //return Array (key : oldToken, value : new token - you must change the token in your database )
        $downstreamResponse->tokensToModify();

        //return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();

    }
    public function getSubjectNumberByCourse () {
        $subjects = [];
        $subjectCount = CourseSubject::join('courses', 'course_subjects.course_id', 'courses.id')
                        ->select('courses.id',
                            DB::raw('count(*) as total')
                            )
                            ->groupBy('courses.id')
                        ->get();
        foreach ($subjectCount as $key=>$element) {
            switch ($element->id) {
                case 3:
                    $subjects[] = (Object) [
                        'name' => 'JSC',
                        'total' => $element->total
                    ];
                break;
                case 1:
                    $subjects[] = (Object) [
                        'name' => 'SSC',
                        'total' => $element->total
                    ];
                break;
                case 2:
                    $subjects[] = (Object) [
                        'name' => 'HSC',
                        'total' => $element->total
                    ];
                break;
                case 5:
                    $subjects[] = (Object) [
                        'name' => 'Medical',
                        'total' => $element->total
                    ];
                break;
                case 12:
                    $subjects[] = (Object) [
                        'name' => 'University Unit A',
                        'total' => $element->total
                    ];
                break;
                case 13:
                    $subjects[] = (Object) [
                        'name' => 'University Unit B',
                        'total' => $element->total
                    ];
                break;
                case 14:
                    $subjects[] = (Object) [
                        'name' => 'University Unit C',
                        'total' => $element->total
                    ];
                break;
                case 15:
                    $subjects[] = (Object) [
                        'name' => 'University Unit D',
                        'total' => $element->total
                    ];
                break;
            }
        }
        return FacadeResponse::json($subjects);
    }

    public function geteBooklist () {
        $list = [];
         $list[] = (Object) [
                "id" => 51,
                "name" => "Class One",
                "name_bn" => "প্রথম শ্রেণী",
                "e_books" => eBook::where('course_id', 51)->get()
            ];

         $list[] = (Object) [
                "id" => 52,
                "name" => "Class Two",
                "name_bn" => "দ্বিতীয় শ্রেণী",
                "e_books" => eBook::where('course_id', 52)->get()
            ];
         $list[] = (Object) [
                "id" => 53,
                "name" => "Class Three",
                "name_bn" => "তৃতীয় শ্রেণী",
                "e_books" => eBook::where('course_id', 53)->get()
            ];
         $list[] = (Object) [
                "id" => 54,
                "name" => "Class Four",
                "name_bn" => "চতুর্থ শ্রেণী",
                "e_books" => eBook::where('course_id', 54)->get()
            ];
         $list[] = (Object) [
                "id" => 55,
                "name" => "Class Five",
                "name_bn" => "পঞ্চম শ্রেণী",
                "e_books" => eBook::where('course_id', 55)->get()
            ];
         $list[] = (Object) [
                "id" => 56,
                "name" => "Class Six",
                "name_bn" => "ষষ্ঠ শ্রেণী",
                "e_books" => eBook::where('course_id', 56)->get()
            ];
         $list[] = (Object) [
                "id" => 57,
                "name" => "Class Seven",
                "name_bn" => "সপ্তম শ্রেণী",
                "e_books" => eBook::where('course_id', 57)->get()
            ];

        $courses = Course::with('eBooks')->has('eBooks')->select('id', 'name', 'name_bn')->get();

        foreach ($courses as $course) {
            $list[] = $course;
        }
        $casio = (Object) [
                "id" => 0,
                "name" => "Casio Book",
                "name_bn" => "ক্যাসিও বুক",
                "e_books" => eBook::where('course_id', 0)->get()
            ];
            $list[] = $casio;
        return FacadeResponse::json($list);
    }

    public function getCourseListWithSubject () {
        // $user = User::where('id', $request->userId)->first();
        // $list = CourseType::where('status', 'Active')->with(['courses'=> function ($query) use ($user) {
        //     return $query->where('id', $user->current_course_id);
        // }])->whereNotIn('id', [7,10])->select('id','name', 'name_bn', 'name_jp')->orderBy('sequence', 'asc')->get();
        $list = CourseType::where('status', 'Active')->with('courses','courses.subjects')->whereNotIn('id', [7,10])->select('id','name', 'name_bn', 'name_jp')->orderBy('sequence', 'asc')->get();
        return $list ;
    }

    public function chapterEnterJson (Request $request) 
    {
        $course_id = $request->course_id ? $request->course_id : 0;
        $subject_id = $request->subject_id ? $request->subject_id : 0;

        if(!$course_id || !$subject_id){
            $response = (Object) [
                "message"   => "Please, Attach course or subject ID",
                "data"      => [],
            ];
            return FacadeResponse::json($response); 
        }

        $list = Chapter::where('course_id', $course_id)->where('subject_id', $subject_id)->get();

        $data = [];
        foreach ($list as $item) {
            array_push($data, [
                'chapter_id' => $item->id,
                'chap_name' => $item->name,
                'lectures' => 0,
            ]);
        }

        $response = (Object) [
            "message"   => "Chapter List",
            "data"      => $data,
        ];
        return FacadeResponse::json($response);
    }
    
}


