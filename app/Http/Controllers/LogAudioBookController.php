<?php

namespace App\Http\Controllers;

use App\LogAudioBook;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

class LogAudioBookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    public function storeAudioListenHistory (Request $request)
    {
        
        $response = new ResponseObject;
        $data = $request->all();

        try {
            foreach ($data as $item) {
                $isSubmitted = LogAudioBook::where('user_id', $item['user_id'])
                ->where('start_time', date('Y-m-d H:i:s', strtotime($item['start_time'])))->count();
                if (!$isSubmitted)
                LogAudioBook::create([
                    'course_id' => $item['course_id'],
                    'subject_id' => $item['subject_id'],
                    'chapter_id' => $item['chapter_id'],
                    'lecture_id' => $item['lecture_id'],
                    'user_id' => $item['user_id'],
                    'duration' => $item['duration'],
                    'start_time' => date('Y-m-d H:i:s', strtotime($item['start_time'])),
                    'end_time' =>date('Y-m-d H:i:s', strtotime($item['end_time']))
                ]);
            }


            $response->status = $response::status_ok;
            $response->messages = "Thank you for listening";

        } catch (Exception $e) {

            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
    
        }


        return FacadeResponse::json($response);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\LogAudioBook  $logAudioBook
     * @return \Illuminate\Http\Response
     */
    public function show(LogAudioBook $logAudioBook)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LogAudioBook  $logAudioBook
     * @return \Illuminate\Http\Response
     */
    public function edit(LogAudioBook $logAudioBook)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LogAudioBook  $logAudioBook
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LogAudioBook $logAudioBook)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LogAudioBook  $logAudioBook
     * @return \Illuminate\Http\Response
     */
    public function destroy(LogAudioBook $logAudioBook)
    {
        //
    }
}
