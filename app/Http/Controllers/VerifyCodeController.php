<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use \Illuminate\Http\Response;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use App\User;
use App\UserCode;
use App\Course;
use App\CourseSubject;
use App\SubjectExam;
use App\ResultSubject;
use App\UserToken;
use App\UserAllPayment;
use App\eBook;
use App\LectureSheet;
use App\BmoocCorner;
use JWTFactory;
use JWTAuth;
use Validator;
use Carbon\Carbon;

class VerifyCodeController extends Controller
{


//   public function VerifyCode(Request $request){
//       $response = new ResponseObject;
//       $subjects = [];

//       $data = $request->json()->all();

//       $validator = Validator::make($data, [
//           'id' => 'required',
//           'code' => 'required',
//       ]);
//       if ($validator->fails()) {
//          $response->status = $response::status_fail;
//          $response->messages = $validator->errors();
//          return FacadeResponse::json($response);
//      }
//       $user_id =  $request->get('id');
//       $user_code =  $request->get('code');




//       $user_code_data = UserCode::where('user_id', $user_id)
//                         ->where('code', $user_code)
//                         ->where('expire_at','>=',Carbon::now())
//                         ->first();
//       if(!$user_code_data){
//          $response->status = $response::status_fail;
//          $response->messages = "Code not found or code expire";
//          return FacadeResponse::json($response);
//       };


//       $user = User::where('id', $user_id)->where('isCompleteRegistration', false)->first();
//       if (!is_null($user)) {
//       if ($user->refference_id) {
//          $reffence = User::where('id', $user->refference_id)->select('id', 'fcm_id')->first();

//          $refferedUserNumber = User::where('refference_id', $reffence->id)->where('isCompleteRegistration', true)->count();

//          $fcmObject = (object) [
//           "title" => "Congratulations!",
//           "body" => $user->name . " has just registered with your refference",
//           "data" => $refferedUserNumber + 1
//         ];
//         if ($reffence->fcm_id) {
//           $registerController = new APIRegisterController();
//           $registerController->sendFCMNotification($reffence->fcm_id, $fcmObject);
//         }
//       }
//         User::where('id', $user_id)->update(['isCompleteRegistration'=> true]);
//       }


//     $result_data = $this->getLoginData($user_id);
//     $response->status = $response::status_ok;

//     $response->messages = "Code matched";
//     $response->result = $result_data;

//     return FacadeResponse::json($response);
//   }


   public function VerifyCode(Request $request){
      $response = new ResponseObject;
      $subjects = [];

      $data = $request->json()->all();

      $validator = Validator::make($data, [
          'id' => 'required',
          'code' => 'required',
      ]);
      if ($validator->fails()) {
         $response->status = $response::status_fail;
         $response->messages = $validator->errors();
         return FacadeResponse::json($response);
     }
      $user_id =  $request->get('id');
      $user_code =  $request->get('code');

      $user_code_data = UserCode::where('user_id', $user_id)
                        ->where('code', $user_code)
                        ->where('expire_at','>=',Carbon::now())
                        ->first();
      if(!$user_code_data){
         $response->status = $response::status_fail;
         $response->messages = "Code not found or code expire";
         return FacadeResponse::json($response);
      };


    $result_data = $this->getLoginData($user_id);
    $response->status = $response::status_ok;

    $response->messages = "Code matched";
    $response->result = $result_data;

    return FacadeResponse::json($response);
   }

