<?php

namespace App\Http\Controllers;

use App\Feedback;
use Validator;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Response as FacadeResponse;

use App\Http\Helper\ResponseObject;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function sendSms(Request $request) {
    //     $to = "01740131090";
    //     $token = "01ea8bf427b9f797cc05e77e783e7d63";
    //     $message = $request->message;

    //     $url = "http://api.greenweb.com.bd/api.php";


    //     $data= array(
    //     'to'=>"$to",
    //     'message'=>"$message",
    //     'token'=>"$token"
    //     ); // Add parameters in key value
    //     $ch = curl_init(); // Initialize cURL
    //     curl_setopt($ch, CURLOPT_URL,$url);
    //     curl_setopt($ch, CURLOPT_ENCODING, '');
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
    //     $smsresult = curl_exec($ch);
    //     return $smsresult;

    // }

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
    public function store(Request $request)
    {
        $response = new ResponseObject;

        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'comment' => 'required|max:255',
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            // $response->messages = "Please fill up all required fields";
            return FacadeResponse::json($response);
        }
        try {
            $feedback = Feedback::create($data);
            $response->status = $response::status_ok;
            $response->messages = "Thank you. We have recieved your feedback.";
            $response->result = $feedback;
            return FacadeResponse::json($response);
        } catch (Exception $e) {
            $response->status = $response::status_fail;
            $response->messages = "Comment failed";
            return FacadeResponse::json($response);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function show(Feedback $feedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function edit(Feedback $feedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Feedback $feedback)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(Feedback $feedback)
    {
        //
    }
}
