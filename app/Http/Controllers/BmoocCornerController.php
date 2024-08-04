<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use App\BmoocCorner;
use Illuminate\Http\Request;
use Validator;
class BmoocCornerController extends Controller
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


    // protected $fillable = ['title', 'description', 'url', 'thumbnail', 'duration'];

    public function store(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($request->all(), [
            'course_id' => 'required',
            'title' => 'required',
            'url' => 'required',
            'duration' => 'required',

        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $time = time();
            $thumnailName = "YCthumbnail".$time.'.'.$thumbnail->getClientOriginalExtension();
            $destinationThumbnail = 'uploads/thumbnails';
            $thumbnail->move($destinationThumbnail,$thumnailName);
            $corner = (array)[
                "course_id" => $request->course_id,
                "title" => $request->title,
                "url" => $request->url,
                "description" => $request->description,
                "duration" => $request->duration,
                "status" => $request->status,
                "thumbnail" => "http://".$_SERVER['HTTP_HOST'].'/uploads/thumbnails/'.$thumnailName
            ];
            $youtubeCorner = BmoocCorner::create($corner);
            $response->status = $response::status_ok;
            $response->messages = "Successfully inserted at youtube corners";
            $response->result = $youtubeCorner;
            return FacadeResponse::json($response);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\BmoocCorner  $bmoocCorner
     * @return \Illuminate\Http\Response
     */
    public function show(BmoocCorner $bmoocCorner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BmoocCorner  $bmoocCorner
     * @return \Illuminate\Http\Response
     */
    public function edit(BmoocCorner $bmoocCorner)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\BmoocCorner  $bmoocCorner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'url' => 'required',
            'duration' => 'required',

        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        $corner = (array)[
            "title" => $request->title,
            "url" => $request->url,
            "description" => $request->description,
            "duration" => $request->duration,
            "status" => $request->status
        ];

        $youCorner = BmoocCorner::where('id', $id)->first();

        if ($request->hasFile('thumbnail')) {
            if($youCorner->thumbnail) {
                unlink($youCorner->thumbnail);
            }
            $thumbnail = $request->file('thumbnail');
            $time = time();
            $thumnailName = "YCthumbnail".$time.'.'.$thumbnail->getClientOriginalExtension();
            $destinationThumbnail = 'uploads/thumbnails';
            $thumbnail->move($destinationThumbnail,$thumnailName);
            $corner['thumbnail'] = "http://".$_SERVER['HTTP_HOST'].'/uploads/thumbnails/'.$thumnailName;
        }
        $youCorner->update($corner);
        $response->status = $response::status_ok;
        $response->messages = "Successfully updated.";
        $response->result = $youCorner;
        return FacadeResponse::json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\BmoocCorner  $bmoocCorner
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response = new ResponseObject;
        $deleted = BmoocCorner::where('id', $id)->delete();
        $response->status = $response::status_ok;
        $response->messages = "Successfully deleted.";
        $response->result = $deleted;
        return FacadeResponse::json($response);
    }
}
