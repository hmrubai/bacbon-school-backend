<?php

namespace App\Http\Controllers;

use App\Education;
use Illuminate\Http\Request;
use Validator;
class EducationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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
    public function store($educations, $user_id)
    {
        foreach($educations as $education) {
            $education['user_id'] = $user_id;
            $validator = Validator::make($education, Education::$rules);
            if ($validator->fails()) {
                $obj = (object) [
                    "status" => false,
                    "message" => $validator->errors()->first()
                ];
                return $obj;
            }
            try {
                Education::insert($education);

            $obj = (object) [
                "status" => true,
                "message" => "Successfully Saved"
            ];

            } catch(Exception $e) {
                $obj = (object) [
                    "status" => false,
                    "message" => "Successfully Saved"
                ];
            }

        }
        return $obj;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Education  $education
     * @return \Illuminate\Http\Response
     */
    public function show(Education $education)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Education  $education
     * @return \Illuminate\Http\Response
     */
    public function edit(Education $education)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Education  $education
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Education $education)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Education  $education
     * @return \Illuminate\Http\Response
     */
    public function destroy(Education $education)
    {
        //
    }
}