   public function getLoginData ($user_id) {


      $user_data = User::where('users.id', $user_id)
      ->leftJoin('courses','users.current_course_id','=','courses.id')
      ->leftJoin('divisions','users.division_id','=','divisions.id')
      ->leftJoin('districts','users.district_id','=','districts.id')
      ->leftJoin('thanas','users.thana_id','=','thanas.id')
      ->select('users.id as id', 'users.name as name', 'users.email as email','users.image','users.institute', 'users.address as address', 'users.gender as gender',
      'users.points as points', 'users.isBangladeshi as isBangladeshi',
      'users.mobile_number as mobile_number', 'courses.name as courseName',
      'courses.name_bn as course_name_bn', 'courses.name_jp as course_name_jp',
      'users.current_course_id as current_course_id',
      'users.user_code as refferal_code', 'users.isLampFormSubmitted as isLampFormSubmitted', 'users.is_applied_scholarship as is_applied_scholarship',
      'users.isCompleteRegistration', 'users.isSetPassword', 'users.is_staff', 'users.is_e_edu_3', 'users.is_e_edu_c_unit',
      'users.c_unit_start_date',
      'users.b_unit_start_date',
      'users.d_unit_start_date',
      'users.is_c_unit_purchased',
      'users.is_b_unit_purchased',
      'users.is_d_unit_purchased',
      'users.is_jicf_teacher',
      'users.c_unit_optional_subject_id',
      'users.division_id',
      'users.district_id',
      'users.thana_id',
      'divisions.name as division',
      'districts.name as district',
      'thanas.name as thana',
      'users.user_type',
       )->withCount([
        'fcmMessage as unseen_messages_count' => function ($query) {
            $query->where('seen', 0);
        }])->first();
      // creating the token
         try {
            if (! $token = JWTAuth::fromUser($user_data)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
            } catch (JWTException $e) {
                  return response()->json(['error' => 'could_not_create_token'], 500);
         }
         $refferenceCount = User::where('refference_id', $user_id)->where('isCompleteRegistration', 1)->count();
         $favorite = new LectureFavoriteController();

         $eBooks = UserAllPayment::join('e_books','user_all_payments.item_id','e_books.id')
        ->where('user_all_payments.user_id', $user_id)->where('user_all_payments.item_type','=','E-Book')
        ->select('e_books.*')
        ->get();

         $ebookIds =  UserAllPayment::where('user_all_payments.user_id', $user_id)->where('user_all_payments.item_type','=','E-Book')->pluck('item_id');
         $eBooks = eBook::whereIn('id', $ebookIds)->with('e_book_feature','e_book_description_title.e_book_description_detial')->get();

         foreach ($eBooks as $ebook) {
              $ebook->is_bought = true;
         }

        $lectureSheetIds =  UserAllPayment::where('user_all_payments.user_id', $user_id)->where('user_all_payments.item_type','=','Lecture Sheet')->pluck('item_id');
         $lectureSheets = LectureSheet::whereIn('id', $lectureSheetIds)->with('lecture_sheet_feature','lecture_sheet_description_title.lecture_sheet_description_detial')->get();

         foreach ($lectureSheets as $lectureSheet) {
              $lectureSheet->is_bought = true;
         }

         $user = (object)[
            "id" => $user_data->id,
            "name" =>$user_data->name,
            "email" =>$user_data->email,
            "image" => $user_data->image ? 'https://api.bacbonschool.com/uploads/userImages/'.$user_data->image: null,
            "mobile_number" =>$user_data->mobile_number,
            "user_type" => $user_data->user_type,
            "gender" => $user_data->gender,
            "points" => $user_data->points,
            "institute" => $user_data->institute,
            "division_id" => $user_data->division_id,
            "district_id" => $user_data->district_id,
            "thana_id" => $user_data->thana_id,
            "division" => $user_data->division,
            "district" => $user_data->district,
            "thana" => $user_data->thana,
            "address" => $user_data->address,
            "courseName" => $user_data->courseName,
            "course_name_bn" => $user_data->course_name_bn,
            "course_name_jp" => $user_data->course_name_jp,
            "current_course_id" => $user_data->current_course_id,
            "refferal_code" => $user_data->refferal_code,
            "isBangladeshi" => $user_data->isBangladeshi,
            "isCompleteRegistration" => $user_data->isCompleteRegistration,
            "isSetPassword" => $user_data->isSetPassword,
            "token" =>$token,
            "favorite_courses" => $favorite->getFavoriteLectureByUserId($user_id),
            "refferenceCount" => $refferenceCount,
            "isLampFormSubmitted" => $user_data->isLampFormSubmitted,
            "is_applied_scholarship" => $user_data->is_applied_scholarship,
            "is_e_edu_3" => $user_data->is_e_edu_3,
            "is_e_edu_c_unit" => $user_data->is_e_edu_c_unit,
            "c_unit_start_date" => $user_data->c_unit_start_date,
            "b_unit_start_date" => $user_data->b_unit_start_date,
            "d_unit_start_date" => $user_data->d_unit_start_date,
            "is_c_unit_purchased" => $user_data->is_c_unit_purchased,
            "is_b_unit_purchased" => $user_data->is_b_unit_purchased,
            "is_d_unit_purchased" => $user_data->is_d_unit_purchased,
             "is_jicf_teacher" => $user_data->is_jicf_teacher,
            "unseen_messages_count" => $user_data->unseen_messages_count,
            "c_unit_optional_subject_id" => $user_data->c_unit_optional_subject_id,
            'qr-code-url' => 'api/getQRCodeByUserId/'. $user_data->id,
            'purchased_ebooks' => $eBooks,
            'purchased_lecture_sheets' => $lectureSheets
         ];
         UserToken::create([
             "user_id" => $user_data->id,
             "token" => $token
        ]);
         $bmoocCorner = [];
          if ($user_data->current_course_id == 9) {

            $subjects = CourseSubject::where('course_id', 9)
            ->join('subjects','course_subjects.subject_id','=','subjects.id')
            ->select('subjects.name as name', 'subjects.name_bn as name_bn', 'subjects.name_jp as name_jp', 'subjects.id as id', 'subjects.color_name as color_name', 'course_subjects.subject_id as subject_id')
            ->orderBy('course_subjects.sequence', 'asc')
            ->get();
            foreach ($subjects as $subject ) {
                // $results = ResultSubject::where('user_id', $user_id)->get();
                $exams = SubjectExam::where('subject_id', $subject->id)->get();

              foreach ($exams as $exam) {
                $result = ResultSubject::where('user_id', $user_id)->where('subject_exam_id', $exam->id)->first();
                $exam->mark = $result ?  $result->mark : null;
                $exam->subject_exam_id = $result ?   $result->subject_exam_id : null;
                $exam->user_id =  $result ?  $result->user_id : null;
                $exam->details_url = "api/question/getSubjectExamQuestionsById/" .$exam->id .'/'.$exam->question_number;
              }
             $subject->exams = $exams;
            }

          } else {
            if ($user_data->current_course_id == 4) {
                $subjects = Course::where('isSubCourse', true)->where('parent_course_id', 4)->select('id', 'name', 'name_bn', 'name_jp')->get();

                foreach($subjects as $subject) {
                    $subject->color_name = "blue";
                    $subject->exams = [];
                }
              } else if ($user_data->current_course_id == 28) {
                $subjects = Course::where('isSubCourse', true)->where('parent_course_id', 28)->select('id', 'name', 'name_bn')->get();

                foreach($subjects as $subject) {
                    $subject->color_name = "blue";
                    $subject->exams = [];
                }
              } else if ($user_data->current_course_id == 34) {
                $subjects = Course::where('isSubCourse', true)->where('parent_course_id', 34)->select('id', 'name', 'name_bn')->get();

                foreach($subjects as $subject) {
                    $subject->color_name = "blue";
                    $subject->exams = [];
                }
              }
              else {

                $subjects = CourseSubject::where('course_id', $user_data->current_course_id)
                // ->has('chapters')
                ->join('subjects','course_subjects.subject_id','=','subjects.id')
                ->select('subjects.name as name', 'subjects.name_bn as name_bn', 'subjects.name_jp as name_jp', 'subjects.id as id', 'subjects.color_name as color_name', 'course_subjects.subject_id as subject_id')
                ->orderBy('subjects.sequence', 'asc')
                ->with('exams')
                ->get();

                foreach ($subjects as $subject) {
                  foreach ($subject->exams as $exam) {
                    $exam->details_url = "api/question/getSubjectExamQuestionsById/" .$exam->id .'/'.$exam->question_number;
                  }
                }
              }
          }


         return $result_data =  (object)[
             'user' => $user,
             'subjects' => $subjects,
             'bmooc_corner' => $bmoocCorner
         ];

   }
}
