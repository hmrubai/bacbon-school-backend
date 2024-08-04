<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Response;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use Validator;
use File;
use DB;
use App\LectureScript;
use App\LectureVideo;
use App\Chapter;

class LectureScriptController extends Controller
{


    public function getScriptlistByLectureId ($id) {
        $response = new ResponseObject;
        $scriptlist = LectureScript::where('lecture_id', $id)->get();
        return FacadeResponse::json($scriptlist);
    }
    public function storeLectureScript(Request $request) {


        $response = new ResponseObject;

        $formData = json_decode($request->data, true);
        // $data = $request->json()->all();

        $request['status'] = "Available";
        $validator = Validator::make($formData, [
            'subject_id' => 'required',
            'chapter_id' => 'required',
            'lecture_id' => 'required',
            'title' => 'required',
            'course_id' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            // $response->messages = "Validation failed";
            return FacadeResponse::json($response);
        }
        if ($request->hasFile('file') ) {


            $courseController = new CourseController();
            $course = $courseController->courseDetail($formData['course_id']);
            $courseName = str_replace(' ', '_', $course->name);
            $subjectController = new SubjectController();
            $subjectDetails = $subjectController->getSubjectDetailsById($formData['subject_id']);

            $subjectName = str_replace(' ', '_', $subjectDetails->name);

            $chapterDetails = Chapter::where('id', $formData['chapter_id'])->first();

            $chapterName = str_replace(' ', '_', $chapterDetails->name);

            // $destinationPath = 'uploads/' . $courseName . '/' . $subjectName . '/' . $chapterName . '/scripts';
            $destinationPath = 'uploads/Lectures';
            // if(!File::exists($destinationPath)) {
            //     File::makeDirectory($destinationPath, $mode = 0777, true, true);
            // }
            


            $file = $request->file('file');
            $formData['filename'] = 'LT_'.time().'.'.$file->getClientOriginalExtension();
            $formData['status'] = "Available";
            $file->move($destinationPath,$formData['filename']);

            $script = LectureScript::create([
                'course_id' =>$formData['course_id'],
                'subject_id' =>$formData['subject_id'],
                'chapter_id' => $formData['chapter_id'],
                'lecture_id' => $formData['lecture_id'],
                'title' => $formData['title'],
                'title_bn' => $formData['title_bn'],
                'status' => $formData['status'],
                'url' => $formData['filename']
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

    public function deleteLectureScript (Request $request) {
        $response = new ResponseObject;
        $lectureScript = LectureScript::find($request->id);


        if ($lectureScript) {
            if(file_exists($lectureScript->url)){
                unlink($lectureScript->url);
            }
        }
        $deleted = $lectureScript->delete();
        $response->status = $response::status_ok;
        $response->messages = "Successfully deleted";
        $response->result = $deleted;
        return FacadeResponse::json($response);
    }


    public function getLecturesScriptNumberForLMS () {
        $lectureCount = LectureScript::join('lecture_videos', 'lecture_scripts.lecture_id', 'lecture_videos.id')
                        ->join('courses', 'lecture_videos.course_id', 'courses.id')
                        ->select('courses.name',
                            DB::raw('count(*) as total')
                            )
                            ->groupBy('courses.name')
                        ->get();

        return FacadeResponse::json($lectureCount);
    }
}
