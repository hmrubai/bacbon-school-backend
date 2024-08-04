<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


use App\ScholarshipApplication;
use App\User;
use Illuminate\Http\Request;
use Validator;
class ScholarshipApplicationController extends Controller
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
    public function applyForScholarship(Request $request)
    {
        $response = new ResponseObject;
        $user = User::where('id', $request->user_id)->select('id','name', 'is_applied_scholarship')->first();
        if ($user->is_applied_scholarship) {
            $response->status = $response::status_fail;
            $response->messages = "You have applied for scholarship before";
            return FacadeResponse::json($response);
        }
            $response = new ResponseObject;
            $data = $request->json()->all();
            $validator = Validator::make($data, ScholarshipApplication::$rules);
            if ($validator->fails()) {
                $response->status = $response::status_fail;
                $response->messages = $validator->errors()->first();
                return FacadeResponse::json($response);
            }
        $scholarship = $data;
        unset($scholarship['educations']);
        $savedScholarship = ScholarshipApplication::create($scholarship);
        $educations = $request->educations;
        $education = new EducationController();
        $educationStatus = $education->store($educations, $request->user_id);
        
        
        
        
        

        $body = "Name : ".   $user->name." \r\n";
        // $body = "Phone : ".  $scholarship['phone']." \r\n";
        // $body = "District : ".  $scholarship['district_name']." \r\n\r\n";

        $body .= "Address : ".  $scholarship['address']." \r\n";
        
        
        
        $body .= "Father Name : ".  $scholarship['father_name']." \r\n";
        $body .= "Father Occupation : ".  $scholarship['father_occupation']." \r\n";
        $body .= "Father Yearly Income : ".  $scholarship['father_yearly_income']." \r\n";
        $body .= "Father Contact No : ".  $scholarship['father_contact_no']." \r\n\r\n";

        $body .= "Mother Name : ".  $scholarship['mother_name']." \r\n";
        $body .= "Mother Occupation : ".  $scholarship['mother_occupation']." \r\n";
        $body .= "Mother Yearly Income : ".  $scholarship['mother_yearly_income']." \r\n";
        $body .= "Mother Contact No : ".  $scholarship['mother_contact_no']." \r\n\r\n\r\n";


        foreach ($educations as $education) {
            $body .= " ".  $education['discipline']." \r\n";
            $body .= "Board: ".  $education['board']." \r\n";
            $body .= "Class: ".  $education['current_class']." \r\n";
            $body .= "Year: ".  $education['exam_year']." \r\n";
            $body .= "Institute: ".  $education['institution_name']." \r\n\r\n";
        }
    


        $email = new PHPMailer();
        $email->SetFrom('contact@bacbonschool.com', $user->name); //Name is optional
        $email->Subject   = 'Application for Scholarship';
        // $email->Body = $data['name']. ' has applied with expected salary '. $data['salary']."\r\n".$data['message'];
        $email->Body = $body;


        $email->AddAddress( 'scholarship@bacbonschool.com' );
        
        $email->Send();


        
        
        
        
        
        
        if ($educationStatus->status) {
            User::where('id', $request->user_id)->update(["is_applied_scholarship" => true]);
            $response->status = $response::status_ok;
            $response->messages = "Your application has been accepted";
            return FacadeResponse::json($response);
        } else {
            $savedScholarship->delete();
            $response->status = $response::status_fail;
            $response->messages = $educationStatus->message;
            return FacadeResponse::json($response);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ScholarshipApplication  $scholarshipApplication
     * @return \Illuminate\Http\Response
     */
    public function show(ScholarshipApplication $scholarshipApplication)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ScholarshipApplication  $scholarshipApplication
     * @return \Illuminate\Http\Response
     */
    public function edit(ScholarshipApplication $scholarshipApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ScholarshipApplication  $scholarshipApplication
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ScholarshipApplication $scholarshipApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ScholarshipApplication  $scholarshipApplication
     * @return \Illuminate\Http\Response
     */
    public function destroy(ScholarshipApplication $scholarshipApplication)
    {
        //
    }
}
