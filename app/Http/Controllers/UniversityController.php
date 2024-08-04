<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Validator;

use App\University;
use App\User;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    public function getUniversityByShortName($shortName) {
        return University::where('short_name', $shortName)->first();
    }
    public function getLampRegisteredUserAll (Request $request, $shortName) {
        $university = $this->getUniversityByShortName($shortName);
        $users = User::where('university_id', $university->id)
        ->select('name', 'email', 'mobile_number as phone')->get();
        $obj = (Object) [
            "application_number" => count($users),
            "university" => $university,
            "students" => $users
        ];
        return FacadeResponse::json($obj);
    }

    public function getLampRegisteredUser (Request $request, $shortName) {
        $result = University::where('short_name', $shortName)->first();
        $users = User::where('university_id', $result->id)
        ->whereBetween('lamp_aplication_date', [date($request->from), date($request->to)])
        ->select('name', 'email', 'mobile_number as phone')->get();
        $obj = (Object) [
            "application_number" => count($users),
            "university" => $result,
            "students" => $users
        ];
        return FacadeResponse::json($obj);
    }

    // public function getLampRegisteredUser (Request $request, $shortName) {
    //     $result = University::where('short_name', $shortName)
    //     ->with('students')
    //     ->withCount('students')
    //     ->first();
    //     return FacadeResponse::json($result);
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUniversityListWithLimit($limit) {
        $university_list = University::limit($limit)->get();
        return FacadeResponse::json($university_list);
    }
    public function getUniversityList()
    {
        $university_list = University::all();

        return FacadeResponse::json($university_list);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\University  $university
     * @return \Illuminate\Http\Response
     */
    public function show(University $university)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\University  $university
     * @return \Illuminate\Http\Response
     */
    public function edit(University $university)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\University  $university
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, University $university)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\University  $university
     * @return \Illuminate\Http\Response
     */
    public function destroy(University $university)
    {
        //
    }
}
