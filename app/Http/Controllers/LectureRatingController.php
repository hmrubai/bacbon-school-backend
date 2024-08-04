<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Validator;
use App\LectureRating;
use App\LectureVideo;
use Illuminate\Http\Request;

class LectureRatingController extends Controller
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
    public function store(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'lecture_id' => 'required',
            'user_id' => 'required',
            'rating' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }
        $isRated = LectureRating::where('user_id', $request->user_id)->where('lecture_id', $request->lecture_id)->first();

        if (empty($isRated)) {
            $rating = LectureRating::create($data);
        } else {
            $rating = $isRated->update($data);
        }
        $lectureRating = LectureRating::where('lecture_id', $request->lecture_id)->avg('rating');
        LectureVideo::where('id', $request->lecture_id)->update(["rating" => $lectureRating]);
        $response->status = $response::status_ok;
        $response->messages = "Thank you for your rating";
        $response->result = $lectureRating;

        return FacadeResponse::json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LectureRating  $lectureRating
     * @return \Illuminate\Http\Response
     */
    public function show(LectureRating $lectureRating)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LectureRating  $lectureRating
     * @return \Illuminate\Http\Response
     */
    public function edit(LectureRating $lectureRating)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LectureRating  $lectureRating
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LectureRating $lectureRating)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LectureRating  $lectureRating
     * @return \Illuminate\Http\Response
     */
    public function destroy(LectureRating $lectureRating)
    {
        //
    }
}
