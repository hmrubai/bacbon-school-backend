<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Throwable;
use Exception;

use App\CrashCourse;
use App\PaidCourse;
use App\PaidCourseCoupon;
use App\CrashCourseDescriptionTitle;
use App\CrashCourseDescriptionDetail;
use App\CrashCourseFeature;
use App\CrashCourseTrailFeature;
use App\CrashCourseSubject;
use App\CrashCourseMaterial;
use App\CrashCourseQuizQuestion;
use App\CrashCourseQuizParticipationCount;
use App\CrashCourseParticipant;
use App\CrashCourseParticipantQuizAccess;
use App\UserCrashCoursePayment;
use App\UserAllPayment;
use App\UserAllPaymentDetails;
use App\eBook;
use App\LectureSheet;
use App\LectureVideoParticipant;

use Validator;
use App\User;

use Carbon\Carbon;

use Illuminate\Http\Request;

class CrashCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getAllCrashCourse()
    {
        return CrashCourse::get();
    }
    public function getAllCrashCourseSubject()
    {
        return CrashCourseSubject::orderBy('id', 'DESC')->with('crash_course')->get();
    }
    public function getCrashCourseListAdmin()
    {
        return CrashCourse::where('is_active', true)->get();
    }

    public function getSubjectListByCourse($courseId)
    {
        return CrashCourseSubject::where('crash_course_id', $courseId)->orderBy('sort', 'ASC')->get();
    }

    public function getLatestCrashCourseListWeb()
    {
          return CrashCourse::where('is_active', true)->with('crash_course_feature')->orderBy('id', 'DESC')->get();
    }

    public function getSubjectMaterials($subjectId)
    {
        $response = new ResponseObject;

        $material_video = CrashCourseMaterial::where('crash_course_subject_id', $subjectId)->where('type', 'video')->orderBy('sort', 'ASC')->get();
        $material_script = CrashCourseMaterial::where('crash_course_subject_id', $subjectId)->where('type', 'script')->orderBy('sort', 'ASC')->get();
        $material_quiz = CrashCourseMaterial::where('crash_course_subject_id', $subjectId)->where('type', 'quiz')->orderBy('sort', 'ASC')->get();

        $obj = (object)[
            "videos" => $material_video,
            "scripts" => $material_script,
            "quizzes" => $material_quiz
        ];

        $response->status = $response::status_ok;
        $response->messages = "";
        $response->result = $obj;
        return FacadeResponse::json($response);
    }

    public function getCrashCourseListWeb(Request $request)
    {
        // $response = new ResponseObject;
        $userId = $request->userId;
        $crash_courses = CrashCourse::where('crash_courses.is_active', true)->orderBy('crash_courses.sort', 'asc')->get();
          return FacadeResponse::json($crash_courses);
    }

    public function getCrashCourseListV2(Request $request)
    {
        // $response = new ResponseObject;
        $userId = $request->userId;
        $crash_courses = CrashCourse::where('crash_courses.is_active', true)
            ->leftJoin('crash_course_participants', function ($join) use ($userId) {
                $join->on('crash_course_participants.crash_course_id', '=', 'crash_courses.id');
                $join->where('crash_course_participants.user_id', '=', $userId);
                $join->where('crash_course_participants.is_active', true);
            })->select(
                "crash_courses.id",
                "crash_courses.name",
                "crash_courses.name_bn",
                "crash_courses.description",
                "crash_courses.description_features",
                "crash_courses.youtube_url",
                "crash_courses.thumbnail",
                "crash_courses.crash_course_icon",
                "crash_courses.coupon_code",
                "crash_courses.package_details",
                "crash_courses.regular_amount",
                "crash_courses.sales_amount",
                "crash_courses.discount_percentage",
                "crash_courses.is_active",
                "crash_courses.is_only_test",
                "crash_courses.has_trail",
                "crash_courses.trail_day",
                "crash_courses.appeared_from",
                "crash_courses.appeared_to",
                "crash_courses.sort",
                "crash_courses.number_of_students_enrolled",
                "crash_courses.number_of_videos",
                "crash_courses.number_of_scripts",
                "crash_courses.number_of_quizzes",
                "crash_courses.number_of_model_tests",
                "crash_courses.promo_status",
                "crash_course_participants.user_id",
                "crash_course_participants.is_fully_paid",
                "crash_course_participants.is_trial_taken",
                "crash_course_participants.trial_expiry_date"
            )->with('crash_course_feature','crash_course_trail_feature','crash_course_description_title.crash_course_description_detial')
            ->orderBy('crash_courses.sort', 'asc')->get();

        foreach ($crash_courses as $course) {

            $check_payment = UserAllPayment::where('user_id', $userId)->where('item_id', $course->id)
            ->where('item_type','=','Crash Course')
            ->where('transaction_status','=','Complete')->first();

            $material = CrashCourseSubject::join('crash_course_materials', 'crash_course_subjects.id', 'crash_course_materials.crash_course_subject_id')->where('crash_course_subjects.crash_course_id', $course->id)->get();

            $date = Date('Y-m-d H:i:s');

            $course->is_fully_paid = $course->is_fully_paid ? true : false;
            $course->is_trial_taken = $course->is_trial_taken ? true : false;
            $course->is_trial_expired = $course->is_trial_taken ? ($course->trial_expiry_date < $date ?  true : false) : null;

            // if($course->is_trial_expired != null){

               if (!$course->is_trial_expired){
                $current= Carbon::now()->toDateString();
                $formatted_dt1=Carbon::parse($current);
                $formatted_dt2=Carbon::parse($course->trial_expiry_date);
                $date_diff=$formatted_dt1->diffInDays($formatted_dt2);
                $course->trial_days_left = $date_diff;
               }else {
                $course->trial_days_left = 0;
               }

            // $current= Carbon::now()->toDateString();
            // $formatted_dt1=Carbon::createFromFormat('Y-m-d H:s:i',$course->trial_expiry_date );
            // $formatted_dt2=Carbon::createFromFormat('Y-m-d H:s:i', $current);
            // $date_diff= $formatted_dt1->diffInDays($formatted_dt2);

            // // $days = $interval->format('%a');

            // } else {
            //     $course->trial_days_left = 0;
            // }

            $course->payment_status = empty($check_payment) ? null : $check_payment->status;

            $crash_course_subjects = CrashCourseSubject::where('crash_course_id', $course->id)->with('crash_course_material')->get();


            foreach ($crash_course_subjects as $course_subject) {

                foreach ($course_subject->crash_course_material as $material) {
                    $material->script_url = $material->script_url ? 'http://api.bacbonschool.com/uploads/Lectures/' . $material->script_url : null;
                    if ($material->type == 'quiz') {
                        $material->quiz_question_url = "api/get-crash-course-quiz-questions-by-id/" . $material->id;
                    } else {
                        $material->quiz_question_url = null;
                    }
                    if ($course->is_fully_paid || ($course->is_trial_taken && $course->is_trial_expired != true)) {
                        $material->is_accessible = true;
                    }
                }
            }

            if ($course->is_only_test) {
                $course->crash_course_material  =  $crash_course_subjects[0]->crash_course_material;
            } else {
                $course->crash_course_subjects = $crash_course_subjects;
            }

        }
        return FacadeResponse::json($crash_courses);
    }

    public function getCrashCourseList(Request $request)
    {
        // $response = new ResponseObject;
        $userId = $request->userId;
        $crash_courses = CrashCourse::where('crash_courses.is_active', true)
            ->leftJoin('crash_course_participants', function ($join) use ($userId) {
                $join->on('crash_course_participants.crash_course_id', '=', 'crash_courses.id');
                $join->where('crash_course_participants.user_id', '=', $userId);
                $join->where('crash_course_participants.is_active', true);
            })->select(
                "crash_courses.id",
                "crash_courses.name",
                "crash_courses.name_bn",
                "crash_courses.description",
                "crash_courses.description_features",
                "crash_courses.youtube_url",
                "crash_courses.thumbnail",
                "crash_courses.crash_course_icon",
                "crash_courses.coupon_code",
                "crash_courses.package_details",
                "crash_courses.regular_amount",
                "crash_courses.sales_amount",
                "crash_courses.discount_percentage",
                "crash_courses.is_active",
                "crash_courses.is_only_test",
                "crash_courses.has_trail",
                "crash_courses.trail_day",
                "crash_courses.appeared_from",
                "crash_courses.appeared_to",
                "crash_courses.sort",
                "crash_courses.number_of_students_enrolled",
                "crash_courses.number_of_videos",
                "crash_courses.number_of_scripts",
                "crash_courses.number_of_quizzes",
                "crash_courses.number_of_model_tests",
                "crash_courses.promo_status",
                "crash_course_participants.user_id",
                "crash_course_participants.is_fully_paid",
                "crash_course_participants.is_trial_taken",
                "crash_course_participants.trial_expiry_date"
            )->with('crash_course_feature','crash_course_trail_feature','crash_course_description_title.crash_course_description_detial')
            ->orderBy('crash_courses.sort', 'asc')->get();

        foreach ($crash_courses as $course) {

            $check_payment = UserCrashCoursePayment::where('user_id', $userId)
            ->where('type','=','Crash Course')
            ->where('course_id', $course->id)->first();

            $material = CrashCourseSubject::join('crash_course_materials', 'crash_course_subjects.id', 'crash_course_materials.crash_course_subject_id')->where('crash_course_subjects.crash_course_id', $course->id)->get();

            $date = Date('Y-m-d H:i:s');

            $course->is_fully_paid = $course->is_fully_paid ? true : false;
            $course->is_trial_taken = $course->is_trial_taken ? true : false;
            $course->is_trial_expired = $course->is_trial_taken ? ($course->trial_expiry_date < $date ?  true : false) : null;

            $course->payment_status = empty($check_payment) ? null : $check_payment->status;

            $crash_course_subjects = CrashCourseSubject::where('crash_course_id', $course->id)->with('crash_course_material')->get();


            foreach ($crash_course_subjects as $course_subject) {

                foreach ($course_subject->crash_course_material as $material) {
                    $material->script_url = $material->script_url ? 'http://api.bacbonschool.com/uploads/Lectures/' . $material->script_url : null;
                    if ($material->type == 'quiz') {
                        $material->quiz_question_url = "api/get-crash-course-quiz-questions-by-id/" . $material->id;
                    } else {
                        $material->quiz_question_url = null;
                    }
                    if ($course->is_fully_paid || $course->is_trial_taken) {
                        $material->is_accessible = true;
                    }
                }
            }

            if ($course->is_only_test) {
                $course->crash_course_material  =  $crash_course_subjects[0]->crash_course_material;
            } else {
                $course->crash_course_subjects = $crash_course_subjects;
            }

        }
        return FacadeResponse::json($crash_courses);
    }

    public function getCrashCourseDetailsV2(Request $request)
    {
        $crashCourseId = $request->crashCourseId;
        $userId = $request->userId;


        $user_course = CrashCourseParticipant::where('crash_course_participants.crash_course_id', $crashCourseId)
            ->where('crash_course_participants.user_id', $userId)->where('crash_course_participants.is_active', true)->first();

        $check_payment = UserAllPayment::where('user_id', $userId)->where('item_id', $crashCourseId)
        ->where('item_type','=','Crash Course')
        ->where('transaction_status','=','Complete')->first();

        if (empty($user_course)) {
            $course =  CrashCourse::where('id', $crashCourseId)->first();
        } else {
            $course = CrashCourseParticipant::where('crash_course_participants.crash_course_id', $crashCourseId)
                ->where('crash_course_participants.user_id', $userId)
                ->join('crash_courses', 'crash_course_participants.crash_course_id', 'crash_courses.id')->first();
        }
        $course->crash_course_feature = CrashCourseFeature::where('crash_course_id',$crashCourseId)->get();
        $course->crash_course_trail_feature = CrashCourseTrailFeature::where('crash_course_id',$crashCourseId)->get();
        $course->crash_course_description_title = CrashCourseDescriptionTitle::where('crash_course_id',$crashCourseId)->with('crash_course_description_detial')->get();


        $material =  CrashCourseSubject::join('crash_course_materials', 'crash_course_subjects.id', 'crash_course_materials.crash_course_subject_id')->where('crash_course_subjects.crash_course_id', $course->id)->get();
        // $course->number_of_students_enrolled = 15 +  CrashCourseParticipant::where('crash_course_id', $course->id)->count();
        // $course->number_of_videos = $material->where('type', 'video')->count();
        // $course->number_of_scripts = $material->where('type', 'script')->count();
        // $course->number_of_quizzes = $material->where('type', 'quiz')->count();

        $date = Date('Y-m-d H:i:s');
        $course->is_active = $course->is_active ? true : false;
        $course->is_only_test = $course->is_only_test ? true : false;
        $course->has_trail = $course->has_trail ? true : false;
        $course->is_fully_paid = $course->is_fully_paid ? true : false;
        $course->is_trial_taken = $course->is_trial_taken ? true : false;
        $course->is_trial_expired = $course->is_trial_taken ? ($course->trial_expiry_date < $date ?  true : false) : null;

            if (!$course->is_trial_expired){
            $current= Carbon::now()->toDateString();
            $formatted_dt1=Carbon::parse($current);
            $formatted_dt2=Carbon::parse($course->trial_expiry_date);
            $date_diff=$formatted_dt1->diffInDays($formatted_dt2);
            $course->trial_days_left = $date_diff;
           }else {
            $course->trial_days_left = 0;
           }

        // if($course->is_trial_expired){
        // $current= Carbon::now()->toDateString();


        // $formatted_dt1=Carbon::parse($current)->format('Y-m-d');
        // $formatted_dt2=Carbon::parse($course->trial_expiry_date)->format('Y-m-d');

        // $date_diff=$formatted_dt1->diffInDays($formatted_dt2);




        // // $days = $interval->format('%a');
        // $course->trial_days_left = $date_diff;
        // } else {
        //     $course->trial_days_left = 0;
        // }


        $course->payment_status = empty($check_payment) ? null : $check_payment->status;

        $crash_course_subjects = CrashCourseSubject::where('crash_course_id', $course->id)->with('crash_course_material')->get();


        foreach ($crash_course_subjects as $course_subject) {

           // $subject_material =  CrashCourseMaterial::where('crash_course_subject_id', $course_subject->id)->get();

            // $course_subject->number_of_videos = $subject_material->where('type', 'video')->count();
            // $course_subject->number_of_scripts = $subject_material->where('type', 'script')->count();
            // $course_subject->number_of_quizzes = $subject_material->where('type', 'quiz')->count();

            foreach ($course_subject->crash_course_material as $material) {
                $material->script_url = $material->script_url ? 'http://api.bacbonschool.com/uploads/Lectures/' . $material->script_url : null;
                if ($material->type == 'quiz') {
                    $material->quiz_question_url = "api/get-crash-course-quiz-questions-by-id/" . $material->id;
                } else {
                    $material->quiz_question_url = null;
                }
                if ($course->is_fully_paid || ($course->is_trial_taken && $course->is_trial_expired != true)) {
                    $material->is_accessible = true;
                }
            }
        }

        if ($course->is_only_test) {
            $course->crash_course_material  =  $crash_course_subjects[0]->crash_course_material;
        } else {
            $course->crash_course_subjects = $crash_course_subjects;
        }



        // $obj = (object) [
        //     "notice" => $examList->count() > 0 ? '' : "No model test found",
        //     "records" => $examList
        // ];
        // return FacadeResponse::json($obj);
        return FacadeResponse::json($course);
    }

    public function getCrashCourseDetails(Request $request)
    {
        $crashCourseId = $request->crashCourseId;
        $userId = $request->userId;


        $user_course = CrashCourseParticipant::where('crash_course_participants.crash_course_id', $crashCourseId)
            ->where('crash_course_participants.user_id', $userId)->where('crash_course_participants.is_active', true)->first();

        $check_payment = UserCrashCoursePayment::where('user_id', $userId)->where('course_id', $crashCourseId)->where('type','=','Crash Course')->first();

        if (empty($user_course)) {
            $course =  CrashCourse::where('id', $crashCourseId)->first();
        } else {
            $course = CrashCourseParticipant::where('crash_course_participants.crash_course_id', $crashCourseId)
                ->where('crash_course_participants.user_id', $userId)
                ->join('crash_courses', 'crash_course_participants.crash_course_id', 'crash_courses.id')->first();
        }
        $course->crash_course_feature = CrashCourseFeature::where('crash_course_id',$crashCourseId)->get();
        $course->crash_course_trail_feature = CrashCourseTrailFeature::where('crash_course_id',$crashCourseId)->get();
        $course->crash_course_description_title = CrashCourseDescriptionTitle::where('crash_course_id',$crashCourseId)->with('crash_course_description_detial')->get();


        $material =  CrashCourseSubject::join('crash_course_materials', 'crash_course_subjects.id', 'crash_course_materials.crash_course_subject_id')->where('crash_course_subjects.crash_course_id', $course->id)->get();
        // $course->number_of_students_enrolled = 15 +  CrashCourseParticipant::where('crash_course_id', $course->id)->count();
        // $course->number_of_videos = $material->where('type', 'video')->count();
        // $course->number_of_scripts = $material->where('type', 'script')->count();
        // $course->number_of_quizzes = $material->where('type', 'quiz')->count();

        $date = Date('Y-m-d H:i:s');
        $course->is_active = $course->is_active ? true : false;
        $course->is_only_test = $course->is_only_test ? true : false;
        $course->has_trail = $course->has_trail ? true : false;
        $course->is_fully_paid = $course->is_fully_paid ? true : false;
        $course->is_trial_taken = $course->is_trial_taken ? true : false;
        $course->is_trial_expired = $course->is_trial_taken ? ($course->trial_expiry_date < $date ?  true : false) : null;

        $course->payment_status = empty($check_payment) ? null : $check_payment->status;

        $crash_course_subjects = CrashCourseSubject::where('crash_course_id', $course->id)->with('crash_course_material')->get();


        foreach ($crash_course_subjects as $course_subject) {

           // $subject_material =  CrashCourseMaterial::where('crash_course_subject_id', $course_subject->id)->get();

            // $course_subject->number_of_videos = $subject_material->where('type', 'video')->count();
            // $course_subject->number_of_scripts = $subject_material->where('type', 'script')->count();
            // $course_subject->number_of_quizzes = $subject_material->where('type', 'quiz')->count();

            foreach ($course_subject->crash_course_material as $material) {
                $material->script_url = $material->script_url ? 'http://api.bacbonschool.com/uploads/Lectures/' . $material->script_url : null;
                if ($material->type == 'quiz') {
                    $material->quiz_question_url = "api/get-crash-course-quiz-questions-by-id/" . $material->id;
                } else {
                    $material->quiz_question_url = null;
                }
                if ($course->is_fully_paid || ($course->is_trial_taken && $course->is_trial_expired != true)) {
                    $material->is_accessible = true;
                }
            }
        }

        if ($course->is_only_test) {
            $course->crash_course_material  =  $crash_course_subjects[0]->crash_course_material;
        } else {
            $course->crash_course_subjects = $crash_course_subjects;
        }



        // $obj = (object) [
        //     "notice" => $examList->count() > 0 ? '' : "No model test found",
        //     "records" => $examList
        // ];
        // return FacadeResponse::json($obj);
        return FacadeResponse::json($course);
    }

    public function getCrashCourseQuizDetails($quizId){
        return FacadeResponse::json(CrashCourseMaterial::where('id', $quizId)->first());
    }

    public function copyQuizQuestions(Request $request)
    {
        $response = new ResponseObject;

        $copy_from_id = $request->copy_from_id ? $request->copy_from_id : 0;
        $copy_to_id = $request->copy_to_id ? $request->copy_to_id : 0;

        try {
            DB::beginTransaction();

            $get_questions = CrashCourseQuizQuestion::where('crash_course_material_id', $copy_from_id)->get();

            foreach ($get_questions as $question) {
                CrashCourseQuizQuestion::create([
                    'crash_course_material_id' => $copy_to_id,
                    'question' => $question['question'],
                    'option1' => $question['option1'],
                    'option2' => $question['option2'],
                    'option3' => $question['option3'],
                    'option4' => $question['option4'],
                    'option5' => $question['option5'],
                    'option6' => $question['option6'],
                    'correct_answer' => $question['correct_answer'],
                    'correct_answer2' => $question['correct_answer2'],
                    'correct_answer3' => $question['correct_answer3'],
                    'correct_answer4' => $question['correct_answer4'],
                    'correct_answer5' => $question['correct_answer5'],
                    'correct_answer6' => $question['correct_answer6'],
                    'explanation_text' => $question['explanation_text']
                ]);
            }

            DB::commit();

            $response->status   = $response::status_ok;
            $response->messages = "Question created successful";
            $response->data     = [];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response->status   = $response::status_fail;
            $response->message  = $e->getMessage();
            $response->data = [];
            return response()->json($response);
        }
    }

    public function createQuiz(Request $request)
    {
        $response = new ResponseObject;


        try {
            DB::beginTransaction();

            $quiz = CrashCourseMaterial::create([
                'crash_course_subject_id' => $request->subject_id,
                'type' => 'quiz',
                'name' => $request->quiz_name,
                'name_bn' => $request->quiz_name_bn ? $request->quiz_name_bn : $request->quiz_name,
                'quiz_duration' => $request->duration,
                'quiz_positive_mark' => $request->positive_mark ? $request->positive_mark : 1,
                'quiz_negative_mark' => $request->negative_mark ? $request->negative_mark : 0,
                'quiz_total_mark' => $request->total_mark,
                'quiz_question_number' => $request->question_number,
                'sort' => $request->sort,
                'status' => $request->status,
                'is_active' => true
            ]);

            foreach ($request->questions as $question) {
                CrashCourseQuizQuestion::create([
                    'crash_course_material_id' => $quiz->id,
                    'question' => $question['question'],
                    'option1' => $question['option1'],
                    'option2' => $question['option2'],
                    'option3' => $question['option3'],
                    'option4' => $question['option4'],
                    'option5' => $question['option5'],
                    'option6' => $question['option6'],
                    'correct_answer' => $question['correct_answer'],
                    'correct_answer2' => $question['correct_answer2'],
                    'correct_answer3' => $question['correct_answer3'],
                    'correct_answer4' => $question['correct_answer4'],
                    'correct_answer5' => $question['correct_answer5'],
                    'correct_answer6' => $question['correct_answer6'],
                    'explanation_text' => $question['explanation_text']
                ]);
            }

            DB::commit();

            $response->status          = $response::status_ok;
            $response->messages =       "Quiz test has been created";
            $response->data            = [];
            return response()->json($response);
        } catch (Exception $e) {
            DB::rollback();
            $response->status          = $response::status_fail;
            $response->message         = $e->getMessage();
            $response->data            = [];
            return response()->json($response);
        }
    }

    public function createScript(Request $request)
    {


        $response = new ResponseObject;

        $formData = json_decode($request->data, true);
        // $data = $request->json()->all();

        $request['status'] = "Available";
        $validator = Validator::make($formData, [
            'subject_id' => 'required',
            'title' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            // $response->messages = "Validation failed";
            return FacadeResponse::json($response);
        }
        if ($request->hasFile('file')) {
            $destinationPath = 'uploads/CrashCourse';
            $file = $request->file('file');
            $formData['filename'] = 'CCS_' . time() . '.' . $file->getClientOriginalExtension();
            $formData['status'] = "Available";
            $file->move($destinationPath, $formData['filename']);

            $script = CrashCourseMaterial::create([
                'crash_course_subject_id' => $formData['subject_id'],
                'type' => 'script',
                'name' => $formData['title'],
                'name_bn' => $formData['title_bn'],
                'status' => $formData['status'],
                'sort' => $formData['sort'],
                'script_url' => $formData['filename']
            ]);


            $response->status = $response::status_ok;
            $response->messages = "Successfully uploaded";
            $response->result = $script;
            return FacadeResponse::json($response);
        } else {
            $response->status = $response::status_fail;
            $response->messages = "Please select script";
            return FacadeResponse::json($response);
        }
    }

    public function createCourse(Request $request)
    {
        $response = new ResponseObject;

        $formData = json_decode($request->data, true);

        if ($request->hasFile('iconThumbnail') && $request->hasFile('thumbnail')) {


            $thumbnail = $request->file('thumbnail');
            $time = time();
            $thumnailName = "CCthumbnail" . $time . '.' . $thumbnail->getClientOriginalExtension();
            $destinationThumbnail = 'uploads/crash_course_thumbnails';
            $thumbnail->move($destinationThumbnail, $thumnailName);

            $iconThumbnail = $request->file('iconThumbnail');
            $time = time();
            $iconThumnailName = "CCIcon" . $time . '.' . $iconThumbnail->getClientOriginalExtension();
            $destinationIconThumbnail = 'uploads/crash_course_icons';
            $iconThumbnail->move($destinationIconThumbnail, $iconThumnailName);

            try {
                DB::beginTransaction();
            $course = (array)[
                "name" => $formData['name'],
                "name_bn" => isset($formData['name_bn']) ? $formData['name_bn'] : null,
                "gp_product_id" => isset($formData['gp_product_id']) ? $formData['gp_product_id'] : null,
                "description" => $formData['description'],
                "youtube_url" => isset($formData['youtube_url']) ? $formData['youtube_url'] : null,
                "thumbnail" => "http://" . $_SERVER['HTTP_HOST'] . '/uploads/crash_course_thumbnails/' . $thumnailName,
                "crash_course_icon" => "http://" . $_SERVER['HTTP_HOST'] . '/uploads/crash_course_icons/' . $iconThumnailName,
                "coupon_code" => isset($formData['coupon_code']) ? $formData['coupon_code'] : null,
                "regular_amount" => $formData['regular_amount'],
                "sales_amount" => $formData['sales_amount'],
                "is_active" => $formData['is_active'],
                "promo_status" => isset($formData['promo_status']) ? $formData['promo_status'] : null,
                "has_trail" => $formData['has_trail'],
                "is_only_test" => $formData['is_only_test'],
                "folder_name" => str_replace(' ', '_', $formData['folder_name']),
                "sort" => $formData['sort']
            ];
            $courseObj = CrashCourse::create($course);

            foreach ($formData['features'] as $feature) {
                CrashCourseFeature::create([
                    "crash_course_id" =>$courseObj->id,
                    "name" => $feature['name']
                ]);

            }

            foreach ($formData['desTitles'] as $desTitle) {

               $title =  CrashCourseDescriptionTitle::create([
                    "crash_course_id" =>$courseObj->id,
                    "name" => $desTitle['title']
                ]);

                foreach ($desTitle['details'] as $detail) {
                    CrashCourseDescriptionDetail::create([
                        "crash_course_description_title_id" =>$title->id,
                        "name" => $detail
                    ]);
                }

            }

            DB::commit();
            $response->status = $response::status_ok;
            $response->messages = "Crash course has been created";
            $response->result = $courseObj;
            return FacadeResponse::json($response);

        } catch (Exception $e) {
            DB::rollback();
            $response->status          = $response::status_fail;
            $response->message         = $e->getMessage();
            $response->data            = [];
              return FacadeResponse::json($response);
        }

        } else {
            $response->status = $response::status_fail;
            $response->messages = "Video or Thumbnail is missing";
            return FacadeResponse::json($response);
        }
    }

    public function createCourseSubject(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $subject = (array)[
            "name" => $data['name'],
            "crash_course_id" => $data['crash_course_id'],
            "name_bn" => isset($data['name_bn']) ? $data['name_bn'] : null,
            "folder_name" => str_replace(' ', '_', $data['folder_name']),
            "sort" => $data['sort']
        ];
        $res = CrashCourseSubject::create($subject);

        $response->status = $response::status_ok;
        $response->messages = "Crash course Subject has been created";
        $response->result = $res;
        return FacadeResponse::json($response);
    }

    public function createVideo(Request $request)
    {
        $response = new ResponseObject;

        $formData = json_decode($request->data, true);
        $validator = Validator::make($formData, [
            'title' => 'required|max:100',
            'description' => 'max:300',
            'subject_id' => 'required',
            'status' => 'required|string'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }

        if ($request->hasFile('video') && $request->hasFile('thumbnail')) {


           $course_subject = CrashCourseSubject::where('crash_course_subjects.id',$formData['subject_id'])
           ->join('crash_courses','crash_course_subjects.crash_course_id','crash_courses.id')
           ->select('crash_course_subjects.id','crash_course_subjects.folder_name as subject_folder_name', 'crash_courses.folder_name as course_folder_name')
           ->first();

            $video = $request->file('video');
            $thumbnail = $request->file('thumbnail');
            $time = time();
            $videoName = "CCvideo" . $time . '.' . $video->getClientOriginalExtension();
            $thumnailName = "CCthumbnail" . $time . '.' . $thumbnail->getClientOriginalExtension();

            $destinationVideo = 'uploads/crash_course/videos';
            $destinationThumbnail = 'uploads/crash_course/thumbnails';
            $video->move($destinationVideo, $videoName);
            $thumbnail->move($destinationThumbnail, $thumnailName);

            $getID3 = new \getID3;
            // $videoInfo = $getID3->analyze('/home/bacbonschool/api.bacbonschool.com/uploads/lecture_videos/'.$lectureVideoName);
            $videoInfo = $getID3->analyze('uploads/crash_course/videos/' . $videoName);
            $videoSequence = 1;
            $lastSubjectVideo = CrashCourseMaterial::where('crash_course_subject_id', $formData['subject_id'])->orderBy('id', 'desc')->first();
            if ($lastSubjectVideo) {
                $ar = explode('0', $lastSubjectVideo->video_code);
                $videoSequence = end($ar) + 1;
            }
            $code_number = str_pad($videoSequence, 4, "0", STR_PAD_LEFT);


            $arrVideoName = explode(".",$videoName);

            $full_url = 'https://bacbonschool.s3.ap-south-1.amazonaws.com/uploads/CrashCourse/'. $course_subject->course_folder_name .'/'. $course_subject->subject_folder_name.'/videos'. $arrVideoName[0]. '/index.m3u8';

            $material = (array)[

                'crash_course_subject_id' => $formData['subject_id'],
                'type' => 'video',
                'name' => $formData['title'],
                'name_bn' => $formData['title_bn'],
                'sort' => $formData['sort'],
                "description" => $formData["description"],
                "video_url" =>  $videoName,
                "video_full_url" =>  $full_url,
                "thumbnail" => "http://" . $_SERVER['HTTP_HOST'] . '/uploads/thumbnails/' . $thumnailName,
                "status" =>  $formData["status"],
                "video_code" =>  'CC' . $formData['subject_id'] . '0' . $code_number,
                "video_duration" => $videoInfo['playtime_seconds']
            ];
            $material = CrashCourseMaterial::create($material);

            $response->status = $response::status_ok;
            $response->messages = "Crash course video has been created";
            $response->result = $material;
            return FacadeResponse::json($response);
        } else {
            $response->status = $response::status_fail;
            $response->messages = "Video or Thumbnail is missing";
            return FacadeResponse::json($response);
        }
    }

    public function getCrashCourseQuizQuestionsById($materialId)
    {

        $questions = CrashCourseQuizQuestion::where('crash_course_material_id', $materialId)->get();
        $obj = (object) [
            "data" => $questions,
            "exam_id" => (int)$materialId,
            "submission_url" => "api/submit-crash-course-quiz-result",
            "start_url" => "api/start-crash-course-quiz"
        ];
        return FacadeResponse::json($obj);
    }

    public function getCrashCourseDetailsWeb(Request $request)
    {
        $response = new ResponseObject;
        $crashCourseId = $request->crashCourseId;
        $course =  CrashCourse::where('id', $crashCourseId)->first();
        if (empty($course)) {
            $response->status = $response::status_fail;
            $response->messages = "No Course Found";
            $response->result = null;
            return FacadeResponse::json($response);
        }
        $course->crash_course_feature = CrashCourseFeature::where('crash_course_id',$crashCourseId)->get();
        $course->crash_course_description_title = CrashCourseDescriptionTitle::where('crash_course_id',$crashCourseId)->with('crash_course_description_detial')->get();
        $material =  CrashCourseSubject::join('crash_course_materials', 'crash_course_subjects.id', 'crash_course_materials.crash_course_subject_id')->where('crash_course_subjects.crash_course_id', $course->id)->get();
        // $course->number_of_students_enrolled = 15 +  CrashCourseParticipant::where('crash_course_id', $course->id)->count();
        // $course->number_of_videos = $material->where('type', 'video')->count();
        // $course->number_of_scripts = $material->where('type', 'script')->count();
        // $course->number_of_quizzes = $material->where('type', 'quiz')->count();

        $date = Date('Y-m-d H:i:s');
        $course->is_active = $course->is_active ? true : false;
        $course->is_only_test = $course->is_only_test ? true : false;
        $course->has_trail = $course->has_trail ? true : false;
        $course->is_fully_paid = $course->is_fully_paid ? true : false;
        $course->is_trial_taken = $course->is_trial_taken ? true : false;
        $course->is_trial_expired = $course->is_trial_taken ? ($course->trial_expiry_date < $date ?  true : false) : null;

        $crash_course_subjects = CrashCourseSubject::where('crash_course_id', $course->id)->with('crash_course_material')->get();


        foreach ($crash_course_subjects as $course_subject) {

            $subject_material =  CrashCourseMaterial::where('crash_course_subject_id', $course_subject->id)->get();

            // $course_subject->number_of_videos = $subject_material->where('type', 'video')->count();
            // $course_subject->number_of_scripts = $subject_material->where('type', 'script')->count();
            // $course_subject->number_of_quizzes = $subject_material->where('type', 'quiz')->count();

            foreach ($course_subject->crash_course_material as $material) {
                $material->script_url = $material->script_url ? 'http://api.bacbonschool.com/uploads/Lectures/' . $material->script_url : null;
                if ($material->type == 'quiz') {
                    $material->quiz_question_url = "api/get-crash-course-quiz-questions-by-id/" . $material->id;
                } else {
                    $material->quiz_question_url = null;
                }
                if ($course->is_fully_paid || $course->is_trial_taken) {
                    $material->is_accessible = true;
                }
            }
        }

        if ($course->is_only_test) {
            $course->crash_course_material  =  $crash_course_subjects[0]->crash_course_material;
        } else {
            $course->crash_course_subjects = $crash_course_subjects;
        }


        return FacadeResponse::json($course);
    }

    public function saveUserPayment(Request $request)
    {

        $response = new ResponseObject;

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'course_id' => 'required',
            'type' => 'crash_course',
            'paid_amount' => 'required',
            'payment_method' => 'required',
            'transaction_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        $user =  User::where('id', $request->user_id)->first();
        if (empty($user)) {
            $response->status = $response::status_fail;
            $response->messages = "No User Found";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $course =  CrashCourse::where('id', $request->crash_course_id)->first();
        if (empty($course)) {
            $response->status = $response::status_fail;
            $response->messages = "No Course Found";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $check_payment = UserCrashCoursePayment::where('user_id', $request->user_id)->where('course_id', $request->crash_course_id)->where('type','=','Crash Course')->first();

        if (!empty($check_payment)) {
            $response->status = $response::status_fail;
            $response->messages = "Already payment done for this course";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $payment = UserCrashCoursePayment::create([
            'user_id' => $request->user_id,
            'course_id' => $request->crash_course_id,
            'type'=> 'crash_course',
            'course_amount' => $course->sales_amount,
            'paid_amount' => $request->paid_amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'transaction_status' => 'Success',
            'status' => 'Pending'
        ]);


        $response->status = $response::status_ok;
        $response->messages = "Successfully Done";
        $response->result = $payment;
        return FacadeResponse::json($response);
    }

    public function getUserPaymentList(Request $request)
    {

        $list = UserCrashCoursePayment::where('user_crash_course_payments.type','=','Crash Course')
        ->select(
            'users.id as user_id',
             'users.name as user_name',
              'users.mobile_number',
               'crash_courses.id as crash_course_id',
                'crash_courses.name as crash_course_name',
                 'user_crash_course_payments.*')
                 ->join('users', 'user_crash_course_payments.user_id', 'users.id')
                 ->join('crash_courses', 'user_crash_course_payments.course_id', 'crash_courses.id')
            ->when($request->search_item, function ($q) use ($request) {
                return $q->where('users.name', 'like', '%' . $request->search_item . '%')
                    ->orWhere('users.mobile_number', 'like', '%' . $request->search_item . '%')
                    ->orWhere('user_crash_course_payments.transaction_id', 'like', '%' . $request->search_item . '%');
            })->orderBy('user_crash_course_payments.id','Desc')->get();

        return FacadeResponse::json($list);
    }

    public function getStudentAdmin(Request $request)
    {

        $user = User::where('mobile_number','=',$request->search_item)->first();

        return FacadeResponse::json($user);
    }


    public function updateUserPayment(Request $request)
    {
        $response = new ResponseObject;
        $payment = UserCrashCoursePayment::where('id', $request->id)->first();

        if (empty($payment)) {
            $response->status = $response::status_fail;
            $response->messages = "No data found";
            $response->result = null;
            return FacadeResponse::json($response);
        }
        $checkParticipant = CrashCourseParticipant::where('crash_course_id', $request->crash_course_id)->where('user_id', $request->user_id)->first();
        if ($request->status == 'Enrolled') {

            if (empty($checkParticipant)) {
                CrashCourseParticipant::create([
                    'user_id' => $request->user_id,
                    'course_id' => $request->crash_course_id,
                    'type' => 'crash_course',
                    'course_amount' => $request->course_amount,
                    'paid_amount' => $request->paid_amount,
                    'is_fully_paid' => true
                ]);
            } else {
                $checkParticipant->update([
                    'paid_amount' => $request->paid_amount,
                    'is_active' => true,
                    'is_fully_paid' => true
                ]);
            }

               $course =  CrashCourse::find($request->crash_course_id);
               $course->update([
                'number_of_students_enrolled' => $course->number_of_students_enrolled + 1,
               ]);

              $course_subjects = CrashCourseSubject::where('crash_course_id',$request->crash_course_id)->get();
              foreach ($course_subjects as $subject) {
               $materialIds =  CrashCourseMaterial::where('crash_course_subject_id',$subject->id)->where('type','=','quiz')->pluck('id');
                    foreach ($materialIds as $materialId) {
                        $quizAccess = CrashCourseParticipantQuizAccess::where('user_id', $request->user_id)
                        ->where('crash_course_material_id', $materialId)->first();

                        if(empty($quizAccess)){
                            CrashCourseParticipantQuizAccess::create([
                                'user_id' => $request->user_id,
                                'crash_course_material_id' => $materialId,
                                'access_count' => 100
                            ]);
                        }else{
                            $quizAccess->update([
                                'access_count' => 100
                            ]);
                        }
                    }
                }

        }

        $data = $request->json()->all();
        $payment->update($data);

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "Successfully updated";
        $response->result = $payment;
        return FacadeResponse::json($response);
    }

    public function deleteUserPayment(Request $request)
    {
        $response = new ResponseObject;

        $user =  User::where('id', $request->user_id)->first();
        if (empty($user)) {
            $response->status = $response::status_fail;
            $response->messages = "No User Found";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        if (isset($request->crash_course_ids)) {

            UserCrashCoursePayment::whereIn('course_id', $request->crash_course_ids)->where('user_id', $request->user_id)->where('type','=','Crash Course')->delete();
        } else {
            UserCrashCoursePayment::where('user_id', $request->user_id)->where('type','=','Crash Course')->delete();
        }

        $response->status = $response::status_ok;
        $response->messages = "Successfully Deleted";
        $response->result = null;
        return FacadeResponse::json($response);
    }


    public function addUserToTrail(Request $request){
        $response = new ResponseObject;

        $checkParticipant = CrashCourseParticipant::where('crash_course_id', $request->crash_course_id)->where('user_id', $request->user_id)->first();

        if(!empty($checkParticipant)){
            $response->status = $response::status_fail;
            $response->messages = "Already Course taken";
            $response->result = null;
            return FacadeResponse::json($response);
        }


        $course =  CrashCourse::where('id', $request->crash_course_id)->first();
        $trailDay = (int)$course->trail_day;
        $date7ahead = \Carbon\Carbon::now()->addDays($trailDay);
        CrashCourseParticipant::create([
            'user_id' => $request->user_id,
            'crash_course_id' => $course->id,
            'course_amount' => $course->sales_amount,
            'paid_amount' => 0,
            'is_fully_paid' => false,
            'is_trial_taken' => true,
            'is_active' => true,
            'trial_expiry_date' => $date7ahead,
        ]);

        $course_subjects = CrashCourseSubject::where('crash_course_id',$request->crash_course_id)->get();
        foreach ($course_subjects as $subject) {
         $materialIds =  CrashCourseMaterial::where('crash_course_subject_id',$subject->id)->where('type','=','quiz')->pluck('id');
              foreach ($materialIds as $materialId) {

                    $quizAccess = CrashCourseParticipantQuizAccess::where('user_id', $request->user_id)
                    ->where('crash_course_material_id', $materialId)->first();

                    if(empty($quizAccess)){
                        CrashCourseParticipantQuizAccess::create([
                            'user_id' => $request->user_id,
                            'crash_course_material_id' => $materialId,
                            'access_count' => 100
                        ]);
                    }else{
                        $quizAccess->update([
                            'access_count' => 100
                        ]);
                    }

              }
          }


        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "Welcome! You have successfully enrolled with 7 days free trial";
        $response->result = "";
        return FacadeResponse::json($response);
    }

    // public function getUserCourses($userId){
    //     $list =  CrashCourseParticipant::where('crash_course_participants.user_id',$userId)
    //                 ->where('crash_course_participants.is_active',true)
    //                 ->join('crash_courses','crash_course_participants.crash_course_id','crash_courses.id')->get();
    //     return FacadeResponse::json($list);
    // }

    public function getUserPurchaseList($userId){
        $purchaseList =  UserAllPayment::where('user_id',$userId)->where('transaction_status','Complete')->orderBy('id','Desc')->select(
            'id',
            'item_id',
            'item_name',
            'item_type',
            'status',
            'transaction_status'
        )->get();
        foreach ($purchaseList as $item) {
            if($item->item_type == 'Crash Course'){
              $crash_course = CrashCourse::where('id',$item->item_id)->first();
              $item->name = $crash_course->name;
              $item->thumbnail = $crash_course->thumbnail;
              $item->icon = $crash_course->crash_course_icon;
              $item->data = $crash_course;
            }
            if($item->item_type == 'E-Book'){
                $eBook = eBook::where('id',$item->item_id)->first();
                $item->regular_amount = $eBook->regular_price;
                $item->sales_amount = $eBook->price;
                $item->name = $eBook->name;
                $item->thumbnail = $eBook->thumbnails;
                $item->data = $eBook;
             }
            if($item->item_type == 'Lecture Sheet'){
                $lectureSheet = LectureSheet::where('id',$item->item_id)->first();
                $item->regular_amount = $lectureSheet->regular_price;
                $item->sales_amount = $lectureSheet->price;
                $item->name = $lectureSheet->name;
                $item->thumbnail = $lectureSheet->thumbnails;
                $item->data = $lectureSheet;
            }
            if($item->item_type == 'Paid Course'){
                $paidCourse = PaidCourse::where('id',$item->item_id)->first();
                $item->regular_amount = $paidCourse->regular_amount;
                $item->sales_amount = $paidCourse->sales_amount;
                $item->name = $paidCourse->name;
                $item->thumbnail = $paidCourse->thumbnail;
                $item->data = $paidCourse;
            }
            if($item->item_type == 'Lecture Videos'){
                $lecture = LectureVideoParticipant::where('user_id', $userId)
                    ->where('course_subject_id', $item->item_id)
                    ->first();
                $item->regular_amount = $item->payable_amount;
                $item->sales_amount = $item->paid_amount;
                $item->name = $item->item_name;
                $item->thumbnail = null;
                $item->data = $lecture;
            }
        }
        return FacadeResponse::json($purchaseList);
    }

    public function getUsertransactions($userId){

        $list =  UserAllPayment::where('user_id',$userId)->where('transaction_status','Complete')->orderBy('id','Desc')->get();
        foreach ($list as $item) {
            if($item->item_type == 'Crash Course'){
               $crash_course = CrashCourse::where('id',$item->item_id)->first();
               $item->name = $crash_course->name;
               $item->coupon_code = null;
               $item->created_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->created_at)));
               $item->updated_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->updated_at)));
            }
            if($item->item_type == 'E-Book'){
                $eBook = eBook::where('id',$item->item_id)->first();
                $item->name = $eBook->name;
                $item->coupon_code = null;
                $item->created_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->created_at)));
               $item->updated_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->updated_at)));
             }
            if($item->item_type == 'Lecture Sheet'){
                $lectureSheet = LectureSheet::where('id',$item->item_id)->first();
                $item->name = $lectureSheet->name;
                $item->coupon_code = null;
                $item->created_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->created_at)));
                $item->updated_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->updated_at)));
            }
            if($item->item_type == 'Paid Course'){
                $paidCourse = PaidCourse::where('id',$item->item_id)->first();
                if($item->coupon_id){
                    $coupon = PaidCourseCoupon::where('id', $item->coupon_id)->first();
                    $item->coupon_code = $coupon->coupon_code;
                }else{
                    $item->coupon_code = null;
                }
                $item->name = $paidCourse->name;
                $item->created_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->created_at)));
                $item->updated_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->updated_at)));
            }
        }
        return FacadeResponse::json($list);
        // $list =  UserCrashCoursePayment::where('user_crash_course_payments.user_id',$userId)
        //             ->where('user_crash_course_payments.crash_course_id',$courseId)
        //             ->join('crash_courses','user_crash_course_payments.crash_course_id','crash_courses.id')
        //             ->first();
        // return FacadeResponse::json($list);
    }

    public function getMyCrashCourses($userId){
        $list =  UserAllPayment::where('user_id',$userId)->where('transaction_status','Complete')->orderBy('id','Desc')->get();
        foreach ($list as $item) {
            if($item->item_type == 'Crash Course'){
               $crash_course = CrashCourse::where('id',$item->item_id)->first();
               $item->thumbnail = $crash_course->thumbnail;
               $item->name = $crash_course->name;
               $item->description = $crash_course->description;
               $item->regular_amount = $crash_course->regular_amount;
               $item->sales_amount = $crash_course->sales_amount;
            }
            // if($item->item_type == 'E-Book'){
            //     $eBook = eBook::where('id',$item->item_id)->first();
            //     $item->name = $eBook->name;
            //     $item->created_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->created_at)));
            //    $item->updated_at = date("Y-m-d H:i:s", strtotime('+6 hours', strtotime($item->updated_at)));
            //  }
        }
        return FacadeResponse::json($list);
    }

    public function createUserCrashCoursePayment(Request $request){
        $response = new ResponseObject;

        $check_payment = UserAllPayment::where('user_id', $request->user_id)->where('item_id', $request->item_id)
        ->where('item_type','=','Crash Course')
        ->where('transaction_status','=','Complete')->first();

        if (!empty($check_payment)) {
            $response->status = $response::status_fail;
            $response->messages = "Already payment done for this course";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $user =  User::where('id', $request->user_id)->first();
        $course =  CrashCourse::where('id', $request->item_id)->first();

        $payment = UserAllPayment::updateOrCreate([
            'user_id' => $request->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->mobile_number,
            'address' => $user->address,
            'currency' => $request->currency,
            'item_id' => $request->item_id,
            'item_name' => $course->name,
            'item_type'=> "Crash Course",
            'payable_amount' => $request->amount,
            'paid_amount' => $request->amount,
            'card_type'  => $request->card_type,
            'transaction_id' => $request->transaction_id,
            'transaction_status' => 'Complete',
            'transaction_status' => 'Complete',
            'status' => 'Enrolled'
        ]);

        $user_payment_details = UserAllPaymentDetails::updateOrCreate([
            'payment_id' => $payment->id,
            'amount' => $payment->paid_amount
        ]);

        $checkParticipant = CrashCourseParticipant::where('crash_course_id', $request->item_id)->where('user_id', $request->user_id)->first();

            if (empty($checkParticipant)) {
                CrashCourseParticipant::create([
                    'user_id' => $request->user_id,
                    'crash_course_id' => $request->item_id,
                    'course_amount' => $course->sales_amount,
                    'paid_amount' => $request->amount,
                    'is_fully_paid' => true
                ]);
            } else {
                $checkParticipant->update([
                    'paid_amount' => $request->amount,
                    'is_active' => true,
                    'is_fully_paid' => true
                ]);
            }

               $course->update([
                'number_of_students_enrolled' => $course->number_of_students_enrolled + 1,
               ]);

              $course_subjects = CrashCourseSubject::where('crash_course_id',$request->item_id)->get();
              foreach ($course_subjects as $subject) {
               $materialIds =  CrashCourseMaterial::where('crash_course_subject_id',$subject->id)->where('type','=','quiz')->pluck('id');
                    foreach ($materialIds as $materialId) {
                        $quizAccess = CrashCourseParticipantQuizAccess::where('user_id', $request->user_id)
                        ->where('crash_course_material_id', $materialId)->first();

                        if(empty($quizAccess)){
                            CrashCourseParticipantQuizAccess::create([
                                'user_id' => $request->user_id,
                                'crash_course_material_id' => $materialId,
                                'access_count' => 100
                            ]);
                        }else{
                            $quizAccess->update([
                                'access_count' => 100
                            ]);
                        }
                    }
                }



        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = " You have successfully enrolled this course";
        $response->result = "";
        return FacadeResponse::json($response);
    }

    public function getUserPurchases(Request $request){
        $list = [];
        $from = null;
        $to = null;
        if ($request->from) {
            $from = date('Y-m-d', strtotime($request->from));
            $to = date('Y-m-d', strtotime($request->to));
        }
        $purchaseList =  UserAllPayment::where('user_id', $request->userId)->where('transaction_status', 'Complete')
        ->when($request->tranId, function ($q) use ($request) {
            return $q->where('transaction_id', 'like', '%' . $request->tranId . '%');
        })->when($request->itemType, function ($q) use ($request) {
            return $q->where('item_type', 'like', '%' . $request->itemType . '%');
        })->when($from && $to, function($q) use ($from, $to) {
            return $q->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);
        })->orderBy('id','Desc')
        ->select(
            'id',
            'item_id',
            'item_name',
            'item_type',
            'transaction_id',
            'created_at',
            'payable_amount',
            'paid_amount',
            'discount',
            'status',
            'transaction_status'
        )->get();
        foreach ($purchaseList as $item) {
            if($item->item_type == 'Crash Course'){
               $crash_course = CrashCourse::where('id',$item->item_id)->first();
               $item->name = $crash_course->name;
               $item->thumbnail = $crash_course->thumbnail;
               $item->icon = $crash_course->crash_course_icon;
               $item->data = $crash_course;
            }
            if($item->item_type == 'E-Book'){
                $eBook = eBook::where('id',$item->item_id)->first();
                $item->regular_amount = $eBook->regular_price;
                $item->sales_amount = $eBook->price;
                $item->name = $eBook->name;
                $item->thumbnail = $eBook->thumbnails;
                $item->data = $eBook;
             }
            if($item->item_type == 'Paid Course'){
                $paidCourse = PaidCourse::where('id',$item->item_id)->first();
                $item->regular_amount = $item->payable_amount;
                $item->sales_amount = $item->paid_amount;
                $item->name = $paidCourse->name;
                $item->thumbnail = $paidCourse->thumbnail;
                $item->data = $paidCourse;
            }
            if($item->item_type == 'Lecture Videos'){
                $item->regular_amount = $item->payable_amount;
                $item->sales_amount = $item->paid_amount;
                $item->name = $item->item_name;
                $item->thumbnail = null;
                $item->data = null;
            }
        }

        if($request->name){
                foreach ($purchaseList as $purchase) {
                    if (strpos($purchase->name, $request->name) === FALSE) {
                     }else {
                        $list[] = $purchase;
                     }
                }
        }else{
            $list = $purchaseList;
        }


        return FacadeResponse::json($list);
    }

    public function getUsertransactionsWeb(Request $request){
        $list = [];
        $from = null;
        $to = null;
        if ($request->from) {
            $from = date('Y-m-d', strtotime($request->from));
            $to = date('Y-m-d', strtotime($request->to));
        }
        $purchaseList =  UserAllPayment::where('user_id', $request->userId)->where('transaction_status', 'Complete')
        ->when($request->tranId, function ($q) use ($request) {
            return $q->where('transaction_id', 'like', '%' . $request->tranId . '%');
        })->when($request->itemType, function ($q) use ($request) {
            return $q->where('item_type', 'like', '%' . $request->itemType . '%');
        })->when($from && $to, function($q) use ($from, $to) {
            return $q->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);
        })->orderBy('id','Desc')
        ->select(
            'id',
            'item_id',
            'item_name',
            'item_type',
            'transaction_id',
            'coupon_id',
            'created_at',
            'paid_amount as amount',
            'payable_amount',
            'paid_amount',
            'discount',
            'status',
            'transaction_status'
        )->get();
        foreach ($purchaseList as $item) {
            if($item->item_type == 'Crash Course'){
               $crash_course = CrashCourse::where('id',$item->item_id)->first();
               $item->name = $crash_course->name;
               $item->thumbnail = $crash_course->thumbnail;
               $item->icon = $crash_course->crash_course_icon;
               $item->data = $crash_course;
               $item->coupon_code = null;
            }
            if($item->item_type == 'E-Book'){
                $eBook = eBook::where('id',$item->item_id)->first();
                $item->regular_amount = $eBook->regular_price;
                $item->sales_amount = $eBook->price;
                $item->name = $eBook->name;
                $item->thumbnail = $eBook->thumbnails;
                $item->data = $eBook;
                $item->coupon_code = null;
             }
            if($item->item_type == 'Lecture Sheet'){
                $lectureSheet = LectureSheet::where('id',$item->item_id)->first();
                $item->regular_amount = $lectureSheet->regular_price;
                $item->sales_amount = $lectureSheet->price;
                $item->name = $lectureSheet->name;
                $item->thumbnail = $lectureSheet->thumbnails;
                $item->data = $lectureSheet;
                $item->coupon_code = null;
             }
            if($item->item_type == 'Paid Course'){
                $paidCourse = PaidCourse::where('id',$item->item_id)->first();
                if($item->coupon_id){
                    $coupon = PaidCourseCoupon::where('id', $item->coupon_id)->first();
                    $item->coupon_code = $coupon->coupon_code;
                }else{
                    $item->coupon_code = null;
                }
                $item->regular_amount = $paidCourse->regular_amount;
                $item->sales_amount = $paidCourse->sales_amount;
                $item->name = $paidCourse->name;
                $item->thumbnail = $paidCourse->thumbnail;
                $item->data = $paidCourse;
            }
            if($item->item_type == 'Lecture Videos'){
                $item->regular_amount = $item->payable_amount;
                $item->sales_amount = $item->paid_amount;
                $item->name = $item->item_name;
                $item->thumbnail = null;
                $item->data = null;
            }
        }

        if($request->name){
                foreach ($purchaseList as $purchase) {
                    if (strpos($purchase->name, $request->name) === FALSE) {
                     }else {
                        $list[] = $purchase;
                     }
                }
        }else{
            $list = $purchaseList;
        }


        return FacadeResponse::json($list);
    }

    public function getAllTransactoins(Request $request){

        $from = null;
        $to = null;
        if ($request->from) {
            $from = date('Y-m-d', strtotime($request->from));
            $to = date('Y-m-d', strtotime($request->to));
        }

        $list =  UserAllPayment::where('transaction_status', 'Complete')
        ->when($request->search_item, function ($q) use ($request) {
            return $q->where('name', 'like', '%' . $request->search_item . '%')
                ->orWhere('phone', 'like', '%' . $request->search_item . '%')
                ->orWhere('item_type', 'like', '%' . $request->search_item . '%')
                ->orWhere('transaction_id', 'like', '%' . $request->search_item . '%');
        })->when($from && $to, function($q) use ($from, $to) {
            return $q->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);
        })->orderBy('id','Desc')->get();

        return FacadeResponse::json($list);
    }


//   public function copyParticipantToAllPayment(){
//      $payments = UserCrashCoursePayment::get();
//      foreach($payments as $payment){
//          $user = User::where('id',$payment->user_id)->first();
//          $course =  CrashCourse::where('id', $payment->crash_course_id)->first();

//              $user_payment = UserAllPayment::updateOrCreate([
//                 'user_id' => $user->id,
//                 'name' => $user->name,
//                 'email' => $user->email ? $user->email : '',
//                 'phone' => $user->mobile_number,
//                 'item_id' => $course->id,
//                 'item_name' => $course->name,
//                 'item_type'=> "Crash Course",
//                 'payable_amount' => $payment->paid_amount,
//                 'paid_amount' => $payment->paid_amount,
//                 'transaction_status' => 'Complete',
//                 'address' => $user->address ? $user->address : '',
//                 'transaction_id' => uniqid(),
//                 'currency' => "BDT",
//                 'status' => 'Enrolled'
//             ]);

//             $user_payment_details = UserAllPaymentDetails::updateOrCreate([
//                 'payment_id' => $user_payment->id,
//                 'amount' => $user_payment->paid_amount
//             ]);
//      }
//   }

}

