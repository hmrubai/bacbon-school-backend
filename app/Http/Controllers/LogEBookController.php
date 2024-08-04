<?php

namespace App\Http\Controllers;

use App\LogEBook;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

class LogEBookController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
 
    public function storeEBookReadHistory (Request $request)
    {
        
        $response = new ResponseObject;
        $data = $request->all();

        try {
            foreach ($data as $item) {
                $isSubmitted = LogEBook::where('user_id', $item['user_id'])
                ->where('start_time', date('Y-m-d H:i:s', strtotime($item['start_time'])))->count();
                if (!$isSubmitted)
                LogEBook::create([
                    'course_id' => $item['course_id'],
                    'subject_id' => $item['subject_id'],
                    'e_book_id' => $item['e_book_id'],
                    'user_id' => $item['user_id'],
                    'start_time' => date('Y-m-d H:i:s', strtotime($item['start_time'])),
                    'end_time' =>date('Y-m-d H:i:s', strtotime($item['end_time']))
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


    /**
     * Display the specified resource.
     *
     * @param  \App\LogEBook  $logEBook
     * @return \Illuminate\Http\Response
     */
    public function show(LogEBook $logEBook)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LogEBook  $logEBook
     * @return \Illuminate\Http\Response
     */
    public function edit(LogEBook $logEBook)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LogEBook  $logEBook
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LogEBook $logEBook)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LogEBook  $logEBook
     * @return \Illuminate\Http\Response
     */
    public function destroy(LogEBook $logEBook)
    {
        //
    }
}
