<?php

namespace App\Http\Controllers;

use App\LogScript;
use App\LectureScript;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Carbon\Carbon;
class LogScriptController extends Controller
{



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function storeScriptHistory (Request $request)
    {
        
        $response = new ResponseObject;
        $data = $request->all();

        try {
            foreach ($data as $item) {
                $isSubmitted = LogScript::where('user_id', $item['user_id'])
                ->where('start_time', date('Y-m-d H:i:s', strtotime($item['start_time'])))->count();
                $script = LectureScript::join('lecture_videos', 'lecture_scripts.lecture_id', 'lecture_videos.id')->where('lecture_scripts.id', $item['script_id'])->first();
                if (is_null($script)) {

                    $response->status = $response::status_fail;
                    $response->messages = "No Script found";

                    return FacadeResponse::json($response);

                }
                if (!$isSubmitted) {
                    LogScript::create([
                        'course_id' => $script->course_id,
                        'subject_id' => $script->subject_id,
                        'chapter_id' => $script->chapter_id,
                        'lecture_id' => $script->lecture_id,
                        'script_id' => $item['script_id'],
                        'user_id' => $item['user_id'],
                        'start_time' => date('Y-m-d H:i:s', strtotime($item['start_time'])),
                        'end_time' =>date('Y-m-d H:i:s', strtotime($item['end_time']))
                    ]);
                }
            }


            $response->status = $response::status_ok;
            $response->messages = "Thank you for reading";

        } catch (Exception $e) {

            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
    
        }


        return FacadeResponse::json($response);
    }

    public function saveScriptHistory (Request $request)
    {
        
        $response = new ResponseObject;
      

        try {
         
                $isSubmitted = LogScript::where('user_id', $request->user_id)
                ->where('start_time', date('Y-m-d H:i:s', strtotime($request->start_time)))->count();               
                if (!$isSubmitted) {
                    LogScript::create([
                        'course_id' => $request->course_id,
                        'subject_id' => $request->subject_id,
                        'chapter_id' => $request->chapter_id,
                        'lecture_id' => $request->lecture_id,
                        'script_id' => $request->script_id,
                        'user_id' => $request->user_id,
                        'start_time' => Carbon::now(),
                        'end_time' =>date('Y-m-d H:i:s', strtotime($request->end_time))
                    ]);
                }
          


            $response->status = $response::status_ok;
            $response->messages = "Thank you for reading";

        } catch (Exception $e) {

            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
    
        }

        return FacadeResponse::json($response);
    }

    public function getIsDownloaded ($lectureId, $userId) {
        $countLogScript = LogScript::where('lecture_id', $lectureId)->where('user_id', $userId)->count();
        return $countLogScript ? true : false;
    }
  
  
}
