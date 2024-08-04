<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
// use Illuminate\Support\Facades\Response;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use App\Custom\Common;
use Carbon\Carbon;
use Validator;
use DB;
use Session;
use File;
use App\TranscoderVideoLog;
use App\LectureVideo;
use App\LectureQuestion;
use App\LectureExamQuestion;
use App\LectureExam;
use App\SubjectExam;
use App\Chapter;
use App\ChapterExam;
use App\ReviewExam;
use App\ReviewExamDetail;
use App\PaymentLecture;
use App\ChapterExamQuestion;
use App\SubjectExamQuestion;
use App\Events\LectureCreated;
use App\Events\LectureCount;
use App\Events\LectureSum;
use App\Events\LectureFree;
use App\Payment;
use App\User;
class LectureController extends Controller
{


    public function createReviewTest() {
        $chapters = Chapter::where('course_id', 2)->where('subject_id', 2)->with('lectureVideos')->get();
        foreach ($chapters as $chapter) {
            $exams = [];
            foreach ($chapter->lectureVideos as $key=>$lecture) {
                if (count($chapter->lectureVideos) > 1) {

                    $exs = LectureExam::where('lecture_id', $lecture->id)->with('questionIds')->get();

                    foreach($exs as $ex)
                    $exams[] = $ex;

                    if ($key+1 == count($chapter->lectureVideos)) {

                        $exam = LectureExam::create([
                            'course_id' => $chapter->course_id,
                            'subject_id' => $chapter->subject_id,
                            'chapter_id' => $lecture->chapter_id,
                            'lecture_id' => $lecture->id,
                            'exam_name' => $chapter->name . " Review Test",
                            'exam_name_bn' => $chapter->name . " Review Test",
                            'exam_name_jp' => null,
                            'duration' => 10,
                            'positive_mark' => 1,
                            'negative_mark' => 0,
                            'total_mark' => 20,
                            'question_number' => 20,
                            'status' => "Published"
                        ]);

                        foreach ($exams as $exm) {
                            foreach ($exm->questionIds as $que) {
                                LectureExamQuestion::create([
                                    'subject_id' => $chapter->subject_id,
                                    'chapter_id' => $chapter->id,
                                    'lecture_id' => $lecture->id,
                                    'exam_id' => $exam->id,
                                    'question_id' => $que->question_id,
                                    'status' => "Available"
                                ]);
                            }
                        }
                    }
                }

            }

        }
        return FacadeResponse::json("Ok");
    }

    public function copyQuiz () {
        $examList = LectureExam::where('course_id', 13)->where('subject_id', 2)->with('questionIds')->where('created_at', '>', '2020-08-17 05:08:26')->get();


        // return FacadeResponse::json($examList);
        foreach ($examList as $lecture) {

           $lectureVideo = LectureVideo::where('id', $lecture->lecture_id)->first();
           $lectureNew = LectureVideo::where('title', $lectureVideo->title)->where('course_id', 15)->first();

            // LectureExam::where('id', $lecture->id)->update([
            //         "lecture_id" => $lectureNew->id
            //     ]);
            $exam = LectureExam::create([
                "course_id" => 15,
                "subject_id" => $lecture->subject_id,
                "chapter_id" => $lecture->chapter_id,
                "lecture_id" => $lectureNew->id,
                "exam_name" => $lecture->exam_name,
                "exam_name_bn" => $lecture->exam_name_bn,
                "exam_name_jp" => $lecture->exam_name_jp,
                "duration" => $lecture->duration,
                "positive_mark" => $lecture->positive_mark,
                "negative_mark" => $lecture->negative_mark,
                "total_mark" => $lecture->total_mark,
                "question_number" => $lecture->question_number,
                "status" => $lecture->status
            ]);
            foreach ($lecture->questionIds as $question) {

                // $lectureVideo = LectureVideo::where('id', $question->lecture_id)->first();
                // $lectureNew = LectureVideo::where('title', $lectureVideo->title)->where('course_id', 15)->first();
                LectureExamQuestion::create([

                "subject_id" => $question->subject_id,
                "chapter_id" => $lectureNew->chapter_id,
                "lecture_id" => $lectureNew->id,
                "exam_id" => $exam->id,
                "question_id" => $question->question_id,
                "status" => "Available",
                ]);
            }
        }

        return FacadeResponse::json($examList);
    }

    public function getUserNameByIdArray (Request $request) {
        $users = [];
        $data = $request->all();
        for ($i = 0; $i< count($data); $i++) {
            $id = (int) $data[$i];
            $user = User::select('id','name')->find($id);
            $users[] = $user;
        }
        return FacadeResponse::json($users);
    }
    public function getUserName ($userId) {
        $user = User::select('id','name')->find($userId);
        return FacadeResponse::json($user);
    }
    public function getUserAndLecture($userId, $lectureId) {
        $user = User::select('id','name')->find($userId);
        $lecture = LectureVideo::select('id','title', 'title_bn')->find($lectureId);
        $obj = (Object) [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'lecture_id' => $lecture->id,
            'lecture_title' => $lecture->title,
            'lecture_title_bn' => $lecture->title_bn
        ];

        return FacadeResponse::json($obj);
     }
    public function demoCountWithDate() {

        $datetime = date("Y-m-d H:i:s");
        $timestamp = strtotime($datetime);
        if (date('H') < 13) {
            $time = $timestamp - (18 * 60 * 60);
        } else {
            $time = $timestamp - (6 * 60 * 60);
        }
        $fromTime = date("Y-m-d H:i:s", $time);
        // return  date("Y-m-d H:i:s");
        return LectureVideo::whereBetween('created_at', [$fromTime, date("Y-m-d H:i:s")])->count();
    }

    public function getLectureExamDetailById($id) {
        return LectureExam::where('id', $id)->with('questions')->select('id','exam_name', 'question_number', 'total_mark', 'positive_mark', 'negative_mark')->withCount('questions')->first();
     }


    public function getVideoById($id) {
        $lecture = LectureVideo::where('id', $id)->first();

        $data = explode(".",$lecture->thumbnail);

        $lecturePath = str_replace("thumbnails","lectures", $data[2]);
        $url = $data[0].'.'.$data[1].'.'.$lecturePath.'.'.'mp4';
        return $url;
        // $lecture = Lecture
        // $filePath = 'uploads/lecture_videos/'.$fileName;
        // $stream = new VideoStream($filePath);
        // $stream->start();
    }

