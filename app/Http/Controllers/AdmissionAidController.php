<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use Validator;
use Illuminate\Http\Request;

class AdmissionAidController extends Controller
{

    const MODEL = "App\AdmissionAid";
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAidListByCourse($course_id)
    {
        $m = self::MODEL;
        $aidList = $m::where('course_id', $course_id)->get();
        return FacadeResponse::json($aidList);
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
        $m = self::MODEL;
        $lastData = $m::orderBy('id', 'desc')->first();
        $number = empty($lastData) ? '1' : $lastData->id + 1;
        // return FacadeResponse::json($isLastData);
        $validator = Validator::make($request->all(), $m::$rules);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            // $response->messages = "Validation failed";
            return FacadeResponse::json($response);
        }

        $data = $request;
        $pdfUrl = null;
        $imageUrl = null;
        $pdfname = '';
        $imagename = '';
        $site_url = "http://".$_SERVER['HTTP_HOST'];
        if ($request->hasFile('pdf') ) {
            $pdfFile = $request->file('pdf');
            $pdfname = $request->name.'_'. $number.'.'.$pdfFile->getClientOriginalExtension();
            $destinationPathFile = 'uploads/admission_aids/files';
            $pdfFile->move($destinationPathFile, $pdfname);
            $pdfUrl = $site_url.'/'.$destinationPathFile.'/'.$pdfname;
        }

        if ($request->hasFile('image') ) {
            $file = $request->file('image');
            $imagename = $request->name.'_'. $number.'.'.$file->getClientOriginalExtension();
            $destinationPathImage = 'uploads/admission_aids/images';
            $file->move($destinationPathImage,$imagename);

            $imageUrl = $site_url.'/'.$destinationPathImage.'/'.$imagename;
        }
        $aid = $m::create([
            'course_id' => $request->course_id,
            'name' => $request->name,
            'name_bn' => $request->name_bn,
            'name_jp' => $request->name_jp,
            'pdf_url' => $pdfUrl,
            'image_url' => $imageUrl
        ]);
        $response->status = $response::status_ok;
        $response->messages = "Successfully uploaded";
        $response->result = $aid;
        return FacadeResponse::json($response);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AdmissionAid  $admissionAid
     * @return \Illuminate\Http\Response
     */
    public function show(AdmissionAid $admissionAid)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AdmissionAid  $admissionAid
     * @return \Illuminate\Http\Response
     */
    public function edit(AdmissionAid $admissionAid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AdmissionAid  $admissionAid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AdmissionAid $admissionAid)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AdmissionAid  $admissionAid
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdmissionAid $admissionAid)
    {
        //
    }
}
