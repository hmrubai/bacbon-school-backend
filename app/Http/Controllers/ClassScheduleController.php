<?php

namespace App\Http\Controllers;

use Exception;
use App\User;
use Validator;
use Carbon\Carbon;
use App\PaidCourse;
use App\MentorZoomLink;
use App\PaidCourseMentor;
use Illuminate\Http\Request;
use App\PaidCourseParticipant;
use App\PaidCourseClassSchedule;
use App\PaidCourseStudentMapping;
use Illuminate\Support\Facades\DB;
use App\Http\Helper\ResponseObject;
use \Illuminate\Support\Facades\Response as FacadeResponse;

class ClassScheduleController extends Controller
{

    public function mentorCourseList(Request $request)
    {
        $response = new ResponseObject;
        $user_id = $request->user_id;

        $mentor = User::where('id', $user_id)->where('user_type', 'Teacher')->first();

        if (empty($mentor)) {
            $response->status = $response::status_fail;
            $response->messages = "Mentor not found!";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $course = PaidCourseMentor::select(
            'paid_course_mentors.user_id as mentor_id',
            'paid_course_mentors.paid_course_id',
            'paid_courses.name as course_name_en',
            'paid_courses.name_bn as course_name_bn'
        )
            ->where('paid_course_mentors.user_id', $user_id)
            ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_mentors.paid_course_id')
            ->get();

        $response->status = $response::status_ok;
        $response->messages = "Course List successful!";
        $response->result = $course;
        return FacadeResponse::json($response);
    }

    public function mentorStudentList(Request $request)
    {
        $response = new ResponseObject;
        $user_id = $request->user_id;
        $paid_course_id = $request->paid_course_id;

        $mentor = User::where('id', $user_id)->where('user_type', 'Teacher')->first();

        if (empty($mentor)) {
            $response->status = $response::status_fail;
            $response->messages = "Mentor not found!";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $student = PaidCourseStudentMapping::select(
            'paid_course_student_mappings.student_id',
            'paid_course_student_mappings.mentor_id',
            'paid_course_student_mappings.paid_course_id',
            'paid_course_student_mappings.id as mapping_id',
            'paid_courses.name as course_name_en',
            'paid_courses.name_bn as course_name_bn',
            'students.name as student_name',
            'students.mobile_number as student_mobile_number',
            'teachers.name as mentor_name',
            'teachers.mobile_number as mentor_mobile_number'
        )
            ->where('paid_course_student_mappings.mentor_id', $mentor->id)
            ->where('paid_course_student_mappings.paid_course_id', $paid_course_id)
            ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_student_mappings.paid_course_id')
            ->leftJoin('users as students', 'paid_course_student_mappings.student_id', '=', 'students.id')
            ->leftJoin('users as teachers', 'paid_course_student_mappings.mentor_id', '=', 'teachers.id')
            ->get();

        $response->status = $response::status_ok;
        $response->messages = "Student List successful!";
        $response->result = $student;
        return FacadeResponse::json($response);
    }

    public function addClassSchedule(Request $request)
    {
        $response = new ResponseObject;
        
        $mapping_id = $request->mapping_id ? $request->mapping_id : 0;
        $schedule_date = $request->schedule_date ? $request->schedule_date : 0;

        if (!$mapping_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, attach mapping ID";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $mapping_details = PaidCourseStudentMapping::where('id', $mapping_id)->first();

        PaidCourseClassSchedule::create([
            "paid_course_student_mapping_id" => $mapping_id,
            "paid_course_id" => $mapping_details->paid_course_id,
            "student_id" => $mapping_details->student_id,
            "mentor_id" => $mapping_details->mentor_id,
            "schedule_datetime" => $schedule_date,
            "has_started" => false,
            "has_completed" => false,
            "start_time" => null,
            "end_time" => null,
            "is_active" => true,
            "student_end_time" => null
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Schedule added successful!";
        $response->result = null;
        return FacadeResponse::json($response);
    }

    public function updateClassSchedule(Request $request)
    {
        $response = new ResponseObject;

        $schedule_id = $request->schedule_id ? $request->schedule_id : 0;
        $schedule_date = $request->schedule_date ? $request->schedule_date : 0;

        if (!$schedule_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, attach ID";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $schedule_details = PaidCourseClassSchedule::where('id', $schedule_id)->first();

        $schedule_details->update([
            "schedule_datetime" => $schedule_date
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Schedule updated successfully!";
        $response->result = null;
        return FacadeResponse::json($response);
    }

    public function deleteClassSchedule(Request $request)
    {
        $response = new ResponseObject;

        $schedule_id = $request->schedule_id ? $request->schedule_id : 0;

        if (!$schedule_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, attach ID";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $schedule_details = PaidCourseClassSchedule::where('id', $schedule_id)->first();
        if ($schedule_details->has_started) {
            $response->status = $response::status_fail;
            $response->messages = "You cannot delete the class, because it\'s already been started!";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        PaidCourseClassSchedule::where('id', $schedule_id)->delete();

        $response->status = $response::status_ok;
        $response->messages = "Class deleted successful!";
        $response->result = null;
        return FacadeResponse::json($response);
    }

    public function mentorClassScheduleList(Request $request)
    {
        $response = new ResponseObject;
        $mapping_id = $request->mapping_id ? $request->mapping_id : 0;

        if (!$mapping_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, attach mapping ID";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $class = PaidCourseClassSchedule::select(
            'paid_course_class_schedules.*',
            'paid_courses.name as course_name_en',
            'paid_courses.name_bn as course_name_bn',
            'students.name as student_name',
            'students.mobile_number as student_mobile_number',
            'teachers.name as mentor_name',
            'teachers.mobile_number as mentor_mobile_number'
        )
            ->where('paid_course_class_schedules.paid_course_student_mapping_id', $mapping_id)
            ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_class_schedules.paid_course_id')
            ->leftJoin('users as students', 'paid_course_class_schedules.student_id', '=', 'students.id')
            ->leftJoin('users as teachers', 'paid_course_class_schedules.mentor_id', '=', 'teachers.id')
            ->orderBy('paid_course_class_schedules.schedule_datetime', 'DESC')
            ->get();

        foreach ($class as $item) {

            if($item->start_time){
                $item->start_time = $this->addHour($item->start_time, 6);
            }

            if($item->end_time){
                $item->end_time = $this->addHour($item->end_time, 6);
            }

            $isToday = date('Ymd') == date('Ymd', strtotime($item->schedule_datetime));

            $zoomLink = MentorZoomLink::where('mentor_id', $item->mentor_id)->first();

            if ($isToday) {
                $item->can_join = true;
                $item->join_link = $zoomLink->live_link;
            } else {
                $item->can_join = false;
                $item->join_link = null;
            }

            $item->has_passed = false;
            if (time() > strtotime($item->schedule_datetime)) {
                $item->has_passed = true;
            }
        }

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $class;
        return FacadeResponse::json($response);
    }

    public function studentClassScheduleList(Request $request)
    {
        $response = new ResponseObject;
        $user_id = $request->user_id ? $request->user_id : 0;

        if (!$user_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, attach Student ID";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $class = PaidCourseClassSchedule::select(
            'paid_course_class_schedules.*',
            'paid_courses.name as course_name_en',
            'paid_courses.name_bn as course_name_bn',
            'students.name as student_name',
            'students.mobile_number as student_mobile_number',
            'teachers.name as mentor_name',
            'teachers.mobile_number as mentor_mobile_number'
        )
            ->where('paid_course_class_schedules.student_id', $user_id)
            ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_class_schedules.paid_course_id')
            ->leftJoin('users as students', 'paid_course_class_schedules.student_id', '=', 'students.id')
            ->leftJoin('users as teachers', 'paid_course_class_schedules.mentor_id', '=', 'teachers.id')
            ->orderBy('paid_course_class_schedules.schedule_datetime', 'DESC')
            ->get();

        foreach ($class as $item) {

            if($item->start_time){
                $item->start_time = $this->addHour($item->start_time, 6);
            }

            if($item->end_time){
                $item->end_time = $this->addHour($item->end_time, 6);
            }

            $isToday = date('Ymd') == date('Ymd', strtotime($item->schedule_datetime));

            $zoomLink = MentorZoomLink::where('mentor_id', $item->mentor_id)->first();

            if ($isToday) {
                $item->can_join = true;
                $item->join_link = $zoomLink->live_link;
            } else {
                $item->can_join = false;
                $item->join_link = null;
            }

            $item->has_passed = false;
            if (time() > strtotime($item->schedule_datetime)) {
                $item->has_passed = true;
            }
        }

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $class;
        return FacadeResponse::json($response);
    }

    public function updateZoomLink(Request $request)
    {
        $response = new ResponseObject;
        $user_id = $request->user_id;
        $mentor = User::where('id', $user_id)->where('user_type', 'Teacher')->first();

        $zoomLink = MentorZoomLink::where('mentor_id', $mentor->id)->first();
        
        if(!empty($zoomLink)){
            MentorZoomLink::where('id', $zoomLink->id)->update([
                'live_link' => $request->live_link,
            ]);
        }else{
            MentorZoomLink::create([
                'mentor_id' => $mentor->id,
                'live_link' => $request->live_link,
                'is_active' => true
            ]);
        }

        $response->status = $response::status_ok;
        $response->messages = "Link has been updated successfully";
        $response->result = null;
        return FacadeResponse::json($response);
    }

    public function getZoomLink(Request $request)
    {
        $response = new ResponseObject;

        $user_id = $request->user_id;
        $mentor = User::where('id', $user_id)->where('user_type', 'Teacher')->first();

        $zoomLink = MentorZoomLink::where('mentor_id', $mentor->id)->first();
        
        if(empty($zoomLink)){
           $zoomLink = (object)[ 
               "id" => $user_id,
               "live_link" => "",
               "mentor_id" => $mentor->id,
               "is_active" => true,
               "created_at" => null,
               "updated_at" => null
            ];
        }

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $zoomLink;
        return FacadeResponse::json($response);
    }
}