    public function getVideo($fileName) {
        $filePath = 'uploads/lecture_videos/'.$fileName;
        $stream = new VideoStream($filePath);
        $stream->start();
    }
    public function getAllFreeLectureListPagination($page_size, $page_number) {

        $freeLectureList = LectureVideo::where('isFree', true)
        ->join('courses', 'lecture_videos.course_id', 'courses.id')
        ->join('subjects', 'lecture_videos.subject_id', 'subjects.id')
        ->join('chapters', 'lecture_videos.chapter_id', 'chapters.id')
        ->select(
            'lecture_videos.id as id',
            'lecture_videos.subject_id as subject_id',
            'lecture_videos.course_id as course_id',
            'lecture_videos.chapter_id as chapter_id',
            'lecture_videos.title as title',
            'lecture_videos.title_bn as title_bn',
            'lecture_videos.description as description',
            'lecture_videos.url as url',
            'lecture_videos.thumbnail as thumbnail',
            'lecture_videos.duration as duration',
            'lecture_videos.code as code',
            'lecture_videos.course_id as course_id',
            'lecture_videos.subject_id as subject_id',
            'lecture_videos.chapter_id as chapter_id',
            'lecture_videos.status as status',
            'courses.name as course_name',
            'courses.name_bn as course_name_bn',
            'subjects.name as subject_name',
            'subjects.name_bn as subject_name_bn',
            'chapters.name_bn as chapter_name_bn',
            'chapters.name as chapter_name'
            )
            ->where('lecture_videos.course_id', '!=', 14)
            ->skip($page_number * $page_size)
            ->limit($page_size)
        ->get();
        $lectureList = [];
        foreach ($freeLectureList as $lt) {
            $total_lectures = LectureVideo::where('subject_id', $lt->subject_id)->where('course_id', $lt->course_id)->count();
            $sumOfLectureLength = LectureVideo::where('subject_id', $lt->subject_id)->where('course_id', $lt->course_id)
            ->groupBy('course_id', 'subject_id')
            ->selectRaw('sum(duration) as total_duration')
            ->first();
            $total_minutes = floor($sumOfLectureLength->total_duration / 60);
            $hours = floor($total_minutes / 60);
            $minutes = $total_minutes % 60;
            $total_duration =  $hours.':'.$minutes;
            $duration = floor($lt->duration / 60).':'. $lt->duration % 60;
            $lectureObj = (object)[
                "id" => $lt->id,
                "code" => $lt->code,
                "title" => $lt->title,
                "description" => $lt->description,
                "isFree" => $lt->isFree,
                "url" => $lt->url,
                "thumbnail" => $lt->thumbnail,
                "duration" => $duration,
                "total_duration" => $total_duration,
                "total_lectures" => $total_lectures,
                "price" => $lt->price,
                "status" => $lt->status,
                "course_name" => $lt->course_name,
                "subject_name" => $lt->subject_name,
                "chapter_name" => $lt->chapter_name
             ];
             $lectureList[] = $lectureObj;
        }

        return $lectureList;

     }
    public function getAllFreeLectureList() {
        $freeLectureList = LectureVideo::where('isFree', true)
        ->join('courses', 'lecture_videos.course_id', 'courses.id')
        ->join('subjects', 'lecture_videos.subject_id', 'subjects.id')
        ->join('chapters', 'lecture_videos.chapter_id', 'chapters.id')
        ->select(
            'lecture_videos.id as id',
            'lecture_videos.subject_id as subject_id',
            'lecture_videos.course_id as course_id',
            'lecture_videos.chapter_id as chapter_id',
            'lecture_videos.title as title',
            'lecture_videos.title_bn as title_bn',
            'lecture_videos.description as description',
            'lecture_videos.url as url',
            'lecture_videos.thumbnail as thumbnail',
            'lecture_videos.duration as duration',
            'lecture_videos.code as code',
            'lecture_videos.course_id as course_id',
            'lecture_videos.subject_id as subject_id',
            'lecture_videos.chapter_id as chapter_id',
            'lecture_videos.status as status',
            'courses.name as course_name',
            'courses.name_bn as course_name_bn',
            'subjects.name as subject_name',
            'subjects.name_bn as subject_name_bn',
            'chapters.name_bn as chapter_name_bn',
            'chapters.name as chapter_name'
            )
            ->where('courses.id', '!=', 14)
            ->limit(8)
        ->get();
        $lectureList = [];
        foreach ($freeLectureList as $lt) {
            $total_lectures = LectureVideo::where('subject_id', $lt->subject_id)->where('course_id', $lt->course_id)->count();
            $sumOfLectureLength = LectureVideo::where('subject_id', $lt->subject_id)->where('course_id', $lt->course_id)
            ->groupBy('course_id', 'subject_id')
            ->selectRaw('sum(duration) as total_duration')
            ->first();
            $total_minutes = floor($sumOfLectureLength->total_duration / 60);
            $hours = floor($total_minutes / 60);
            $minutes = $total_minutes % 60;
            $total_duration =  $hours.':'.$minutes;
            $duration = floor($lt->duration / 60).':'. $lt->duration % 60;
            $lectureObj = (object)[
                "id" => $lt->id,
                "course_id" => $lt->course_id,
                "code" => $lt->code,
                "title" => $lt->title,
                "description" => $lt->description,
                "isFree" => $lt->isFree,
                "url" => $lt->url,
                "thumbnail" => $lt->thumbnail,
                "duration" => $duration,
                "total_duration" => $total_duration,
                "total_lectures" => $total_lectures,
                "price" => $lt->price,
                "status" => $lt->status,
                "course_name" => $lt->course_name,
                "subject_name" => $lt->subject_name,
                "chapter_name" => $lt->chapter_name
             ];
             $lectureList[] = $lectureObj;
        }

        return $lectureList;

    }

