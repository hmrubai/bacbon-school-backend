<?php

namespace App\Http\Controllers;

use App\SchoolAssistance;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

class SchoolAssistanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSchoolAssistanceList($pageSize, $pageNumber) 
    {
        $allRows = SchoolAssistance::count();
        $list = SchoolAssistance::limit($pageSize)->skip($pageSize*$pageNumber)->get();

        $obj = (Object) [
            "records" => $list,
            "totalRows" => $allRows
        ];
        return FacadeResponse::json($obj);
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
    public function saveSchoolAssistance (Request $request)
    {
        $items = $request->items;
        $count = 0;
        foreach ($items as $item) {
            $count++;
            SchoolAssistance::create($item);
        }
        
        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "Submitted";
        $response->result = $count;
        return FacadeResponse::json($response);



    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SchoolAssistance  $schoolAssistance
     * @return \Illuminate\Http\Response
     */
    public function show(SchoolAssistance $schoolAssistance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SchoolAssistance  $schoolAssistance
     * @return \Illuminate\Http\Response
     */
    public function edit(SchoolAssistance $schoolAssistance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SchoolAssistance  $schoolAssistance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SchoolAssistance $schoolAssistance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SchoolAssistance  $schoolAssistance
     * @return \Illuminate\Http\Response
     */
    public function destroy(SchoolAssistance $schoolAssistance)
    {
        //
    }
}
