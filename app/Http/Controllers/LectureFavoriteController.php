<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Validator;

use App\LectureFavorite;
use Illuminate\Http\Request;

class LectureFavoriteController extends Controller
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
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }
        $isFavorite = LectureFavorite::where('user_id', $request->user_id)->where('lecture_id', $request->lecture_id)->first();

        if (empty($isFavorite)) {
            $favorite = LectureFavorite::create($data);
            $response->messages = "Congratulations! You have made this lecture favourite";
        } else {
            $favorite = $isFavorite->delete();
            $response->messages = "Removed Successfully from bookmark list";
        }
        $response->status = $response::status_ok;

        return FacadeResponse::json($response);
    }

    public function getFavoriteLectureByUserId($user_id) {
        return LectureFavorite::where('lecture_favorites.user_id', $user_id)
        ->join('lecture_videos', 'lecture_favorites.lecture_id', 'lecture_videos.id')
        ->join('chapters', 'lecture_videos.chapter_id', 'chapters.id')
        ->join('subjects', 'lecture_videos.subject_id', 'subjects.id')
        ->join('courses', 'lecture_videos.course_id', 'courses.id')
        ->select('lecture_videos.id', 'lecture_videos.title',
        'lecture_videos.title_bn', 'lecture_videos.thumbnail',
        'lecture_videos.course_id',
        'lecture_videos.subject_id',
        'lecture_videos.chapter_id',
        'lecture_favorites.lecture_id',
        'lecture_videos.url',
        'lecture_videos.full_url',
        'lecture_videos.duration',
        'lecture_videos.description',
        'lecture_videos.rating',
        'lecture_videos.isFree',
        'lecture_videos.code',
        'lecture_videos.price',
        'lecture_videos.status',
        'chapters.name_bn as chapter_name_bn',
        'chapters.name as chapter_name',
        'chapters.id as chapter_id',
        'subjects.name as subject_name',
        'subjects.name_bn as subject_name_bn',
        'courses.name as courses_name',
        'courses.name_bn as course_name_bn'
        )
        ->with('exams', 'lectureScripts', 'lectureRating')
        ->get();
    }

    public function getFavoriteLectureByUserIdLatest($user_id) {
        return LectureFavorite::where('lecture_favorites.user_id', $user_id)
        ->join('lecture_videos', 'lecture_favorites.lecture_id', 'lecture_videos.id')
        ->join('chapters', 'lecture_videos.chapter_id', 'chapters.id')
        ->join('subjects', 'lecture_videos.subject_id', 'subjects.id')
        ->join('courses', 'lecture_videos.course_id', 'courses.id')
        ->select('lecture_videos.id', 'lecture_videos.title',
        'lecture_videos.title_bn', 'lecture_videos.thumbnail',
        'lecture_videos.course_id',
        'lecture_videos.subject_id',
        'lecture_videos.chapter_id',
        'lecture_favorites.lecture_id',
        'lecture_videos.url',
        'lecture_videos.duration',
        'lecture_videos.description',
        'lecture_videos.rating',
        'lecture_videos.isFree',
        'lecture_videos.code',
        'lecture_videos.price',
        'lecture_videos.status',
        'chapters.name_bn as chapter_name_bn',
        'chapters.name as chapter_name',
        'chapters.id as chapter_id',
        'subjects.name as subject_name',
        'subjects.name_bn as subject_name_bn',
        'courses.name as courses_name',
        'courses.name_bn as course_name_bn'
        )
        ->get();
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\LectureFavorite  $lectureFavorite
     * @return \Illuminate\Http\Response
     */
    public function show(LectureFavorite $lectureFavorite)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LectureFavorite  $lectureFavorite
     * @return \Illuminate\Http\Response
     */
    public function edit(LectureFavorite $lectureFavorite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LectureFavorite  $lectureFavorite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LectureFavorite $lectureFavorite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LectureFavorite  $lectureFavorite
     * @return \Illuminate\Http\Response
     */
    public function destroy(LectureFavorite $lectureFavorite)
    {
        //
    }
}