    public function getFreeLectureList() {
        $freeLectureList = LectureVideo::where('isFree', true)
        ->join('courses', 'lecture_videos.course_id', 'courses.id')
        ->join('subjects', 'lecture_videos.subject_id', 'subjects.id')
        ->join('chapters', 'lecture_videos.chapter_id', 'chapters.id')
        ->select(
            'lecture_videos.id as id',
            'lecture_videos.subject_id as subject_id',
            'lecture_videos.course_id as course_id',
            'lecture_videos.chapter_id as chapter_id',
            'lecture_videos.title as title',
            'lecture_videos.title_bn as title_bn',
            'lecture_videos.description as description',
            'lecture_videos.url as url',
            'lecture_videos.thumbnail as thumbnail',
            'lecture_videos.duration as duration',
            'lecture_videos.code as code',
            'lecture_videos.course_id as course_id',
            'lecture_videos.subject_id as subject_id',
            'lecture_videos.chapter_id as chapter_id',
            'lecture_videos.status as status',
            'courses.name as course_name',
            'courses.name_bn as course_name_bn',
            'subjects.name as subject_name',
            'subjects.name_bn as subject_name_bn',
            'chapters.name_bn as chapter_name_bn',
            'chapters.name as chapter_name'
            )
            ->where('courses.id', '!=', 14)
            ->where('courses.id', '!=', 25)
            ->inRandomOrder(time())
            ->limit(8)
        ->get();
        $lectureList = [];
        foreach ($freeLectureList as $lt) {
            $total_lectures = LectureVideo::where('subject_id', $lt->subject_id)->where('course_id', $lt->course_id)->count();
            $sumOfLectureLength = LectureVideo::where('subject_id', $lt->subject_id)->where('course_id', $lt->course_id)
            ->groupBy('course_id', 'subject_id')
            ->selectRaw('sum(duration) as total_duration')
            ->first();
            $total_minutes = floor($sumOfLectureLength->total_duration / 60);
            $hours = floor($total_minutes / 60);
            $minutes = $total_minutes % 60;
            $total_duration =  $hours.':'.$minutes;
            $duration = floor($lt->duration / 60).':'. $lt->duration % 60;
            $lectureObj = (object)[
                "id" => $lt->id,
                "course_id" => $lt->course_id,
                "code" => $lt->code,
                "title" => $lt->title,
                "description" => $lt->description,
                "isFree" => $lt->isFree,
                "url" => $lt->url,
                "thumbnail" => $lt->thumbnail,
                "duration" => $duration,
                "total_duration" => $total_duration,
                "total_lectures" => $total_lectures,
                "price" => $lt->price,
                "status" => $lt->status,
                "course_name" => $lt->course_name,
                "subject_name" => $lt->subject_name,
                "chapter_name" => $lt->chapter_name
             ];
             $lectureList[] = $lectureObj;
        }

        return $lectureList;
    }
    public function getExamListByLecture($id) {
        $lectureExams = LectureExam::where('lecture_id', $id)->get();
        return FacadeResponse::json($lectureExams);

    }

    public function getLectureDetails($lecture_id) {
        $lecture = LectureVideo::where('id', $lecture_id)->with('course', 'subject', 'chapter', 'examList', 'lectureScripts')->withCount('examList')->first();
        if( $lecture) {
            $duration = floor($lecture->duration/ 60 ).':'.$lecture->duration % 60;
            $lectureObj = (object)[
                "id" => $lecture->id,
                "code" => $lecture->code,
                "title" => $lecture->title,
                "title_bn" => $lecture->title_bn,
                "description" => $lecture->description,
                "isFree" => $lecture->isFree,
                "full_url" => $lecture->full_url,
                "download_url" =>  $lecture->download_url,
                "is_downloadable" =>  $lecture->is_downloadable,
                "youtube_video_id" => $lecture->youtube_video_id,
                "youtube_url" => $lecture->youtube_url,
                "url" => $lecture->url,
                "thumbnail" => $lecture->thumbnail,
                "duration" => $duration,
                "price" => $lecture->price,
                "status" => $lecture->status,
                "course" => $lecture->course,
                "subject" => $lecture->subject,
                "chapter" => $lecture->chapter,
                "exams" => $lecture->examList,
                "scripts" => $lecture->lectureScripts,
             ];
             return FacadeResponse::json($lectureObj);
        }

        return FacadeResponse::json("No data found");

    }

    public function getLecturelistByChapterId($chapter_id) {
        $lectures = LectureVideo::where('chapter_id', $chapter_id)->get();

        for($i = 0; $i < count($lectures); $i++) {
            $lectures[$i]->durations = floor($lectures[$i]->duration / 60 ). ":" . $lectures[$i]->duration % 60;
        }
        return $lectures;
    }
    public function explodeString (Request $request) {
        $demo = LectureVideo::orderBy('id', 'desc')->first();
        $value = explode( '0', $demo->code);
        return end($value);
    }

    public function deleteLectureVideo (Request $request) {

        $response = new ResponseObject;
        $lectureVideo = LectureVideo::find($request->id);
        if ($lectureVideo) {
            if(file_exists('uploads/lecture_videos/'.$lectureVideo->url)){
                unlink('uploads/lecture_videos/'.$lectureVideo->url);
                unlink('uploads/thumbnails/'.$lectureVideo->thumbnail);
            }
        }
        $deleted = $lectureVideo->delete();
        $response->status = $response::status_ok;
        $response->messages = "Successfully deleted";
        $response->result = $deleted;
        return FacadeResponse::json($response);
    }


    public function updateLectureVideo (Request $request) {
        $response = new ResponseObject;

        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'id' => 'required',
            'title' => 'required|max:250',
            'title_bn' => 'required|max:250',
            'price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'status' => 'required|string'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        $lecture = LectureVideo::where('id', $request->id)->first();
        $lecture->update([
            'title' => $request->title,
            'title_bn' => $request->title_bn,
            'tutor_name' => $request->tutor_name,
            'tutor_info' => $request->tutor_info,
            'description' => $request->description,
            'sequence' => $request->sequence,
            'price' => $request->price,
            'youtube_video_id' => $request->youtube_video_id,
            'isFree' => $request->price > 0 ? false : true
        ]);
        $this->getLecturesNumberIsFreeByParams($lecture->isFree);

        $response->status = $response::status_ok;
        $response->messages = "Lecture video has been updated";
        return FacadeResponse::json($response);

    }



