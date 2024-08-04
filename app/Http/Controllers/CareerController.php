<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;


use Validator;
use App\Career;
use App\User;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class CareerController extends Controller
{
     
     public function deleteCareerApplication (Request $request) {
        $response = new ResponseObject;
         $career = Career::where('id', $request->id)->first();
         if (is_null($career)) {
             
            $response->status = $response::status_fail;
            $response->messages = "No item found to delete";
            return FacadeResponse::json($response);
         }
         $career->delete();
            $response->status = $response::status_ok;
            $response->messages = "Item has been deleted successfully";
            return FacadeResponse::json($response);
     }
     
     
    public function getCareerAppliedListPaginated ($pageSize, $pageNumber) {
        $total = Career::count();
        $careers = Career::limit($pageSize)->skip($pageSize * $pageNumber)->get();

        $obj = (Object) [
            "total_page" => ceil($total/$pageSize),
            "records" => $careers
        ];
        return FacadeResponse::json($obj);
    }
    
    
    public function checkJobPosted (Request $request) {

        $response = new ResponseObject;

        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'job_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        $isSubmitted = Career::where('phone', $request->phone)->where('job_id', $request->job_id)->count();

        if ($isSubmitted) {

            $response->status = $response::status_fail;
            $response->messages = "You applied to this job before.";
            return FacadeResponse::json($response);
        } else {

            $response->status = $response::status_ok;
            $response->messages = "You did not applied to this job yet.";
            return FacadeResponse::json($response);
        }

    }
   
    public function submitCareerForm (Request $request)
    {
        
        $response = new ResponseObject;
        
        $data = json_decode($request->data, true);
        
        // return FacadeResponse::json($data);
        // $data = $request->data;
        $validator = Validator::make($data, [
            'university_id' => 'numeric',
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'work_experience' => 'numeric',
            'salary' => 'numeric'
        ]);

        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        // return FacadeResponse::json($data);
        if (array_key_exists('cover_letter', $data)) {
            $data['cover_letter'] = strip_tags($data['cover_letter'], '');
            $data['cover_letter'] = strip_tags($data['cover_letter'], '');
        }
        
        $email = new PHPMailer();
        $email->SetFrom('contact@bacbonschool.com', $data['name']); //Name is optional
        $email->Subject   = $data['subject'];

        $email->Body      = $data['name'].' has applied with expected salary '. $data['salary']."\r\n". $data['cover_letter'] . '\r\n Email: '. $data['email']. '\r\n Phone: '. $data['phone'];
       
        

        $email->AddAddress( 'career@bacbonschool.com' );
        
        if ($request->hasFile('file') ) {
            $rand = time();
            $file = $request->file('file');
            $filename = $data['name'].'-'.$rand.'-file.'.$file->getClientOriginalExtension();
            $destinationPath = 'uploads/careers';
            $file->move($destinationPath,$filename);
            
            
            $file_to_attach = 'uploads/careers/'.$filename;

            $email->AddAttachment( $file_to_attach , $filename );
            $university_id = null;
            
            
                if ($email->Send()) {
                    
                    $career = Career::create([
                        "job_id" => array_key_exists("job_id", $data) ? $data['job_id'] : null,
                        "job_title" => $data['subject'],
                        "user_id" => $data['user_id'],
                        "university_id" => $data['university_id'] != -1 ? $data['university_id']: null,
                        "university_name" => $data['university_name'],
                        "name" => $data['name'],
                        "phone" => $data['phone'],
                        "email" => $data['email'],
                        "expected_salary" => $data['salary'],
                        "work_experience" => $data['work_experience'],
                        "work_experience_duration_type" => "Year",
                        "cover_letter" => $data['cover_letter'],
                        "file" => $file_to_attach
                    ]);
        
                    $response->status = $response::status_ok;
                    $response->messages = "Thank you for submission";
                    return FacadeResponse::json($response);
                } else {
                    
                    $response->status = $response::status_fail;
                    $response->messages = "Email submission failed";
                    return FacadeResponse::json($response);
                }
            
            
        } else {
                $email->Send();

                $career = Career::create([
                    "job_id" => array_key_exists("job_id", $data) ? $data['job_id'] : null,
                    "job_title" => $data['subject'],
                    "user_id" => $data['user_id'],
                    "university_id" => $data['university_id'] != -1 ? $data['university_id']: null,
                    "university_name" => $data['university_name'],
                    "name" => $data['name'],
                    "phone" => $data['phone'],
                    "email" => $data['email'],
                    "expected_salary" => $data['salary'],
                    "work_experience" => $data['work_experience'],
                    "work_experience_duration_type" => "Year",
                    "cover_letter" => $data['cover_letter'],
                    "file" => null
                ]);
        
                $response->status = $response::status_ok;
                $response->messages = "Thank you for submission";
                return FacadeResponse::json($response);
        }

    }
    
    
    public function sendTopStudentsToEmails (Request $request)    {
            
        
        $response = new ResponseObject;
       
             
        $mail = new PHPMailer(true);
        try {
            // //Server settings
            $mail->SMTPDebug = 0;                                 // Enable verbose debug output
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';                                    // Set mailer to use SMTP
            $mail->Host = 'mail.bacbonschool.com';               // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                              // Enable SMTP authentication
            $mail->Username = "contact@bacbonschool.com";        // SMTP username
            $mail->Password = "contact@bacbonschool";       

            
            // SMTP password
            $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;                                    // TCP port to connect to

            $students = '';
            foreach ($request->students as $student) {
            $user = User::where('id', $student)->first();
            $address =  $user->address ? ', '. $user->address : null;
            $text = '<b>'. $user->name .'</b>'. $address;

            $students.="<tr style='border-collapse: collapse; text-align: center'> <td class='esd-block-image es-p5t es-p5b es-p10r es-p20l' valign='top' align='left' style='font-size: 0;padding: 0;margin: 0;padding-top: 5px;padding-bottom: 5px;padding-right: 10px;padding-left: 20px;'><a href target='_blank' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 14px;text-decoration: underline;color: #3d5ca3;'><img src='https://tlr.stripocdn.email/content/guids/CABINET_66498ea076b5d00c6f9553055acdb37a/images/73991527593698032.png' alt style='display: block;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;' width='16'></a></td><td align='left' style='padding: 0;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-block-text' align='left' style='padding: 0;margin: 0;'> <p style='margin: 0;-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-size: 14px;font-family: arial;line-height: 150%;color: #333333;'> $text <br></p></td></tr></tbody> </table> </td></tr>";
            }
            $mail->setFrom('info@bacbonschool.com', 'BacBon School');
            // $mail->addReplyTo('no-reply@bacbonschool.com', 'Register');
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $request->subject;
            $mail->Body    = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml' xmlns:o='urn:schemas-microsoft-com:office:office' style='width: 100%;font-family: arial;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;padding: 0;margin: 0;'><head> <meta charset='UTF-8'> <meta content='width=device-width, initial-scale=1' name='viewport'> <meta name='x-apple-disable-message-reformatting'> <meta http-equiv='X-UA-Compatible' content='IE=edge'> <meta content='telephone=no' name='format-detection'> <title></title></head><body style='width: 100%;font-family: arial;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;padding: 0;margin: 0;'> <div class='es-wrapper-color' style='background-color: #f7f7f7;'> <table class='es-wrapper' width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;padding: 0;margin: 0;width: 100%;height: 100%;background-image: ;background-repeat: repeat;background-position: center top;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-email-paddings' valign='top' style='padding: 0;margin: 0;'> <table class='es-content es-preheader esd-header-popover' cellspacing='0' cellpadding='0' align='center' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;width: 100%;table-layout: fixed !important;'> <tbody> <tr style='border-collapse: collapse;'> <td class='es-adaptive esd-stripe' align='center' esd-custom-block-id='88589' style='padding: 0;margin: 0;'> <table class='es-content-body' style='background-color: transparent;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;' width='600' cellspacing='0' cellpadding='0' bgcolor='#ffffff' align='center'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-structure es-p10' align='left' style='padding: 10px;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-container-frame' width='580' valign='top' align='center' style='padding: 0;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td align='center' class='es-infoblock esd-block-text' style='padding: 0;margin: 0;line-height: 120%;font-size: 11px;color: #999999;'> </td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> <table class='es-header' cellspacing='0' cellpadding='0' align='center' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;width: 100%;background-color: transparent;background-image: ;background-repeat: repeat;background-position: center top;table-layout: fixed !important;'> <tbody> <tr style='border-collapse: collapse;'> <td class='es-adaptive esd-stripe' align='center' esd-custom-block-id='88593' style='padding: 0;margin: 0;'> <table class='es-header-body' style='background-color: #3d5ca3;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;' width='600' cellspacing='0' cellpadding='0' bgcolor='#3d5ca3' align='center'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-structure es-p20t es-p20b es-p20r es-p20l' style='background-color: #3d5ca3;padding: 0;margin: 0;padding-top: 20px;padding-bottom: 20px;padding-left: 20px;padding-right: 20px;' bgcolor='#3d5ca3' align='left'> <table class='es-left' cellspacing='0' cellpadding='0' align='left' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;float: left;'> <tbody> <tr style='border-collapse: collapse;'> <td class='es-m-p20b esd-container-frame' width='270' align='left' style='padding: 0;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-block-image es-m-p0l es-m-txt-c' align='left' style='font-size: 0;padding: 0;margin: 0;'><a href='https://bacbonschool.com' target='_blank' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 14px;text-decoration: underline;color: #3d5ca3;'><img src='https://bacbonschool.com/images-for-email-template/BBS%20Logo-01.png' alt style='display: block;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;' width='183'></a></td></tr></tbody> </table> </td></tr></tbody> </table> <table class='es-right' cellspacing='0' cellpadding='0' align='right' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;float: right;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-container-frame' width='270' align='left' style='padding: 0;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-block-button es-p10t es-m-txt-c' align='right' style='padding: 0;margin: 0;padding-top: 10px;'><span class='es-button-border' style='border-style: solid solid solid solid;border-color: #3d5ca3 #3d5ca3 #3d5ca3 #3d5ca3;background: #ffffff;border-width: 2px 2px 2px 2px;display: inline-block;border-radius: 4px;width: auto;'><a href='https://bacbonschool.com/ssc-hsc-jsc-psc-university-admission-bank-exam-preparation' class='es-button' target='_blank' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 16px;text-decoration: none !important;color: #3d5ca3;border-style: solid;border-color: #ffffff;border-width: 10px 15px 10px 15px;display: inline-block;background: #ffffff;border-radius: 4px;font-weight: normal;font-style: normal;line-height: 120%;width: auto;text-align: center;mso-style-priority: 100 !important;'>Try free courses</a></span></td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> <table class='es-content' cellspacing='0' cellpadding='0' align='center' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;width: 100%;table-layout: fixed !important;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-stripe' align='center' style='padding: 0;margin: 0;'> <table class='es-content-body' style='background-color: #ffffff;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;' width='600' cellspacing='0' cellpadding='0' bgcolor='#ffffff' align='center'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-structure' style='background-color: #cfebea;padding: 0;margin: 0;' bgcolor='#cfebea' align='left'> <table class='es-left' cellspacing='0' cellpadding='0' align='center' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;float: center;'> <tbody> <tr style='border-collapse: collapse;'> <td class='es-m-p20b esd-container-frame' width='260' align='left' style='padding: 0;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-block-image es-p35t' align='center' style='font-size: 0;padding: 0;margin: 0;padding-top: 35px;'><a target='_blank' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 14px;text-decoration: underline;color: #3d5ca3;'><img class='adapt-img' src='https://tlr.stripocdn.email/content/guids/CABINET_66498ea076b5d00c6f9553055acdb37a/images/52771527593179368.png' alt style='display: block;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;' width='260'></a></td></tr></tbody> </table> </td></tr></tbody> </table> <table class='es-right' cellspacing='0' cellpadding='0' align='center' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;float: center;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-container-frame' width='330' align='left' style='padding: 0;margin: 0;'> <table style='background-color: #ffffff;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;' width='600px' cellspacing='0' cellpadding='0' bgcolor='#ffffff'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-block-text es-p15t es-p10b es-p30r es-p20l es-m-txt-l' align='left' style='padding: 0;margin: 0;padding-bottom: 10px;padding-top: 15px;padding-left: 20px;padding-right: 30px;'> <h1 style='color: #333333;margin: 0;line-height: 120%;mso-line-height-rule: exactly;font-family: arial;font-size: 30px;font-style: normal;font-weight: normal; text-align: center;'><strong>". $request->subject ."</strong></h1> </td></tr></tbody></table></td></tr><tr style='border-collapse: collapse;'> <td class='esd-block-text es-p10b es-p20r es-p20l' align='left' style='padding: 0;margin: 0;padding-bottom: 20px;padding-top: 20px;padding-left: 20px;padding-right: 20px;'> <p style='margin: 0;-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-size: 14px;font-family: arial;line-height: 150%;color: #333333; text-align: center;'>".$request->message."<br></p></td></tr><tr style='border-collapse: collapse;'> <td style='padding: 0;margin: 0;'> <table class='es-table-not-adapt' cellspacing='0' cellpadding='0' style=' margin: auto; mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> ". $students." </tbody> </table> </td></tr><tr style='border-collapse: collapse;'> <td class='esd-block-button es-p15t es-p15b es-p20r es-p20l' align='center' style='padding: 0;margin: 0;padding-top: 45px;padding-bottom: 15px;padding-left: 20px;padding-right: 20px;'><span class='es-button-border' style='border-style: solid solid solid solid;border-color: #3d5ca3 #3d5ca3 #3d5ca3 #3d5ca3;background: #ffffff;border-width: 2px 2px 2px 2px;display: inline-block;border-radius: 4px;width: auto;'><a href='https://play.google.com/store/apps/details?id=com.bacbonltd.bacbonschool&hl=en' class='es-button' target='_blank' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 16px;text-decoration: none !important;color: #3d5ca3;border-style: solid;border-color: #ffffff;border-width: 10px 15px 10px 15px;display: inline-block;background: #ffffff;border-radius: 4px;font-weight: normal;font-style: normal;line-height: 120%;width: auto;text-align: center;mso-style-priority: 100 !important;'>Download BacBon School App »</a></span></td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> <table class='es-content' cellspacing='0' cellpadding='0' align='center' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;width: 100%;table-layout: fixed !important;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-stripe' align='center' esd-custom-block-id='88591' style='padding: 0;margin: 0;'> <table class='es-content-body' width='600' cellspacing='0' cellpadding='0' bgcolor='#ffffff' align='center' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;background-color: #ffffff;'> <tbody> <tr style='border-collapse: collapse;' style='background-color: #f7c052;padding: 0;margin: 0;padding-left: 10px;padding-right: 10px;padding-top: 15px;padding-bottom: 15px;' bgcolor='#f7c052'> <td style='width:50%' class='esd-structure es-p15t es-p15b es-p10r es-p10l' align='left'> <table class='es-left' cellspacing='0' cellpadding='0' align='left' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;float: left;'> <tbody> <tr style='border-collapse: collapse;'> <td class='es-m-p0r es-m-p20b esd-container-frame' align='center' style='padding: 10px 5px;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-block-image es-p5b' align='center' style='font-size: 0;padding: 0;margin: 0;padding-bottom: 5px;'><a target='_blank' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 14px;text-decoration: underline;color: #3d5ca3;'><img src='https://tlr.stripocdn.email/content/guids/CABINET_66498ea076b5d00c6f9553055acdb37a/images/39911527588288171.png' alt style='display: block;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;' width='24'></a></td></tr><tr style='border-collapse: collapse;'> <td class='esd-block-text es-p5r es-p5l' align='center' style='padding: 0;margin: 0;padding-left: 5px;padding-right: 5px;'> <p style='color: #ffffff;font-size: 16px;margin: 0;-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial, sans-serif;line-height: 150%;'> House # 13 (5th Floor), Block-C, Main Road, Banasree, Rampura, Dhaka-1219</p></td></tr></tbody> </table> </td><td class='es-hidden' width='20' style='padding: 0;margin: 0;'></td></tr></tbody> </table><!-- <table class='es-right' cellspacing='0' cellpadding='0' align='right' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;float: right;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-container-frame' width='180' align='center' style='padding: 0;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-block-image es-p5b' align='center' style='font-size: 0;padding: 0;margin: 0;padding-bottom: 5px;'><a target='_blank' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 14px;text-decoration: underline;color: #3d5ca3;'><img src='https://tlr.stripocdn.email/content/guids/CABINET_66498ea076b5d00c6f9553055acdb37a/images/50681527588357616.png' alt style='display: block;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;' width='24'></a></td></tr><tr style='border-collapse: collapse;'> <td class='esd-block-text' align='center' esd-links-color='#ffffff' style='padding: 0;margin: 0;'> <p style='color: #ffffff;font-size: 16px;margin: 0;-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;line-height: 150%;'><a style='font-size: 16px;color: #ffffff;-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;text-decoration: underline;' href='javascript:;'>02 8396601</a></p></td></tr></tbody> </table> </td></tr></tbody> </table> --> </td><td style='width:50%' align='left'> <table class='es-left' cellspacing='0' cellpadding='0' align='left' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;float: left;'> <tbody> <tr style='border-collapse: collapse;'> <td class='es-m-p20b esd-container-frame' style='padding: 10px 5px;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-block-image es-p5b' align='center' style='font-size: 0;padding: 0;margin: 0;width: 40px;'><a target='_blank' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 14px;text-decoration: underline;color: #3d5ca3;'><img src='https://tlr.stripocdn.email/content/guids/CABINET_66498ea076b5d00c6f9553055acdb37a/images/35681527588356492.png' alt style='display: block;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;' width='24'></a></td><td esdev-links-color='#ffffff' class='esd-block-text' style='padding: 0;margin: 0;'> <p style='color: #ffffff;-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-size: 14px;font-family: arial;line-height: 150%;'><a style='color: #ffffff;font-size: 18px;-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;text-decoration: underline;' href='javascript:;'>info@bacbonschool.com</a></p></td></tr><tr style='border-collapse: collapse;'> <tr style='border-collapse: collapse;'> <td class='esd-block-image es-p5b' align='center' style='font-size: 0;padding: 0;margin: 0;width: 40px;'><a target='_blank' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 14px;text-decoration: underline;color: #3d5ca3;'><img src='https://tlr.stripocdn.email/content/guids/CABINET_66498ea076b5d00c6f9553055acdb37a/images/50681527588357616.png' alt style='display: block;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;' width='24'></a></td><td class='esd-block-text' esd-links-color='#ffffff' style='padding: 0;margin: 0;'> <p style='color: #ffffff;font-size: 16px;-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;line-height: 150%;'><a style='font-size: 16px;color: #ffffff;-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;text-decoration: underline;' href='javascript:;'>02 8396601</a></p></td></tr></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> <table class='es-footer' cellspacing='0' cellpadding='0' align='center' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;width: 100%;background-color: transparent;background-image: ;background-repeat: repeat;background-position: center top;table-layout: fixed !important;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-stripe' align='center' esd-custom-block-id='88592' style='padding: 0;margin: 0;'> <table class='es-footer-body' width='600' cellspacing='0' cellpadding='0' align='center' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;background-color: transparent;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-structure es-p20t es-p10r es-p10l' align='left' style='padding: 0;margin: 0;padding-left: 10px;padding-right: 10px;padding-top: 20px;'> <table class='es-left' cellspacing='0' cellpadding='0' align='left' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;float: left;'> <tbody> <tr style='border-collapse: collapse;'> <td class='es-m-p0r es-m-p20b esd-container-frame' width='190' valign='top' align='center' style='padding: 0;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-block-text es-p5t es-m-txt-c' esdev-links-color='#666666' align='right' style='padding: 0;margin: 0;padding-top: 5px;'> <h4 style='color: #666666;margin: 0;line-height: 120%;mso-line-height-rule: exactly;font-family: arial;'>Follow us:</h4> </td></tr></tbody> </table> </td></tr></tbody> </table> <table cellspacing='0' cellpadding='0' align='right' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-container-frame' width='370' align='left' style='padding: 0;margin: 0;'> <table width='100%' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='esd-block-social es-m-txt-c' align='left' style='font-size: 0;padding: 0;margin: 0;'> <table class='es-table-not-adapt es-social' cellspacing='0' cellpadding='0' style='mso-table-lspace: 0pt;mso-table-rspace: 0pt;border-collapse: collapse;border-spacing: 0px;'> <tbody> <tr style='border-collapse: collapse;'> <td class='es-p15r' valign='top' align='center' style='padding: 0;margin: 0;padding-right: 15px;'><a target='_blank' href='https://www.facebook.com/BacBonSchool/' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 12px;text-decoration: underline;color: #666666;'><img title='Facebook' src='https://tlr.stripocdn.email/content/assets/img/social-icons/rounded-gray/facebook-rounded-gray.png' alt='Fb' width='32' height='32' style='display: block;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;'></a></td><td class='es-p15r' valign='top' align='center' style='padding: 0;margin: 0;padding-right: 15px;'><a target='_blank' href='https://www.youtube.com/channel/UCPEzztiWQWWnzSuD7aPCntg?reload=9' style='-webkit-text-size-adjust: none;-ms-text-size-adjust: none;mso-line-height-rule: exactly;font-family: arial;font-size: 12px;text-decoration: underline;color: #666666;'><img title='Youtube' src='https://tlr.stripocdn.email/content/assets/img/social-icons/rounded-gray/youtube-rounded-gray.png' alt='Yt' width='32' height='32' style='display: block;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;'></a></td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> </td></tr></tbody> </table> </div></body></html>";
            $mail->AltBody = '';
            
            foreach ($request->emails as $email) {

                $mail->addAddress($email, null);      // Name is optional

                $mail->SMTPOptions = array(
                'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
                ));
    
                $mail->send();
                
                $mail->ClearAllRecipients(); 
            }
      
            $response->status = $response::status_ok;
            $response->messages = "Successfull Send Email";
            $response->result = null;
            return FacadeResponse::json($response);
            
        } catch (Exception $e) {
            
            $response->status = $response::status_fail;
            $response->messages = $mail->ErrorInfo;
            return FacadeResponse::json($response);
            
           // echo 'Message could not be sent.';
           // echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    
}

    


}
