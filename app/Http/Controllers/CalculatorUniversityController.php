<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\CalculatorUniversity;
use Illuminate\Http\Request;

class CalculatorUniversityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function calculatorUniversityList()
    {
        $path =  "json/calculator_condition.json"; // ie: /var/www/laravel/app/storage/json/filename.json

        $json = json_decode(file_get_contents($path), true); 
        return FacadeResponse::json($json);


        // $universities = CalculatorUniversity::all();
        // return FacadeResponse::json($universities);
        
    }

    public function getJobList()
    {
        $path =  "json/jobList.json"; // ie: /var/www/laravel/app/storage/json/filename.json

        $json = json_decode(file_get_contents($path), true); 
        return FacadeResponse::json($json);


        // $universities = CalculatorUniversity::all();
        // return FacadeResponse::json($universities);
        
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
     * @param  \App\CalculatorUniversity  $calculatorUniversity
     * @return \Illuminate\Http\Response
     */
    public function show(CalculatorUniversity $calculatorUniversity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CalculatorUniversity  $calculatorUniversity
     * @return \Illuminate\Http\Response
     */
    public function edit(CalculatorUniversity $calculatorUniversity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CalculatorUniversity  $calculatorUniversity
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CalculatorUniversity $calculatorUniversity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CalculatorUniversity  $calculatorUniversity
     * @return \Illuminate\Http\Response
     */
    public function destroy(CalculatorUniversity $calculatorUniversity)
    {
        //
    }
}