    public function storeLectureVideo (Request $request) {
        $response = new ResponseObject;

        $formData = json_decode($request->data, true);
        $validator = Validator::make($formData, [
            'title' => 'required|max:100',
            'description' => 'max:300',
            'course_id' => 'required',
            'subject_id' => 'required',
            'chapter_id' => 'required',
            'price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'status' => 'required|string',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }

        if ($request->hasFile('lectureVideo') && $request->hasFile('thumbnail')) {

            $lectureVideo = $request->file('lectureVideo');
            $thumbnail = $request->file('thumbnail');
            $time = time();
            $lectureVideoName = "Lvideo".$time.'.'.$lectureVideo->getClientOriginalExtension();
            $thumnailName = "Lthumbnail".$time.'.'.$thumbnail->getClientOriginalExtension();

            $destinationVideo = 'uploads/lecture_videos';
            $destinationThumbnail = 'uploads/thumbnails';
            $lectureVideo->move($destinationVideo,$lectureVideoName);
            $thumbnail->move($destinationThumbnail,$thumnailName);

            $getID3 = new \getID3;
            // $videoInfo = $getID3->analyze('/home/bacbonschool/api.bacbonschool.com/uploads/lecture_videos/'.$lectureVideoName);
            $videoInfo = $getID3->analyze('uploads/lecture_videos/'.$lectureVideoName);
            $lectureSequence = 1;
            $lastLecture = LectureVideo::where('chapter_id', $request->chapter_id)->orderBy('id', 'desc')->first();
            if ($lastLecture) {
                $ar = explode( '0', $lastLecture->code);
                $lectureSequence = end($ar) + 1;
            }
            $code_number = str_pad( $lectureSequence, 4, "0", STR_PAD_LEFT );

            $lecture = (array)[
                "course_id" => $formData["course_id"],
                "subject_id" => $formData["subject_id"],
                "chapter_id" => $formData["chapter_id"],
                "title" => $formData["title"],
                "description" => $formData["description"],
                "price" => $formData["price"],
                "url" =>  $lectureVideoName,
                "thumbnail" => "http://".$_SERVER['HTTP_HOST'].'/uploads/thumbnails/'.$thumnailName,
                "status" =>  $formData["status"],
                "code" =>  'LC'.$formData['chapter_id'].'0'.$code_number ,
                "isFree" => $formData['price'] > 0 ? false : true,
                "duration" => $videoInfo['playtime_seconds']
            ];

            $lecture = LectureVideo::create($lecture);

            broadcast(new LectureCreated($lecture))->toOthers();
            $this->getLecturesNumberByCourse($lecture->course_id);
            $this->getLecturesSumForLMSByCourse($lecture->course_id);
            $this->getLecturesNumberIsFreeByParams($lecture->isFree);
            $response->status = $response::status_ok;
            $response->messages = "Lecture video has been created";
            $response->result = $lecture;
            return FacadeResponse::json($response);
        } else {
            $response->status = $response::status_fail;
            $response->messages = "Video or Thumbnail is missing";
            return FacadeResponse::json($response);
        }

    }


    public function getQuestionsByPostMethod (Request $request) {
        $questions = [];
        $response = new ResponseObject;

        $validator = Validator::make($request->all(), [
            'subject_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        if ($request->chapter_id != null) {
            if ($request->lecture_id != null) {
                $questions = LectureExamQuestion::where('exam_id', $request->id)
                ->join('lecture_questions', 'lecture_exam_questions.question_id', 'lecture_questions.id')
                ->orderBy('lecture_exam_questions.id', 'asc')
                ->get();
            } else {
                $questions = ChapterExamQuestion::where('exam_id', $request->id)
                ->join('chapter_questions', 'chapter_exam_questions.question_id', 'chapter_questions.id')
                ->orderBy('chapter_exam_questions.id', 'asc')
                ->get();
            }
        } else {
            $questions = SubjectExamQuestion::where('exam_id', $request->id)
            ->join('subject_questions', 'subject_exam_questions.question_id', 'subject_questions.id')
            ->orderBy('subject_exam_questions.id', 'asc')
            ->get();
        }
        return $questions;
    }
    public function updateExamGeneric (Request $request) {

        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'id' => 'required',
            'subject_id' => 'required',
            'exam_name' => 'required|string|max:250|min:5',
            'duration' => 'required|numeric',
            'total_mark' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'question_number' => 'required|numeric',
            'status' => 'required|string',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        $question = (array) [
            'subject_id' => $data['subject_id'],
            'exam_name' => $data['exam_name'],
            'duration' => $data['duration'],
            'total_mark' => $data['total_mark'],
            'question_number' => $data['question_number'],
            'positive_mark' => $data['positive_mark'],
            'negative_mark' => $data['negative_mark'],
            'status' => $data['status']
        ];
        if ($data['chapter_id'] != null) {
            if ($data['lecture_id'] != null) {
                $exam = LectureExam::where('id', $data['id'])->update($question);
            } else {
                $exam = chapterExam::where('id', $data['id'])->update($question);
            }
        } else {
            $exam = SubjectExam::where('id', $data['id'])->update($question);
        }
        $response->status = $response::status_ok;
        $response->messages = "Exam has been updated";
        $response->result = $exam;
        return FacadeResponse::json($response);

    }

    public function storeExamGeneric (Request $request) {

        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'subject_id' => 'required',
            'exam_name' => 'required|string|max:250|min:5',
            'duration' => 'required|numeric',
            'total_mark' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'question_number' => 'required|numeric',
            'status' => 'required|string',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        if ($data['chapter_id'] != null) {
            if ($data['lecture_id'] != null) {
                $exam = LectureExam::create($data);
            } else {
                $exam = chapterExam::create($data);
            }
        } else {
            $exam = SubjectExam::create($data);
        }
        $response->status = $response::status_ok;
        $response->messages = "Exam has been created";
        $response->result = $exam;
        return FacadeResponse::json($response);


    }

    public function storeLectureExam (Request $request) {

        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'subject_id' => 'required',
            'chapter_id' => 'required',
            'lecture_id' => 'required',
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
            $response->messages = $validator->errors()->first();
            // $response->messages = "Please fill up all required fields";
            return FacadeResponse::json($response);
        }

        $exam = LectureExam::create($data);
        $response->status = $response::status_ok;
        $response->messages = "Exam has been created";
        $response->result = $exam;
        return FacadeResponse::json($response);

    }
    public function storeLectureQuestions (Request $request) {

        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'subject_id' => 'required',
            'chapter_id' => 'required',
            'lecture_id' => 'required',
            'exam_id' => 'required',
            'question' => 'required|string',
            'option1' => 'required|string',
            'option2' => 'required|string',
            'option3' => 'required|string',
            'option4' => 'required|string'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            // $response->messages = "Please fill up all required fields";
            return FacadeResponse::json($response);
        }
        $data['status'] = "Available";

        // $question = (array)[
        //     "subject_id" => $data['subject_id'],
        //     "chapter_id" => $data['chapter_id'],
        //     "lecture_id" => $data['lecture_id'],
        //     "exam_id" => $data['exam_id'],
        //     "question" => $data['question'],
        //     "option1" => $data['option1'],
        //     "option2" => $data['option2'],
        //     "option3" => $data['option3'],
        //     "option4" => $data['option4'],
        //     "option5" => $data['option5'] ?  $data['option5'] : null,
        //     "option6" => $data['option6'] ?  $data['option6'] : null,
        //     "correct_answer" => $data['correct_answer'] ? $data['correct_answer'] : null,
        //     "correct_answer2" => $data['correct_answer2'] ? $data['correct_answer2'] : null,
        //     "correct_answer3" => $data['correct_answer3'] ? $data['correct_answer3'] : null,
        //     "correct_answer4" => $data['correct_answer4'] ? $data['correct_answer4'] : null,
        //     "correct_answer5" => $data['correct_answer5'] ? $data['correct_answer5'] : null,
        //     "correct_answer6" => $data['correct_answer6'] ? $data['correct_answer6'] : null,
        //     "status" => $data['status'],
        //  ];


        $lectureQue = LectureQuestion::create($data);


        $examQuestion = (array)[
            "subject_id" => $data['subject_id'],
            "chapter_id" => $data['chapter_id'],
            "lecture_id" => $data['lecture_id'],
            "exam_id" => $data['exam_id'],
            "question_id" => $lectureQue->id,
            "status" => $data['status']
         ];
        $this->storeLectureExamQuestions($examQuestion);

        $response->status = $response::status_ok;
        $response->messages = "Question has been created";
        $response->result = $lectureQue;

        return FacadeResponse::json($response);
    }

    public function storeLectureExamQuestions ($data) {
        return LectureExamQuestion::create($data);
    }



    public function getLectureExamQuestionsById(Request $request, $examId, $pageSize) {

        if (Session::get('session_rand')) {
            if((time() - Session::get('session_rand') > 3600)) {
                Session::put('session_rand', time());
            }
        }else{
            Session::put('session_rand', time());
        }


        if ($request->type == "review") {
            $quiz = ReviewExam::where('id', $examId)
            ->select(
                "id",
                "exam_name",
                "question_number",
                "total_mark",
                "positive_mark",
                "negative_mark")
            ->first();
            $examIds = ReviewExamDetail::where('review_exam_id', $examId)->pluck('lecture_exam_id')->toArray();
            $questions = LectureExamQuestion::whereIn('lecture_exam_questions.exam_id', $examIds)
            ->inRandomOrder(time())->limit($quiz->question_number)
            ->join('lecture_questions', 'lecture_exam_questions.question_id', 'lecture_questions.id')
            ->select('lecture_questions.*', 'lecture_exam_questions.exam_id as exam_id')
            ->limit($pageSize)
            ->get();
            $obj = (Object) [
                "data" => $questions,
                "submission_url" => "api/submitReviewExamResult"
            ];

            return FacadeResponse::json($obj);
        }


        $questions = LectureExamQuestion::where('exam_id', $examId)
        ->join('lecture_questions','lecture_exam_questions.question_id','=', 'lecture_questions.id')
        ->select(
            'lecture_questions.id as id',
            'lecture_questions.question as question',
            'lecture_questions.option1 as option1',
            'lecture_questions.option2 as option2',
            'lecture_questions.option3 as option3',
            'lecture_questions.option4 as option4',
            'lecture_questions.option5 as option5',
            'lecture_questions.option6 as option6',
            'lecture_questions.explanation as explanation',
            'lecture_questions.explanation_text as explanation_text',
            'lecture_questions.correct_answer as correct_answer',
            'lecture_questions.correct_answer2 as correct_answer2',
            'lecture_questions.correct_answer3 as correct_answer3',
            'lecture_questions.correct_answer4 as correct_answer4',
            'lecture_questions.correct_answer5 as correct_answer5',
            'lecture_questions.correct_answer6 as correct_answer6'
            )

        ->inRandomOrder(Session::get('session_rand'))
        ->limit($pageSize)
        ->get();
        $obj = (Object) [
            "data" => $questions,
            "submission_url" => "api/submitLectureExamResult"
        ];

        return FacadeResponse::json($obj);

    }



    // public function getLectureExamQuestionsById(Request $request, $examId, $pageSize) {
    //     if (Session::get('session_rand')) {
    //         if((time() - Session::get('session_rand') > 3600)) {
    //             Session::put('session_rand', time());
    //         }
    //     }else{
    //         Session::put('session_rand', time());
    //     }


    //     $questions = LectureExamQuestion::where('exam_id', $examId)
    //     ->join('lecture_questions','lecture_exam_questions.question_id','=', 'lecture_questions.id')
    //     ->select(
    //         'lecture_questions.id as id',
    //         'lecture_questions.question as question',
    //         'lecture_questions.option1 as option1',
    //         'lecture_questions.option2 as option2',
    //         'lecture_questions.option3 as option3',
    //         'lecture_questions.option4 as option4',
    //         'lecture_questions.explanation as explanation',
    //         'lecture_questions.explanation_text as explanation_text',
    //         'lecture_questions.correct_answer as correct_answer')

    //     ->inRandomOrder(Session::get('session_rand'))
    //     ->paginate($pageSize);

    //     return FacadeResponse::json($questions);
    // }
    // public function castInt ($value) {
    //     return (int) $value;
    // }
    // public function getLectureQuestionsSession () {
    //     if(Session::flush()) {
    //         return "Success";
    //     } else {
    //         return "Failed";
    //     }
    // }

    public function buyLecture (Request $request, Common $com) {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'lecture_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            // $response->messages = "Please fill up all required fields";
            return FacadeResponse::json($response);
        }
        $isPaymentOccured = PaymentLecture::where('user_id',  $data['user_id'])
        ->where('lecture_id',  $data['lecture_id'])->where('isPaid', true)->count();
        if($isPaymentOccured) {
            $response->status = $response::status_fail;
            $response->messages = "Payment has been done before for this lecture";
            return FacadeResponse::json($response);
        } else {

        $lecture = LectureVideo::where('id', $data['lecture_id'])->first();
        $lecturePrice = $lecture->price - $data['discount'];
        $current_date_time = Carbon::now();
        // return $current_date_time;
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

        $due = $lastDue + ($lecturePrice - $data['amount']) - $lastBalance;
        $balance = $lastBalance + ($data['amount'] - $lecturePrice) - $lastDue;
        } else {
            $due = $lecturePrice - $data['amount'];
            $balance = $data['amount'] - $lecturePrice;
        }

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

        //  Private Method Here
        $result = $com->lecturePaymentGlobal($data, $payment, $lecture);
            $response->status = $result? $response::status_ok : $response::status_fail;
            $response->messages = $result? "This is lecture has been bought": "failed";
            return FacadeResponse::json($response);
        }
    }
    public function purchaseLecture (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'lecture_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        $current_date_time = Carbon::now();

        $isPrevioslyBought = PaymentLecture::where('user_id', $request->user_id)->where('lecture_id', $request->lecture_id)->count();
        if ($isPrevioslyBought) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this Lecture";
            return FacadeResponse::json($response);
        }

        $user = User::where('id', $request->user_id)->first();
        $lecture = LectureVideo::where('id', $request->lecture_id)->first();
        if ( ($user->balance + $request->amount +  $request->discount) < $lecture->price) {
            $response->status = $response::status_fail;
            $response->messages = "insufficient balance";
            return FacadeResponse::json($response);
        } else {
            $paying_amount = $request->amount;
            $amount_from_balance = 0;
            $amount_to_balance = 0;
            if ( ( $request->amount + $request->discount ) <  $lecture->price) {
                $amount_from_balance = $lecture->price - ( $request->amount + $request->discount );
                $balance = $user->balance - $amount_from_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            } else {
                $amount_to_balance =  ( $request->amount + $request->discount) - $lecture->price ;
                $balance = $user->balance + $amount_to_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            }
            $payment = Payment::create([
                'user_id' => $request->user_id,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'amount_from_balance' => $amount_from_balance,
                'amount_to_balance' => $amount_to_balance,
                'payment_date' => $current_date_time,
                'discount' => $request->discount
            ]);

            $paymentLecture = PaymentLecture::create([
                'payment_id' => $payment->id,
                'user_id' => $request->user_id,
                'course_id' => $lecture->course_id,
                'subject_id' => $lecture->subject_id,
                'chapter_id' => $lecture->chapter_id,
                'lecture_id' => $lecture->id,
                'amount' => $lecture->price
            ]);
            $response->status = $response::status_ok;
            $response->messages = "Successfully Bought";
            return FacadeResponse::json($response);
        }
    }

    public function copyLecturesHSC () {
        $lectures = LectureVideo::where('lecture_videos.course_id', 14)
        ->join('subjects', 'lecture_videos.subject_id', 'subjects.id')
        ->join('chapters', 'lecture_videos.chapter_id', 'chapters.id')
        ->select(
            'lecture_videos.id as id',
            'subjects.name as subject_name',
            'chapters.name as chapter_name',
            'lecture_videos.url as url',
            'lecture_videos.full_url as full_url',
            'lecture_videos.thumbnail as thumbnail'
            )
            ->get();
            $count = 0;
            $fountList= [];
            $notFountList= [];
            $notFound = 0;
            foreach ($lectures as $lecture) {
                // return FacadeResponse::json($this->copyLectures($lecture));
                if ($this->copyLectures($lecture)) {
                    $count++;
                    $fountList[] = $lecture;
                } else {
                    $notFountList[] = $lecture;
                    $notFound++;
                }
                // $this->deleteOldLecture($lecture);
            }
            $obj = (Object) [
                "found" => $fountList,
                "notFound" => $notFountList,
                ];
            return FacadeResponse::json($obj);
            $numberOfLecture = count($lectures);
            return FacadeResponse::json($numberOfLecture . ' : '. $count . ' : '. $notFound);
    }
    public function deleteLecturesFile ($id) {
        $lectures = LectureVideo::where('course_id', $id)->get();
        foreach ($lectures as $lecture) {
            $this->deleteOldLecture($lecture);
        }
        return FacadeResponse::json(true);
    }
    public function deleteOldLecture ($lecture) {
        // if(!File::exists('uploads/lecture_videos/'.$lecture->url)) {
            unlink('uploads/lecture_videos/'.$lecture->url);
        // }
        return true;
    }
    public function copyLectures ($lecture) {
        if (File::exists('uploads/lecture_videos/'.$lecture->url) ) {
            return true;
        }
        return  false;
        $arr = explode(".",$lecture->url);

        $chapterName = str_replace(' ', '_', $lecture->chapter_name);
        $subjectName = str_replace(' ', '_', $lecture->subject_name);

        // $destinationThumbnail = 'uploads/SSC/English_2nd/'.$chapterName.'/thumbnails';
        $destinationThumbnail = 'uploads/Unit_C/'. $subjectName. '/'.$chapterName.'/thumbnails';
        $destinationVideo = 'uploads/Unit_C/'. $subjectName. '/'.$chapterName.'/lectures';

        $full_url = 'https://bacbonschool.s3.ap-south-1.amazonaws.com/uploads/Unit_C/'. $subjectName .'/'. $chapterName. '/lectures/'. $arr[0]. '/index.m3u8';

        if(!File::exists($destinationVideo)) {
            File::makeDirectory($destinationThumbnail, $mode = 0777, true, true);
            File::makeDirectory($destinationVideo, $mode = 0777, true, true);
        }

        copy('uploads/lecture_videos/'.$lecture->url, $destinationVideo.'/'.$lecture->url);

        // if(!File::exists($destinationThumbnail.'/'.$arr[count($arr) - 1])) {
        //     copy('uploads/thumbnail/'.$arr[count($arr) - 1], $destinationThumbnail.'/'.$arr[count($arr) - 1]);
        // }
        LectureVideo::where('id', $lecture->id)->update([
            "full_url" => $full_url
        ]);


        return true;

    }
    public function getLecturesNumberForLMS () {
        $lectures = [];
        $lectureCount = LectureVideo::join('courses', 'lecture_videos.course_id', 'courses.id')
                        ->select('courses.id',
                            DB::raw('count(*) as total')
                            )
                            ->groupBy('courses.id')
                        ->get();

                        foreach ($lectureCount as $key=>$element) {
                            switch ($element->id) {
                                case 1:
                                    $lectures[] = (Object) [
                                        'name' => 'SSC',
                                        'total' => $element->total
                                    ];
                                break;
                                case 2:
                                    $lectures[] = (Object) [
                                        'name' => 'HSC',
                                        'total' => $element->total
                                    ];
                                break;
                                case 3:
                                    $lectures[] = (Object) [
                                        'name' => 'JSC',
                                        'total' => $element->total
                                    ];
                                break;
                                case 5:
                                    $lectures[] = (Object) [
                                        'name' => 'Medical',
                                        'total' => $element->total
                                    ];
                                break;
                                case 12:
                                    $lectures[] = (Object) [
                                        'name' => 'University Unit A',
                                        'total' => $element->total
                                    ];
                                break;
                                case 13:
                                    $lectures[] = (Object) [
                                        'name' => 'University Unit B',
                                        'total' => $element->total
                                    ];
                                break;
                                case 27:
                                    $lectures[] = (Object) [
                                        'name' => 'University Unit C',
                                        'total' => $element->total
                                    ];
                                break;
                                case 15:
                                    $lectures[] = (Object) [
                                        'name' => 'University Unit D',
                                        'total' => $element->total
                                    ];
                                break;
                            }
                        }
        return FacadeResponse::json($lectures);
    }

    public function getLecturesExamNumberForLMS () {
        $lectureCount = LectureExam::join('courses', 'lecture_exams.course_id', 'courses.id')
                        ->select('courses.name',
                            DB::raw('count(*) as total')
                            )
                            ->groupBy('courses.name')
                        ->get();

        return FacadeResponse::json($lectureCount);
    }
    public function getLecturesNumberIsFreeByParams ($isFree) {
        $lectureFree = LectureVideo::where('isFree', $isFree)
                            ->select('isFree',
                            DB::raw('count(*) as total')
                            )
                            ->groupBy('isFree')
                        ->first();

        $notificationResult = (Object) [
            'isFree' => $lectureFree->isFree,
            'total' => $lectureFree->total,
        ];
        broadcast(new LectureFree($notificationResult))->toOthers();
        // broadcast(new LectureFree($lectureFree))->toOthers();
        return FacadeResponse::json($lectureFree);
    }
    public function getLecturesNumberByFree () {
        $lectureFree = LectureVideo::select('isFree',
                            DB::raw('count(*) as total')
                            )
                            ->groupBy('isFree')
                        ->get();

        return FacadeResponse::json($lectureFree);
    }

    public function getLecturesSumForLMS () {
        $lectures = [];
        $lectureSum = LectureVideo::join('courses', 'lecture_videos.course_id', 'courses.id')
                        ->select('courses.id',
                            DB::raw('sum(lecture_videos.duration) as total')
                            )
                            ->groupBy('courses.id')
                        ->get();

        foreach ($lectureSum as $key=>$element) {
            switch ($element->id) {
                case 1:
                    $lectures[] = (Object) [
                        'name' => 'SSC',
                        'total' => $element->total
                    ];
                break;
                case 2:
                    $lectures[] = (Object) [
                        'name' => 'HSC',
                        'total' => $element->total
                    ];
                break;
                case 3:
                    $lectures[] = (Object) [
                        'name' => 'JSC',
                        'total' => $element->total
                    ];
                break;
                case 5:
                    $lectures[] = (Object) [
                        'name' => 'Medical',
                        'total' => $element->total
                    ];
                break;
                case 12:
                    $lectures[] = (Object) [
                        'name' => 'University Unit A',
                        'total' => $element->total
                    ];
                break;
                case 13:
                    $lectures[] = (Object) [
                        'name' => 'University Unit B',
                        'total' => $element->total
                    ];
                break;
                case 27:
                    $lectures[] = (Object) [
                        'name' => 'University Unit C',
                        'total' => $element->total
                    ];
                break;
                case 15:
                    $lectures[] = (Object) [
                        'name' => 'University Unit D',
                        'total' => $element->total
                    ];
                break;
            }
        }
        return FacadeResponse::json($lectures);
    }
    public function getLecturesSumForLMSByCourse ($course_id) {
        $lectureSum = LectureVideo::join('courses', 'lecture_videos.course_id', 'courses.id')
                        ->where('courses.id', $course_id)
                        ->select('courses.name',
                            DB::raw('sum(lecture_videos.duration) as total')
                            )
                            ->groupBy('courses.name')
                        ->first();

            $notificationResult = (Object) [
                'name' => $lectureSum->name,
                'total' => $lectureSum->total,
            ];
        broadcast(new LectureSum($notificationResult))->toOthers();

        return FacadeResponse::json($lectureSum);
    }
    public function getLecturesNumberByCourse ($course_id) {
        $lectureCount = LectureVideo::where('course_id', $course_id)->count();
        $course = new CourseController();
        $courseDetails = $course->courseDetail($course_id);
        $notificationResult = (Object) [
            'name' => $courseDetails->name,
            'total' => $lectureCount,
        ];
        broadcast(new LectureCount($notificationResult))->toOthers();

        return FacadeResponse::json($lectureCount);
    }


    public function broadcastLecture($id){
        // $lecture = LectureVideo::find($id);
        broadcast(new LectureCreated('Pusher Message'))->toOthers();
        return FacadeResponse::json('Pusher Message');
    }

    public function lecture_url_rename () {
        $lectures = LectureVideo::whereNotNull('full_url')->get();
        foreach ($lectures as $lecture) {

            $url = str_replace("https://bacbonschool.s3.ap-south-1.amazonaws.com", "http://api.bacbonschool.com", $lecture->full_url);
            $url = str_replace("/index.m3u8", ".mp4", $url);


            LectureVideo::where('id', $lecture->id)->update([
                "url" => $url
            ]);
        }

        return FacadeResponse::json("Ok");
    }

    public function getAudioBooklistByCourseId ($courseId, $pageSize, $pageNumber) {
        $audioCount = LectureVideo::where('course_id', $courseId)->whereNotNull('audio_book')->count();
        $audios = LectureVideo::where('course_id', $courseId)->whereNotNull('audio_book')
        ->select('id', 'title', 'title_bn', 'title_jp', 'description', 'audio_book', 'audio_book_aws', 'thumbnail', 'duration', 'code', 'course_id' )
        ->skip($pageSize* ($pageNumber -1))->limit($pageSize)->get();
        $obj = (Object) [
            "total_page" => ceil($audioCount/ $pageSize),
            "records" => $audios
            ];
        return FacadeResponse::json($obj);
    }

    public function getLectureExamDetailsByExamId ($id) {
        $exam = LectureExam::where('id', $id)->first();
         $questions = LectureExamQuestion::where('exam_id', $id)
         ->join('lecture_questions', 'lecture_exam_questions.question_id' ,'lecture_questions.id')
         ->select('lecture_questions.*')
         ->limit($exam->question_number)
         ->inRandomOrder()

         ->get();
         $exam->questions = $questions;
        return $exam;
    }

    public function uploadVideoDownloadableUrl(Request $request) {
      $items =  $request->items;
      $notFoundVideo = [];
      $count = 0;
      foreach($items as $item) {
        $video = LectureVideo::where('id', $item['id'])->first();

        if(is_null($video)){
            $notFoundVideo[] = $item['id'];
        } else {
            $video->update([
                'download_url' => $item['download_url'],
                'is_downloadable' => true,
         ]);
         $count++;
        }
      }

      $obj = (Object) [
        "status" => "Ok",
        "messages" => "Url Upldated Successfully",
        "updated" => $count,
        "not_found" => $notFoundVideo
        ];
      return FacadeResponse::json($obj);

    }


    public function keepLectureVideo (Request $request) {

        DB::beginTransaction();
        try{


        $response = new ResponseObject;
        $course_id=$request->course_id;
        $subject_id=$request->subject_id;
        $data = $request->data;

        $chapterCount = 0;
        $count = 0;
        foreach ($data as $dt) {
            $chapterCount++;
            $chapterDetails = Chapter::where('id', $dt['chapter_id'])->first();
            $chapterName = str_replace(' ', '_', $chapterDetails->name);
            $destinationVideo = 'uploads/Unit_B/English/' . $chapterName . '/lectures';
            $destinationThumbnail = 'uploads/Unit_B/English/' . $chapterName . '/thumbnails';

            if(!File::exists($destinationVideo)) {
                File::makeDirectory($destinationVideo, $mode = 0777, true, true);
            }
            for ($lectureCount=1; $lectureCount <= $dt['lectures']; $lectureCount++) {

                $time = time();
                $lectureVideoName = $time.$lectureCount.'.mp4';

                copy('Unit-B/English/'.$dt['chap_name'].'/'.$lectureCount.'.mp4', $destinationVideo.'/'.$lectureVideoName);
                $getID3 = new \getID3;
                $videoInfo = $getID3->analyze($destinationVideo.'/'.$lectureVideoName);

                $lectureSequence = $lectureCount;

                $lastLecture = LectureVideo::where('chapter_id', $dt['chapter_id'])->orderBy('id', 'desc')->first();
//                if ($lastLecture) {
//                    $ar = explode( '0', $lastLecture->code);
//                    $lectureSequence = end($ar) + 1;
//                }
                $code_number = str_pad( $lectureSequence, 4, "0", STR_PAD_LEFT );

                $part = $dt['lectures'] >1 ? ' part '. $lectureCount : '';
                $part_bn = $dt['lectures'] >1 ? ' পর্ব '. $lectureCount : '';
                
                $lecture = (array)[
                    "course_id" => $course_id,
                    "subject_id" => $subject_id,
                    "chapter_id" => $dt["chapter_id"],
                    "title" => $chapterDetails->name . $part,
                    "title_bn" => $chapterDetails->name_bn .  $part_bn,
                    "description" => null,
                    "price" => 0,
                    "url" =>  $lectureVideoName,
                    "full_url" => "https://bacbonschool.s3.ap-south-1.amazonaws.com/".$destinationVideo."/".$time.$lectureCount."/index.m3u8",
                    "thumbnail" => 'http://api.bacbonschool.com/uploads/thumbnails/thumb.png',
                    "status" =>  "Available",
                    "code" =>  'LC'.$dt['chapter_id'].'0'.$code_number ,
                    "isFree" => true,
                    "duration" => $videoInfo['playtime_seconds']
                ];
                $lecture = LectureVideo::create($lecture);
                $count++;
            }

        }

        $response->status = $response::status_ok;
        $response->messages = "Lecture video has been created " .$count;
        $response->result = $lecture;

        DB::commit();
        return FacadeResponse::json($response);

        }catch (\Exception $e ){

            DB::rollback();
            return $e->getMessage();
        }

    }

    public function uploadLectureVideosToS3 (Request $request) {

        DB::beginTransaction();
        try{

            $response   = new ResponseObject;

            $course_id = $request->course_id;
            $subject_id = $request->subject_id;
            $chapter_id = $request->chapter_id;
            $lecture_video_id = $request->lecture_video_id ? $request->lecture_video_id : 0;

            if (!$course_id || !$subject_id || !$chapter_id || !$request->full_url) {
                $response->status = $response::status_fail;
                $response->messages = "Please, Check the details";
                $response->result = [];
                return FacadeResponse::json($response);
            }

            if($lecture_video_id){
                LectureVideo::where('id', $lecture_video_id)->update([
                    "title" => $request->title,
                    "title_bn" => $request->title_bn,
                    "description" => $request->description ?? null,
                    "full_url" => $request->full_url,
                    "duration" => $request->duration ?? 0
                ]);
                $lecture_video = LectureVideo::where('id', $lecture_video_id)->first();
            }
            else
            {
                $lecture_video_count = LectureVideo::where('chapter_id', $chapter_id)->get()->count();

                $code_number = 'LC'.$chapter_id.'0'.$lecture_video_count;
    
                $lecture = (array)[
                    "course_id" => $course_id,
                    "subject_id" => $subject_id,
                    "chapter_id" => $chapter_id,
                    "title" => $request->title,
                    "title_bn" => $request->title_bn,
                    "description" => $request->description ?? null,
                    "price" => 0,
                    "url" => $request->url ?? null,
                    "full_url" => $request->full_url,
                    "thumbnail" => 'http://api.bacbonschool.com/uploads/thumbnails/thumb.png',
                    "status" => "Available",
                    "code" => $code_number,
                    "isFree" => true,
                    "duration" => $request->duration ?? 0
                ];
    
                $lecture_video = LectureVideo::create($lecture);
            }



            TranscoderVideoLog::create([
                "lecture_video_id" => $lecture_video->id ?? 0,
                "url" => $request->url ?? null,
                "full_url" => $request->full_url,
                "download_url" => $request->full_url
            ]);

            $response->status = $response::status_ok;
            $response->messages = "Lecture video has been uploaded successfully";
            $response->result = [];

        DB::commit();
        return FacadeResponse::json($response);

        }catch (\Exception $e ){
            DB::rollback();
            return $e->getMessage();
        }
    }
    
    public function filterLectureVideoList(Request $request)
    {
        $response   = new ResponseObject;

        $course_id = $request->course_id ? $request->course_id : 0; 
        $subject_id = $request->subject_id ? $request->subject_id : 0; 
        $chapter_id = $request->chapter_id ? $request->chapter_id : 0; 

        $lecture_video = LectureVideo::select('lecture_videos.*')
        ->when($course_id, function ($query, $course_id) {
            return $query->where('course_id', $course_id);
        })
        ->when($subject_id, function ($query, $subject_id) {
            return $query->where('subject_id', $subject_id);
        })
        ->when($chapter_id, function ($query, $chapter_id) {
            return $query->where('chapter_id', $chapter_id);
        })
        ->get();

        $response->status = $response::status_ok;
        $response->messages = "Lecture video List";
        $response->result = $lecture_video;
        return FacadeResponse::json($response);
    }

    public function getLectureExamForVAB($id){
        $exam = LectureExam::where('id', $id)->select('exam_name as chapter_name')->first();
        $questions = LectureExamQuestion::where('exam_id', $id)
        ->join('lecture_questions', 'lecture_exam_questions.question_id' ,'lecture_questions.id')
        ->select('lecture_questions.question','lecture_questions.option1','lecture_questions.option2','lecture_questions.option3','lecture_questions.option4','lecture_questions.correct_answer','lecture_questions.correct_answer2','lecture_questions.correct_answer3','lecture_questions.correct_answer4')
        ->get();

        $exam->questions = $questions;
        return FacadeResponse::json($exam);
    }

}