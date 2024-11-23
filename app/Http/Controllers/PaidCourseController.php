<?php

namespace App\Http\Controllers;
use Auth;
use PDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Mpdf\Mpdf;
use Excel;

use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Exception;
use App\User;
use Carbon\Carbon;
use App\Subject;
use App\PaidCourse;
use App\QuizQuestionSet;
use App\UserAllPayment;
use App\PaidCourseCoupon;
use App\PaidCourseApplyCoupon;
use App\Http\Helper\ResponseObject;
use App\PaidCourseDescriptionDetail;
use App\PaidCourseDescriptionTitle;
use App\PaidCourseFeature;
use App\PaidCourseCoreSubject;
use App\PaidCourseQuizSubject;
use App\PaidCourseMaterial;
use App\PaidCourseParticipant;
use App\PaidCourseParticipantQuizAccess;
use App\PaidCourseQuizParticipationCount;
use App\PaidCourseStudentMapping;
use App\PaidCourseQuizQuestion;
use App\PaidCourseSubject;
use App\ResultPaidCouresQuiz;
use App\PaidCourseWrittenQuestion;
use App\PaidCourseWrittenAttachment;
use App\ResultPaidCouresQuizAnswer;
use App\ResultPaidCourseWrittenMark;
use App\ResultPaidCourseQuizSubjectWiseAnswer;
use App\ResultPaidCourseWrittenAttachment;
use App\UserAllPaymentDetails;

use App\Exports\StudentExamResultExport;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Response as FacadeResponse;

class PaidCourseController extends Controller
{
    public function getAllPaidCourseAdmin()
    {
        $response = new ResponseObject;

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = PaidCourse::orderby('id', 'desc')->get();
        return FacadeResponse::json($response);
    }

    public function getAllActivePaidCourseAdmin()
    {
        $response = new ResponseObject;

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = PaidCourse::where('is_active', true)->orderby('id', 'desc')->get();
        return FacadeResponse::json($response);
    }

    public function getAllPaidCourse()
    {
        $response = new ResponseObject;

        $all_courses = PaidCourse::where('is_active', true)
        ->where('course_type', 'universityAdmissionCourse')
        ->orderby('id', 'desc')->get();

        foreach ($all_courses as $item) {
            $item->features = PaidCourseFeature::where('paid_course_id', $item->id)->get();
        }

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $all_courses;
        return FacadeResponse::json($response);
    }

    public function getAllPaidCourseForMobile(Request $request)
    {
        $user_id = $request->user_id ? $request->user_id : 0;
        $course_type = $request->course_type ?? "universityAdmissionCourse";

        $all_course = PaidCourse::select('id', 'name', 'name_bn', 'gp_product_id', 'description', 
            'youtube_url', 'thumbnail', 
            'number_of_students_enrolled', 
            'regular_amount', 
            'sales_amount', 
            'discount_percentage',
            'is_only_test', 
            'has_trail', 
            'trail_day',
            'is_lc_enable'
        )
        ->where('course_type', $course_type)
        ->where('is_active', true)
        ->orderby('id', 'desc')
        ->get();

        foreach ($all_course as $item) {

            if ($user_id) {
                $is_purchased = PaidCourseParticipant::where('user_id', $user_id)
                    ->where('paid_course_id', $item->id)->first();

                if (!empty($is_purchased)) {
                    $item->is_lc_activated = $is_purchased->is_lc_activated;
                    $item->is_purchased = true;
                } else {
                    $item->is_lc_activated = false;
                    $item->is_purchased = false;
                }
            } else {
                $item->is_lc_activated = false;
                $item->is_purchased = false;
            }
        }
        return FacadeResponse::json($all_course);
    }

    public function getPaidCourseStudentList(Request $request){
        $students = PaidCourseParticipant::select("paid_course_participants.id as participant_id", "paid_course_participants.is_lc_activated", "users.id", "users.name", "users.mobile_number", "users.email")
        ->leftJoin('users', 'users.id', 'paid_course_participants.user_id')
        ->where('paid_course_participants.paid_course_id', $request->paid_course_id)
        ->where('is_lc_activated', true)
        ->get();

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "Students listed successfully";
        $response->data = $students;
        return response()->json($response);
    }
    
    public function getPaidCourseLCStudentList(Request $request)
    {
        $students = PaidCourseParticipant::select("paid_course_participants.id as participant_id", "paid_course_participants.is_lc_activated", "users.id", "users.name", "users.mobile_number", "users.email")
        ->leftJoin('users', 'users.id', 'paid_course_participants.user_id')
        ->where('paid_course_participants.paid_course_id', $request->paid_course_id)
        ->get();

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "Students listed successfully";
        $response->data = $students;
        return response()->json($response);
    }

    public function getPaidCourseLCStudentListByMentor(Request $request)
    {
        $student_ids = PaidCourseStudentMapping::where('mentor_id', $request->mentor_id)->where('paid_course_id', $request->paid_course_id)->pluck('student_id');
        $students = User::select("users.id", "users.name", "users.mobile_number", "users.email")->whereIn("id", $student_ids)->get();

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "Students listed successfully";
        $response->data = $students;
        return response()->json($response);
    }

    public function activateLC(Request $request)
    {
        $response = new ResponseObject;

        PaidCourseParticipant::where('id', $request->id)->update([
            "is_lc_activated" => true
        ]);

        $response->status = $response::status_ok;
        $response->messages = "LC Activated successfully";
        $response->data = [];
        return response()->json($response);
    }


    public function deactivateLC(Request $request)
    {
        $response = new ResponseObject;

        PaidCourseParticipant::where('id', $request->id)->update([
            "is_lc_activated" => false
        ]);

        $response->status = $response::status_ok;
        $response->messages = "LC deactivated successfully";
        $response->data = [];
        return response()->json($response);
    }

    public function paidCourseMapping(Request $request){
        $response = new ResponseObject;
        try {
            DB::beginTransaction();
            if (!$request->paid_course_id) {
                $response->status = $response::status_fail;
                $response->messages = "Please, Select Paid Course!";
                $response->data = [];
                return response()->json($response);
            }

            if(!empty($request->map_data)){
                $mapping = [];
                foreach ($request->map_data as $key => $value) {

                    $is_exist = PaidCourseStudentMapping::where('paid_course_id', $request->paid_course_id)
                        ->where('student_id', $value['student_id'])
                        ->where('mentor_id', $value['mentor_id'])
                        ->first();

                    if(empty($is_exist)){
                        $mapping[] = [
                            'paid_course_id' => $request->paid_course_id,
                            'student_id' => $value['student_id'],
                            'mentor_id' => $value['mentor_id'],
                            'is_active' => true,
                        ];
                    }
                }

                PaidCourseStudentMapping::insert($mapping);
                DB::commit();

                $response->status = $response::status_ok;
                $response->messages = "Mapping has been inserted successfully!";
                $response->data = [];
                return response()->json($response);
            }

        } catch (Exception $e) {
            DB::rollback();
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            $response->data = [];
            return response()->json($response);
        }
    }

    public function getPaidCourseMappingList(Request $request)
    {
        $response = new ResponseObject;

        $mapping_data = PaidCourseStudentMapping::select(
            'paid_course_student_mappings.id',
            'paid_course_student_mappings.paid_course_id',
            'paid_course_student_mappings.is_active',
            'students.id as student_id',
            'students.name as student_name',
            'students.mobile_number as student_mobile_number',

            'teachers.id as mentor_id',
            'teachers.name as mentor_name',
            'teachers.mobile_number as mentor_mobile_number'
        )
        ->leftJoin('users as students', 'paid_course_student_mappings.student_id', '=', 'students.id')
        ->leftJoin('users as teachers', 'paid_course_student_mappings.mentor_id', '=', 'teachers.id')
        ->where('paid_course_student_mappings.paid_course_id', $request->paid_course_id)
        ->get();

        $response->status = $response::status_ok;
        $response->messages = "Mapping list successfull!";
        $response->data = $mapping_data;
        return response()->json($response);
    }

    public function removeMappingFromPaidCourse(Request $request)
    {
        $response = new ResponseObject;

        PaidCourseStudentMapping::where('id', $request->id)->delete();

        $response->status = $response::status_ok;
        $response->messages = "Mapping Deleted successfully";
        $response->data = [];
        return response()->json($response);
    }

    public function getPaidCourseFilterListForMobile(Request $request)
    {
        $user_id = $request->user_id ? $request->user_id : 0;
        $is_cunit = $request->is_cunit ?? false;
        $course_type = "universityAdmissionCourse";

        $all_course = PaidCourse::select('id', 'name', 'name_bn', 'gp_product_id', 'description', 
            'youtube_url', 'thumbnail', 
            'number_of_students_enrolled', 
            'regular_amount', 
            'sales_amount', 
            'discount_percentage',
            'is_only_test', 
            'has_trail', 
            'trail_day',
            'is_lc_enable'
        )
        ->where('is_cunit', $is_cunit)
        ->where('course_type', $course_type)
        ->where('is_active', true)
        ->orderby('id', 'desc')
        ->get();

        foreach ($all_course as $item) {

            if ($user_id) {
                $is_purchased = PaidCourseParticipant::where('user_id', $user_id)
                    ->where('paid_course_id', $item->id)->first();

                if (!empty($is_purchased)) {
                    $item->is_lc_activated = $is_purchased->is_lc_activated;
                    $item->is_purchased = true;
                } else {
                    $item->is_purchased = false;
                    $item->is_lc_activated = false;
                }
            } else {
                $item->is_purchased = false;
                $item->is_lc_activated = false;
            }
        }
        return FacadeResponse::json($all_course);
    }

    public function myPaidCourselist(Request $request)
    {
        $user_id = $request->user_id ? $request->user_id : 0;

        $my_paid_courses = PaidCourseParticipant::where('user_id', $user_id)->pluck('paid_course_id');

        $all_course = PaidCourse::select('id', 'name', 'name_bn', 'gp_product_id', 'description', 
            'youtube_url', 
            'thumbnail', 
            'number_of_students_enrolled', 
            'regular_amount', 
            'sales_amount', 
            'discount_percentage',
            'is_only_test', 
            'has_trail', 
            'trail_day',
            'is_lc_enable'
        )
        ->whereIn('id', $my_paid_courses)
        ->get();

        return FacadeResponse::json($all_course);
    }

    public function createPaidCourse(Request $request)
    {

        //`test_type` enum('RevisionTest','ModelTest','WeeklyTest','') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'RevisionTest',
        $response = new ResponseObject;

        $formData = json_decode($request->data, true);

        if ($request->hasFile('iconThumbnail') && $request->hasFile('thumbnail')) {

            $thumbnail = $request->file('thumbnail');
            $time = time();
            $thumnailName = "CCthumbnail" . $time . '.' . $thumbnail->getClientOriginalExtension();
            $destinationThumbnail = 'uploads/paid_course_thumbnails';
            $thumbnail->move($destinationThumbnail, $thumnailName);

            $iconThumbnail = $request->file('iconThumbnail');
            $time = time();
            $iconThumnailName = "CCIcon" . $time . '.' . $iconThumbnail->getClientOriginalExtension();
            $destinationIconThumbnail = 'uploads/paid_course_icons';
            $iconThumbnail->move($destinationIconThumbnail, $iconThumnailName);

            $scheduleName = null;
            $scheduleNameUrl = null;

            if($request->file('studySchedule')){
                $studySchedule = $request->file('studySchedule');
                $time = time();
                $scheduleName = "CCschedule" . $time . '.' . $studySchedule->getClientOriginalExtension();
                $destinationSchedule = 'uploads/paid_course_schedules';
                $studySchedule->move($destinationSchedule, $scheduleName);
                $scheduleNameUrl = "http://" . $_SERVER['HTTP_HOST'] . '/uploads/paid_course_schedules/' . $scheduleName;
            }

            $solvedScheduleName = null;
            $solvedScheduleNameUrl = null;

            if($request->file('solvedSchedule')){
                $solvedSchedule = $request->file('solvedSchedule');
                $time = time();
                $solvedScheduleName = "SCschedule" . $time . '.' . $solvedSchedule->getClientOriginalExtension();
                $destinationSchedule = 'uploads/paid_course_schedules';
                $solvedSchedule->move($destinationSchedule, $solvedScheduleName);
                $solvedScheduleNameUrl = "http://" . $_SERVER['HTTP_HOST'] . '/uploads/paid_course_schedules/' . $solvedScheduleName;
            }

            try {
                DB::beginTransaction();
                $course = (array) [
                    "name" => $formData['name'],
                    "name_bn" => isset($formData['name_bn']) ? $formData['name_bn'] : null,
                    "course_type" => isset($formData['course_type']) ? $formData['course_type'] : 'universityAdmissionCourse',
                    "gp_product_id" => isset($formData['gp_product_id']) ? $formData['gp_product_id'] : null,
                    "description" => $formData['description'],
                    "youtube_url" => isset($formData['youtube_url']) ? $formData['youtube_url'] : null,
                    "thumbnail" => "http://" . $_SERVER['HTTP_HOST'] . '/uploads/paid_course_thumbnails/' . $thumnailName,
                    "paid_course_icon" => "http://" . $_SERVER['HTTP_HOST'] . '/uploads/paid_course_icons/' . $iconThumnailName,
                    "paid_course_schedule" => $scheduleNameUrl,
                    "paid_solve_class_schedule" => $solvedScheduleNameUrl,
                    "coupon_code" => isset($formData['coupon_code']) ? $formData['coupon_code'] : null,
                    "regular_amount" => $formData['regular_amount'],
                    "sales_amount" => $formData['sales_amount'],
                    "discount_percentage" => $formData['discount_percentage'] ?? 0,
                    "number_of_videos" => $formData['number_of_videos'] ?? 0,
                    "number_of_scripts" => $formData['number_of_scripts'] ?? 0,
                    "number_of_quizzes" => $formData['number_of_quizzes'] ?? 0,
                    "number_of_model_tests" => $formData['number_of_model_tests'] ?? 0,
                    "is_active" => $formData['is_active'],
                    "promo_status" => isset($formData['promo_status']) ? $formData['promo_status'] : null,
                    "has_trail" => $formData['has_trail'],
                    "is_only_test" => $formData['is_only_test'],
                    "is_cunit" => $formData['is_cunit'],
                    "folder_name" => str_replace(' ', '_', $formData['folder_name']),
                    "sort" => $formData['sort'],
                    "is_lc_enable" => $formData['is_lc_enable'] ?? 0,
                    "appeared_from" => date("Y-m-d H:i:s", strtotime($formData['appeared_from'])),
                    "appeared_to" => date("Y-m-d H:i:s", strtotime($formData['appeared_to'])),
                ];
                $courseObj = PaidCourse::create($course);

                foreach ($formData['features'] as $feature) {
                    PaidCourseFeature::create([
                        "paid_course_id" => $courseObj->id,
                        "name" => $feature['name'],
                    ]);
                }

                foreach ($formData['desTitles'] as $desTitle) {

                    $title = PaidCourseDescriptionTitle::create([
                        "paid_course_id" => $courseObj->id,
                        "name" => $desTitle['title'],
                    ]);

                    foreach ($desTitle['details'] as $detail) {
                        PaidCourseDescriptionDetail::create([
                            "paid_course_description_title_id" => $title->id,
                            "name" => $detail,
                        ]);
                    }
                }

                DB::commit();
                $response->status = $response::status_ok;
                $response->messages = "Paid course has been created";
                $response->result = $courseObj;
                return FacadeResponse::json($response);

            } catch (Exception $e) {
                DB::rollback();
                $response->status = $response::status_fail;
                $response->messages = $e->getMessage();
                $response->result = [];
                return FacadeResponse::json($response);
            }

        } else {
            $response->status = $response::status_fail;
            $response->messages = "Video or Thumbnail is missing";
            return FacadeResponse::json($response);
        }
    }

