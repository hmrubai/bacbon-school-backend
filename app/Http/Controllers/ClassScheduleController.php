<?php

namespace App\Http\Controllers;

use Exception;
use App\User;
use Validator;
use Carbon\Carbon;
use App\PaidCourse;
use App\MentorZoomLink;
use App\PaidCourseMentor;
use App\StudentJoinHistory;
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

    public function startLiveClass(Request $request)
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
            $response->messages = "You can not start this class! Because it\'s already been started!";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $schedule_details->update([
            "start_time" => date("Y-m-d H:i:s"),
            "has_started" => true
        ]);

        $response->status = $response::status_ok;
        $response->messages = "The class has been started! Please take care of your student!";
        $response->result = null;
        return FacadeResponse::json($response);
    }

    public function studentJoinClass(Request $request)
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

        if (!$schedule_details->has_started) {
            $response->status = $response::status_fail;
            $response->messages = "You can not join this class! Because this class has not been started yet!!";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        StudentJoinHistory::create([
            'paid_course_class_schedule_id' => $schedule_id,
            'student_id' => $schedule_details->student_id,
            'join_time' => date("Y-m-d H:i:s")
        ]);

        $response->status = $response::status_ok;
        $response->messages = "Enjoy your class!!";
        $response->result = null;
        return FacadeResponse::json($response);
    }

    public function studentClassJoinHistory(Request $request)
    {
        $response = new ResponseObject;
        $schedule_id = $request->schedule_id ? $request->schedule_id : 0;

        if (!$schedule_id) {
            $response->status = $response::status_fail;
            $response->messages = "Please, attach ID";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $schedule_details = PaidCourseClassSchedule::select(
            'paid_course_class_schedules.*',
            'paid_courses.name as course_name_en',
            'paid_courses.name_bn as course_name_bn',
            'teachers.name as mentor_name',
            'teachers.mobile_number as mentor_mobile_number'
        )
            ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_class_schedules.paid_course_id')
            ->leftJoin('users as teachers', 'paid_course_class_schedules.mentor_id', '=', 'teachers.id')
            ->where('paid_course_class_schedules.id', $schedule_id)
            ->first();

        $history = StudentJoinHistory::where('paid_course_class_schedule_id', $schedule_id)->get();
        foreach ($history as $item) {
            $item->join_time = $this->addHour($item->join_time, 6);
            $item->schedule_datetime = $schedule_details->schedule_datetime;
            $item->course_name_en = $schedule_details->course_name_en;
            $item->course_name_bn = $schedule_details->course_name_bn;
            $item->mentor_name = $schedule_details->mentor_name;
            $item->mentor_mobile_number = $schedule_details->mentor_mobile_number;
        }

        $response->status = $response::status_ok;
        $response->messages = "History List!";
        $response->result = $history;
        return FacadeResponse::json($response);
    }

    public function endLiveClass(Request $request)
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

        if (!$schedule_details->has_started) {
            $response->status = $response::status_fail;
            $response->messages = "Please start class first! You can not end a class before starts!";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $schedule_details->update([
            "end_time" => date("Y-m-d H:i:s"),
            "has_completed" => true
        ]);

        $response->status = $response::status_ok;
        $response->messages = "The class has been ended! Thank You!";
        $response->result = null;
        return FacadeResponse::json($response);
    }

    public function studentEndLiveClass(Request $request)
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

        if (!$schedule_details->has_started) {
            $response->status = $response::status_fail;
            $response->messages = "Please start class first! You can not end a class before starts!";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $schedule_details->update([
            "student_end_time" => date("Y-m-d H:i:s")
        ]);

        $response->status = $response::status_ok;
        $response->messages = "The class has been ended! Thank You!";
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

    public function mentorOngoingClassList(Request $request)
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

        $zoomLink = MentorZoomLink::where('mentor_id', $mentor->id)->first();

        $class = PaidCourseClassSchedule::select(
            'paid_course_class_schedules.*',
            'paid_courses.name as course_name_en',
            'paid_courses.name_bn as course_name_bn',
            'students.name as student_name',
            'students.mobile_number as student_mobile_number',
            'teachers.name as mentor_name',
            'teachers.mobile_number as mentor_mobile_number'
        )
            ->where('paid_course_class_schedules.mentor_id', $mentor->id)
            ->where('paid_course_class_schedules.has_completed', false)
            ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_class_schedules.paid_course_id')
            ->leftJoin('users as students', 'paid_course_class_schedules.student_id', '=', 'students.id')
            ->leftJoin('users as teachers', 'paid_course_class_schedules.mentor_id', '=', 'teachers.id')
            ->get();

        $class_list = [];

        foreach ($class as $item) {

            if($item->start_time){
                $item->start_time = $this->addHour($item->start_time, 6);
            }

            if($item->end_time){
                $item->end_time = $this->addHour($item->end_time, 6);
            }

            $isToday = date('Ymd') == date('Ymd', strtotime($item->schedule_datetime));

            if (!empty($zoomLink)) {
                $item->join_link = $zoomLink->live_link;
            } else {
                $item->join_link = null;
            }

            if ($isToday) {
                $item->can_join = true;
                array_push($class_list, $item);
            } else {
                $item->can_join = false;
                //array_push($class_list, $item);
            }
        }

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $class_list;
        return FacadeResponse::json($response);
    }

    public function mentorCompletedClassList(Request $request)
    {
        $response = new ResponseObject;

        $user_id = $request->user_id;
        $from = $request->start_date ? $request->start_date.' 00:00:00' : '';
        $to = $request->end_date ? $request->end_date.' 23:59:59' : '';

        $mentor = User::where('id', $user_id)->where('user_type', 'Teacher')->first();

        if (empty($mentor)) {
            $response->status = $response::status_fail;
            $response->messages = "Mentor not found!";
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
        ->where('paid_course_class_schedules.mentor_id', $mentor->id)
        ->where('paid_course_class_schedules.has_completed', true)
        ->whereBetween('paid_course_class_schedules.schedule_datetime', [$from, $to])
        ->leftJoin('paid_courses', 'paid_courses.id', 'paid_course_class_schedules.paid_course_id')
        ->leftJoin('users as students', 'paid_course_class_schedules.student_id', '=', 'students.id')
        ->leftJoin('users as teachers', 'paid_course_class_schedules.mentor_id', '=', 'teachers.id')
        ->get();
        
        $times = [];
        foreach ($class as $key => $item) {
            $item->start_time_gmt = $this->addHour($item->start_time, 6);
            $item->end_time_gmt = $this->addHour($item->end_time, 6);
            $item->total_minutes = $this->getTimeDifference($item->start_time, $item->end_time);
            array_push($times, $this->getTimeDifference($item->start_time, $item->end_time));
        }

        $data_response = [
            "total_time" => $this->calculateTime($times),
            "list" => $class
        ];

        $response->status = $response::status_ok;
        $response->messages = "Successful";
        $response->result = $data_response;
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

    public function addHour($date_time, $hour){
        return date("Y-m-d H:i:s", strtotime('+'.$hour.' hours', strtotime($date_time)));
    }

    public function getUTCTime($date) {
        return new Carbon($date, 'UTC');
    }

    public function getTimeDifference($start, $end) {
        $start_time  = new Carbon($start);
        $end_time    = new Carbon($end);
        return $start_time->diff($end_time)->format('%H:%I:%S');
    }

    public function calculateTime($time_array){
        $sum = strtotime('00:00:00');
        $totaltime = 0;
        
        foreach( $time_array as $element ) {
            $timeinsec = strtotime($element) - $sum;
            $totaltime = $totaltime + $timeinsec;
        }

        $h = intval($totaltime / 3600);
        $totaltime = $totaltime - ($h * 3600);
        $m = intval($totaltime / 60);
        $s = $totaltime - ($m * 60);
        
        return "$h:$m:$s";
    }
}
