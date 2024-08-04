<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

use App\Subject;
use App\Lesson;
use App\Video;

class LessonController extends Controller
{
    public function GetLessonList(Request $request){
        $response = new ResponseObject;
        $data = $request->json()->all();
        // validating the request       
        $validator = Validator::make($data, [
            'subject_id' => 'required',
            'course_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        } 

        try {
            $subject_id = $request->get('subject_id');
            $course_id = $request->get('course_id');
            $result_data = Lesson::where('subject_id', $subject_id)->where('course_id', $course_id)->get();
            $response->status = $response::status_ok;
            $response->result = $result_data;
            return FacadeResponse::json($response);

        } catch (Exception $e) {
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);
        }
    }

    public function GetLessonVideoList(Request $request){
        $response = new ResponseObject;
        $data = $request->json()->all();
        // validating the request       
        $validator = Validator::make($data, [
            'lesson_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        } 

        try {
            $lesson_id = $request->get('lesson_id');
            $result_data = Video::where('lesson_id', $lesson_id)->get();
            $response->status = $response::status_ok;
            $response->result = $result_data;
            return FacadeResponse::json($response);

        } catch (Exception $e) {
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);
        }
    }


    public function GetLessonVideo(Request $request){
        $response = new ResponseObject;
        $data = $request->json()->all();
        // validating the request       
        $validator = Validator::make($data, [
            'video_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        } 

        try {
            $video_id = $request->get('video_id');
            $result_data = Video::where('id', $video_id);
            $response->status = $response::status_ok;
            $response->result = $result_data;
            return FacadeResponse::json($response);

        } catch (Exception $e) {
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);
        }
    }


    public function GetVideoList(){
        $response = new ResponseObject;
        try {
            $data = Video::all();
            $response->status = $response::status_ok;
            $response->result = $data;
            return FacadeResponse::json($response);

        } catch (Exception $e) {
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);
        }
    }


}