    public function updatePaidCourse(Request $request)
    {
        $response = new ResponseObject;

        $formData = json_decode($request->data, true);
            
        $thumnailName = null;
        if($request->hasFile('thumbnail')){
            $thumbnail = $request->file('thumbnail');
            $time = time();
            $thumnailName = "CCthumbnail" . $time . '.' . $thumbnail->getClientOriginalExtension();
            $destinationThumbnail = 'uploads/paid_course_thumbnails';
            $thumbnail->move($destinationThumbnail, $thumnailName);

            PaidCourse::where('id', $formData['id'])->update([
                "thumbnail" => "http://" . $_SERVER['HTTP_HOST'] . '/uploads/paid_course_thumbnails/' . $thumnailName
            ]);
        }
        
        $iconThumnailName = null;
        if ($request->hasFile('iconThumbnail')) {
            $iconThumbnail = $request->file('iconThumbnail');
            $time = time();
            $iconThumnailName = "CCIcon" . $time . '.' . $iconThumbnail->getClientOriginalExtension();
            $destinationIconThumbnail = 'uploads/paid_course_icons';
            $iconThumbnail->move($destinationIconThumbnail, $iconThumnailName);

            PaidCourse::where('id', $formData['id'])->update([
                "paid_course_icon" => "http://" . $_SERVER['HTTP_HOST'] . '/uploads/paid_course_icons/' . $iconThumnailName
            ]);
        }

        $scheduleName = null;

        if($request->file('studySchedule')){
            $studySchedule = $request->file('studySchedule');
            $time = time();
            $scheduleName = "CCschedule" . $time . '.' . $studySchedule->getClientOriginalExtension();
            $destinationSchedule = 'uploads/paid_course_schedules';
            $studySchedule->move($destinationSchedule, $scheduleName);

            PaidCourse::where('id', $formData['id'])->update([
                "paid_course_schedule" => "http://" . $_SERVER['HTTP_HOST'] . '/uploads/paid_course_schedules/' . $scheduleName
            ]);
        }

        $solvedScheduleName = null;

        if($request->file('solvedSchedule')){
            $solvedSchedule = $request->file('solvedSchedule');
            $time = time();
            $solvedScheduleName = "SCschedule" . $time . '.' . $solvedSchedule->getClientOriginalExtension();
            $destinationSchedule = 'uploads/paid_course_schedules';
            $solvedSchedule->move($destinationSchedule, $solvedScheduleName);

            PaidCourse::where('id', $formData['id'])->update([
                "paid_solve_class_schedule" => "http://" . $_SERVER['HTTP_HOST'] . '/uploads/paid_course_schedules/' . $solvedScheduleName
            ]);
        }

        try {
            DB::beginTransaction();
            $course = (array) [
                "name" => $formData['name'],
                "name_bn" => isset($formData['name_bn']) ? $formData['name_bn'] : null,
                "course_type" => isset($formData['course_type']) ? $formData['course_type'] : 'universityAdmissionCourse',
                "description" => $formData['description'],
                "youtube_url" => isset($formData['youtube_url']) ? $formData['youtube_url'] : null,
                "coupon_code" => isset($formData['coupon_code']) ? $formData['coupon_code'] : null,
                "regular_amount" => $formData['regular_amount'],
                "sales_amount" => $formData['sales_amount'],
                "discount_percentage" => $formData['discount_percentage'] ?? 0,
                "number_of_videos" => $formData['number_of_videos'] ?? 0,
                "number_of_scripts" => $formData['number_of_scripts'] ?? 0,
                "number_of_quizzes" => $formData['number_of_quizzes'] ?? 0,
                "number_of_model_tests" => $formData['number_of_model_tests'] ?? 0,
                "is_active" => $formData['is_active'],
                "promo_status" => isset($formData['promo_status']) ? $formData['promo_status'] : null,
                "has_trail" => $formData['has_trail'],
                "is_only_test" => $formData['is_only_test'],
                "is_cunit" => $formData['is_cunit'],
                "folder_name" => str_replace(' ', '_', $formData['folder_name']),
                "sort" => $formData['sort'],
                "is_lc_enable" => $formData['is_lc_enable'] ?? 0,
                "appeared_from" => date("Y-m-d H:i:s", strtotime($formData['appeared_from'])),
                "appeared_to" => date("Y-m-d H:i:s", strtotime($formData['appeared_to'])),
            ];
            $courseObj = PaidCourse::where('id', $formData['id'])->update($course);

            if(sizeof($formData['features'])){
                PaidCourseFeature::where("paid_course_id", $formData['id'])->delete();
            }

            if(sizeof($formData['desTitles'])){
                $allTitles = PaidCourseDescriptionTitle::where("paid_course_id", $formData['id'])->get();
                foreach ($allTitles as $old_title) {
                    PaidCourseDescriptionDetail::where("paid_course_description_title_id", $old_title->id)->delete();
                }

                PaidCourseDescriptionTitle::where("paid_course_id", $formData['id'])->delete();
            }

            foreach ($formData['features'] as $feature) {
                PaidCourseFeature::create([
                    "paid_course_id" => $formData['id'],
                    "name" => $feature['name'],
                ]);
            }

            foreach ($formData['desTitles'] as $desTitle) {
                $title = PaidCourseDescriptionTitle::create([
                    "paid_course_id" => $formData['id'],
                    "name" => $desTitle['title'],
                ]);

                foreach ($desTitle['details'] as $detail) {
                    PaidCourseDescriptionDetail::create([
                        "paid_course_description_title_id" => $title->id,
                        "name" => $detail,
                    ]);
                }
            }

            DB::commit();
            $response->status = $response::status_ok;
            $response->messages = "Paid course has been Updated";
            $response->result = [];
            return FacadeResponse::json($response);

        } catch (Exception $e) {
            DB::rollback();
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            $response->result = [];
            return FacadeResponse::json($response);
        }
    }

    public function getPaidCourseDetailsByID(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_id = $request->id;

        if (!$paid_course_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check ID";
            return FacadeResponse::json($response);
        }

        $paid_course = PaidCourse::where('id', $paid_course_id)->first();
        $paid_course->feature_list = PaidCourseFeature::where('paid_course_id', $paid_course_id)->get();

        $title_list = PaidCourseDescriptionTitle::where('paid_course_id', $paid_course_id)->get();
        $descriptions = [];

        foreach ($title_list as $title) {
            $details_list = PaidCourseDescriptionDetail::where('paid_course_description_title_id', $title->id)->pluck('name');
            array_push($descriptions, ["title" => $title->name, "details" => $details_list]);
        }

        $paid_course->descriptions = $descriptions;

        $response->status = $response::status_ok;
        $response->messages = "Paid course details!";
        $response->result = $paid_course;
        return FacadeResponse::json($response);
    }

    public function createPaidTest(Request $request)
    {
        $response = new ResponseObject;

        // $response->status   = $response::status_ok;
        // $response->messages = "Paid test has been created";
        // $response->data     = [$request->test_type];
        // return response()->json($response);

        try {
            DB::beginTransaction();

            $appeared_from = null;
            $appeared_to = null;
            if ($request->appeared_from) {
                $appeared_from = date("Y-m-d H:i:s", strtotime($request->appeared_from));
                $appeared_to = date("Y-m-d H:i:s", strtotime($request->appeared_to));
            }

            $paid_course_material = PaidCourseMaterial::create([
                'paid_course_id' => $request->paid_course_id,
                'type' => 'quiz',
                'name' => $request->name,
                'name_bn' => $request->name_bn ? $request->name_bn : $request->name_bn,
                'description' => $request->description,
                'test_type' => $request->test_type,
                'quiz_duration' => $request->quiz_duration,
                'has_schedule' => $request->has_schedule ? 1 : 0,
                'quiz_positive_mark' => $request->quiz_positive_mark ? $request->quiz_positive_mark : 1,
                'quiz_negative_mark' => $request->quiz_negative_mark ? $request->quiz_negative_mark : 0,
                'quiz_total_mark' => $request->quiz_total_mark,
                'quiz_question_number' => $request->quiz_question_number,
                'sort' => $request->sort,
                'status' => "Active",
                'appeared_from' => $appeared_from,
                'appeared_to' => $appeared_to,
                'is_active' => true,
            ]);

            foreach ($request->questions as $item) {
                PaidCourseQuizQuestion::create([
                    'paid_course_material_id' => $paid_course_material->id,
                    'question' => $item['question'],
                    'option1' => $item['option1'] ? $item['option1'] : '',
                    'option2' => $item['option2'] ? $item['option2'] : '',
                    'option3' => $item['option3'] ? $item['option3'] : '',
                    'option4' => $item['option4'] ? $item['option4'] : '',
                    'option5' => $item['option5'] ?? '',
                    'option6' => $item['option6'] ?? '',
                    'correct_answer' => $item['correct_answer'] ?? null,
                    'correct_answer2' => $item['correct_answer2'] ?? null,
                    'correct_answer3' => $item['correct_answer3'] ?? null,
                    'correct_answer4' => $item['correct_answer4'] ?? null,
                    'correct_answer5' => $item['correct_answer5'] ?? null,
                    'correct_answer6' => $item['correct_answer6'] ?? null,
                    'explanation_text' => $item['explanation_text'] ?? '',
                ]);
            }

            $this->AddPermissionOfPurchasedUser($request);

            DB::commit();

            $response->status = $response::status_ok;
            $response->messages = "Paid test has been created";
            $response->data = [];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response->status = $response::status_fail;
            $response->message = $e->getMessage();
            $response->data = [];
            return response()->json($response);
        }
    }

    public function uploadPaidTestViaExcel(Request $request)
    {
        $response = new ResponseObject;

        // $response->status   = $response::status_ok;
        // $response->messages = "Paid test has been created";
        // $response->data     = $request->data;
        // return response()->json($response);

        if(!sizeof($request->data)){
            $response->status   = $response::status_ok;
            $response->messages = "Please, Attach File";
            $response->data     = [];
            return response()->json($response);
        }

        if(!$request->paid_course_id){
            $response->status   = $response::status_ok;
            $response->messages = "Please, Attach course ID";
            $response->data     = [];
            return response()->json($response);
        }

        try {
            DB::beginTransaction();
            foreach ($request->data as $item) {
                $appeared_from = null;
                $appeared_to = null;
                if ($item['appeared_from']) {
                    $appeared_from = date("Y-m-d H:i:s", strtotime($item['appeared_from']));
                    $appeared_to = date("Y-m-d H:i:s", strtotime($item['appeared_to']));
                }

                $paid_course_material = PaidCourseMaterial::create([
                    'paid_course_id' => $request->paid_course_id,
                    'type' => 'quiz',
                    'name' => $item['name'],
                    'name_bn' => $item['name_bn'] ? $item['name_bn'] : $item['name_bn'],
                    'description' => $item['description'],
                    'test_type' => $item['test_type'],
                    'quiz_duration' => $item['quiz_duration'],
                    'has_schedule' => $item['has_schedule'] ? 1 : 0,
                    'quiz_positive_mark' => $item['quiz_positive_mark'] ? $item['quiz_positive_mark'] : 1,
                    'quiz_negative_mark' => $item['quiz_negative_mark'] ? $item['quiz_negative_mark'] : 0,
                    'quiz_total_mark' => $item['quiz_total_mark'],
                    'quiz_question_number' => $item['quiz_question_number'],
                    'sort' => $item['sort'],
                    'status' => "Active']",
                    'appeared_from' => $appeared_from,
                    'appeared_to' => $appeared_to,
                    'is_active' => true,
                ]);
            }

            $this->AddPermissionOfPurchasedUser($request);

            DB::commit();

            $response->status = $response::status_ok;
            $response->messages = "Paid test has been created";
            $response->data = [];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response->status = $response::status_fail;
            $response->message = $e->getMessage();
            $response->data = [];
            return response()->json($response);
        }
    }

    public function updatePaidTest(Request $request)
    {
        $response = new ResponseObject;

        try {
            DB::beginTransaction();

            $appeared_from = null;
            $appeared_to = null;
            if ($request->appeared_from) {
                $appeared_from = date("Y-m-d H:i:s", strtotime($request->appeared_from));
                $appeared_to = date("Y-m-d H:i:s", strtotime($request->appeared_to));
            }

            $paid_course_material = PaidCourseMaterial::where('id', $request->id)->update([
                'paid_course_id' => $request->paid_course_id,
                'type' => 'quiz',
                'name' => $request->name,
                'name_bn' => $request->name_bn ? $request->name_bn : $request->name_bn,
                'description' => $request->description,
                'test_type' => $request->test_type,
                'quiz_duration' => $request->quiz_duration,
                'has_schedule' => $request->has_schedule ? 1 : 0,
                'quiz_positive_mark' => $request->quiz_positive_mark ? $request->quiz_positive_mark : 1,
                'quiz_negative_mark' => $request->quiz_negative_mark ? $request->quiz_negative_mark : 0,
                'quiz_total_mark' => $request->quiz_total_mark,
                'quiz_question_number' => $request->quiz_question_number,
                'sort' => $request->sort,
                'status' => "Active",
                'appeared_from' => $appeared_from,
                'appeared_to' => $appeared_to,
                'is_active' => true,
            ]);

            DB::commit();

            $response->status = $response::status_ok;
            $response->messages = "Paid test has been updated";
            $response->data = [];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response->status = $response::status_fail;
            $response->message = $e->getMessage();
            $response->data = [];
            return response()->json($response);
        }
    }

    public function getAllTestList(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_id = $request->paid_course_id;

        $examList = PaidCourseMaterial::when($paid_course_id, function ($query, $paid_course_id) {
            return $query->where('paid_course_id', $paid_course_id);
        })
        ->orderby('id', 'desc')->get();

        // foreach ($examList as $course_material) {
        //     $quizSubjects = PaidCourseQuizSubject::where('paid_course_material_id', $course_material->id)->get();
        //     $is_accessable = true;

        //     if(!sizeof($quizSubjects)){
        //         $is_accessable = false;
        //     }

        //     foreach ($quizSubjects as $subject) {
        //         $is_material_added = paidCourseQuizQuestion::where('paid_course_material_subject_id', $subject->id)->where('paid_course_material_id', $course_material->id)->get()->count();
        //         if($is_material_added < $subject->number_of_questions){
        //             $is_accessable = false;
        //         }
        //     }
        //     $course_material->sufficient_question = $is_accessable;
        //     if($is_accessable){
        //         PaidCourseMaterial::where('id', $course_material->id)->update([
        //             'sufficient_question' => $is_accessable
        //         ]);
        //     }
        // }

        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $examList;
        return FacadeResponse::json($response);
    }

    public function adminGetAllTestList(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_id = $request->paid_course_id;

        $examList = PaidCourseMaterial::when($paid_course_id, function ($query, $paid_course_id) {
            return $query->where('paid_course_id', $paid_course_id);
        })
        ->orderby('id', 'desc')->get();

        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $examList;
        return FacadeResponse::json($response);
    }

    public function getPaidCourseTestAllQuestionList(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_material_id = $request->paid_course_material_id;

        if (!$paid_course_material_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check ID";
            return FacadeResponse::json($response);
        }

        $quizQuestionList = PaidCourseQuizQuestion::select('paid_course_quiz_questions.*', 'quiz_question_sets.name as set_name', 'paid_course_quiz_subjects.name as subject_name')
            ->where('paid_course_quiz_questions.paid_course_material_id', $paid_course_material_id)
            ->leftJoin('paid_course_quiz_subjects', 'paid_course_quiz_subjects.id', 'paid_course_quiz_questions.paid_course_material_subject_id')
            ->leftJoin('quiz_question_sets', 'quiz_question_sets.id', 'paid_course_quiz_questions.question_set_id')
            ->get();
        $writtenQuestionList = PaidCourseWrittenAttachment::where('paid_course_material_id', $paid_course_material_id)->get();
        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = ['mcq' => $quizQuestionList, 'written' => $writtenQuestionList];
        return FacadeResponse::json($response);
    }

    public function getPaidCourseTestDetails(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_material_id = $request->paid_course_material_id;

        if (!$paid_course_material_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check ID";
            return FacadeResponse::json($response);
        }
        $PCTest = PaidCourseMaterial::where('id', $paid_course_material_id)->first();
        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $PCTest;
        return FacadeResponse::json($response);
    }

