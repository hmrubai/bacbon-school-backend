<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\AppRating;
use Illuminate\Http\Request;

use Validator;

class AppRatingController extends Controller
{
    
    public function saveAppRating (Request $request) {

        $response = new ResponseObject;

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'rating' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        try {

            $rating = AppRating::create($request->all());

            $response->status = $response::status_ok;
            $response->messages = "Thank you for your rating";
            $response->result = $rating;
            return FacadeResponse::json($response);
            
        } catch (Exception $e) {
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);
        }



    }
}