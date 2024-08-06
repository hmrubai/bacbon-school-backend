<?php

namespace App\Http\Controllers;

use Exception;
use App\User;
use Validator;
use Carbon\Carbon;
use App\PaidCourseMentor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Helper\ResponseObject;
use \Illuminate\Support\Facades\Response as FacadeResponse;

class TeacherController extends Controller
{
    public function addNewTeacher(Request $request){
        $response = new ResponseObject;

        if(!$request->id){
            $is_exist_email = User::where('email', '=', $request->email)->first();
            if (!empty($is_exist_email)) {
                $response->status = $response::status_fail;
                $response->messages = "Please, User Already exist!";
                $response->data = [];
                return response()->json($response);
            }
            $is_exist_mobile = User::where('mobile_number', '=', $request->mobile_number)->first();
            if (!empty($is_exist_mobile)) {
                $response->status = $response::status_fail;
                $response->messages = "Please, User Already exist!";
                $response->data = [];
                return response()->json($response);
            }
        }

        try {
            DB::beginTransaction();

            if(!$request->id){
                $user = User::create([
                    'name' => $request->name ? $request->name : $request->name,
                    'email' => $request->email ? $request->email : $request->email,
                    'institute' => $request->institute ? $request->institute : $request->institute,
                    'experiance' => $request->experiance ? $request->experiance : $request->experiance,
                    'mobile_number' => $request->mobile_number ? $request->mobile_number : $request->mobile_number,
                    'bio' => $request->bio ? $request->bio : $request->bio,
                    'user_type' => "Teacher"
                ]);
    
                $user_code = 'BS' . (1000 + $user->id);
                $user->update(['user_code' => $user_code]);
    
                $inserted_user = User::where('mobile_number', '=', $request->mobile_number)->first();

                DB::commit();
    
                $response->status = $response::status_ok;
                $response->messages = "Teacher has been added successfully!";
                $response->data = $inserted_user;
                return response()->json($response);
                
            }else{
                
                $update_user = User::where('id', $request->id)->update([
                    'name' => $request->name ? $request->name : $request->name,
                    'email' => $request->email ? $request->email : $request->email,
                    'institute' => $request->institute ? $request->institute : $request->institute,
                    'experiance' => $request->experiance ? $request->experiance : $request->experiance,
                    'mobile_number' => $request->mobile_number ? $request->mobile_number : $request->mobile_number,
                    'bio' => $request->bio ? $request->bio : $request->bio
                ]);

                DB::commit();

                $response->status = $response::status_ok;
                $response->messages = "Teacher has been updated successfully";
                $response->data = $update_user;
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

    public function teacherList(Request $request){
        $response = new ResponseObject;
        $teachers = User::where('user_type', 'Teacher')->orderby('id', 'DESC')->get();
        
        $response->status = $response::status_ok;
        $response->messages = "Teacher listed successfully";
        $response->data = $teachers;
        return response()->json($response);
    }

    public function assignTeacher(Request $request)
    {
        $response = new ResponseObject;
        try {
            DB::beginTransaction();
            if (!$request->paid_course_id) {
                $response->status = $response::status_fail;
                $response->messages = "Please, Select Paid Course!";
                $response->data = [];
                return response()->json($response);
            }

            if(!empty($request->mentors)){
                $mentor = [];
                foreach ($request->mentors as $key => $value) {

                    $is_exist = PaidCourseMentor::where('paid_course_id', $request->paid_course_id)->where('user_id', $value['id'])->first();

                    if(empty($is_exist)){
                        $mentor[] = [
                            'paid_course_id' => $request->paid_course_id,
                            'user_id' => $value['id'],
                            'is_active' => true,
                        ];
                    }
                }

                PaidCourseMentor::insert($mentor);
                DB::commit();

                $response->status = $response::status_ok;
                $response->messages = "Expert has been added successfully!";
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

    public function teacherListbyCourseID($paid_course_id){
        $response = new ResponseObject;
        $teachers = PaidCourseMentor::select("paid_course_mentors.*", "users.name", "users.mobile_number", "users.email")
        ->where('paid_course_mentors.paid_course_id', $paid_course_id)
        ->leftJoin('users', 'users.id', 'paid_course_mentors.user_id')
        ->orderby('paid_course_mentors.id', 'DESC')->get();
        
        $response->status = $response::status_ok;
        $response->messages = "Teacher listed successfully";
        $response->data = $teachers;
        return response()->json($response);
    }

    public function removeMentorFromPaidCourse(Request $request)
    {
        $response = new ResponseObject;

        PaidCourseMentor::where('id', $request->id)->delete();

        $response->status = $response::status_ok;
        $response->messages = "Expert Deleted successfully";
        $response->data = [];
        return response()->json($response);
    }
}
