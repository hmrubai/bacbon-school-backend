<?php

namespace App\Http\Controllers;

use App\LectureViewLog;
use App\LectureVideo;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Validator;
use DB;
use Carbon\Carbon;
use App\Events\LectureView;

class LectureViewLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTodayVideos()
    {
        $date = Date('Y-m-d');
        $lectureViews = LectureViewLog::where(
            'created_at', '>=', $date // Carbon::now()->subDays(1)->toDateTimeString()
        )
        ->select('lecture_id', DB::raw('count(*) as total'))
        ->groupBy('lecture_id')
        ->orderBy('total', 'desc')
        ->get();
        foreach ($lectureViews as $lv) {
            $lecture = LectureVideo::where('id', $lv->lecture_id)->first();
            $lv['lecture'] =  $lecture;
        }
        return FacadeResponse::json($lectureViews);
        // return FacadeResponse::json( Carbon::now()->subDays(0)->toDateTimeString());

    }

    public function getTopVideosByDate( $date)
    {
        $lectureViews = LectureViewLog::whereDate('created_at', '=', $date.' 00:00:00')
        // where(
        //     'created_at', '>=', $date->subDays(1)->toDateTimeString()
        // )
        ->select('lecture_id', DB::raw('count(*) as total'))
        ->groupBy('lecture_id')
        ->get();
        foreach ($lectureViews as $lv) {
            $lecture = LectureVideo::where('id', $lv->lecture_id)->first();
            $lv['lecture'] =  $lecture;
        }
        return FacadeResponse::json($lectureViews);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeLectureLog(Request $request)
    {
        $response = new ResponseObject;
        $validator = Validator::make($request->all(), LectureViewLog::$rules);
        if ($validator->fails()) {
            $obj = (object) [
                "status" => false,
                "message" => $validator->errors()->first()
            ];
            return FacadeResponse::json($obj);
        }
        $lectureView = LectureViewLog::create($request->all());
        broadcast(new LectureView($lectureView))->toOthers();
        $response->status = $response::status_ok;
        $response->messages = "You have watched this lecture.";
        $response->result = $lectureView;
        return FacadeResponse::json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LectureViewLog  $lectureViewLog
     * @return \Illuminate\Http\Response
     */
    public function show(LectureViewLog $lectureViewLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LectureViewLog  $lectureViewLog
     * @return \Illuminate\Http\Response
     */
    public function edit(LectureViewLog $lectureViewLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LectureViewLog  $lectureViewLog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LectureViewLog $lectureViewLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LectureViewLog  $lectureViewLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(LectureViewLog $lectureViewLog)
    {
        //
    }
}