    public function updatePaidCourseTestQuestion(Request $request)
    {
        $response = new ResponseObject;
        $question_id = $request->id;

        if (!$question_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, select question";
            return FacadeResponse::json($response);
        }

        PaidCourseQuizQuestion::where('id', $question_id)->update([
            'question' => $request->question,
            'option1' => $request->option1 ? $request->option1 : '',
            'option2' => $request->option2 ? $request->option2 : '',
            'option3' => $request->option3 ? $request->option3 : '',
            'option4' => $request->option4 ? $request->option4 : '',
            'option5' => $request->option5 ?? '',
            'option6' => $request->option6 ?? '',
            'correct_answer' => $request->correct_answer ?? null,
            'correct_answer2' => $request->correct_answer2 ?? null,
            'correct_answer3' => $request->correct_answer3 ?? null,
            'correct_answer4' => $request->correct_answer4 ?? null,
            'correct_answer5' => $request->correct_answer5 ?? null,
            'correct_answer6' => $request->correct_answer6 ?? null,
            'explanation_text' => $request->explanation_text ?? '',
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Question has been updated successful.";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function addPaidCourseTestQuestion(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;
        $paid_course_material_subject_id = $request->paid_course_material_subject_id ? $request->paid_course_material_subject_id : 0;

        if (!$paid_course_material_id || $paid_course_material_subject_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        $correct_answer = null;
        $correct_answer2 = null;
        $correct_answer3 = null;
        $correct_answer4 = null;

        if($request->correct_answer == 'true'){
            $correct_answer = 1;
        }
        if($request->correct_answer2 == 'true'){
            $correct_answer2 = 2;
        }
        if($request->correct_answer3 == 'true'){
            $correct_answer3 = 3;
        }
        if($request->correct_answer4 == 'true'){
            $correct_answer4 = 4;
        }

        PaidCourseQuizQuestion::create([
            'paid_course_material_id' => $paid_course_material_id,
            'paid_course_material_subject_id' => $paid_course_material_subject_id,
            'question' => $request->question,
            'option1' => $request->option1 ? $request->option1 : '',
            'option2' => $request->option2 ? $request->option2 : '',
            'option3' => $request->option3 ? $request->option3 : '',
            'option4' => $request->option4 ? $request->option4 : '',
            'option5' => $request->option5 ?? '',
            'option6' => $request->option6 ?? '',
            'correct_answer' => $correct_answer,
            'correct_answer2' => $correct_answer2,
            'correct_answer3' => $correct_answer3,
            'correct_answer4' => $correct_answer4,
            'correct_answer5' => $request->correct_answer5 ?? null,
            'correct_answer6' => $request->correct_answer6 ?? null,
            'explanation_text' => $request->explanation_text ?? '',
        ]);

        // $total_quiz_question_no = PaidCourseQuizQuestion::where('paid_course_material_id', $paid_course_material_id)->get()->count();
        // //$total_written_question_no = PaidCourseWrittenQuestion::where('paid_course_material_id', $paid_course_material_id)->get()->count();

        // PaidCourseMaterial::where('id', $paid_course_material_id)->update([
        //     "quiz_question_number" => $total_quiz_question_no,
        // ]);

        $response->status = $response::status_ok;
        $response->messages = "Question has been added successful.";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function updateTestQuestionAttachment(Request $request)
    {
        $response = new ResponseObject;
        $question_id = $request->id;

        if (!$question_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, attach question ID";
            return FacadeResponse::json($response);
        }

        $correct_answer = null;
        $correct_answer2 = null;
        $correct_answer3 = null;
        $correct_answer4 = null;

        if($request->correct_answer == 'true'){
            $correct_answer = 1;
        }
        if($request->correct_answer2 == 'true'){
            $correct_answer2 = 2;
        }
        if($request->correct_answer3 == 'true'){
            $correct_answer3 = 3;
        }
        if($request->correct_answer4 == 'true'){
            $correct_answer4 = 4;
        }

        PaidCourseQuizQuestion::where('id', $question_id)->update([
            'question' => $request->question,
            'option1' => $request->option1 ? $request->option1 : '',
            'option2' => $request->option2 ? $request->option2 : '',
            'option3' => $request->option3 ? $request->option3 : '',
            'option4' => $request->option4 ? $request->option4 : '',
            'option5' => $request->option5 ?? '',
            'option6' => $request->option6 ?? '',
            'correct_answer' => $correct_answer,
            'correct_answer2' => $correct_answer2,
            'correct_answer3' => $correct_answer3,
            'correct_answer4' => $correct_answer4,
            'correct_answer5' => $request->correct_answer5 ?? null,
            'correct_answer6' => $request->correct_answer6 ?? null,
            'explanation_text' => $request->explanation_text ?? '',
        ]);

        $fileDestination = 'uploads/quiz_questions';

        $question_image_name = null;
        if($request->file('question_image')){
            $question_image = $request->file('question_image');
            $time = time();
            $question_image_name = "PCTQI_" . $time . '.' . $question_image->getClientOriginalExtension();
            $question_image->move($fileDestination, $question_image_name);
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                "question_image" => "https://" . $_SERVER['HTTP_HOST'] . '/uploads/quiz_questions/' . $question_image_name
            ]);
        }

        $option1_image_name = null;
        if($request->file('option1_image')){
            $option1_image = $request->file('option1_image');
            $time = time();
            $option1_image_name = "PCTQ1_" . $time . '.' . $option1_image->getClientOriginalExtension();
            $option1_image->move($fileDestination, $option1_image_name);
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                "option1_image" => "https://" . $_SERVER['HTTP_HOST'] . '/uploads/quiz_questions/' . $option1_image_name
            ]);
        }

        $option2_image_name = null;
        if($request->file('option2_image')){
            $option2_image = $request->file('option2_image');
            $time = time();
            $option2_image_name = "PCTQ2_" . $time . '.' . $option2_image->getClientOriginalExtension();
            $option2_image->move($fileDestination, $option2_image_name);
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                "option2_image" => "https://" . $_SERVER['HTTP_HOST'] . '/uploads/quiz_questions/' . $option2_image_name
            ]);
        }

        $option3_image_name = null;
        if($request->file('option3_image')){
            $option3_image = $request->file('option3_image');
            $time = time();
            $option3_image_name = "PCTQ3_" . $time . '.' . $option3_image->getClientOriginalExtension();
            $option3_image->move($fileDestination, $option3_image_name);
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                "option3_image" => "https://" . $_SERVER['HTTP_HOST'] . '/uploads/quiz_questions/' . $option3_image_name
            ]);
        }

        $option4_image_name = null;
        if($request->file('option4_image')){
            $option4_image = $request->file('option4_image');
            $time = time();
            $option4_image_name = "PCTQ4_" . $time . '.' . $option4_image->getClientOriginalExtension();
            $option4_image->move($fileDestination, $option4_image_name);
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                "option4_image" => "https://" . $_SERVER['HTTP_HOST'] . '/uploads/quiz_questions/' . $option4_image_name
            ]);
        }

        $option5_image_name = null;
        if($request->file('option5_image')){
            $option5_image = $request->file('option5_image');
            $time = time();
            $option5_image_name = "PCTQ5_" . $time . '.' . $option5_image->getClientOriginalExtension();
            $option5_image->move($fileDestination, $option5_image_name);
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                "option5_image" => "https://" . $_SERVER['HTTP_HOST'] . '/uploads/quiz_questions/' . $option5_image_name
            ]);
        }

        $option6_image_name = null;
        if($request->file('option6_image')){
            $option6_image = $request->file('option6_image');
            $time = time();
            $option6_image_name = "PCTQ6_" . $time . '.' . $option6_image->getClientOriginalExtension();
            $option6_image->move($fileDestination, $option6_image_name);
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                "option6_image" => "https://" . $_SERVER['HTTP_HOST'] . '/uploads/quiz_questions/' . $option6_image_name
            ]);
        }

        $explanation_name = null;
        if($request->file('explanation')){
            $explanation = $request->file('explanation');
            $time = time();
            $explanation_name = "PCTQ6_" . $time . '.' . $explanation->getClientOriginalExtension();
            $explanation->move($fileDestination, $explanation_name);
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                "explanation" => "https://" . $_SERVER['HTTP_HOST'] . '/uploads/quiz_questions/' . $explanation_name
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = "Question has been updated successful.";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function deleteCourseTestQuestionImage(Request $request){
        $response = new ResponseObject;
        $question_id = $request->id;

        if (!$question_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, attach question ID";
            return FacadeResponse::json($response);
        }

        if($request->option == 'question_image'){
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                'question_image' => null
            ]);
        }

        if($request->option == 'option1_image'){
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                'option1_image' => null
            ]);
        }

        if($request->option == 'option2_image'){
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                'option2_image' => null
            ]);
        }

        if($request->option == 'option3_image'){
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                'option3_image' => null
            ]);
        }

        if($request->option == 'option4_image'){
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                'option4_image' => null
            ]);
        }

        if($request->option == 'option5_image'){
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                'option5_image' => null
            ]);
        }

        if($request->option == 'option6_image'){
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                'option6_image' => null
            ]);
        }

        if($request->option == 'explanation'){
            PaidCourseQuizQuestion::where('id', $question_id)->update([
                'explanation' => null
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = "Question Image has been removed successfully";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function addUpdatePaidCourseTestWrittenQuestion(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        if (!$paid_course_material_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        if ($request->id) {
            PaidCourseWrittenQuestion::where('id', $request->id)->update([
                'paid_course_material_id' => $paid_course_material_id,
                'question' => $request->question,
                'mark' => $request->mark ? $request->mark : '',
                'question_type' => $request->question_type ? $request->question_type : '',
                'explanation_text' => $request->explanation_text ?? '',
            ]);
        } else {
            PaidCourseWrittenQuestion::create([
                'paid_course_material_id' => $paid_course_material_id,
                'question' => $request->question,
                'mark' => $request->mark ? $request->mark : '',
                'question_type' => $request->question_type ? $request->question_type : '',
                'explanation_text' => $request->explanation_text ?? '',
            ]);
        }

        $total_quiz_question_no = PaidCourseQuizQuestion::where('paid_course_material_id', $paid_course_material_id)->get()->count();
        $total_written_question_no = PaidCourseWrittenQuestion::where('paid_course_material_id', $paid_course_material_id)->get()->count();

        PaidCourseMaterial::where('id', $paid_course_material_id)->update([
            "quiz_question_number" => $total_quiz_question_no + $total_written_question_no,
        ]);

        $quizSubjects = PaidCourseQuizSubject::where('paid_course_material_id', $paid_course_material_id)->get();
        $is_accessable = true;

        if(!sizeof($quizSubjects)){
            $is_accessable = false;
        }

        foreach ($quizSubjects as $subject) {
            $is_material_added = paidCourseQuizQuestion::where('paid_course_material_subject_id', $subject->id)->where('paid_course_material_id', $paid_course_material_id)->get()->count();
            if($is_material_added < $subject->number_of_questions){
                $is_accessable = false;
            }
        }
        
        if($is_accessable){
            PaidCourseMaterial::where('id', $paid_course_material_id)->update([
                'sufficient_question' => $is_accessable
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = "Question has been added successful.";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function createUpdatePaidCourseMeterialSubject(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        if (!$paid_course_material_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        $message = 'Unsuccessful';

        if ($request->id) {

            if($request->is_optional){
                $is_optional_exist = PaidCourseQuizSubject::where('id', '!=', $request->id)->where('paid_course_material_id', $paid_course_material_id)->where('is_optional', true)->first();
                if(!empty($is_optional_exist)){
                    $response->status = $response::status_fail;
                    $response->messages = "You cannot add multiple optional subject.";
                    $response->result = [];
                    return FacadeResponse::json($response); 
                }
            }

            PaidCourseQuizSubject::where('id', $request->id)->update([
                'paid_course_material_id' => $paid_course_material_id,
                'name' => $request->name,
                'number_of_questions' => $request->number_of_questions,
                'is_active' => $request->is_active,
                'is_optional' => $request->is_optional,
                'optional_subject_id' => $request->optional_subject_id ? $request->optional_subject_id : null
            ]);

            if($request->is_optional && $request->optional_subject_id){
                $get_subject_name = Subject::where('id', $request->optional_subject_id)->first();
                PaidCourseMaterial::where('id', $paid_course_material_id)->update([
                    "optional_subject_id" => $request->optional_subject_id,
                    "optional_subject_name" => $get_subject_name->name
                ]);
            }
            $message = 'Subject has been updated successful.';

        } else {
            if ($request->is_optional) {
                $is_optional_exist = PaidCourseQuizSubject::where('paid_course_material_id', $paid_course_material_id)->where('is_optional', true)->first();
                if (!empty($is_optional_exist)) {
                    $response->status = $response::status_fail;
                    $response->messages = "You cannot add multiple optional subject.";
                    $response->result = [];
                    return FacadeResponse::json($response);
                }
            }

            PaidCourseQuizSubject::create([
                'paid_course_material_id' => $paid_course_material_id,
                'name' => $request->name,
                'number_of_questions' => $request->number_of_questions,
                'is_active' => $request->is_active,
                'is_optional' => $request->is_optional,
                'optional_subject_id' => $request->optional_subject_id ? $request->optional_subject_id : null
            ]);

            if($request->is_optional && $request->optional_subject_id){
                $get_subject_name = Subject::where('id', $request->optional_subject_id)->first();
                PaidCourseMaterial::where('id', $paid_course_material_id)->update([
                    "optional_subject_id" => $request->optional_subject_id,
                    "optional_subject_name" => $get_subject_name->name
                ]);
            }

            $message = 'Subject has been added successful.';
        }

        $response->status = $response::status_ok;
        $response->messages = $message;
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function createPaidCourseMeterialSubjectBulk(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        $subjects = $request->subjects;
        if (!sizeof($subjects)) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Add subject!";
            return FacadeResponse::json($response);
        }

        foreach ($subjects as $subject) {
            PaidCourseQuizSubject::create([
                'paid_course_material_id' => $paid_course_material_id,
                'name' => $subject['name'],
                'number_of_questions' => $subject['number_of_questions'],
                'is_optional' => $subject['is_optional'],
                'optional_subject_id' => $subject['optional_subject_id'] ? $subject['optional_subject_id'] : null
            ]);

            if ($subject['is_optional'] && $subject['optional_subject_id']) {
                $get_subject_name = Subject::where('id', $subject['optional_subject_id'])->first();
                PaidCourseMaterial::where('id', $paid_course_material_id)->update([
                    "optional_subject_id" => $subject['optional_subject_id'],
                    "optional_subject_name" => $get_subject_name->name
                ]);
            }
        }

        $response->status = $response::status_ok;
        $response->messages = "Subject added successful";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function updatePaidCourseMeterialSubjectBulk(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        $subjects = $request->subjects;
        if (!sizeof($subjects)) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Add subject!";
            return FacadeResponse::json($response);
        }

        foreach ($subjects as $subject) {
            PaidCourseQuizSubject::where('id', $request->id)->update([
                'paid_course_material_id' => $paid_course_material_id,
                'name' => $subject['name'],
                'number_of_questions' => $subject['number_of_questions'],
                'is_optional' => $subject['is_optional'],
                'optional_subject_id' => $subject['optional_subject_id'] ? $subject['optional_subject_id'] : null
            ]);

            if ($subject['is_optional'] && $subject['optional_subject_id']) {
                $get_subject_name = Subject::where('id', $subject['optional_subject_id'])->first();
                PaidCourseMaterial::where('id', $paid_course_material_id)->update([
                    "optional_subject_id" => $subject['optional_subject_id'],
                    "optional_subject_name" => $get_subject_name->name
                ]);
            }
        }

        $response->status = $response::status_ok;
        $response->messages = "Subject added successful";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function deleteMeterialSubject(Request $request)
    {
        $response = new ResponseObject;
        $subject_id = $request->id ? $request->id : 0;

        if (!$subject_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        $is_question_exist = PaidCourseQuizQuestion::where('paid_course_material_subject_id', $request->id)->get()->count();
        
        if ($is_question_exist) {
            $response->status = $response::status_fail;
            $response->messages = "You can not delete subject, Because there are some questions under this subject!";
            return FacadeResponse::json($response);
        }

        PaidCourseQuizSubject::where('id', $request->id)->delete();

        $response->status = $response::status_ok;
        $response->messages = "Subject has been deleted successful";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function PaidCourseMeterialSubjectList(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        if (!$paid_course_material_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        $paidCourseMaterialList = PaidCourseQuizSubject::select('paid_course_quiz_subjects.*', 'subjects.name as subject_name')->where('paid_course_material_id', $paid_course_material_id)
        ->leftJoin('subjects', 'subjects.id', 'paid_course_quiz_subjects.optional_subject_id')
        ->orderby('paid_course_quiz_subjects.name', 'ASC')
        ->get();

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $paidCourseMaterialList;
        return FacadeResponse::json($response);
    }

    public function getOptionalSubjectList()
    {
        $response = new ResponseObject;
        $optional_subject_list = Subject::whereIn('id', [18, 39])->get();

        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $optional_subject_list;
        return FacadeResponse::json($response);
    }

    public function getCoreSubjectList()
    {
        $response = new ResponseObject;

        $core_subject_list = PaidCourseCoreSubject::where('is_active', true)->orderby('name', 'ASC')->get();

        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $core_subject_list;
        return FacadeResponse::json($response);
    }
    
    public function getQuizQuestionSettList()
    {
        $response = new ResponseObject;
        $set_list = QuizQuestionSet::all();

        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $set_list;
        return FacadeResponse::json($response);
    }

    public function addPaidCourseQuizQuestionExcel(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;
        $paid_course_material_subject_id = $request->paid_course_material_subject_id ? $request->paid_course_material_subject_id : 0;
        $question_set_id = $request->question_set_id ? $request->question_set_id : 0;

        if(!$paid_course_material_id || !$paid_course_material_subject_id || !$question_set_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        $questions = $request->questions;
        if(!sizeof($questions)){
            $response->status = $response::status_fail;
            $response->messages = "Please, Attach questions";
            return FacadeResponse::json($response);
        }

        foreach ($questions as $item) {

            $correct_answer = NULL;
            $correct_answer2 = NULL;
            $correct_answer3 = NULL;
            $correct_answer4 = NULL;
            $correct_answer5 = NULL;
            $correct_answer6 = NULL;

            if(isset($item['correct_answer']))
                $correct_answer = 1;
            if(isset($item['correct_answer2']))
                $correct_answer2 = 2;
            if(isset($item['correct_answer3']))
                $correct_answer3 = 3;
            if(isset($item['correct_answer4']))
                $correct_answer4 = 4;
            if(isset($item['correct_answer5']))
                $correct_answer5 = 5;
            if(isset($item['correct_answer6']))
                $correct_answer6 = 6;

            PaidCourseQuizQuestion::create([
                'paid_course_material_id' => $paid_course_material_id,
                'paid_course_material_subject_id' => $paid_course_material_subject_id,
                'question_set_id' => $question_set_id,
                'question' => $item['question'],
                'option1' => $item['option1'] ? $item['option1'] : '',
                'option2' => $item['option2'] ? $item['option2'] : '',
                'option3' => $item['option3'] ? $item['option3'] : '',
                'option4' => $item['option4'] ? $item['option4'] : '',
                'option5' => $item['option5'] ?? '',
                'option6' => $item['option6'] ?? '',
                'correct_answer' => $correct_answer,
                'correct_answer2' => $correct_answer2,
                'correct_answer3' => $correct_answer3,
                'correct_answer4' => $correct_answer4,
                'correct_answer5' => $correct_answer5,
                'correct_answer6' => $correct_answer6,
                'explanation_text' => $item['explanation_text'] ?? '',
            ]);
        }

        $quizSubjects = PaidCourseQuizSubject::where('paid_course_material_id', $paid_course_material_id)->get();
        $set_list = QuizQuestionSet::all();

        $upload_completed = [];

        if(!sizeof($quizSubjects)){
            array_push($upload_completed, false);
        }

        foreach ($set_list as $set){
            foreach ($quizSubjects as $subject) {
                $is_material_added = paidCourseQuizQuestion::where('paid_course_material_subject_id', $subject->id)
                    ->where('paid_course_material_id', $paid_course_material_id)
                    ->where('question_set_id', $set->id)
                    ->get()
                    ->count();

                if($is_material_added < $subject->number_of_questions){
                    array_push($upload_completed, false);
                }
            }
        }

        if(in_array(false, $upload_completed)){
            PaidCourseMaterial::where('id', $paid_course_material_id)->update([
                'sufficient_question' => false
            ]);
        }else{
            PaidCourseMaterial::where('id', $paid_course_material_id)->update([
                'sufficient_question' => true
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = "Question has been added successful.";
        $response->result = $upload_completed;
        return FacadeResponse::json($response);
    }

    public function paidCourseCouponSaveUpdate(Request $request)
    {
        $response = new ResponseObject;

        $request_coupon_id = $request->id ? $request->id : 0;

        if (!$request->coupon_code || !$request->coupon_value) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        $coupon = PaidCourseCoupon::where('coupon_code', strtolower($request->coupon_code))->first();

        if (empty($coupon) && $request_coupon_id) {
            $response->status = $response::status_fail;
            $response->messages = "Coupon Not Found!";
            return FacadeResponse::json($response);
        }


        if (!empty($coupon)) {
            $is_coupon_applied = PaidCourseApplyCoupon::where('coupon_id', $coupon->id)->where('applied_status', 'successful')->first();
            if (!empty($is_coupon_applied)) {
                $response->status = $response::status_fail;
                $response->messages = "You can not modify coupon. Someone has applied this coupon!";
                return FacadeResponse::json($response);
            }
        }

        if ($request->id) {
            PaidCourseCoupon::where('id', $request->id)->update([
                'coupon_code' => strtolower($request->coupon_code),
                'coupon_value' => $request->coupon_value,
                'paid_course_id' => $request->paid_course_id,
                'limit' => $request->limit ? $request->limit : 0,
                'expiry_date' => $request->expiry_date ? date("Y-m-d H:i:s", strtotime($request->expiry_date)) : null,
                'is_active' => $request->is_active,
                'remarks' => $request->remarks,
                'created_by' => $request->created_by
            ]);
        } else {

            $coupon = PaidCourseCoupon::where('coupon_code', strtolower($request->coupon_code))->first();

            if (!empty($coupon)) {
                $response->status = $response::status_fail;
                $response->result = [];
                $response->messages = "Coupon already exist!";
                return FacadeResponse::json($response);
            }

            PaidCourseCoupon::create([
                'coupon_code' => strtolower($request->coupon_code),
                'coupon_value' => $request->coupon_value,
                'paid_course_id' => $request->paid_course_id,
                'limit' => $request->limit ? $request->limit : 0,
                'expiry_date' => $request->expiry_date ? date("Y-m-d H:i:s", strtotime($request->expiry_date)) : null,
                'is_active' => $request->is_active,
                'remarks' => $request->remarks,
                'created_by' => $request->created_by
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function couponList(Request $request)
    {
        $response = new ResponseObject;

        $couponList = PaidCourseCoupon::select('paid_course_coupons.*', 'admins.name as created_by_name', 'admins.email', 'admins.role', "paid_courses.name as course_name")
        ->leftJoin('admins', 'admins.id', 'paid_course_coupons.created_by')
        ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_coupons.paid_course_id')
        ->orderby('paid_course_coupons.id', 'desc')
        ->get();

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $couponList;
        return FacadeResponse::json($response);
    }

    public function couponDropdownList(Request $request)
    {
        $response = new ResponseObject;

        $couponList = PaidCourseCoupon::select('paid_course_coupons.*')
        ->orderby('paid_course_coupons.id', 'desc')
        ->get();

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $couponList;
        return FacadeResponse::json($response);
    }

    public function checkCouponValidity(Request $request)
    {
        $response = new ResponseObject;

        $coupon_code = $request->coupon_code ? strtolower($request->coupon_code) : 0;
        $paid_course_id = $request->paid_course_id ? $request->paid_course_id : 0;
        $user_id = $request->user_id ? $request->user_id : 0;

        if (!$coupon_code || !$paid_course_id || !$user_id) {
            $response->status = $response::status_fail;
            $response->result = [];
            $response->messages = "Please, Check Details";
            return FacadeResponse::json($response);
        }

        $is_user_exist = User::where('id', $user_id)->first();
        if (empty($is_user_exist)) {
            $response->status = $response::status_fail;
            $response->messages = "User Not Found!";
            return FacadeResponse::json($response);
        }

        $is_course_exist = PaidCourse::where('id', $paid_course_id)->first();
        if (empty($is_course_exist)) {
            $response->status = $response::status_fail;
            $response->messages = "Course Not Found!";
            return FacadeResponse::json($response);
        }

        $new_time = date("Y-m-d H:i:s", strtotime('+6 hours'));

        $coupon = PaidCourseCoupon::select('id', 'coupon_code', 'coupon_value as amount', 'expiry_date', 'limit')
            ->where('expiry_date', '>=', $new_time)
            ->where('paid_course_id', $paid_course_id)
            ->where('coupon_code', $coupon_code)
            ->where('is_active', true)
            ->first();

        if (empty($coupon)) {
            $response->status = $response::status_fail;
            $response->result = [];
            $response->messages = "Invalid coupon! Please enter valid coupon!";
            return FacadeResponse::json($response);
        }
        else{

            $is_coupon_applied_limit_check = PaidCourseApplyCoupon::where('coupon_id', $coupon->id)->where('applied_status', 'successful')->get()->count();

            if($coupon->limit){
                if($is_coupon_applied_limit_check >= $coupon->limit){
                    $response->status = $response::status_fail;
                    $response->result = [];
                    $response->messages = "Coupon limit is exceeded!";
                    return FacadeResponse::json($response);
                }
            }

            if($coupon->amount > $is_course_exist->sales_amount){
                $response->status = $response::status_fail;
                $response->result = [];
                $response->messages = "Invalid coupon! Please enter valid coupon!";
                return FacadeResponse::json($response);
            }
            
            $is_coupon_applied = PaidCourseApplyCoupon::where('coupon_id', $coupon->id)
                ->where('user_id', $user_id)
                ->where('paid_course_id', $paid_course_id)
                ->where('applied_status', 'successful')
                ->first();
            
            if (!empty($is_coupon_applied)) {
                $response->status = $response::status_fail;
                $response->result = [];
                $response->messages = "Coupon already applied! Please, enter new coupon!";
                return FacadeResponse::json($response);
            }

            $is_coupon_pending = PaidCourseApplyCoupon::where('coupon_id', $coupon->id)
                ->where('user_id', $user_id)
                ->where('paid_course_id', $paid_course_id)
                ->where('applied_status', 'panding')
                ->first();

            if(!empty($is_coupon_pending))
            {
                $response->status = $response::status_ok;
                $response->messages = "Coupon applied successful";
                $response->result = $coupon;
                return FacadeResponse::json($response);
            }
            else{
                PaidCourseApplyCoupon::create([
                    'user_id' => $user_id,
                    'paid_course_id' => $paid_course_id,
                    'coupon_id' => $coupon->id,
                    'applied_from' => 'website',
                    'applied_status' => 'panding'
                ]);

                $response->status = $response::status_ok;
                $response->messages = "Coupon applied successful";
                $response->result = $coupon;
                return FacadeResponse::json($response);
            }
        }

        $response->status = $response::status_fail;
        $response->result = [];
        $response->messages = "Please, Check details!";
        return FacadeResponse::json($response);
    }

    public function checkCouponValidityFromMobile(Request $request)
    {
        $response = new ResponseObject;

        $coupon_code = $request->coupon_code ? $request->coupon_code : 0;
        $paid_course_id = $request->paid_course_id ? $request->paid_course_id : 0;
        $user_id = $request->user_id ? $request->user_id : 0;

        if (!$coupon_code || !$paid_course_id || !$user_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check Details";
            return FacadeResponse::json($response);
        }

        $is_user_exist = User::where('id', $user_id)->first();
        if (empty($is_user_exist)) {
            $response->status = $response::status_fail;
            $response->messages = "User Not Found!";
            return FacadeResponse::json($response);
        }

        $is_course_exist = PaidCourse::where('id', $paid_course_id)->first();
        if (empty($is_course_exist)) {
            $response->status = $response::status_fail;
            $response->messages = "Course Not Found!";
            return FacadeResponse::json($response);
        }

        $new_time = date("Y-m-d H:i:s", strtotime('+6 hours'));

        $coupon = PaidCourseCoupon::select('id', 'coupon_code', 'coupon_value as amount', 'expiry_date', 'limit')
            ->where('expiry_date', '>=', $new_time)
            ->where('paid_course_id', $paid_course_id)
            ->where('coupon_code', $coupon_code)
            ->where('is_active', true)
            ->first();

        if (empty($coupon)) {
            $response->status = $response::status_fail;
            $response->messages = "Invalid coupon! Please enter valid coupon!";
            return FacadeResponse::json($response);
        }
        else{

            if($coupon->amount > $is_course_exist->sales_amount){
                $response->status = $response::status_fail;
                $response->result = [];
                $response->messages = "Invalid coupon! Please enter valid coupon!";
                return FacadeResponse::json($response);
            }
            
            $is_coupon_applied = PaidCourseApplyCoupon::where('coupon_id', $coupon->id)
                ->where('user_id', $user_id)
                ->where('paid_course_id', $paid_course_id)
                ->where('applied_status', 'successful')
                ->first();
            
            if (!empty($is_coupon_applied)) {
                $response->status = $response::status_fail;
                $response->messages = "Coupon already applied! Please, enter new coupon!";
                return FacadeResponse::json($response);
            }

            $is_coupon_pending = PaidCourseApplyCoupon::where('coupon_id', $coupon->id)
                ->where('user_id', $user_id)
                ->where('paid_course_id', $paid_course_id)
                ->where('applied_status', 'panding')
                ->first();

            if(!empty($is_coupon_pending))
            {
                $response->status = $response::status_ok;
                $response->messages = "Coupon valid coupon";
                $response->result = $coupon;
                return FacadeResponse::json($response);
            }
            else{
                PaidCourseApplyCoupon::create([
                    'user_id' => $user_id,
                    'paid_course_id' => $paid_course_id,
                    'coupon_id' => $coupon->id,
                    'applied_from' => 'mobile',
                    'applied_status' => 'panding'
                ]);

                $response->status = $response::status_ok;
                $response->messages = "Coupon valid coupon";
                $response->result = $coupon;
                return FacadeResponse::json($response);
            }
        }

        $response->status = $response::status_fail;
        $response->messages = "Please, Check details!";
        return FacadeResponse::json($response);
    }

    public function uploadPaidCourseTestWrittenAttachment(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        if (!$paid_course_material_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        $attachment_file = '';
        if($request->attachment){
            $attachment_file  =  'Written_question_'. $paid_course_material_id . "_" .time().'.'.$request->attachment->getClientOriginalExtension();
            $request->attachment->move('uploads/paid_course_questions/', $attachment_file);
        }

        if(!$attachment_file) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the file";
            return FacadeResponse::json($response);
        }

        $is_attachment_exist = PaidCourseWrittenAttachment::where('paid_course_material_id', $paid_course_material_id)->get();

        if(sizeof($is_attachment_exist)) {
            $response->status = $response::status_fail;
            $response->messages = "Please, question already exist!";
            return FacadeResponse::json($response);
        }

        PaidCourseWrittenAttachment::create([
            'paid_course_material_id' => $paid_course_material_id,
            'attachment_url' => url('/')."/uploads/paid_course_questions/".$attachment_file,
            'total_marks' => $request->total_marks ? $request->total_marks : 0,
            'no_of_questions' => $request->no_of_questions ? $request->no_of_questions : 0,
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Question has been uploaded successful.";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function deleteCourseTestQuestion(Request $request)
    {
        $response = new ResponseObject;
        $question_id = $request->id ? $request->id : 0;

        if (!$question_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Enter ID";
            return FacadeResponse::json($response);
        }

        $is_question_exist = PaidCourseQuizQuestion::where('id', $question_id)->first();


        if (empty($is_question_exist)) {
            $response->status = $response::status_fail;
            $response->messages = "No question found.";
            return FacadeResponse::json($response);
        }

        $paid_course_material_id = $is_question_exist->paid_course_material_id;

        $is_question_exist->delete();


        $quizSubjects = PaidCourseQuizSubject::where('paid_course_material_id', $paid_course_material_id)->get();
        $set_list = QuizQuestionSet::all();

        $upload_completed = [];

        if(!sizeof($quizSubjects)){
            array_push($upload_completed, false);
        }

        foreach ($set_list as $set){
            foreach ($quizSubjects as $subject) {
                $is_material_added = paidCourseQuizQuestion::where('paid_course_material_subject_id', $subject->id)
                    ->where('paid_course_material_id', $paid_course_material_id)
                    ->where('question_set_id', $set->id)
                    ->get()
                    ->count();

                if($is_material_added < $subject->number_of_questions){
                    array_push($upload_completed, false);
                }
            }
        }

        if(in_array(false, $upload_completed)){
            PaidCourseMaterial::where('id', $paid_course_material_id)->update([
                'sufficient_question' => false
            ]);
        }else{
            PaidCourseMaterial::where('id', $paid_course_material_id)->update([
                'sufficient_question' => true
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = "Question has been deleted successful.";
        $response->result = $upload_completed;
        return FacadeResponse::json($response);
    }

    public function AddUpdateSubject(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_id = $request->paid_course_id;

        if (!$paid_course_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, select Course";
            return FacadeResponse::json($response);
        }

        if ($request->id) {
            PaidCourseSubject::where('id', $request->id)->update([
                'paid_course_id' => $paid_course_id,
                'name' => $request->name,
                'name_bn' => $request->name_bn ? $request->name_bn : '',
                'url' => $request->url ? $request->url : '',
                'course_id' => $request->course_id ? $request->course_id : 0,
                'subject_id' => $request->subject_id ? $request->subject_id : 0,
                'is_optional' => $request->is_optional ? $request->is_optional : 0,
                'is_active' => $request->is_active ? $request->is_active : 0,
                'folder_name' => $request->folder_name ? $request->folder_name : 0,
                'sort' => $request->sort ? $request->sort : 0,
            ]);

            $response->status = $response::status_ok;
            $response->messages = "Subject has been updated successful.";
            $response->result = [];
        } else {
            PaidCourseSubject::create([
                'paid_course_id' => $paid_course_id,
                'name' => $request->name,
                'name_bn' => $request->name_bn ? $request->name_bn : '',
                'url' => $request->url ? $request->url : '',
                'course_id' => $request->course_id ? $request->course_id : 0,
                'subject_id' => $request->subject_id ? $request->subject_id : 0,
                'is_optional' => $request->is_optional ? $request->is_optional : 0,
                'is_active' => $request->is_active ? $request->is_active : 0,
                'folder_name' => $request->folder_name ? $request->folder_name : 0,
                'sort' => $request->sort ? $request->sort : 0,
            ]);

            $response->status = $response::status_ok;
            $response->messages = "Subject has been added successful.";
            $response->result = [];
        }

        return FacadeResponse::json($response);
    }

    public function SubjectList(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_id = $request->paid_course_id;

        if (!$paid_course_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check ID";
            return FacadeResponse::json($response);
        }
        $PCSubject = PaidCourseSubject::where('paid_course_id', $paid_course_id)->orderby('id', 'desc')->get();
        $response->status = $response::status_ok;
        $response->messages = "Successful.";
        $response->result = $PCSubject;
        return FacadeResponse::json($response);
    }

    public function getPaidCourseDetailsWeb(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_id = $request->paid_course_id;
        $user_id = $request->user_id ? $request->user_id : 0;
        $course = PaidCourse::where('id', $paid_course_id)->first();
        if (empty($course)) {
            $response->status = $response::status_fail;
            $response->messages = "No Course Found";
            $response->result = null;
            return FacadeResponse::json($response);
        }
        $course->paid_course_feature = PaidCourseFeature::where('paid_course_id', $paid_course_id)->get();
        $course->paid_course_description_title = PaidCourseDescriptionTitle::where('paid_course_id', $paid_course_id)->with('paid_course_description_detial')->get();
        $material = PaidCourseSubject::join('paid_course_materials', 'paid_course_subjects.id', 'paid_course_materials.paid_course_subject_id')->where('paid_course_subjects.paid_course_id', $course->id)->get();
        // $course->number_of_students_enrolled = 15 +  PaidCourseParticipant::where('paid_course_id', $course->id)->count();

        $date = Date('Y-m-d H:i:s');
        $course->is_active = $course->is_active ? true : false;
        $course->is_only_test = $course->is_only_test ? true : false;
        $course->has_trail = $course->has_trail ? true : false;
        $course->is_fully_paid = $course->is_fully_paid ? true : false;
        $course->is_trial_taken = $course->is_trial_taken ? true : false;
        $course->is_trial_expired = $course->is_trial_taken ? ($course->trial_expiry_date < $date ? true : false) : null;
        $course->is_lc_activated = false;

        if ($user_id) {
            $is_purchased = PaidCourseParticipant::where('user_id', $user_id)
                ->where('paid_course_id', $paid_course_id)->first();

            if (!empty($is_purchased)) {
                $course->is_active = true;
                $course->is_fully_paid = true;
                $course->is_lc_activated = $is_purchased->is_lc_activated;
            }
        }

        $course->paid_course_subjects = PaidCourseSubject::where('paid_course_id', $course->id)->with('paid_course_material')->get();

        $paid_course_material = PaidCourseMaterial::where('paid_course_id', $course->id)->get();

        foreach ($paid_course_material as $course_material) {

            $quizSubjects = PaidCourseQuizSubject::where('paid_course_material_id', $course_material->id)->get();
            // $is_accessable = true;

            // if(!sizeof($quizSubjects)){
            //     $is_accessable = false;
            // }

            // foreach ($quizSubjects as $subject) {
            //     $is_material_added = paidCourseQuizQuestion::where('paid_course_material_subject_id', $subject->id)->where('paid_course_material_id', $course_material->id)->get()->count();
            //     if($is_material_added < $subject->number_of_questions){
            //         $is_accessable = false;
            //     }
            // }

            $subject_lists = [];
            foreach ($quizSubjects as $item){
                array_push($subject_lists, ["subject_id" => $item->id, "subject_name" => $item->name]);
            }

            $course_material->quiz_subjects = $subject_lists;
            $course_material->can_participate = $course_material->sufficient_question;
            $course_material->quiz_question_url = "api/mobile/get-paid-course-quiz-questions-by-id/" . $course_material->id;
            $course_material->quiz_start_url = "api/mobile/paid-course-start/" . $course_material->id . "/" . $user_id;
        }

        $course->paid_course_material = $paid_course_material;

        return FacadeResponse::json($course);
    }

    //For Mobile V1
    public function getPaidCourseQuizQuestionsById(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        if (!$paid_course_material_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        //$total_quiz_question = PaidCourseQuizQuestion::where('paid_course_material_id', $paid_course_material_id)->get();

        $total_quiz_question = [];

        $all_subject_list = PaidCourseQuizSubject::where('paid_course_material_id', $paid_course_material_id)->get();

        $set = QuizQuestionSet::inRandomOrder()->first();

        foreach ($all_subject_list as $subject) {
            $questions = PaidCourseQuizQuestion::inRandomOrder(time())
            ->where('paid_course_material_id', $paid_course_material_id)
            ->where('paid_course_material_subject_id', $subject->id)
            ->where('question_set_id', $set->id)
            ->limit($subject->number_of_questions)
            ->get();
            foreach ($questions as $single) {
                array_push($total_quiz_question, $single);
            }
            //array_push($total_quiz_question, $questions);
        }

        $total_written_question = PaidCourseWrittenAttachment::where('paid_course_material_id', $paid_course_material_id)->first();

        $obj = (object) [
            "data" => ['quiz_question' => $total_quiz_question, 'written_question' => $total_written_question],
            "exam_id" => (int) $paid_course_material_id,
            "submission_url" => "api/mobile/submit-paid-course-quiz-result",
            "start_url" => "api/mobile/start-paid-course-quiz",
            "result_expanation_enabled" => true,
        ];
        return FacadeResponse::json($obj);
    }

    //For Mobile v2 (20.02.22)
    public function getPaidCourseQuizQuestionsByIdV2(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        if (!$paid_course_material_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        //$total_quiz_question = PaidCourseQuizQuestion::where('paid_course_material_id', $paid_course_material_id)->get();

        $total_quiz_question = [];

        $all_subject_list = PaidCourseQuizSubject::where('paid_course_material_id', $paid_course_material_id)->get();
        $set = QuizQuestionSet::inRandomOrder()->first();

        foreach ($all_subject_list as $subject) {
            $questions = PaidCourseQuizQuestion::inRandomOrder(time())
            ->where('paid_course_material_id', $paid_course_material_id)
            ->where('paid_course_material_subject_id', $subject->id)
            ->where('question_set_id', $set->id)
            ->limit($subject->number_of_questions)
            ->get();
            foreach ($questions as $single) {
                array_push($total_quiz_question, $single);
            }
            //array_push($total_quiz_question, $questions);
        }

        foreach ($total_quiz_question as $item) {
            $item->option5 = $item->option5 ? $item->option5 : null;
            $item->option6 = $item->option6 ? $item->option6 : null;
        }

        $total_written_question = PaidCourseWrittenAttachment::where('paid_course_material_id', $paid_course_material_id)->first();

        $obj = (object) [
            "data" => ['quiz_question' => $total_quiz_question, 'written_question' => $total_written_question],
            "exam_id" => (int) $paid_course_material_id,
            "submission_url" => "api/mobile/submit-paid-course-quiz-result",
            "start_url" => "api/mobile/start-paid-course-quiz",
            "result_expanation_enabled" => true,
        ];
        return FacadeResponse::json($obj);
    }

    //For Web
    public function getPaidCourseQuizQuestionsByIdWeb(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        if (!$paid_course_material_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }

        $total_quiz_question = [];

        $all_subject_list = PaidCourseQuizSubject::where('paid_course_material_id', $paid_course_material_id)->get();
        $set = QuizQuestionSet::inRandomOrder()->first();

        foreach ($all_subject_list as $subject) {
            $questions = PaidCourseQuizQuestion::inRandomOrder(time())
            ->where('paid_course_material_id', $paid_course_material_id)
            ->where('paid_course_material_subject_id', $subject->id)
            ->where('question_set_id', $set->id)
            ->limit($subject->number_of_questions)
            ->get();
            foreach ($questions as $single) {
                array_push($total_quiz_question, $single);
            }
            //array_push($total_quiz_question, $questions);
        }

        $total_written_question = PaidCourseWrittenAttachment::where('paid_course_material_id', $paid_course_material_id)->first();

        $obj = (object) [
            "data" => ['quiz_question' => $total_quiz_question, 'written_question' => $total_written_question],
            "exam_id" => (int) $paid_course_material_id,
            "submission_url" => "web/submit-paid-course-quiz-result",
            "start_url" => "web/start-paid-course-quiz",
        ];
        return FacadeResponse::json($obj);
    }

    public function getPaidCoursePaymentHistory(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        $payments = UserAllPayment::select(
                "user_all_payments.*",
                'paid_courses.name as course_name',
                'paid_courses.name_bn as course_name_bn',
                'paid_courses.sales_amount',
                'paid_courses.thumbnail',
                'paid_course_coupons.coupon_code',
                'paid_course_coupons.coupon_value')
            ->where('user_all_payments.item_type', "Paid Course")
            ->where('user_all_payments.transaction_status', "Complete")
            ->when($paid_course_material_id, function ($query) use ($paid_course_material_id){
                return $query->where('user_all_payments.item_id', $paid_course_material_id);
            })
            ->leftJoin('paid_courses', 'paid_courses.id', 'user_all_payments.item_id')
            ->leftJoin('paid_course_coupons', 'paid_course_coupons.id', 'user_all_payments.coupon_id')
            ->orderby('user_all_payments.id', 'desc')
            ->get();
        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $payments;
        return FacadeResponse::json($response);
    }

    public function getPaidCourseQuizDetails(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        if (!$paid_course_material_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Check the details";
            return FacadeResponse::json($response);
        }
        return FacadeResponse::json(PaidCourseMaterial::where('id', $paid_course_material_id)->first());
    }

    public function startPaidCouseQuiz(Request $request)
    {
        $response = new ResponseObject;
        $number_of_participation = 0;

        $exam = PaidCourseMaterial::where('id', $request->exam_id)->first();

        if (empty($exam)) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Course not found!";
            return FacadeResponse::json($response);
        }

        $access_count = PaidCourseParticipantQuizAccess::where('user_id', $request->user_id)
            ->where('paid_course_material_id', $request->exam_id)->first();

        if (empty($access_count)) {
            $response->status = $response::status_fail;
            $response->messages = "Please, purchase course first!";
            return FacadeResponse::json($response);
        }

        $participation_count = PaidCourseQuizParticipationCount::where('user_id', $request->user_id)
            ->where('paid_course_material_id', $request->exam_id)->first();
        
        // if (empty($participation_count)) {
        //     $response->status = $response::status_fail;
        //     $response->messages = "Please, purchase course first!";
        //     return FacadeResponse::json($response);
        // }

        if (!empty($participation_count)) {
            $number_of_participation = $participation_count->number_of_participation;
        }

        if ($number_of_participation >= $access_count->access_count) {
            $response->status = $response::status_fail;
            $response->messages = "Your exam quota limit is over";
            return FacadeResponse::json($response);
        }

        ResultPaidCouresQuiz::create([
            "user_id" => $request->user_id,
            "paid_course_material_id" => $request->exam_id,
            "mark" => 0,
            "total_mark" => $exam->quiz_total_mark,
        ]);

        if ($number_of_participation == 0) {
            PaidCourseQuizParticipationCount::create([
                "user_id" => $request->user_id,
                "paid_course_material_id" => $request->exam_id,
                "number_of_participation" => 1,
            ]);
        } else {
            PaidCourseQuizParticipationCount::where('user_id', $request->user_id)
                ->where('paid_course_material_id', $request->exam_id)->update([
                "number_of_participation" => $participation_count->number_of_participation + 1,
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = "Your exam has been started";

        return FacadeResponse::json($response);
    }

    public function AddPermissionOfPurchasedUser(Request $request)
    {
        $purchased_code = PaidCourseParticipant::where('paid_course_id', $request->paid_course_id)->get();
        foreach ($purchased_code as $item) {
            $exam_list = PaidCourseMaterial::where('paid_course_id', $item->paid_course_id)->get();
            foreach ($exam_list as $meterial) {
                $access_count = PaidCourseParticipantQuizAccess::where('user_id', $item->user_id)->where('paid_course_material_id', $meterial->id)->first();

                if(empty($access_count)){

                    if($meterial->test_type == "RevisionTest"){
                        PaidCourseParticipantQuizAccess::create([
                            "paid_course_material_id" => $meterial->id,
                            "user_id" => $item->user_id,
                            "access_count" => 100,
                        ]);
                    }

                    if($meterial->test_type == "ModelTest"){
                        PaidCourseParticipantQuizAccess::create([
                            "paid_course_material_id" => $meterial->id,
                            "user_id" => $item->user_id,
                            "access_count" => 1,
                        ]);
                    }

                    if($meterial->test_type == "WeeklyTest"){
                        PaidCourseParticipantQuizAccess::create([
                            "paid_course_material_id" => $meterial->id,
                            "user_id" => $item->user_id,
                            "access_count" => 1,
                        ]);
                    }
                }
            }
        }

        return FacadeResponse::json("Successful!");
    }

    public function createUserPaidCoursePaymentMobile(Request $request){
        $response = new ResponseObject;

        $check_payment = UserAllPayment::where('user_id', $request->user_id)->where('item_id', $request->item_id)
        ->where('item_type','=','Paid Course')
        ->where('transaction_status','=','Complete')->first();

        if (!empty($check_payment)) {
            $response->status = $response::status_fail;
            $response->messages = "Already payment done for this course";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $coupon_code = $request->coupon_code ? $request->coupon_code : '';
        $coupon_price = 0;
        $coupon_id = null;

        if($coupon_code){
            $coupon = PaidCourseCoupon::where('coupon_code', $coupon_code)->first();
            $coupon_price = $coupon->coupon_value;
            $coupon_id = $coupon->id;

            $coupon_details = PaidCourseApplyCoupon::where('coupon_id', $coupon->id)
                ->where('user_id', $request->user_id)
                ->where('paid_course_id', $request->item_id)
                ->where('applied_status', 'panding')
                ->first();

            if(!empty($coupon_details)){
                PaidCourseApplyCoupon::where('id', $coupon_details->id)->update([
                    'applied_status' => 'successful'
                ]);
            }else{
                PaidCourseApplyCoupon::create([
                    'user_id' => $request->user_id,
                    'paid_course_id' => $request->item_id,
                    'coupon_id' => $coupon->id,
                    'applied_from' => 'mobile',
                    'applied_status' => 'successful'
                ]);
            }
        }

        $user =  User::where('id', $request->user_id)->first();
        $course =  PaidCourse::where('id', $request->item_id)->first();

        $payment = UserAllPayment::updateOrCreate([
            'user_id' => $request->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->mobile_number,
            'address' => $user->address,
            'currency' => $request->currency,
            'item_id' => $request->item_id,
            'item_name' => $course->name,
            'item_type'=> "Paid Course",
            'coupon_id' => $coupon_id,
            'payable_amount' => $course->sales_amount,
            'paid_amount' => $request->amount, //$course->sales_amount - $coupon_price,
            'card_type'  => $request->card_type,
            'discount' => $coupon_price,
            'transaction_id' => $request->transaction_id,
            'transaction_status' => 'Complete',
            'payment_status' => "Full Paid",
            'status' => 'Enrolled'
        ]);

        $user_payment_details = UserAllPaymentDetails::updateOrCreate([
            'payment_id' => $payment->id,
            'amount' => $payment->paid_amount,
        ]);

        $checkParticipant = PaidCourseParticipant::where('paid_course_id', $request->item_id)->where('user_id', $request->user_id)->first();

        if (empty($checkParticipant)) {
            PaidCourseParticipant::create([
                'user_id' => $request->user_id,
                'paid_course_id' => $request->item_id,
                'course_amount' => $course->sales_amount,
                'paid_amount' => $payment->paid_amount,
                'is_fully_paid' => true
            ]);
        } else {
            $checkParticipant->update([
                'paid_amount' => $payment->paid_amount,
                'is_active' => true,
                'is_fully_paid' => true
            ]);
        }

        $course->update([
            'number_of_students_enrolled' => $course->number_of_students_enrolled + 1,
        ]);

        $materialIds = PaidCourseMaterial::where('paid_course_id', $request->item_id)->pluck('id');
        foreach ($materialIds as $materialId) {
            $quizAccess = PaidCourseParticipantQuizAccess::where('user_id', $request->user_id)
                ->where('paid_course_material_id', $materialId)->first();

            if(empty($quizAccess)){
                $meterial = PaidCourseMaterial::where('id', $materialId)->first();
                if($meterial->test_type == "RevisionTest"){
                    PaidCourseParticipantQuizAccess::create([
                        "user_id" => $request->user_id,
                        "paid_course_material_id" => $materialId,
                        "access_count" => 100,
                    ]);
                }

                if($meterial->test_type == "ModelTest"){
                    PaidCourseParticipantQuizAccess::create([
                        "user_id" => $request->user_id,
                        "paid_course_material_id" => $materialId,
                        "access_count" => 1,
                    ]);
                }

                if($meterial->test_type == "WeeklyTest"){
                    PaidCourseParticipantQuizAccess::create([
                        "user_id" => $request->user_id,
                        "paid_course_material_id" => $materialId,
                        "access_count" => 1,
                    ]);
                }
            } else {
                $quizAccess->update([
                    'access_count' => $quizAccess->access_count + 100
                ]);
            }
        }

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = " You have successfully enrolled this course";
        $response->result = "";
        return FacadeResponse::json($response);
    }

    public function submitPaidCourseQuizResult(Request $request)
    {
        $response = new ResponseObject;

        $formData = json_decode($request->data, true);

        $user_id = $formData["user_id"];
        $exam_id = $formData["exam_id"];
        $answers = $formData["answers"];
        $attach_count = $formData["attach_count"] ? $formData["attach_count"] : 0;

        $exam = PaidCourseMaterial::where('id', $exam_id)->first();
        $count = 0;
        $negCount = 0;

        $resultPaidCourseQuiz = ResultPaidCouresQuiz::where('user_id',$user_id)
        ->where('paid_course_material_id',$exam_id)->orderBy('id','DESC')->first();

        if($attach_count){
            for($i = 0; $i < $attach_count; $i++){
                $attach_file = "attachment_".$i;
                $attachment_file = '';
                if($request->$attach_file){
                    $attachment_file  =  'paid_course_answer_'. $exam_id . "_" . $i . "_" .time().'.'.$request->$attach_file->getClientOriginalExtension();
                    $request->$attach_file->move('uploads/paid_course_answers/', $attachment_file);
                }

                ResultPaidCourseWrittenAttachment::create([
                    "paid_course_material_id" => $exam_id,
                    "paid_course_quiz_result_id" => $resultPaidCourseQuiz->id,
                    "user_id" => $user_id,
                    "attachment_url" => url('/')."/uploads/paid_course_answers/".$attachment_file,
                ]);
            }
        }

        $paid_course_written_question = PaidCourseWrittenAttachment::where("paid_course_material_id", $exam_id)->first();
        if(!empty($paid_course_written_question)){
            for($i = 1; $i <= $paid_course_written_question->no_of_questions; $i++){
                ResultPaidCourseWrittenMark::create([
                    "paid_course_material_id" => $exam_id,
                    "paid_course_quiz_result_id" => $resultPaidCourseQuiz->id,
                    "user_id" => $user_id,
                    "question_no" => $i,
                    "mark" => 0
                ]);
            }
        }

        $paid_course_quiz_subjects = PaidCourseQuizSubject::where("paid_course_material_id", $exam_id)->get();

        foreach ($paid_course_quiz_subjects as $subject) {
            $subject->positive_count = 0;
            $subject->negetive_count = 0;
        }

        foreach ($answers as $ans) 
        {
            $question = PaidCourseQuizQuestion::where('id', $ans['question_id'])->select(
                'id',
                'paid_course_material_subject_id',
                'correct_answer',
                'correct_answer2',
                'correct_answer3',
                'correct_answer4',
                'correct_answer5',
                'correct_answer6'
            )->first();

            ResultPaidCouresQuizAnswer::insert([
                "paid_course_quiz_question_id" => $ans['question_id'],
                "result_paid_coures_quiz_id" => $resultPaidCourseQuiz->id,
                "user_id" =>  $user_id,
                "paid_course_material_subject_id" => $question->paid_course_material_subject_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);

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
                    foreach ($paid_course_quiz_subjects as $subject) {
                        if($subject->id == $question->paid_course_material_subject_id){
                            $subject->positive_count = $subject->positive_count + 1;
                        }
                    }

                } else {
                    $negCount++;
                    foreach ($paid_course_quiz_subjects as $subject) {
                        if($subject->id == $question->paid_course_material_subject_id){
                            $subject->negetive_count = $subject->negetive_count + 1;
                        }
                    }
                }
            } else {
                if (sizeof($given_answer_array) > sizeof($correct_answer_array)) {
                    $negCount++;
                    foreach ($paid_course_quiz_subjects as $subject) {
                        if($subject->id == $question->paid_course_material_subject_id){
                            $subject->negetive_count = $subject->negetive_count + 1;
                        }
                    }
                }
            }
        }

        foreach ($paid_course_quiz_subjects as $subject) {
            ResultPaidCourseQuizSubjectWiseAnswer::insert([
                "paid_course_material_subject_id" => $subject->id,
                "user_id" =>  $user_id,
                "result_paid_coures_quiz_id" => $resultPaidCourseQuiz->id,
                "paid_coures_quiz_material_id" => $exam_id,
                "positive_count" =>  $subject->positive_count,
                "negetive_count" =>  $subject->negetive_count
            ]);
        }

        $mark = $count * $exam->quiz_positive_mark - $negCount * $exam->quiz_negative_mark;

        ResultPaidCouresQuiz::where('id', $resultPaidCourseQuiz->id)->update([
            "mark" => $mark,
            "submission_status" => "Submitted"
        ]);

        // $paid_result = ResultPaidCouresQuiz::where('id', $resultPaidCourseQuiz->id)->first();

        // $response = new ResponseObject;
        // $response->status = $response::status_ok;
        // $response->messages = "Submmit Successful! Check";
        // $response->result = ["Mark" => $mark, "Result" => $paid_result, "positive" => $count, "negative" => $negCount];
        // return FacadeResponse::json($response);

        $user = User::where('id', $user_id)->first();
        $points = $mark + $user->points;
        User::where('id', $user_id)->update(['points' => $points]);

        $userResult = ResultPaidCouresQuiz::where('result_paid_coures_quizzes.id', $resultPaidCourseQuiz->id)
        ->join('paid_course_materials', 'result_paid_coures_quizzes.paid_course_material_id', 'paid_course_materials.id')
        ->select('result_paid_coures_quizzes.*', 'paid_course_materials.name as exam_name', 'paid_course_materials.quiz_positive_mark as positive_mark', 'paid_course_materials.quiz_negative_mark as negative_mark', 'paid_course_materials.quiz_question_number as question_number')
        ->first();

        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";

        $final_result = PaidCourseQuizQuestion::leftJoin('result_paid_course_quiz_answers', 'paid_course_quiz_questions.id', 'result_paid_course_quiz_answers.paid_course_quiz_question_id')
        ->where('paid_course_quiz_questions.paid_course_material_id', $exam_id)
        ->where('result_paid_course_quiz_answers.result_paid_coures_quiz_id', $userResult->id)
        ->select(
            'paid_course_quiz_questions.*',
            'result_paid_course_quiz_answers.answer as given_answer',
            'result_paid_course_quiz_answers.answer2 as given_answer2',
            'result_paid_course_quiz_answers.answer3 as given_answer3',
            'result_paid_course_quiz_answers.answer4 as given_answer4',
            'result_paid_course_quiz_answers.answer5 as given_answer5',
            'result_paid_course_quiz_answers.answer6 as given_answer6'
            )
        ->get();
        $userResult->correct_answer_number = $count;
        $userResult->wrong_answer_number = $negCount;
        $userResult->questions = $final_result;
        $response->result = $userResult;

        return FacadeResponse::json($response);
    }

    public function updateSubjectIdAccordingToSetup(Request $request){
        $all_answers = ResultPaidCouresQuizAnswer::all();

        foreach ($all_answers as $item) {
            $question = PaidCourseQuizQuestion::where('id', $item->paid_course_quiz_question_id)->first();
            ResultPaidCouresQuizAnswer::where('id', $item->id)->update([
                "paid_course_material_subject_id" => $question->paid_course_material_subject_id
            ]);
        }

        return response()->json('Successful!');
    }

    public function submitPaidCourseQuizResultMobile(Request $request)
    {
        $response = new ResponseObject;

        $formData = json_decode($request->data, true);

        $user_id = $formData["user_id"];
        $exam_id = $formData["exam_id"];
        $answers = $formData["answers"];
        $attach_count = $formData["attach_count"] ? $formData["attach_count"] : 0;

        $exam = PaidCourseMaterial::where('id', $exam_id)->first();
        $count = 0;
        $negCount = 0;

        $resultPaidCourseQuiz = ResultPaidCouresQuiz::where('user_id',$user_id)
        ->where('paid_course_material_id',$exam_id)->orderBy('id','DESC')->first();

        if (empty($resultPaidCourseQuiz)) {
            $response->status = $response::status_fail;
            $response->messages = "Please, Start exam first.";
            return FacadeResponse::json($response);
        }

        $paid_course_quiz_subjects = PaidCourseQuizSubject::where("paid_course_material_id", $exam_id)->get();

        foreach ($paid_course_quiz_subjects as $subject) {
            $subject->positive_count = 0;
            $subject->negetive_count = 0;
        }

        foreach ($answers as $ans) 
        {
            $question = PaidCourseQuizQuestion::where('id', $ans['question_id'])->select(
                'id',
                'paid_course_material_subject_id',
                'correct_answer',
                'correct_answer2',
                'correct_answer3',
                'correct_answer4',
                'correct_answer5',
                'correct_answer6'
            )->first();

            ResultPaidCouresQuizAnswer::insert([
                "paid_course_quiz_question_id" => $ans['question_id'],
                "result_paid_coures_quiz_id" => $resultPaidCourseQuiz->id,
                "user_id" =>  $user_id,
                "paid_course_material_subject_id" => $question->paid_course_material_subject_id,
                "answer" =>  $ans['answer'],
                "answer2" =>  $ans['answer2'],
                "answer3" =>  $ans['answer3'],
                "answer4" =>  $ans['answer4'],
                "answer5" =>  $ans['answer5'],
                "answer6" =>  $ans['answer6']
            ]);

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
                    foreach ($paid_course_quiz_subjects as $subject) {
                        if($subject->id == $question->paid_course_material_subject_id){
                            $subject->positive_count = $subject->positive_count + 1;
                        }
                    }
                } else {
                    $negCount++;
                    foreach ($paid_course_quiz_subjects as $subject) {
                        if($subject->id == $question->paid_course_material_subject_id){
                            $subject->negetive_count = $subject->negetive_count + 1;
                        }
                    }
                }
            } else {
                if (sizeof($given_answer_array) > sizeof($correct_answer_array)) {
                    $negCount++;
                    foreach ($paid_course_quiz_subjects as $subject) {
                        if($subject->id == $question->paid_course_material_subject_id){
                            $subject->negetive_count = $subject->negetive_count + 1;
                        }
                    }
                }
            }
        }

        foreach ($paid_course_quiz_subjects as $subject) {
            ResultPaidCourseQuizSubjectWiseAnswer::insert([
                "paid_course_material_subject_id" => $subject->id,
                "user_id" =>  $user_id,
                "result_paid_coures_quiz_id" => $resultPaidCourseQuiz->id,
                "paid_coures_quiz_material_id" => $exam_id,
                "positive_count" =>  $subject->positive_count,
                "negetive_count" =>  $subject->negetive_count
            ]);
        }

        $mark = $count * $exam->quiz_positive_mark - $negCount * $exam->quiz_negative_mark;

        ResultPaidCouresQuiz::where('id', $resultPaidCourseQuiz->id)->update([
            "mark" => $mark
        ]);
        
        $files = $request->file('files');
        if($attach_count){
            $i = 1;
            foreach ($files as $file) {
                $attachment_file = '';
                if($file){
                    $attachment_file = 'paid_course_answer_'. $exam_id . "_" . $i . "_" .time().'.'.$file->getClientOriginalExtension();
                    $file->move('uploads/paid_course_answers/', $attachment_file);
                }
    
                ResultPaidCourseWrittenAttachment::create([
                    "paid_course_material_id" => $exam_id,
                    "paid_course_quiz_result_id" => $resultPaidCourseQuiz->id,
                    "user_id" => $user_id,
                    "attachment_url" => url('/')."/uploads/paid_course_answers/".$attachment_file,
                ]);
                $i++;
            }
        }

        $paid_course_written_question = PaidCourseWrittenAttachment::where("paid_course_material_id", $exam_id)->first();
        if(!empty($paid_course_written_question)){
            for($i = 1; $i <= $paid_course_written_question->no_of_questions; $i++){
                ResultPaidCourseWrittenMark::create([
                    "paid_course_material_id" => $exam_id,
                    "paid_course_quiz_result_id" => $resultPaidCourseQuiz->id,
                    "user_id" => $user_id,
                    "question_no" => $i,
                    "mark" => 0
                ]);
            }
        }

        ResultPaidCouresQuiz::where('id', $resultPaidCourseQuiz->id)->update([
            "submission_status" => "Submitted"
        ]);

        $user = User::where('id', $user_id)->first();
        $points = $mark + $user->points;
        User::where('id', $user_id)->update(['points' => $points]);

        $response->status = $response::status_ok;
        $response->messages = "Thank you. Your result has been submitted";
        $verifyController = new VerifyCodeController();
        $response->result = $verifyController->getLoginData($user_id);

        return FacadeResponse::json($response);
    }
    
    public function downloadPaidCourseQuizResultPdf(Request $request){
        $response = new ResponseObject;

        $result_data = ResultPaidCouresQuizAnswer::where('result_paid_coures_quiz_id', $request->paid_course_result_id)
        ->select(
            'result_paid_course_quiz_answers.*',
            'result_paid_course_quiz_answers.answer',
            'result_paid_course_quiz_answers.answer2',
            'result_paid_course_quiz_answers.answer3',
            'result_paid_course_quiz_answers.answer4',
            'paid_course_quiz_questions.question',
            'paid_course_quiz_questions.option1',
            'paid_course_quiz_questions.option2',
            'paid_course_quiz_questions.option3',
            'paid_course_quiz_questions.option4',
            'paid_course_quiz_questions.correct_answer',
            'paid_course_quiz_questions.correct_answer2',
            'paid_course_quiz_questions.correct_answer3',
            'paid_course_quiz_questions.correct_answer4',
        )
        ->leftJoin('paid_course_quiz_questions', 'paid_course_quiz_questions.id', 'result_paid_course_quiz_answers.paid_course_quiz_question_id')
        ->get();

        $resultPaidCourseQuiz = ResultPaidCouresQuiz::where('id',$request->paid_course_result_id)->first();
        $user = User::where('id', $resultPaidCourseQuiz->user_id)->first();
        $data = ['title' => 'Quiz Result', 'student_name' => $user->name, 'result' => $result_data];

        // $options = new Options();
        // $options->set('isRemoteEnabled', true);
        // $options->set('isFontSubsettingEnabled', true);
        // $options->set('isHtml5ParserEnabled', true);            
        // $dompdf = new Dompdf($options);          
        
        // $dompdf->getOptions()->setFontDir(public_path('fonts/'));
        // $dompdf->getOptions()->setFontCache(public_path('fonts/'));
        // $dompdf->getOptions()->set('defaultFont', 'SolaimanLipi');    
        
        // $dompdf->getOptions()->set('fontEncoding', 'utf-8');

        $pdf = PDF::loadView('result/pc_quiz_result_pdf', $data);
        $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isFontSubsettingEnabled' => true]);
        return $pdf->download($request->paid_course_result_id . '_pc_quiz_result.pdf');
    }

    public function downloadPCQuizResultmPdf(Request $request){
        $response = new ResponseObject;

        $result_data = ResultPaidCouresQuizAnswer::where('result_paid_coures_quiz_id', $request->paid_course_result_id)
        ->select(
            'result_paid_course_quiz_answers.*',
            'result_paid_course_quiz_answers.answer',
            'result_paid_course_quiz_answers.answer2',
            'result_paid_course_quiz_answers.answer3',
            'result_paid_course_quiz_answers.answer4',
            'paid_course_quiz_questions.question',
            'paid_course_quiz_questions.option1',
            'paid_course_quiz_questions.option2',
            'paid_course_quiz_questions.option3',
            'paid_course_quiz_questions.option4',
            'paid_course_quiz_questions.correct_answer',
            'paid_course_quiz_questions.correct_answer2',
            'paid_course_quiz_questions.correct_answer3',
            'paid_course_quiz_questions.correct_answer4',
            'paid_course_quiz_questions.explanation_text'
        )
        ->leftJoin('paid_course_quiz_questions', 'paid_course_quiz_questions.id', 'result_paid_course_quiz_answers.paid_course_quiz_question_id')
        ->get();

        $resultPaidCourseQuiz = ResultPaidCouresQuiz::where('id',$request->paid_course_result_id)->first();
        $user = User::where('id', $resultPaidCourseQuiz->user_id)->first();
        $data = [
            'title' => 'Quiz Result', 
            'student_name' => $user->name, 
            'mobile' => $user->mobile_number, 
            'result' => $result_data,
            'total_questions' => sizeof($result_data),
            'total_marks' => $resultPaidCourseQuiz->mark,
            'written_marks' => $resultPaidCourseQuiz->written_marks,
            'total_obtained_marks' => $resultPaidCourseQuiz->total_mark,
            'result_status' => $resultPaidCourseQuiz->submission_status,
        ];


        $htmlContent = view('result/pc_quiz_result_pdf', $data)->render();
        
        $fontDir = storage_path('fonts');

        if (!file_exists($fontDir . '/SolaimanLipi.ttf')) {
            die('Font file not found: ' . $fontDir . '/SolaimanLipi.ttf');
        }

        $mpdf = new Mpdf([
            'fontDir' => array_merge((new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [
                $fontDir  // Add your custom font directory here
            ]),
            'fontdata' => [
                'solaimanlipi' => [  // Define the custom font
                    'R' => 'SolaimanLipi.ttf',  // Regular font file
                    'B' => 'SolaimanLipi.ttf',  // Bold (can be same if no separate bold)
                ]
            ],
            'default_font' => 'solaimanlipi'  // Set default font to SolaimanLipi
        ]);

        $mpdf->WriteHTML($htmlContent);

        $pdfOutput = $mpdf->Output('', 'S');  // 'S' returns the PDF as a string

        // Return the PDF as a response with the appropriate headers
        return response($pdfOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="bangla_document.pdf"');
    }

    public function pcQuizResultExcelDownload(Request $request){
        $response = new ResponseObject;
        
        // Fetch the result data
        $result_data = ResultPaidCouresQuizAnswer::where('result_paid_coures_quiz_id', $request->paid_course_result_id)
            ->select(
                'paid_course_quiz_questions.question',
                'paid_course_quiz_questions.option1',
                'paid_course_quiz_questions.option2',
                'paid_course_quiz_questions.option3',
                'paid_course_quiz_questions.option4',
                'result_paid_course_quiz_answers.answer',
                'result_paid_course_quiz_answers.answer2',
                'result_paid_course_quiz_answers.answer3',
                'result_paid_course_quiz_answers.answer4',
                'paid_course_quiz_questions.correct_answer',
                'paid_course_quiz_questions.correct_answer2',
                'paid_course_quiz_questions.correct_answer3',
                'paid_course_quiz_questions.correct_answer4',
                'paid_course_quiz_questions.explanation_text'
            )
            ->leftJoin('paid_course_quiz_questions', 'paid_course_quiz_questions.id', 'result_paid_course_quiz_answers.paid_course_quiz_question_id')
            ->get();

        // Fetch user and result summary data
        $resultPaidCourseQuiz = ResultPaidCouresQuiz::where('id', $request->paid_course_result_id)->first();
        $user = User::where('id', $resultPaidCourseQuiz->user_id)->first();
        $summary = [
            'name' => $user->name,
            'phone' => $user->mobile_number,
            'total_questions' => sizeof($result_data),
            'obtained_marks' => $resultPaidCourseQuiz->mark,
            'submission_status' => $resultPaidCourseQuiz->submission_status,
        ];

        // Prepare data for Excel (without options)
        $data = [];
        foreach ($result_data as $item) {
            $is_skipped = "";
            if(!$item->answer && !$item->answer2 && !$item->answer3 && !$item->answer4){
                $is_skipped = "Skipped";
            }else{
                $options = [];
                if($item->answer){
                    array_push($options, 'A');
                }
                if($item->answer2){
                    array_push($options, 'B');
                }
                if($item->answer3){
                    array_push($options, 'C');
                }
                if($item->answer4){
                    array_push($options, 'D');
                }

                $is_skipped = implode(', ', $options);
            }

            $data[] = [
                'Question' => $item->question,
                'Option 1' => $this->checkAnswer($item->answer, $item->correct_answer, 'A', $item->option1),
                'Option 2' => $this->checkAnswer($item->answer2, $item->correct_answer2, 'B', $item->option2),
                'Option 3' => $this->checkAnswer($item->answer3, $item->correct_answer3, 'C', $item->option3),
                'Option 4' => $this->checkAnswer($item->answer4, $item->correct_answer4, 'D', $item->option4),
                'Student Answer' => $is_skipped,
                'Explanation Text' => $item->explanation_text, // Placed at the end
            ];
        }

        // Define column headings
        $headings = ['Question', 'Option 1', 'Option 2', 'Option 3', 'Option 4', 'Student Answer', 'Explanation Text'];

        // Generate Excel file with conditional styling and summary at the bottom
        return Excel::download(new class($data, $headings, $summary) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithEvents {
            private $data;
            private $headings;
            private $summary;

            public function __construct($data, $headings, $summary)
            {
                $this->data = $data;
                $this->headings = $headings;
                $this->summary = $summary;
            }

            // Populate the Excel file with data
            public function array(): array
            {
                return $this->data;
            }

            // Apply styles to the Excel sheet
            public function styles(Worksheet $sheet)
            {
                // Color the answers based on their correctness
                foreach ($this->data as $index => $row) {
                    $rowIndex = $index + 2;  // Adjust for header row

                    $sheet->getStyle("B$rowIndex")->getFont()->getColor()->setARGB($this->getAnswerColor($row['Option 1']));
                    $sheet->getStyle("C$rowIndex")->getFont()->getColor()->setARGB($this->getAnswerColor($row['Option 2']));
                    $sheet->getStyle("D$rowIndex")->getFont()->getColor()->setARGB($this->getAnswerColor($row['Option 3']));
                    $sheet->getStyle("E$rowIndex")->getFont()->getColor()->setARGB($this->getAnswerColor($row['Option 4']));
                }
            }

            // Define the headings for the data section
            public function headings(): array
            {
                return $this->headings;
            }

            // Add summary information at the bottom of the Excel sheet
            public function registerEvents(): array
            {
                return [
                    \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();

                        // Calculate the row number where the summary will be placed
                        $summaryStartRow = count($this->data) + 3;  // +3 for headings and some padding

                        // Add the summary information at the bottom
                        $sheet->setCellValue("A{$summaryStartRow}", 'MCQ Result Summary');
                        $sheet->setCellValue("A" . ($summaryStartRow + 1), 'Name');
                        $sheet->setCellValue("B" . ($summaryStartRow + 1), $this->summary['name']);
                        $sheet->setCellValue("A" . ($summaryStartRow + 2), 'Phone No.');
                        $sheet->setCellValue("B" . ($summaryStartRow + 2), $this->summary['phone']);
                        $sheet->setCellValue("A" . ($summaryStartRow + 3), 'Number of Questions');
                        $sheet->setCellValue("B" . ($summaryStartRow + 3), $this->summary['total_questions']);
                        $sheet->setCellValue("A" . ($summaryStartRow + 4), 'Obtained Marks in MCQ');
                        $sheet->setCellValue("B" . ($summaryStartRow + 4), $this->summary['obtained_marks']);
                        $sheet->setCellValue("A" . ($summaryStartRow + 5), 'Submission Status');
                        $sheet->setCellValue("B" . ($summaryStartRow + 5), $this->summary['submission_status']);

                        // Set the summary rows' font to bold
                        $sheet->getStyle("A{$summaryStartRow}:B" . ($summaryStartRow + 5))->getFont()->setBold(true);
                    }
                ];
            }

            // Helper function to determine the answer color
            private function getAnswerColor($answer)
            {
                if (strpos($answer, '(Correct Choice)') !== false) {
                    return \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_GREEN;
                } elseif (strpos($answer, '(Student\'s Choice)') !== false) {
                    return \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED;
                }
                return \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK;
            }
        }, 'quiz_results.xlsx');

    }

    public function downloadAllStudentResultsInExcel(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_meterial_id = $request->paid_course_meterial_id;

        if (!$paid_course_meterial_id) {
            return redirect()->back()->with('error', 'Invalid Quiz ID.');
        }

        $quiz_info = PaidCourseMaterial::where('id', $paid_course_meterial_id)->first();
        $download_file_name = $quiz_info->name . " - all_student_results_excel.xlsx";

        return Excel::download(new StudentExamResultExport($paid_course_meterial_id), $download_file_name);
    }

    private function checkAnswer($answer, $correctAnswer, $optionLabel, $optionValue)
    {
        if ($answer === $correctAnswer && $answer !== null) {
            // Correct answer
            return "{$optionLabel}. {$optionValue} (Correct Choice)";
        } elseif ($answer !== null && $answer !== $correctAnswer) {
            // Student's choice but wrong
            return "{$optionLabel}. {$optionValue} (Student's Choice)";
        } elseif ($answer === null && $correctAnswer !== null) {
            // Correct answer but not selected by the student
            return "{$optionLabel}. {$optionValue} (Correct Choice)";
        } else {
            return "{$optionLabel}. {$optionValue}";
        }
    }

    public function getSubjectWiseResult(Request $request)
    {
        $response = new ResponseObject;

        $result_paid_coures_quiz_id = $request->result_id ? $request->result_id : 0;
        $result = ResultPaidCourseQuizSubjectWiseAnswer::where('result_paid_coures_quiz_id', $result_paid_coures_quiz_id)
            ->select('result_paid_course_quiz_subject_wise_answers.*', 'paid_course_quiz_subjects.name as subject_name', 'paid_course_quiz_subjects.number_of_questions' )
            ->leftJoin('paid_course_quiz_subjects', 'paid_course_quiz_subjects.id', 'result_paid_course_quiz_subject_wise_answers.paid_course_material_subject_id')
            ->get();

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $result;
        return FacadeResponse::json($response);
    }

    public function getPaidCourseSubjectWiseAllResultByID(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        $PCTest = PaidCourseMaterial::where('id', $paid_course_material_id)->first();
        $all_subject_list = PaidCourseQuizSubject::where('paid_course_material_id', $paid_course_material_id)->get();

        $data_array =  ["Name", "Phone", "Email", "Course Name", "Total Marks", "Positive Marks", "Negetive Marks", "Total Obtained Marks (MCQ)", "Total Obtained Marks (Written)", "Total Obtained Marks"];

        foreach ($all_subject_list as $subject) {
            array_push($data_array, $subject->name);
        }

        $final_data_array = array(
            $data_array,
            ["", "", "", "", "", "", "", "", "", "", "", "", "", "", ""]
        );

        $history = ResultPaidCouresQuiz::select(
            "result_paid_coures_quizzes.*",
            'users.name as user_name',
            'users.email',
            'users.mobile_number as phone',
        )
        ->when($paid_course_material_id, function ($query) use ($paid_course_material_id){
            return $query->where('result_paid_coures_quizzes.paid_course_material_id', $paid_course_material_id);
        })
        ->leftJoin('users', 'users.id', 'result_paid_coures_quizzes.user_id')
        ->orderby('result_paid_coures_quizzes.id', 'desc')
        ->get();

        foreach ($history as $result) {
            $subject_markes = [
                $result->user_name, 
                $result->phone, 
                $result->email, 
                $PCTest->name, 
                $PCTest->quiz_total_mark, 
                $PCTest->quiz_positive_mark, 
                $PCTest->quiz_negative_mark, 
                $result->mark, 
                $result->written_marks, 
                $result->mark + $result->written_marks
            ];
            $result_details = ResultPaidCourseQuizSubjectWiseAnswer::where('result_paid_coures_quiz_id', $result->id)->get();

            foreach ($result_details as $details) 
            {
                foreach ($all_subject_list as $subject) 
                {
                    if ($subject->id == $details->paid_course_material_subject_id) {
                        $positive_mark = $details->positive_count * $PCTest->quiz_positive_mark;
                        $negative_mark = $details->negetive_count * $PCTest->quiz_negative_mark;
                        $total = $positive_mark - $negative_mark;
                        array_push($subject_markes, $total);
                    }
                }
            }

            $final_data_array[] = array($subject_markes);
        }

        $export = new ExportPaidCourseTest($final_data_array);
        $time = date("Y-m-d h:i:s");
        return Excel::download($export, 'Paid Course - Subject Wise Result ' . $time . ' .xlsx');
    }
    
    public function getPaidCourseApplyCouponHistory(Request $request)
    {
        $response = new ResponseObject;

        $coupon_id = $request->coupon_id ? $request->coupon_id : 0;

        $history = PaidCourseApplyCoupon::select(
                "paid_course_apply_coupons.*",
                'paid_course_coupons.coupon_code',
                'paid_course_coupons.coupon_value',
                'paid_courses.name as course_name',
                'paid_courses.name_bn as course_name_bn',
                'paid_courses.sales_amount',
                'paid_courses.thumbnail',
                'users.name as user_name',
                'users.email',
                'users.mobile_number as phone',
            )
            ->when($coupon_id, function ($query) use ($coupon_id){
                return $query->where('paid_course_apply_coupons.coupon_id', $coupon_id);
            })
            ->where('paid_course_apply_coupons.applied_status', "successful")
            ->leftJoin('paid_course_coupons', 'paid_course_coupons.id', 'paid_course_apply_coupons.coupon_id')
            ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_apply_coupons.paid_course_id')
            ->leftJoin('users', 'users.id', 'paid_course_apply_coupons.user_id')
            ->orderby('paid_course_apply_coupons.id', 'desc')
            ->get();
        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $history;
        return FacadeResponse::json($response);
    }

    public function getPaidCourseResultList(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        $history = ResultPaidCouresQuiz::select(
                "result_paid_coures_quizzes.*",
                'paid_course_materials.name as meterial_name',
                'paid_course_materials.test_type as meterial_test_type',
                'users.name as user_name',
                'users.email',
                'users.mobile_number as phone',
            )
            ->when($paid_course_material_id, function ($query) use ($paid_course_material_id){
                return $query->where('result_paid_coures_quizzes.paid_course_material_id', $paid_course_material_id);
            })
            ->leftJoin('paid_course_materials', 'paid_course_materials.id', 'result_paid_coures_quizzes.paid_course_material_id')
            ->leftJoin('users', 'users.id', 'result_paid_coures_quizzes.user_id')
            ->orderby('result_paid_coures_quizzes.id', 'desc')
            ->get();

        foreach ($history as $item) {
            $written_attachment = ResultPaidCourseWrittenAttachment::where("paid_course_quiz_result_id", $item->id)->get()->count();
            if($written_attachment){
                $item->is_written = true;
            }else{
                $item->is_written = false; 
            }
            $item->created_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->created_at)));
            $item->updated_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->updated_at)));
            // $item->created_at = $this->getUTCTime($item->created_at);
        }

        $all_new_list = $history->groupby('user_id');

        $filter_list = [];
        foreach ($all_new_list as $item) {
            array_push($filter_list, $item[0]);
        }

        foreach ($filter_list as $item) {
            $written = PaidCourseWrittenAttachment::where('paid_course_material_id', $item->paid_course_material_id)->first();
            if(!empty($written)){
                $item->written_exam_mark = $written->total_marks;
                $item->written_no_of_questions = $written->no_of_questions;
            }else{
                $item->written_exam_mark = 0;
                $item->written_no_of_questions = 0;
            }
        }
        
        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $filter_list;
        return FacadeResponse::json($response);
    }

    public function getPaidCourseQuotaList(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;
        $mobile = $request->mobile ? $request->mobile : 0;
        $user_id = 0;

        if($mobile){
            $user_details = User::where('mobile_number', $mobile)->first();
            if(!empty($user_details))
            {
                $user_id = $user_details->id;
            }
        }
        
        $history = PaidCourseParticipantQuizAccess::select(
                "paid_course_participant_quiz_accesses.*",
                'paid_course_materials.name as meterial_name',
                'paid_course_materials.test_type as meterial_test_type',
                'users.name as user_name',
                'users.email',
                'users.mobile_number as phone',
            )
            ->when($paid_course_material_id, function ($query) use ($paid_course_material_id){
                return $query->where('paid_course_participant_quiz_accesses.paid_course_material_id', $paid_course_material_id);
            })
            ->when($user_id, function ($query) use ($user_id){
                return $query->where('paid_course_participant_quiz_accesses.user_id', $user_id);
            })
            ->leftJoin('paid_course_materials', 'paid_course_materials.id', 'paid_course_participant_quiz_accesses.paid_course_material_id')
            ->leftJoin('users', 'users.id', 'paid_course_participant_quiz_accesses.user_id')
            ->orderby('paid_course_participant_quiz_accesses.id', 'desc')
            ->get();
        
        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $history;
        return FacadeResponse::json($response);
    }

    public function updateQuota(Request $request)
    {
        $response = new ResponseObject;

        $access_id = $request->id ? $request->id : 0;

        $update = PaidCourseParticipantQuizAccess::where('id', $access_id)->update([
            'access_count' => $request->new_access_count
        ]);
        
        $response->status = $response::status_ok;
        $response->messages = "Quota Updated Successful";
        $response->result = $update;
        return FacadeResponse::json($response);
    }

    public function getPaidCourseMeritList(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        $history = ResultPaidCouresQuiz::select(
            "result_paid_coures_quizzes.*",
            'paid_course_materials.name as meterial_name',
            'paid_course_materials.test_type as meterial_test_type',
            'users.name as user_name',
            'users.email',
            'users.mobile_number as phone',
            'users.image as profile_image',
        )
        ->when($paid_course_material_id, function ($query) use ($paid_course_material_id){
            return $query->where('result_paid_coures_quizzes.paid_course_material_id', $paid_course_material_id);
        })
        ->leftJoin('paid_course_materials', 'paid_course_materials.id', 'result_paid_coures_quizzes.paid_course_material_id')
        ->leftJoin('users', 'users.id', 'result_paid_coures_quizzes.user_id')
        ->orderBy('result_paid_coures_quizzes.id', 'desc')
        ->get();

        $all_new_list = $history->groupby('user_id');

        $filter_list = [];
        foreach ($all_new_list as $item) {
            array_push($filter_list, $item[0]);
        }

        $marit_list = [];

        foreach ($filter_list as $item) {
            $written = PaidCourseWrittenAttachment::where('paid_course_material_id', $item->paid_course_material_id)->first();
            if(!empty($written)){
                $item->written_exam_mark = $written->total_marks;
                $item->written_no_of_questions = $written->no_of_questions;
            }else{
                $item->written_exam_mark = 0;
                $item->written_no_of_questions = 0;
            }
            $item->total_achieved_mark = $item->mark + $item->written_marks;

            array_push($marit_list, ['id' => $item->id, 'user_name' => $item->user_name, 'email' => $item->email, 'phone' => $item->phone, 'total_achieved_mark' => $item->mark + $item->written_marks, 'profile_image' => $item->profile_image, 'created_at' => $item->created_at]);
        }

        usort($marit_list,function($first,$second){
            return $first['total_achieved_mark'] < $second['total_achieved_mark'];
        });

        $final_list = [];
        $ordinal = 1;
        foreach ($marit_list as $item) {
            $number = $this->ordinal($ordinal);
            array_push($final_list, [
                'id' => $item['id'],
                'user_name' => $item['user_name'], 
                'email' => $item['email'], 
                'phone' => $item['phone'], 
                'total_achieved_mark' => $item['total_achieved_mark'],
                'position' => $number,
                'profile_image' => $item['profile_image'],
                'created_at' => $item['created_at']
            ]);
            $ordinal++;
        }
        
        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $final_list;
        return FacadeResponse::json($response);
    }

    public function getPaidCourseMeritListForMobile(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        $history = ResultPaidCouresQuiz::select(
            "result_paid_coures_quizzes.*",
            'paid_course_materials.name as meterial_name',
            'paid_course_materials.test_type as meterial_test_type',
            'users.name as user_name',
            'users.email',
            'users.mobile_number as phone',
            'users.image as profile_image',
        )
        ->when($paid_course_material_id, function ($query) use ($paid_course_material_id){
            return $query->where('result_paid_coures_quizzes.paid_course_material_id', $paid_course_material_id);
        })
        ->leftJoin('paid_course_materials', 'paid_course_materials.id', 'result_paid_coures_quizzes.paid_course_material_id')
        ->leftJoin('users', 'users.id', 'result_paid_coures_quizzes.user_id')
        ->orderBy('result_paid_coures_quizzes.id', 'desc')
        ->get();

        $all_new_list = $history->groupby('user_id');

        $filter_list = [];
        foreach ($all_new_list as $item) {
            array_push($filter_list, $item[0]);
        }

        $marit_list = [];

        foreach ($filter_list as $item) {
            $written = PaidCourseWrittenAttachment::where('paid_course_material_id', $item->paid_course_material_id)->first();
            if(!empty($written)){
                $item->written_exam_mark = $written->total_marks;
                $item->written_no_of_questions = $written->no_of_questions;
            }else{
                $item->written_exam_mark = 0;
                $item->written_no_of_questions = 0;
            }
            $item->total_achieved_mark = $item->mark + $item->written_marks;

            array_push($marit_list, ['id' => $item->id, 'user_name' => $item->user_name, 'email' => $item->email, 'phone' => $item->phone, 'total_achieved_mark' => $item->mark + $item->written_marks, 'profile_image' => $item->profile_image, 'created_at' => $item->created_at]);
        }

        usort($marit_list,function($first,$second){
            return $first['total_achieved_mark'] < $second['total_achieved_mark'];
        });

        $final_list = [];
        $ordinal = 1;
        foreach ($marit_list as $item) {
            $number = $this->ordinal($ordinal);
            array_push($final_list, [
                'id' => $item['id'],
                'user_name' => $item['user_name'], 
                'email' => $item['email'], 
                'phone' => $item['phone'], 
                'total_achieved_mark' => $item['total_achieved_mark'],
                'position' => $number,
                'profile_image' => $item['profile_image'],
                'created_at' => $item['created_at']
            ]);
            $ordinal++;
        }

        return FacadeResponse::json($final_list);
    }

    public function  ordinal($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13))
            return $number. 'th';
        else
            return $number. $ends[$number % 10];
    }

    public function getPaidCourseResultByUserId(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;
        $user_id = $request->user_id ? $request->user_id : 0;

        $history = ResultPaidCouresQuiz::select(
                "result_paid_coures_quizzes.*",
                'paid_course_materials.name as meterial_name',
                'paid_course_materials.test_type as meterial_test_type',
                'users.name as user_name',
                'users.email',
                'users.mobile_number as phone',
            )
        ->when($paid_course_material_id, function ($query) use ($paid_course_material_id){
            return $query->where('result_paid_coures_quizzes.paid_course_material_id', $paid_course_material_id);
        })
        ->when($user_id, function ($query) use ($user_id){
            return $query->where('result_paid_coures_quizzes.user_id', $user_id);
        })
        ->leftJoin('paid_course_materials', 'paid_course_materials.id', 'result_paid_coures_quizzes.paid_course_material_id')
        ->leftJoin('users', 'users.id', 'result_paid_coures_quizzes.user_id')
        ->get();

        foreach ($history as $item) {
            $written = PaidCourseWrittenAttachment::where('paid_course_material_id', $item->paid_course_material_id)->first();
            if(!empty($written)){
                $item->written_exam_mark = $written->total_marks;
                $item->written_no_of_questions = $written->no_of_questions;
            }else{
                $item->written_exam_mark = 0;
                $item->written_no_of_questions = 0;
            }
        }
        
        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $history;
        return FacadeResponse::json($response);
    }

    public function getPaidCourseTestResult(Request $request)
    {
        $response = new ResponseObject;
        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        if(!$paid_course_material_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Enter ID";
            return FacadeResponse::json($response);
        }

        $history = ResultPaidCouresQuiz::select(
            "result_paid_coures_quizzes.*",
            'paid_course_materials.name as meterial_name',
            'paid_course_materials.test_type as meterial_test_type',
            'users.name as user_name',
            'users.email',
            'users.mobile_number as phone',
        )
        ->when($paid_course_material_id, function ($query) use ($paid_course_material_id){
            return $query->where('result_paid_coures_quizzes.paid_course_material_id', $paid_course_material_id);
        })
        ->leftJoin('paid_course_materials', 'paid_course_materials.id', 'result_paid_coures_quizzes.paid_course_material_id')
        ->leftJoin('users', 'users.id', 'result_paid_coures_quizzes.user_id')
        ->get();

        $data_array = array(
            ["Name", "Phone", "Email", "Test Name", "Test Type", "Exam Marks", "Total Mark Achieved (MCQ)", "Total Mark Achieved (Written)", "Date"],
            ["", "", "", "", "", "", "", "", "", "", "", "", ""]
        );

        foreach ($history as $item) {

            $data_array[] = array(
                $item->user_name,
                $item->phone,
                $item->email,
                $item->meterial_name,
                $item->meterial_test_type,
                $item->total_mark,
                $item->mark,
                $item->written_marks,
                $item->created_at,
            );
        }

        $export = new ExportPaidCourseTest($data_array);
        $time = date("Y-m-d h:i:s");
        return Excel::download($export, 'Paid Course Test Result - ' . $time . ' .xlsx');
    }

    public function downloadPaidCoursePaymentHistory(Request $request)
    {
        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        $payments = UserAllPayment::select(
            "user_all_payments.*",
            'paid_courses.name as course_name',
            'paid_courses.name_bn as course_name_bn',
            'paid_courses.sales_amount',
            'paid_courses.thumbnail',
            'paid_course_coupons.coupon_code',
            'paid_course_coupons.coupon_value',
        )
        ->when($paid_course_material_id, function ($query) use ($paid_course_material_id){
            return $query->where('user_all_payments.item_id', $paid_course_material_id);
        })
        ->where('user_all_payments.item_type', "Paid Course")
        ->where('user_all_payments.transaction_status', "Complete")
        ->leftJoin('paid_courses', 'paid_courses.id', 'user_all_payments.item_id')
        ->leftJoin('paid_course_coupons', 'paid_course_coupons.id', 'user_all_payments.coupon_id')
        ->orderby('user_all_payments.id', 'desc')
        ->get();

        $data_array = array(
            [
                "Name", 
                "Phone", 
                "Email", 
                "Course Name", 
                "Course Price", 
                "Paid", 
                "Discount", 
                "Is Coupon Applied", 
                "Coupon Code", 
                "Coupon Amount", 
                "Payment Type", 
                "Transaction ID", 
                "Transaction Status", 
                "Status", 
                "Date"
            ],
            ["", "", "", "", "", "", "", "", "", "", "", "", "", "", ""]
        );

        foreach ($payments as $item) {

            $is_coupon_applied = $item->coupon_id ? "Yes" : "No";

            $paid_amount = $item->paid_amount ? $item->paid_amount : 0;
            $discount = $item->discount ? $item->discount : 0;
            
            $data_array[] = array(
                $item->name,
                $item->phone,
                $item->email,
                $item->course_name,
                $item->payable_amount,
                $paid_amount,
                $discount,
                $is_coupon_applied,
                $item->coupon_code,
                $item->coupon_value,
                $item->card_type,
                $item->transaction_id,
                $item->transaction_status,
                $item->payment_status,
                $item->created_at,
            );
        }

        $export = new ExportPaidCourseTest($data_array);
        $time = date("Y-m-d h:i:s");
        return Excel::download($export, 'Paid Course - Payment Report - ' . $time . ' .xlsx');
    }

    public function downloadPaidCoursePurchaseHistory(Request $request)
    {
        $paid_course_material_id = $request->paid_course_material_id ? $request->paid_course_material_id : 0;

        $payments = UserAllPayment::select(
            "user_all_payments.*",
            'paid_courses.name as course_name',
            'paid_courses.name_bn as course_name_bn',
            'paid_courses.sales_amount',
            'paid_courses.thumbnail',
            'paid_course_coupons.coupon_code',
            'paid_course_coupons.coupon_value',
        )
        ->when($paid_course_material_id, function ($query) use ($paid_course_material_id){
            return $query->where('user_all_payments.item_id', $paid_course_material_id);
        })
        ->where('user_all_payments.item_type', "Paid Course")
        ->where('user_all_payments.transaction_status', "Complete")
        ->leftJoin('paid_courses', 'paid_courses.id', 'user_all_payments.item_id')
        ->leftJoin('paid_course_coupons', 'paid_course_coupons.id', 'user_all_payments.coupon_id')
        ->orderby('user_all_payments.id', 'desc')
        ->get();

        $data_array = array(
            [
                "Name", 
                "Phone", 
                "Email", 
                "Course Name", 
                "Status", 
                "Date"
            ],
            ["", "", "", "", "", ""]
        );

        foreach ($payments as $item) {

            $is_coupon_applied = $item->coupon_id ? "Yes" : "No";

            $paid_amount = $item->paid_amount ? $item->paid_amount : 0;
            $discount = $item->discount ? $item->discount : 0;
            
            $data_array[] = array(
                $item->name,
                $item->phone,
                $item->email,
                $item->course_name,
                $item->status,
                $item->created_at,
            );
        }

        $export = new ExportPaidCourseTest($data_array);
        $time = date("Y-m-d h:i:s");
        return Excel::download($export, 'Paid Course - Purchase Report ' . $time . ' .xlsx');
    }

    public function getPaidCourseCouponUsesReport(Request $request)
    {
        $response = new ResponseObject;

        $coupons = PaidCourseCoupon::select('paid_course_coupons.*', 'paid_courses.name as course_name')
            ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_coupons.paid_course_id')
            ->get();

        $data_array = array(
            [
                "Coupon", 
                "Amount", 
                "Course Name", 
                "Total No. of Uses", 
                "From Website", 
                "From Mobile",
                "Total Amount",
            ],
            ["", "", "", "", "", "", ""]
        );

        foreach ($coupons as $item) {
            $history = PaidCourseApplyCoupon::where('coupon_id', $item->id)
            ->where('paid_course_apply_coupons.applied_status', "successful")
            ->get();

            if(!empty($history)){
                $total_uses = $history->count();
                $total_amount = $total_uses * $item->coupon_value;
    
                $total_uses_from_web = $history->where('applied_from', 'website')->count();
                $total_uses_from_mobile = $history->where('applied_from', 'mobile')->count();
            }else{
                $total_uses = 0;
                $total_amount = 0;
    
                $total_uses_from_web = 0;
                $total_uses_from_mobile = 0;
            }

            $data_array[] = array(
                $item->coupon_code,
                $item->coupon_value,
                $item->course_name,
                $total_uses,
                $total_uses_from_web,
                $total_uses_from_mobile,
                $total_amount,
            );
        }

        $export = new ExportPaidCourseTest($data_array);
        $time = date("Y-m-d h:i:s");
        return Excel::download($export, 'Paid Course - Coupon Uses Report ' . $time . ' .xlsx');
    }

    public function downloadCouponList(Request $request)
    {
        $response = new ResponseObject;

        $couponList = PaidCourseCoupon::select('paid_course_coupons.*', 'admins.name as created_by_name', 'admins.email', 'admins.role', "paid_courses.name as course_name")
        ->leftJoin('admins', 'admins.id', 'paid_course_coupons.created_by')
        ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_coupons.paid_course_id')
        ->orderby('paid_course_coupons.id', 'desc')
        ->get();

        $data_array = array(
            [
                "Coupon", 
                "Amount", 
                "Course Name", 
                "Limit", 
                "Expire Date", 
                "Created By",
                "Status",
            ],
            ["", "", "", "", "", "", ""]
        );

        foreach ($couponList as $item) {
            $status = $item->is_active ? "Active" : "Inactive";
            $data_array[] = array(
                $item->coupon_code,
                $item->coupon_value,
                $item->course_name,
                $item->limit,
                $item->expiry_date,
                $item->created_by_name . ' (' . $item->role . ')',
                $status
            );
        }

        $export = new ExportPaidCourseTest($data_array);
        $time = date("Y-m-d h:i:s");
        return Excel::download($export, 'Paid Course - Coupon List ' . $time . ' .xlsx');
    }

    public function getPaidCourseWrittenResultDetails(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_result_id = $request->paid_course_result_id ? $request->paid_course_result_id : 0;

        $written_attachment = ResultPaidCourseWrittenAttachment::where("paid_course_quiz_result_id", $paid_course_result_id)->get();
        $written_marks = ResultPaidCourseWrittenMark::where("paid_course_quiz_result_id", $paid_course_result_id)->get();

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = ["written_attachment" => $written_attachment, "written_marks" => $written_marks];
        return FacadeResponse::json($response);
    }

    public function getPaidCourseWrittenMarksUpdate(Request $request)
    {
        $response = new ResponseObject;

        $paid_course_written_question_answer_id = $request->id ? $request->id : 0;

        $written_question = ResultPaidCourseWrittenMark::where("id", $paid_course_written_question_answer_id)->first();

        if (empty($written_question)) {
            $response->status = $response::status_fail;
            $response->messages = "Question Found";
            $response->result = [];
            return FacadeResponse::json($response);
        }

        $paid_course_material_id = $written_question->paid_course_material_id ? $written_question->paid_course_material_id : 0;
        $marks_details = PaidCourseWrittenAttachment::where('paid_course_material_id', $paid_course_material_id)->first();
        $config_total_marks = $marks_details->total_marks ? $marks_details->total_marks : 0;

        $given_markes = ResultPaidCourseWrittenMark::where('paid_course_quiz_result_id', $written_question->paid_course_quiz_result_id)->where('id', '!=', $paid_course_written_question_answer_id)->get()->sum('mark');

        $total_given_marks = $given_markes + $request->mark;

        if($total_given_marks > $config_total_marks){
            $response->status = $response::status_fail;
            $response->messages = "Total marks is exceeded.";
            $response->result = [];
            return FacadeResponse::json($response);
        }

        if($written_question->mark){
            $main_quiz_details = ResultPaidCouresQuiz::where('id', $request->paid_course_quiz_result_id)->first();

            ResultPaidCouresQuiz::where('id', $request->paid_course_quiz_result_id)->update([
                'written_marks' => $main_quiz_details->written_marks - $request->mark
            ]);
        }

        ResultPaidCourseWrittenMark::where("id", $paid_course_written_question_answer_id)->update([
            "mark" => $request->mark
        ]);

        $total_achieved_markes = ResultPaidCourseWrittenMark::where('paid_course_quiz_result_id', $written_question->paid_course_quiz_result_id)->get()->sum('mark');
        ResultPaidCouresQuiz::where('id', $request->paid_course_quiz_result_id)->update([
            'written_marks' => $total_achieved_markes
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Marks updated successful";
        $response->result = [];
        return FacadeResponse::json($response);
    }

    public function getUTCTime($date) {
        return new Carbon($date, 'UTC');
    }

}

  