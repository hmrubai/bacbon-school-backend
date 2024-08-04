<?php

namespace App\Http\Controllers;


use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use Validator;
use App\User;
use App\CourseSubject;
use App\ResetPassword;
use Carbon\Carbon;
use App\BmoocCorner;
use App\LogLectureVideo;
use App\UserFCMMessage;
use QrCode;
use DB;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

use Kawankoding\Fcm\FcmServiceProvider;



class UserController extends Controller
{

    public function checkExistUser (Request $request) {
        $Students = [];
        foreach ($request->students as $student) {
            $user = User::where('mobile_number', $student['mobile_no'])->first();
            if (is_null($user)) {
                $Students[] = $student;
                // $user->update([
                //     "is_e_edu_4" => true
                //     ]);
            }
        }

        return FacadeResponse::json($Students);
    }

    public function getQRCodeByUserId($id) {
        $user = User::where('id', $id)->select('id', 'user_code')->first();
        return QrCode::size(300)->generate($user->user_code);
    }

    public function createReferralCode ($previous_code , $id) {
        preg_match_all('!\d+!', $previous_code, $matches);
        return $matches[0][0] + $id;
    }

    public function getRefferalCode($id) {
        $user = User::where('id', $id)->first();
        // $nameArray = explode(" ", $user->name);
        // $codeInit = '';
        // foreach ($nameArray as $arr) {
        //     $codeInit .= $arr[0];
        // }
        // $refferal_code = strtoupper($codeInit).(1000 + $id);
        $refferal_code = "BBS".(1000 + $id);
        
        // $lastUser = User::orderBy('id', 'desc')->limit(1)->first();
        // if ($lastUser === null) {
        //     $refferal_code = strtoupper($codeInit).'1000';
        // } else {
        //     $refferal_code = strtoupper($codeInit).$this->createReferralCode($lastUser->user_code,  $id);
        // }
        $user->update(['user_code' => $refferal_code]);
        
        //return response([], 200);
        $referralCount = User::where('refference_id', $id)->count();
        $result =  (object)[
            'refferal_code' => $refferal_code,
            'qr-code-url' => 'api/getQRCodeByUserId/'. $id,
            'referral_count' => $referralCount
        ];
        return FacadeResponse::json($result);
    }

    public function searchUserByPhone($search) {
        $users = User::where('mobile_number', 'like', '%' . $search . '%')->limit(10)->get();
        return $users;

    }

    public function getUserList() {
        return User::all();
    }

    public function getUserListPaginated($pageSize, $pageNumber) {
        $totalRows = User::count();
        $list = User::limit($pageSize)->skip($pageSize*$pageNumber)->get();

        $obj = (Object) [
            "totalRows" =>  $totalRows,
            "records" =>  $list
        ];
        return FacadeResponse::json($obj);
    }

    public function getEEdu3StudentList() {

        $list = User::where('users.is_e_edu_3', true)
        ->leftJoin('log_lecture_videos', 'users.id', 'log_lecture_videos.user_id')
        ->select(
            'users.id',
            'users.name',
            'users.user_code',
            'users.email',
            'users.mobile_number',
            'users.address'
            )
        ->groupBy('users.id', 'name', 'user_code', 'email', 'password', 'mobile_number','address')
        ->withCount([
            'userLog AS duration' => function ($query) {
                        $query->select(DB::raw("SUM(duration) as totalDuration"))->where('log_lecture_videos.course_id', 2);
                    }
                ])
        ->orderBy('duration', 'desc')
        ->get();
        return FacadeResponse::json($list);
    }

    public function getEEdu3StudentListNew() {

        $list = User::where('users.is_e_edu_5', true)->where('users.is_chandpur', false)
        ->leftJoin('log_lecture_videos', 'users.id', 'log_lecture_videos.user_id')
        ->select(
            'users.id',
            'users.name',
            'users.user_code',
            'users.email',
            'users.mobile_number',
            'users.address'
            )
        ->groupBy('users.id', 'name', 'user_code', 'email', 'password', 'mobile_number','address')
        ->withCount([
            'userLog AS duration' => function ($query) {
                        $query->select(DB::raw("SUM(duration) as totalDuration"))->where('log_lecture_videos.course_id', 2)->orWhere('log_lecture_videos.course_id', 13);
                    }
                ])
        ->orderBy('duration', 'desc')
        ->get();
        return FacadeResponse::json($list);
    }

    public function getEEdu3StudentListChandpur() {

        $list = User::where('users.is_e_edu_5', true)->where('users.is_chandpur', true)
        ->leftJoin('log_lecture_videos', 'users.id', 'log_lecture_videos.user_id')
        ->select(
            'users.id',
            'users.name',
            'users.user_code',
            'users.email',
            'users.mobile_number',
            'users.address'
            )
        ->groupBy('users.id', 'name', 'user_code', 'email', 'password', 'mobile_number','address')
        ->withCount([
            'userLog AS duration' => function ($query) {
                        $query->select(DB::raw("SUM(duration) as totalDuration"))->where('log_lecture_videos.course_id', 2)->orWhere('log_lecture_videos.course_id', 13);
                    }
                ])
        ->orderBy('duration', 'desc')
        ->get();
        return FacadeResponse::json($list);
    }

    public function getEEduJICFTeacherList() {

        $list = User::where('users.is_jicf_teacher', true)
        ->leftJoin('log_lecture_videos', 'users.id', 'log_lecture_videos.user_id')
        ->select(
            'users.id',
            'users.name',
            'users.user_code',
            'users.email',
            'users.mobile_number',
            'users.address'
            )
        ->groupBy('users.id', 'name', 'user_code', 'email', 'password', 'mobile_number','address')
        ->withCount([
            'userLog AS duration' => function ($query) {
                        $query->select(DB::raw("SUM(duration) as totalDuration"))->where('log_lecture_videos.course_id', 2)->orWhere('log_lecture_videos.course_id', 13);
                    }
                ])
        ->orderBy('duration', 'desc')
        ->get();
        return FacadeResponse::json($list);
    }

    public function getEEduAdmissionStudentList() {

        $list = User::where('users.is_e_edu_admission_2022', true)
        ->leftJoin('log_lecture_videos', 'users.id', 'log_lecture_videos.user_id')
        ->select(
            'users.id',
            'users.name',
            'users.user_code',
            'users.email',
            'users.mobile_number',
            'users.address'
            )
        ->groupBy('users.id', 'name', 'user_code', 'email', 'password', 'mobile_number','address')
        ->withCount([
            'userLog AS duration' => function ($query) {
                        $query->select(DB::raw("SUM(duration) as totalDuration"))->where('log_lecture_videos.course_id', 2)->orWhere('log_lecture_videos.course_id', 13);
                    }
                ])
        ->orderBy('duration', 'desc')
        ->get();
        return FacadeResponse::json($list);
    }

    public function statusUpdateEduStudent (Request $request) {
        $students = $request->students;
        $notRegistered = [];
        $count = 0;
        foreach ($students as $student) {
            $user = User::where('mobile_number', '0'.$student['mobile_no'])->first();
            if (is_null($user)) {
                $notRegistered[] = $student;
            } else {
                $count++;
                $user->update([
                    "is_bae_4" => true
                    ]);
            }
        }
        $obj = (Object) [
            "updatedCount" => $count,
            "notRegistered" => $notRegistered
        ];
        return FacadeResponse::json($obj);
    }

    public function statusUpdateEduStudentChandpur (Request $request) {
        $students = $request->students;
        $notRegistered = [];
        $count = 0;
        foreach ($students as $student) {
            $user = User::where('mobile_number', '0'.$student['mobile_no'])->first();
            if (is_null($user)) {
                $notRegistered[] = $student;
            } else {
                $count++;
                $user->update([
                    "e_edu_id" => $student['e_edu_id'],
                    "is_e_edu_5" => true,
                    "is_chandpur" => true
                    ]);
            }
        }

        $obj = (Object) [
            "updatedCount" => $count,
            "notRegistered" => $notRegistered
        ];
        return FacadeResponse::json($obj);
    }

    public function statusUpdateEduAdmissionStudent (Request $request) {
        $students = $request->students;
        $notRegistered = [];
        $count = 0;
        foreach ($students as $student) {
            $user = User::where('mobile_number', '0'.$student['mobile_no'])->first();
            if (is_null($user)) {
                $notRegistered[] = $student;
            } else {
                $count++;
                $user->update([                   
                    "is_e_edu_admission_2022" => true
                    ]);
            }
        }

        $obj = (Object) [
            "updatedCount" => $count,
            "notRegistered" => $notRegistered
        ];
        return FacadeResponse::json($obj);
    }

    public function searchUserListPaginated(Request $request) {

        $search = $request->searchItem;
        $e_edu_sort = $request->eEduSort;

        $totalRows = User::when($search, function($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('mobile_number', 'like', '%' . $search . '%');
        })->count();


        $list = User::limit($request->pageSize)
        ->when($search, function($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('mobile_number', 'like', '%' . $search . '%');
        })
        ->when(!is_null($e_edu_sort), function($query) use ($e_edu_sort) {
            $query->orderBy('is_e_edu_3', $e_edu_sort);
        })
        ->skip($request->pageSize*$request->pageNumber)->get();

        $obj = (Object) [
            "totalRows" =>  $totalRows,
            "records" =>  $list
        ];
        return FacadeResponse::json($obj);
    }

    public function getRegisteredUserList(Request $request) 
    {
        $from = null;
        $to = null;
        if ($request->from) {
            $from = date('Y-m-d', strtotime($request->from));
            $to = date('Y-m-d', strtotime($request->to));
        }

        $user_data = User::select(
            'users.id as ID', 
            'users.name as Name', 
            'users.email as Email', 
            'users.address as Address', 
            'users.gender as Gender', 
            'divisions.name as Division',
            'districts.name as District',
            'thanas.name as Thana',
            'users.points as Point',
            'users.mobile_number as Mobile', 
            'courses.name as Course', 
            'courses.name_bn as CourseNameBn',
            'users.created_at as RegistrationDate' 
        )
        ->when($from && $to, function($q) use ($from, $to) {
            return $q->whereDate('users.created_at', '>=', $from)
            ->whereDate('users.created_at', '<=', $to);
        })
        ->leftJoin('divisions','users.division_id','=','divisions.id')
        ->leftJoin('districts','users.district_id','=','districts.id')
        ->leftJoin('thanas','users.thana_id','=','thanas.id')
        ->leftJoin('courses','users.current_course_id','=','courses.id')
        ->get();

        $result_data =  (object)[
            'message' => 'User List',
            'data' => $user_data
        ];

        return FacadeResponse::json($result_data);
    }

    public function getUserDetails(Request $request) {
        $verifyController = new VerifyCodeController();
        $user = $verifyController->getLoginData($request->userId);
        return FacadeResponse::json($user);

    }

    public function getUserDetailsWithSubjectsByUserId($id) {
      $user_data = User::where('users.id', $id)
      ->leftJoin('courses','users.current_course_id','=','courses.id')
      ->select('users.id as id', 'users.name as name', 'users.email as email', 'users.address as address', 'users.gender as gender', 'users.points as points',
      'users.mobile_number as mobile_number', 'courses.name as courseName', 'courses.name_bn as course_name_bn', 'users.current_course_id as current_course_id' )
      ->first();

      $user = (object)[
        "id" => $user_data->id,
        "name" =>$user_data->name,
        "email" =>$user_data->email,
        "mobile_number" =>$user_data->mobile_number,
        "address" => $user_data->address,
        "gender" => $user_data->gender,
        "points" => $user_data->points,
        "courseName" => $user_data->courseName,
        "current_course_id" => $user_data->current_course_id
     ];
     $subjects = [];
     if ($user_data->current_course_id != null ) {
        $subjects = CourseSubject::where('course_id', $user_data->current_course_id)
        ->has('chapters')
        ->join('subjects','course_subjects.subject_id','=','subjects.id')
        ->select('subjects.name as name', 'subjects.name_bn as name_bn', 'subjects.id as id', 'subjects.color_name as color_name')
        ->get();
     }
     $bmoocCorner = BmoocCorner::all();

     $result_data =  (object)[
         'user' => $user,
         'subjects' => $subjects,
         'bmooc_corner' => $bmoocCorner
     ];

     return FacadeResponse::json($result_data);
    }

    public function updateUserById(Request $request, $id) {
        $response = new ResponseObject;
        $data = $request->json()->all();
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
            $data['isSetPassword'] = true;
        }
        if (array_key_exists("id",$data)){
            $response->status = $response::status_fail;
            $response->messages = "Id can not be updated";
            return FacadeResponse::json($response);
        }
         else {
             try {
                $user = User::where('id',$id)->first();
                if ($user) {

                // $user = User::where('id', $id)->update($data);
                $user->update($data);
            //     $user_data = User::where('users.id', $id)
            //     ->leftJoin('courses','users.current_course_id','=','courses.id')
            //     ->select('users.id as id', 'users.name as name', 'users.email as email', 'users.address as address', 'users.gender as gender', 'users.points as points',
            //     'users.mobile_number as mobile_number', 'courses.name as courseName', 'courses.name_bn as course_name_bn', 'users.current_course_id as current_course_id' )
            //     ->first();

            //     $user = (object)[
            //       "id" => $user_data->id,
            //       "name" =>$user_data->name,
            //       "email" =>$user_data->email,
            //       "mobile_number" =>$user_data->mobile_number,
            //       "gender" => $user_data->gender,
            //       "points" => $user_data->points,
            //       "address" => $user_data->address,
            //       "courseName" => $user_data->courseName,
            //       "current_course_id" => $user_data->current_course_id
            //    ];
            //    if ($user_data->current_course_id != null ) {
            //       $subjects = CourseSubject::where('course_id', $user_data->current_course_id)
            //       ->has('chapters')
            //       ->join('subjects','course_subjects.subject_id','=','subjects.id')
            //       ->select('subjects.name as name', 'subjects.name_bn as name_bn', 'subjects.id as id', 'subjects.color_name as color_name')
            //       ->get();
            //    }
            //    $bmoocCorner = BmoocCorner::all();

            //    $result_data =  (object)[
            //        'user' => $user,
            //        'subjects' => $subjects,
            //        'bmooc_corner' => $bmoocCorner
            //    ];

                $verify = new VerifyCodeController();

                $response->result =  $verify->getLoginData($id);

                $response->status = $response::status_ok;
                $response->messages = "Successfully Updated";

                } else {
                    $response->status = $response::status_fail;
                    $response->messages = "User not found";
                }
                // return $user;


                return FacadeResponse::json($response);
             } catch (\Illuminate\Database\QueryException $e) {

                $response->status = $response::status_fail;
                $response->messages = "Unexpected field";
                return FacadeResponse::json($response);
            }
         }
        // return $data['id'];
    }

    public function updateUserImage(Request $request, $id) {
        $response = new ResponseObject;
        $user= User::where('id', $id)->first();
        if ($user) {
            $userName = explode(" ", $user->name);
            if($user->image) {
                if(file_exists('uploads/userImages/'.$user->image)){
                    unlink('uploads/userImages/'.$user->image);
                }
            }
            $file = $request->file('image');
            $data= $request;
            $data['filename'] = $userName[0].time().'.'.$file->getClientOriginalExtension();

            $destinationPath = 'uploads/userImages';
            $file->move($destinationPath,$data['filename']);

            try {
            User::where('id', $id)->update(['image' => $data['filename']]);
            $response->status = $response::status_ok;
            $response->messages = "Successfully updated profile picture";

            $verifyController = new VerifyCodeController();



            $result_data = $verifyController->getLoginData($id);
            $response->result = $result_data;


            return FacadeResponse::json($response);
            } catch (\Illuminate\Database\QueryException $e) {

                $response->status = $response::status_fail;
                $response->messages = "Update failed";
                return FacadeResponse::json($response);
            }

        } else {
            $response->status = $response::status_fail;
            $response->messages = "No user found";
            return FacadeResponse::json($response);
        }
    }

    public function getUserImage ($id) {
        $response = new ResponseObject;
        $user= User::where('id', $id)->first();
        if ($user) {
            return FacadeResponse::json($user->image);

        } else {
            $response->status = $response::status_fail;
            $response->messages = "No user found";
            return FacadeResponse::json($response);
        }
    }

    public function savePoint (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'user_id' => 'required',
            'points' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }


        if ($request->course_id == 9) {
            $resultSubject = new ResultSubjectController();
            $result = $resultSubject->saveSLCResult($request);
            $user = User::where('id', $data['user_id'])->first();
            $points = $request->points + $user->points;
            User::where('id', $data['user_id'])->update(['points' => $points]);

            $response->status = $response::status_ok;
            $response->messages = "Thank you for participation.";
            $response->result = $result;
            return FacadeResponse::json($response);

        } else {

        $user = User::where('id', $data['user_id'])->first();

        $points = $request->points + $user->points;

        User::where('id', $data['user_id'])->update(['points' => $points]);
            $response->status = $response::status_ok;
            $response->messages = "Point has been added";
            $response->result = $points;
            return FacadeResponse::json($response);

        }
    }

    public function updateFCM (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'user_id' => 'required',
            'fcm_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        $user = User::where('id', $data['user_id'])->first();
        User::where('id', $data['user_id'])->update(['fcm_id' => $data['fcm_id']]);
        $response->status = $response::status_ok;
        $response->messages = "FCM has been updated";
        return FacadeResponse::json($response);
    }

    public function lampFormSubmission(Request $request)
    {
        $response = new ResponseObject;

        $validator = Validator::make(
            $request->json()->all(),
            [
                "id" => "required",
                "name" => "required",
                "email" => "required|email"
            ]
        );

        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        $user = User::where('id', $request->id)->first();
        if($user->isLampFormSubmitted) {
            $response->status = $response::status_fail;
            $response->messages = "Your have already applied.";
            return FacadeResponse::json($response);
        }
        $lampController = new LampController();
        $lampController->store($request);
        $user->update([
            "name" => $request->name,
            "email" => $request->email,
            "gender" => $request->gender,
            "university_id" => $request->university_id,
            'isLampFormSubmitted' => true,
            'lamp_aplication_date' => date('Y-m-d')
        ]);
        $email = $request->email; // $_POST['email'];
        $name = $request->name;
        // $subject = $request->subject;

        $uploadStatus = 1;

        // Recipient
        $toEmail = 'info@bacbonltd.com';

        // Sender
        $from = $email;
        $fromName = 'Bacbon School';

        // Subject
        $emailSubject = 'Form Submitted by ' . $name;

        $htmlContent = '<html><body>';
        $htmlContent .= '<h2>Form Submission</h2>';
        $htmlContent .= '<p><b>Name:</b> '. $name.'</p>';
        $htmlContent .= '<p><b>Organization / University:</b> ' . $request->organization . '</p>';
        $htmlContent .= '<p><b>Gender:</b> ' .  $request->gender . '</p>';
        $htmlContent .= '<p><b>Age:</b> ' .  $request->age . '</p>';
        $htmlContent .= '<p><b>Phone number:</b> ' .  $user->mobile_number . '</p>';
        $htmlContent .= '<p><b>Passport:</b> ' . $request->passport .'</p>';
        $htmlContent .= '<p><b>Joining reason:</b> ' .  $request->reason . '</p>';
        $htmlContent .= '<p><b>Background and interests:</b> ' .  $request->background . '</p>';
        $htmlContent .= '<p><b>Process of contribution:</b> ' .  $request->contributionProcess . '</p>';
        $htmlContent .= '<p><b>Remark / Questions:</b> ' .  $request->remark . '</p>';
        $htmlContent .= '</body></html>';


        $headers = "From: $fromName" . " <" . $from . ">";
        $headers .= "\r\n" . "MIME-Version: 1.0";
        $headers .= "\r\n" . "Content-Type: text/html; charset=utf-8";
        $headers .= "Reply-To: The Sender <". $from. ">\r\n";
        $headers .= "Return-Path: The Sender <". $from. ">\r\n";


        $success = false;
        if ($request->file) {
            $destinationPath = 'uploads/lamp_resume/';
            $file = base64_decode($request->file);
            $ext = $request->ext;
            $fileName = $emailSubject. '.' . $ext;
            $uploadedFile = $destinationPath.$fileName;
            $success = file_put_contents($destinationPath . $fileName, $file);
        }
        if ($success) {

            // Boundary
            $semi_rand = md5(time());
            $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

            // Headers for attachment
            $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

            // Multipart boundary
            $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
            "Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n";

            // Preparing attachment
            if(is_file($uploadedFile)){
                $message .= "--{$mime_boundary}\n";
                $fp =    @fopen($uploadedFile,"rb");
                $data =  @fread($fp,filesize($uploadedFile));
                @fclose($fp);
                $data = chunk_split(base64_encode($data));
                $message .= "Content-Type: application/octet-stream; name=\"".basename($uploadedFile)."\"\n" .
                "Content-Description: ".basename($uploadedFile)."\n" .
                "Content-Disposition: attachment;\n" . " filename=\"".basename($uploadedFile)."\"; size=".filesize($uploadedFile).";\n" .
                "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
            }

            $message .= "--{$mime_boundary}--";
            $returnpath = "-f" . $email;

            // Send email
            $mail = mail($toEmail, $emailSubject, $message, $headers, $returnpath);

            // Delete attachment file from the server
            @unlink($uploadedFile);
        } else {
            $mail = mail($toEmail, $emailSubject, $htmlContent, $headers);
        }


        // If mail sent
        if ($mail) {

        $response->status = $response::status_ok;
        $response->messages = "Thank you " . $name . ". You application has been submitted successfully";
        $response->result = User::where('id', $request->id)->first();

        return FacadeResponse::json($response);
        } else {

        $response->status = $response::status_fail;
        $response->messages = "Your submission has been failed, please try again";
        return FacadeResponse::json($response);
        }
    }

    public function getLampDeadline () {
        $object = (Object) [
            "japan" => "8th Feb - 15th Feb 2019",
            "mayanmar" => "27th Apr - 3rd May 2019",
            "bangladesh" => "20th October, 2019"
        ];
        return FacadeResponse::json($object);
    }
    public function getUserNumberByGender () {
        $usersCount = User::select('gender',
                            DB::raw('count(*) as total')
                            )
                            ->groupBy('gender')
                        ->get();

        return FacadeResponse::json($usersCount);
    }

    public function getUserNumberByCountry () {
        $usersCount = User::select('isBangladeshi',
                            DB::raw('count(*) as total')
                            )
                            ->groupBy('isBangladeshi')
                            ->orderBy('isBangladeshi', 'desc')
                        ->get();

        return FacadeResponse::json($usersCount);
    }

    public function getUserNumberByCourse () {
        $usersCount = User::join('courses', 'users.current_course_id', 'courses.id')
                        ->select('courses.name',
                            DB::raw('count(*) as total')
                            )
                            ->groupBy('courses.name')
                        ->get();

        return FacadeResponse::json($usersCount);
    }

    public function submitContactForm(Request $request) {
        $request->message = strip_tags($request->message, '');
        $response = new ResponseObject;

        if ($this->sendEMail($request)) {

            $response->status = $response::status_ok;
            $response->messages = "Thank you. Your form has been submitted successfully";
            return FacadeResponse::json($response);
        } else {

            $response->status = $response::status_fail;
            $response->messages = "Please try again later.";
            return FacadeResponse::json($response);
        }
    }

    private function sendEMail($data) {
        // Recipient
        $toEmail = 'contact@bacbonschool.com';

        // Sender
        $from = $data->email;

        // Subject
        $emailSubject = $data->subject;


        $htmlContent = $data->message. "\r\n". "Thank you". "\r\n". $data->name;
        $headers = $data->email. ' | ' .$emailSubject;

        return mail($toEmail, $emailSubject, $htmlContent, $headers);
    }

    public function applyForCareer (Request $request)
    {

        $response = new ResponseObject;
        $data = json_decode($request->data, true);
        // return FacadeResponse::json($data);



        $data['message'] = strip_tags($data['message'], '');
        $data['message'] = strip_tags($data['message'], '');


        $email = new PHPMailer();
        $email->SetFrom('mehedirueen@gmail.com', $data['name']); //Name is optional
        $email->Subject   = $data['subject'];
        $email->Body      = $data['name']. ' has applied with expected salary '. $data['salary']."\r\n".$data['message'];
        $email->AddAddress( 'career@bacbonschool.com' );

        if ($request->hasFile('file') ) {
            $rand = time();
            $file = $request->file('file');
            $filename = $data['name'].'-'.$rand.'-file.'.$file->getClientOriginalExtension();
            $destinationPath = 'uploads/careers';
            $file->move($destinationPath,$filename);


            $file_to_attach = 'uploads/careers/'.$filename;

            $email->AddAttachment( $file_to_attach , $filename );

            if ($email->Send()) {
                unlink($file_to_attach);

                $response->status = $response::status_ok;
                $response->messages = "Thank you for submission";
                return FacadeResponse::json($response);
            }

        } else {
                $email->Send();
                $response->status = $response::status_ok;
                $response->messages = "Thank you for submission";
                return FacadeResponse::json($response);
        }


    }

    public function submitCareerFormMobile (Request $request)
    {

        $response = new ResponseObject;
        $data = json_decode($request->data, true);

        // return FacadeResponse::json($data);

        if (array_key_exists('cover_letter', $data)) {
            $data['cover_letter'] = strip_tags($data['cover_letter'], '');
            $data['cover_letter'] = strip_tags($data['cover_letter'], '');
        }

        $email = new PHPMailer();
        $email->SetFrom('mehedirueen@gmail.com', $data['name']); //Name is optional
        $email->Subject   = $data['subject'];

        if (array_key_exists('salary', $data)) {
            $email->Body      = $data['name'].' has applied with expected salary '. $data['salary']."\r\n". array_key_exists('cover_letter', $data) ? $data['cover_letter'] : ''. '\r\n Email: '. $data['email'];
        } else {
            $email->Body      = $data['name']. ' has applied.'."\r\n". array_key_exists('cover_letter', $data) ? $data['cover_letter'] : ''. '\r\n Email: '. $data['email'];
        }


        $email->AddAddress( 'career@bacbonschool.com' );

        if ($request->hasFile('file') ) {
            $rand = time();
            $file = $request->file('file');
            $filename = $data['name'].'-'.$rand.'-file.'.$file->getClientOriginalExtension();
            $destinationPath = 'uploads/careers';
            $file->move($destinationPath,$filename);


            $file_to_attach = 'uploads/careers/'.$filename;

            $email->AddAttachment( $file_to_attach , $filename );

            if ($email->Send()) {
                unlink($file_to_attach);

                $response->status = $response::status_ok;
                $response->messages = "Thank you for submission";
                return FacadeResponse::json($response);
            }

        } else {
                $email->Send();
                $response->status = $response::status_ok;
                $response->messages = "Thank you for submission";
                return FacadeResponse::json($response);
        }

    }

    public function sendResetPasswordCode2(Request $request) {
        $user = User::where('email', $request->email)->first();

        $response = new ResponseObject;
        if (count((array)$user)) {
            $digits = 6;
            $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
          ResetPassword::create([
            "user_id" => $user->id,
            "reset_code" => $code,
            "reset_till" => Carbon::now()->addMinutes(30)
        ]);
        $emailSubject = "Bacbon School | Forgot Password";
        $message = "Your password recovery code, Please, use this secret code at bacbon school within 30 minutes. ";

        $htmlContent = $message. "\r\n". $code ."\r\n". "Thank you". "\r\n". "BacBon School";
        $headers = 'support@bacbonschool.com'. ' | ' .$emailSubject;

        mail($request->email, $emailSubject, $htmlContent, $headers);
        $obj = (Object) [
            "id" => $user->id
        ];

            $response->status = $response::status_ok;
            $response->messages = "We have sent you secret code to your email";
            $response->result = $obj;
            return FacadeResponse::json($response);

        } else {
            $response->status = $response::status_fail;
            $response->messages = "No account found with this email";
            return FacadeResponse::json($response);
        }
    }

    public function sendResetPasswordCode(Request $request)
    {
        $response = new ResponseObject;

        $user = User::where('email', $request->email)->first();

        if(empty($user)){
            $response->status = $response::status_fail;
            $response->messages = "No user found!";
            return FacadeResponse::json($response);
        }

        if (count((array)$user)) {
            $digits = 6;
            $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
          ResetPassword::create([
            "user_id" => $user->id,
            "reset_code" => $code,
            "reset_till" => Carbon::now()->addMinutes(30)
        ]);

        $message = "Your password recovery code is: <strong>". $code ."</strong>. Please, use this secret code at bacbon school within 30 minutes.";

        $mail = new PHPMailer();

        // $response->status = $response::status_fail;
        // $response->messages = "MK!";
        // return FacadeResponse::json($response);
        try {
            // //Server settings
            $mail->SMTPDebug = 0;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'mail.bacbonschool.com';               // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                              // Enable SMTP authentication
            $mail->Username = "contact@bacbonschool.com";        // SMTP username
            $mail->Password = "bi?#dJba2?DqRs&HDf{Zs@Y!#n*k+r55Rs5z";


            // SMTP password
            $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;                                    // TCP port to connect to


            $mail->setFrom('contact@bacbonschool.com', 'Password Reset | BacBon School');
            $mail->addAddress($user->email, $user->name);      // Name is optional
            // $mail->addReplyTo('no-reply@bacbonschool.com', 'Register');
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = "Bacbon School | Forgot Password";
            $mail->Body    = '<html style="width:100%;font-family:lato, "helvetica neue", helvetica, arial, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0;">
                            <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

                            <meta content="width=device-width, initial-scale=1" name="viewport">
                            <meta name="x-apple-disable-message-reformatting">
                            <meta http-equiv="X-UA-Compatible" content="IE=edge">
                            <meta content="telephone=no" name="format-detection">
                            <title>New email</title>
                            <!--[if (mso 16)]>
                                <style type="text/css">
                                a {text-decoration: none;}
                                </style>
                                <![endif]-->
                            <!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]-->
                            <!--[if !mso]><!-- -->
                            <link href="https://fonts.googleapis.com/css?family=Lato:400,400i,700,700i" rel="stylesheet">
                            <!--<![endif]-->
                            <style type="text/css">
                            @media only screen and (max-width:600px) {p, ul li, ol li, a { font-size:16px!important; line-height:150%!important } h1 { font-size:30px!important; text-align:center; line-height:120%!important } h2 { font-size:26px!important; text-align:center; line-height:120%!important } h3 { font-size:20px!important; text-align:center; line-height:120%!important } h1 a { font-size:30px!important } h2 a { font-size:26px!important } h3 a { font-size:20px!important } .es-menu td a { font-size:16px!important } .es-header-body p, .es-header-body ul li, .es-header-body ol li, .es-header-body a { font-size:16px!important } .es-footer-body p, .es-footer-body ul li, .es-footer-body ol li, .es-footer-body a { font-size:16px!important } .es-infoblock p, .es-infoblock ul li, .es-infoblock ol li, .es-infoblock a { font-size:12px!important } *[class="gmail-fix"] { display:none!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3 { text-align:right!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-button-border { display:block!important } a.es-button { font-size:20px!important; display:block!important; border-width:15px 25px 15px 25px!important } .es-btn-fw { border-width:10px 0px!important; text-align:center!important } .es-adaptive table, .es-btn-fw, .es-btn-fw-brdr, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .es-adapt-td { display:block!important; width:100%!important } .adapt-img { width:100%!important; height:auto!important } .es-m-p0 { padding:0px!important } .es-m-p0r { padding-right:0px!important } .es-m-p0l { padding-left:0px!important } .es-m-p0t { padding-top:0px!important } .es-m-p0b { padding-bottom:0!important } .es-m-p20b { padding-bottom:20px!important } .es-mobile-hidden, .es-hidden { display:none!important } .es-desk-hidden { display:table-row!important; width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } .es-desk-menu-hidden { display:table-cell!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } table.es-social { display:inline-block!important } table.es-social td { display:inline-block!important } }
                            #outlook a {
                                padding:0;
                            }
                            .ExternalClass {
                                width:100%;
                            }
                            .ExternalClass,
                            .ExternalClass p,
                            .ExternalClass span,
                            .ExternalClass font,
                            .ExternalClass td,
                            .ExternalClass div {
                                line-height:100%;
                            }
                            .es-button {
                                mso-style-priority:100!important;
                                text-decoration:none!important;
                            }
                            a[x-apple-data-detectors] {
                                color:inherit!important;
                                text-decoration:none!important;
                                font-size:inherit!important;
                                font-family:inherit!important;
                                font-weight:inherit!important;
                                line-height:inherit!important;
                            }
                            .es-desk-hidden {
                                display:none;
                                float:left;
                                overflow:hidden;
                                width:0;
                                max-height:0;
                                line-height:0;
                                mso-hide:all;
                            }
                            </style>
                            </head>
                            <body style="width:100%;font-family:lato, "helvetica neue", helvetica, arial, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0;">
                            <div class="es-wrapper-color" style="background-color:#F4F4F4;">
                            <!--[if gte mso 9]>
                                        <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
                                            <v:fill type="tile" color="#f4f4f4"></v:fill>
                                        </v:background>
                                    <![endif]-->
                            <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;">
                                <tr class="gmail-fix" height="0" style="border-collapse:collapse;">
                                <td style="padding:0;Margin:0;">
                                <table width="600" cellspacing="0" cellpadding="0" border="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                    <tr style="border-collapse:collapse;">
                                    <td cellpadding="0" cellspacing="0" border="0" style="padding:0;Margin:0;line-height:1px;min-width:600px;" height="0"><img src="https://esputnik.com/repository/applications/images/blank.gif" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;max-height:0px;min-height:0px;min-width:600px;width:600px;" alt width="600" height="1"></td>
                                    </tr>
                                </table></td>
                                </tr>
                                <tr style="border-collapse:collapse;">
                                <td valign="top" style="padding:0;Margin:0;">
                                <table class="es-content" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;">
                                    <tr style="border-collapse:collapse;">
                                    <td align="center" bgcolor="transparent" style="padding:0;Margin:0;background-color:transparent;">
                                    <table class="es-content-body" width="600" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;">
                                        <tr style="border-collapse:collapse;">
                                        <td align="left" style="Margin:0;padding-bottom:10px;padding-left:10px;padding-right:10px;padding-top:20px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                            <tr style="border-collapse:collapse;">
                                            <td width="580" valign="top" align="center" style="padding:0;Margin:0;">
                                            <table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                                <tr style="border-collapse:collapse;">

                                                <td align="center" style="padding:0;Margin:0;">
                                                <div  style=" background: url(http://admin.bacbonschool.com/img/bschool_logo_white.jpg); background-repeat: no-repeat;background-size: 390px auto; display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; width: 400px; height: 78px" width="200">
                                                </div>
                                                </td>
                                                </tr>
                                            </table></td>
                                            </tr>
                                        </table></td>
                                        </tr>
                                    </table></td>
                                    </tr>
                                </table>
                                <table class="es-content" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;">
                                    <tr style="border-collapse:collapse;">
                                    <td style="padding:0;Margin:0;background-color:transparent;" bgcolor="transparent" align="center">
                                    <table class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;" width="600" cellspacing="0" cellpadding="0" align="center" bgcolor="rgba(0, 0, 0, 0)">
                                        <tr style="border-collapse:collapse;">
                                        <td align="left" style="padding:0;Margin:0;">
                                        <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                            <tr style="border-collapse:collapse;">
                                            <td width="600" valign="top" align="center" style="padding:0;Margin:0;">
                                            <table style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;background-color:#FFFFFF;border-radius:4px;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff" role="presentation">

                                                <tr style="border-collapse:collapse;">
                                                <td bgcolor="#ffffff" align="center" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:20px;padding-right:20px;">
                                                <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                                    <tr style="border-collapse:collapse;">
                                                    <td style="padding:0;Margin:0px;border-bottom:1px solid #FFFFFF;background:rgba(0, 0, 0, 0) none repeat scroll 0% 0%;height:1px;width:100%;margin:0px;"></td>
                                                    </tr>
                                                </table></td>
                                                </tr>
                                            </table></td>
                                            </tr>
                                        </table></td>
                                        </tr>
                                    </table></td>
                                    </tr>
                                </table>
                                <table class="es-content" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;">
                                    <tr style="border-collapse:collapse;">
                                    <td align="center" style="padding:0;Margin:0;">
                                    <table class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;" width="600" cellspacing="0" cellpadding="0" align="center">
                                        <tr style="border-collapse:collapse;">
                                        <td align="left" style="padding:0;Margin:0;">
                                        <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                            <tr style="border-collapse:collapse;">
                                            <td width="600" valign="top" align="center" style="padding:0;Margin:0;">
                                            <table style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-radius:4px;background-color:#FFFFFF;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff" role="presentation">
                                                <tr style="border-collapse:collapse;">
                                                <td class="es-m-txt-l" align="left" style="padding:0;Margin:0;padding-top:20px;padding-left:30px;padding-right:30px;"><b>Dear </b> <br>'. $user->name .'</td>
                                                </tr>

                                                <tr style="border-collapse:collapse;">
                                                <td class="es-m-txt-l" align="left" style="padding:0;Margin:0;padding-top:20px;padding-left:30px;padding-right:30px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato, "helvetica neue", helvetica, arial, sans-serif;line-height:27px;color:#666666;">' .$message.' <br><br></p></td>
                                                </tr>

                                                <tr style="border-collapse:collapse;">
                                                <td class="es-m-txt-l" align="left" style="Margin:0;padding-top:20px;padding-left:30px;padding-right:30px;padding-bottom:40px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:18px;font-family:lato, "helvetica neue", helvetica, arial, sans-serif;line-height:27px;color:#666666;">Thank you,<br> BacBon School TEAM,<br>BacBon Ltd.</p></td>
                                                </tr>
                                            </table></td>
                                            </tr>
                                        </table></td>
                                        </tr>
                                    </table></td>
                                    </tr>
                                </table>
                                <table class="es-content" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;">
                                    <tr style="border-collapse:collapse;">
                                    <td align="center" style="padding:0;Margin:0;">
                                    <table class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;" width="600" cellspacing="0" cellpadding="0" align="center">
                                        <tr style="border-collapse:collapse;">
                                        <td align="left" style="padding:0;Margin:0;">
                                        <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                            <tr style="border-collapse:collapse;">
                                            <td width="600" valign="top" align="center" style="padding:0;Margin:0;">
                                            <table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                                <tr style="border-collapse:collapse;">
                                                <td align="center" style="Margin:0;padding-top:10px;padding-bottom:20px;padding-left:20px;padding-right:20px;">
                                                <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                                    <tr style="border-collapse:collapse;">
                                                    <td style="padding:0;Margin:0px;border-bottom:1px solid #F4F4F4;background:rgba(0, 0, 0, 0) none repeat scroll 0% 0%;height:1px;width:100%;margin:0px;"></td>
                                                    </tr>
                                                </table></td>
                                                </tr>
                                            </table></td>
                                            </tr>
                                        </table></td>
                                        </tr>
                                    </table></td>
                                    </tr>
                                </table>
                                <table class="es-content" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;">
                                    <tr style="border-collapse:collapse;">
                                    <td align="center" style="padding:0;Margin:0;">
                                    <table class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;" width="600" cellspacing="0" cellpadding="0" align="center">
                                        <tr style="border-collapse:collapse;">
                                        <td align="left" style="padding:0;Margin:0;">
                                        <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                            <tr style="border-collapse:collapse;">
                                            <td width="600" valign="top" align="center" style="padding:0;Margin:0;">
                                            <table style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;background-color:#FFECD1;border-radius:4px;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffecd1" role="presentation">
                                                <tr style="border-collapse:collapse;">
                                                <td align="center" style="padding:0;Margin:0;padding-top:15px;padding-left:30px;padding-right:30px;"><h3 style="Margin:0;line-height:24px;mso-line-height-rule:exactly;font-family:lato, "helvetica neue", helvetica, arial, sans-serif;font-size:20px;font-style:normal;font-weight:normal;color:#111111;">Need more help?</h3></td>
                                                </tr>
                                                <tr style="border-collapse:collapse;">
                                                <td esdev-links-color="#ffa73b" align="center" style="Margin:0;padding-top:15px;padding-bottom:15px;padding-left:30px;padding-right:30px;"><a target="_blank" href="https://bacbonschool.com/contact-us" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato, "helvetica neue", helvetica, arial, sans-serif;font-size:18px;text-decoration:underline;color:#033D75;">Contact Us</a></td>
                                                </tr>
                                            </table></td>
                                            </tr>
                                        </table></td>
                                        </tr>
                                    </table></td>
                                    </tr>
                                </table>
                                <table cellpadding="0" cellspacing="0" class="es-footer" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top;">
                                    <tr style="border-collapse:collapse;">
                                    <td align="center" style="padding:0;Margin:0;">
                                    <table class="es-footer-body" width="600" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;">
                                        <tr style="border-collapse:collapse;">
                                        <td align="left" style="Margin:0;padding-top:10px;padding-bottom:10px;padding-left:30px;padding-right:30px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                            <tr style="border-collapse:collapse;">
                                            <td width="540" valign="top" align="center" style="padding:0;Margin:0;">
                                            <table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                                <tr style="border-collapse:collapse;">
                                                <td align="left" style="padding:0;Margin:0;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:lato, "helvetica neue", helvetica, arial, sans-serif;line-height:21px;color:#666666;"><br></p></td>
                                                </tr>
                                                <tr style="border-collapse:collapse;">
                                                <td align="left" style="padding:0;Margin:0;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:lato, "helvetica neue", helvetica, arial, sans-serif;line-height:21px;color:#666666;">Mob. +880 18 7260 8521,&nbsp;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;+880 18 7771 5110<br>E-mail: contact@bacbonschool.com</p></td>
                                                </tr>
                                                <tr style="border-collapse:collapse;">
                                                <td align="left" style="padding:0;Margin:0;padding-top:25px;"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:lato, "helvetica neue", helvetica, arial, sans-serif;line-height:21px;color:#666666;">House #13(5th Floor), Block-C, Main Road, Banasree, Rampura, Dhaka-1219</p></td>
                                                </tr>
                                            </table></td>
                                            </tr>
                                        </table></td>
                                        </tr>
                                    </table></td>
                                    </tr>
                                </table>
                                <table class="es-content" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;">
                                    <tr style="border-collapse:collapse;">
                                    <td align="center" style="padding:0;Margin:0;">
                                    <table class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;" width="600" cellspacing="0" cellpadding="0" align="center">
                                        <tr style="border-collapse:collapse;">
                                        <td align="left" style="Margin:0;padding-left:20px;padding-right:20px;padding-top:30px;padding-bottom:30px;">
                                        <table width="100%" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                            <tr style="border-collapse:collapse;">
                                            <td width="560" valign="top" align="center" style="padding:0;Margin:0;">
                                            <table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;">
                                                <tr style="border-collapse:collapse;">
                                                <td class="es-infoblock made_with" align="center" style="padding:0;Margin:0;line-height:14px;font-size:12px;color:#CCCCCC;"><a target="_blank" href="javascript:;" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:lato, "helvetica neue", helvetica, arial, sans-serif;font-size:12px;text-decoration:underline;color:#CCCCCC;"><img src="http://edu-erp-api.bacbonprojects.com/Images/Institute/logo.png" alt width="125" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;"></a></td>
                                                </tr>
                                            </table></td>
                                            </tr>
                                        </table></td>
                                        </tr>
                                    </table></td>
                                    </tr>
                                </table></td>
                                </tr>
                            </table>
                            </div>
                            </body>
                            </html>';
            $mail->AltBody = 'Your password recovery code is: <strong>'. $code .'</strong>. Please, use this secret code at bacbon school within 30 minutes.';

            $mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            ));

            $mail->send();

            $obj = (Object) [
                "id" => $user->id
            ];

            $response->status = $response::status_ok;
            $response->messages = "We have sent you secret code to your email";
            $response->result = $obj;
            return FacadeResponse::json($response);

        } catch (Exception $e) {

            $response->status = $response::status_fail;
            $response->messages = $mail->ErrorInfo;
            return FacadeResponse::json($response);

           // echo 'Message could not be sent.';
           // echo 'Mailer Error: ' . $mail->ErrorInfo;
        }

        } else {
            $response->status = $response::status_fail;
            $response->messages = "No account found with this email";
            return FacadeResponse::json($response);
        }
    }

    public function resetPassword (Request $request) {

        $response = new ResponseObject;

        $resetData = ResetPassword::where('user_id', $request->user_id)
        ->where('reset_code', $request->code)
        ->where('reset_till','>=',Carbon::now())
        ->first();


        if (count((array)$resetData)) {
           $update =  User::where('id', $request->user_id)->update([
                'password' => bcrypt($request->password)
            ]);
            FacadeResponse::json($update);

            $response->status = $response::status_ok;
            $response->messages = "Your password has been updated. Please use new password to login";
            return FacadeResponse::json($response);
        } else {
            $response->status = $response::status_fail;
            $response->messages = "Secret code expired or not found";
            return FacadeResponse::json($response);
        }

    }

    public function setIsEEduPhase3 (Request $request) {
        $count = 0;
        foreach ($request->students as $student) {
            if (User::where('id', $student['b_school_id'])->where('is_staff', false)->update(['is_e_edu_3' => true]))
             $count++;

        }

        return FacadeResponse::json($count);
    }

    public function startCUnit(Request $request) {
        $response = new ResponseObject;
        $userId = $request->user_id;
        $date = date("Y-m-d H:i:s");
        User::where('id', $request->user_id)->update([
            "c_unit_start_date" => $date
        ]);

        $courseController = new CourseController();
        $user = $courseController->getCourseResult($userId, 27);

        $response->status = $response::status_ok;
        $response->messages = "Congratulation! Your C unit course has started now.";
        $response->result = $user;
        return FacadeResponse::json($response);
    }

    public function startBUnit(Request $request) {
        $response = new ResponseObject;
        $userId = $request->user_id;
        $date = date("Y-m-d H:i:s");
        User::where('id', $request->user_id)->update([
            "b_unit_start_date" => $date
        ]);

        $courseController = new CourseController();
        $user = $courseController->getCourseResult($userId, 13);

        $response->status = $response::status_ok;
        $response->messages = "Congratulation! Your B unit course has started now.";
        $response->result = $user;
        return FacadeResponse::json($response);
    }

    public function startDUnit(Request $request) {
        $response = new ResponseObject;
        $userId = $request->user_id;
        $date = date("Y-m-d H:i:s");
        User::where('id', $request->user_id)->update([
            "d_unit_start_date" => $date
        ]);

        $courseController = new CourseController();
        $user = $courseController->getCourseResult($userId, 15);

        $response->status = $response::status_ok;
        $response->messages = "Congratulation! Your D unit course has started now.";
        $response->result = $user;
        return FacadeResponse::json($response);
    }

    public function selectOptionalSubject(Request $request) {
        $response = new ResponseObject;
        User::where('id', $request->user_id)->update([
            "c_unit_optional_subject_id" => $request->subject_id
        ]);


        $courseController = new CourseController();
        $user = $courseController->getCourseResult($request->user_id, 27);

        $response->status = $response::status_ok;
        $response->messages = "Your optional subject has been selected successfully";
        $response->result = $user;
        return FacadeResponse::json($response);
    }

    public function getReferralNumber () {

        $users = User::groupBy('refference_id')
        ->select('refference_id', DB::raw('count(*) as total'))
        ->where('isCompleteRegistration', 1)
        ->get();
        foreach ($users as $user) {
            $u = User::where('id', $user->refference_id)->first();
            if (!is_null($u)) {
            $user->name = $u->name;
            $user->mobile_number = $u->mobile_number;
            }
        }
        return FacadeResponse::json($users);
    }

    public function sendUserMessage (Request $request) {
        $response = new ResponseObject;
        $formData = json_decode($request->data, true);

            try {
                // $userList = User::whereIn('id',[6565])->select('id', 'fcm_id')->get();
                // $userList = User::whereIn('id',[283,32,6,6565,4354,11735])->select('id', 'fcm_id')->get();

                  if($formData['navigate_to_app_location'] == "smart_study_progress"){
                      $userList = User::where('is_jicf_teacher',false)->select('id', 'fcm_id')->where('fcm_id', '!=', null)->get();
                  }else {
                      $userList = User::select('id', 'fcm_id')->where('fcm_id', '!=', null)->get();
                  }

                $count = 0;
                $fcmIds = [];
                
                $thumbnailName = '';
                if ($request->hasFile('thumbnail')) {
                    $thumbnail = $request->file('thumbnail');
                    $time = time();
                    $thumbnailName = "notificatoin_image".$time.'.'.$thumbnail->getClientOriginalExtension();
                    $destinationThumbnail = 'uploads/notification_images';
                    $thumbnail->move($destinationThumbnail,$thumbnailName);
                }

                $fcmData = (array) [
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    "userType" =>"user",
                    "action" =>  "",
                    "showOnApp" => true,
                    "bgColor" => null,
                    "title" =>  $formData['title'],
                    "body" =>  $formData['message'],
                    "image" => $thumbnailName != '' ?"http://".$_SERVER['HTTP_HOST'].'/uploads/notification_images/'.$thumbnailName : null,
                    "navigate_to_app_location" => $formData['navigate_to_app_location']
                ];
                $fcmNotification = (array) [
                    "title" =>  $formData['title'],
                    "body" =>  $formData['message'],
                    "image" => $thumbnailName != '' ?"http://".$_SERVER['HTTP_HOST'].'/uploads/notification_images/'.$thumbnailName : null
                ];


              //  $fcm = new FcmClass();
                $message_counter = 0;
                $bulk_data = [];
                foreach ($userList as $user) {
                    
                    if ($user->fcm_id) {
                        $fcmIds[] = $user->fcm_id;
                        $count++;
                        
                        date_default_timezone_set('Asia/Dhaka');
                        array_push($bulk_data, [
                            'user_id' => $user->id,
                            'title' => $formData['title'],
                            'body' => $formData['message'],
                            'image' => $thumbnailName != '' ?"http://".$_SERVER['HTTP_HOST'].'/uploads/notification_images/'.$thumbnailName : null,
                            'navigate_to_app_location' => $formData['navigate_to_app_location'],
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                        
                        if ($count % 600 == 0) {
                            fcm()
                            ->to($fcmIds) // $recipients must an array
                            ->priority('high')
                            ->timeToLive(0)
                            ->data($fcmData)
                            ->notification($fcmNotification)
                            ->send();
                            
                          $fcmIds = [];
                          //DB::table('user_fcm_messages')->insert($bulk_data);
                          UserFCMMessage::insert($bulk_data);
                          $bulk_data = [];
                        }
                        
                        
                        // UserFCMMessage::create([
                        //     'user_id' => $user->id,
                        //     'title' => $formData['title'],
                        //     'body' => $formData['message'],
                        //     'image' => $thumbnailName != '' ?"http://".$_SERVER['HTTP_HOST'].'/uploads/notification_images/'.$thumbnailName : null,
                        //     'navigate_to_app_location' => $formData['navigate_to_app_location'],
                        //     'created_at' => date('Y-m-d H:i:s') //Carbon::now()->toIso8601String()//Carbon::today($date)->setTimezone('UTC') //date('Y-m-d H:i:s')
                        // ]);
                    }
                    
                }

                //"https://www.tbsnews.net/sites/default/files/styles/big_2/public/images/2020/03/17/coronavirus.jpg"
                //$thumbnailName != '' ?"http://".$_SERVER['HTTP_HOST'].'/uploads/notification_images/'.$thumbnailName : null
                // $fcm->sendAdminFCMNotification($fcmIds, $fcmData);

                //DB::table('user_fcm_messages')->insert($bulk_data);
                UserFCMMessage::insert($bulk_data);
                
                fcm()
                ->to($fcmIds) // $recipients must an array
                ->priority('high')
                ->timeToLive(0)
                ->data($fcmData)
                ->notification($fcmNotification)
                ->send();

                $response->status = $response::status_ok;
                $response->messages = "Message has been sent to users (".$count.")";
                $response->result = null;
                return FacadeResponse::json($response);

            } catch (\Exception $ex) {

                $response->status = $response::status_fail;
                $response->messages = $ex->getMessage();
                $response->result = null;
                return FacadeResponse::json($response);

            }
    }
        
    public function sendSingleMessage (Request $request) {
        $response = new ResponseObject;
            try {
                $userList = User::whereIn('id',[6565,21709,6186])->select('id', 'fcm_id')->get();
                //Rajon ID: 6186

                $count = 0;
                $fcmIds = [];

                $thumbnailName = '';
                if ($request->hasFile('thumbnail')) {
                    $thumbnail = $request->file('thumbnail');
                    $time = time();
                    $thumbnailName = "notificatoin_image".$time.'.'.$thumbnail->getClientOriginalExtension();
                    $destinationThumbnail = 'uploads/notification_images';
                    $thumbnail->move($destinationThumbnail,$thumbnailName);
                }

                $fcmData = (array) [
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    "userType" =>"user",
                    "action" =>  "",
                    "showOnApp" => true,
                    "bgColor" => null,
                    "title" =>  $request->title,
                    "body" =>  $request->message,
                    "image" => $thumbnailName != '' ?"http://".$_SERVER['HTTP_HOST'].'/uploads/notification_images/'.$thumbnailName : null,
                    "navigate_to_app_location" => $request->navigate_to_app_location
                ];
                $fcmNotification = (array) [
                    "title" =>  $request->title,
                    "body" =>  $request->message,
                    "image" => $thumbnailName != '' ?"http://".$_SERVER['HTTP_HOST'].'/uploads/notification_images/'.$thumbnailName : null
                ];

                // $response->status = $response::status_fail;
                // $response->messages = "Test Message";
                // $response->result = [$userList, $fcmData, $fcmNotification];
                // return FacadeResponse::json($response);

                foreach ($userList as $user) {
                    if ($user->fcm_id) {
                        $fcmIds[] = $user->fcm_id;
                        $count++;
                        if ($count % 20 == 0) {
                            fcm()
                            ->to($fcmIds) // $recipients must an array
                            ->priority('high')
                            ->timeToLive(0)
                            ->data($fcmData)
                            ->notification($fcmNotification)
                            ->send();
                            $fcmIds = [];
                        }
                        date_default_timezone_set('Asia/Dhaka');
                        UserFCMMessage::create([
                            'user_id' => $user->id,
                            'title' => $request->title,
                            'body' => $request->message,
                            'image' => $thumbnailName != '' ?"http://".$_SERVER['HTTP_HOST'].'/uploads/notification_images/'.$thumbnailName : null,
                            'navigate_to_app_location' => $request->navigate_to_app_location,
                            'created_at' => date('Y-m-d H:i:s') //Carbon::now()->toIso8601String()//Carbon::today($date)->setTimezone('UTC') //date('Y-m-d H:i:s')
                        ]);

                    }
                }

                fcm()
                ->to($fcmIds) // $recipients must an array
                ->priority('high')
                ->timeToLive(0)
                ->data($fcmData)
                ->notification($fcmNotification)
                ->send();

                $response->status = $response::status_ok;
                $response->messages = "Message has been sent to users (".$count.")";
                $response->result = null;
                return FacadeResponse::json($response);

            } catch (\Exception $ex) {

                $response->status = $response::status_fail;
                $response->messages = $ex->getMessage();
                $response->result = null;
                return FacadeResponse::json($response);
            }
    }

    public function jicfTutorStatusUpdate(Request $request){
        $response = new ResponseObject;
        $notFoundStudentList = [];
        $count = 0;
        foreach ($request->items as $item) {
            if(isset($item['mobile_number'])){
                $student = User::where('mobile_number', '0'.$item['mobile_number'])->first();
                if (is_null($student)) {
                    $notFoundStudentList[] = $item;
                } else {
                    $student->update([
                        "is_jicf_teacher" => true
                        ]);
                    $count++;
                }
            }

        }

        $obj = (Object) [
            "connection_done" => $count,
            "not_found" => $notFoundStudentList
            ];

        $response->status = $response::status_ok;
        $response->messages = "";
        $response->result = $obj;
        return FacadeResponse::json($response);

    }


// public function sendUserMessage (Request $request) {
    //     $response = new ResponseObject;
    //     $formData = json_decode($request->data, true);

    //     try {
    //         $userList = User::whereIn('id',[283])->select('id', 'fcm_id')->get();
    //         $count = 0;
    //         $fcmIds = [];


    //         $thumbnailName = '';
    //         if ($request->hasFile('thumbnail')) {
    //             $thumbnail = $request->file('thumbnail');
    //             $time = time();
    //             $thumbnailName = "notificatoin_image".$time.'.'.$thumbnail->getClientOriginalExtension();
    //             $destinationThumbnail = 'uploads/notification_images';
    //             $thumbnail->move($destinationThumbnail,$thumbnailName);
    //         }

    //         $fcmData = (array) [
    //             "userType" =>"user",
    //             "action" =>  "",
    //             "showOnApp" => true,
    //             "bgColor" => null,
    //             "title" =>  $request->title,
    //             "body" =>  $request->message,
    //             "image" => $thumbnailName != '' ?"http://".$_SERVER['HTTP_HOST'].'/uploads/notification_images/'.$thumbnailName : null
    //         ];

    //         $fcm = new FcmClass();
    //         foreach ($userList as $user) {
    //             if ($user->fcm_id) {
    //                 $fcmIds[] = $user->fcm_id;
    //                 $count++;
    //                 if ($count % 20 == 0) {
    //                   $fcm->sendAdminFCMNotification($fcmIds, $fcmData);
    //                   $fcmIds = [];
    //                 }
    //                 date_default_timezone_set('Asia/Dhaka');
    //                 UserFCMMessage::create([
    //                     'user_id' => $user->id,
    //                     'title' => $formData['title'],
    //                     'body' => $formData['message'],
    //                     'image' => $thumbnailName != '' ?"http://".$_SERVER['HTTP_HOST'].'/uploads/notification_images/'.$thumbnailName : null,
    //                     'created_at' => date('Y-m-d H:i:s') //Carbon::now()->toIso8601String()//Carbon::today($date)->setTimezone('UTC') //date('Y-m-d H:i:s')
    //                 ]);

    //             }
    //         }

    //         $fcm->sendAdminFCMNotification($fcmIds, $fcmData);
    //         $response->status = $response::status_ok;
    //         $response->messages = "Message has been sent to users (".$count.")";
    //         $response->result = null;
    //         return FacadeResponse::json($response);

    //     } catch (\Exception $ex) {

    //         $response->status = $response::status_fail;
    //         $response->messages = $ex->getMessage();
    //         $response->result = null;
    //         return FacadeResponse::json($response);

    //     }
    // }


    //   public function sendUserMessage (Request $request) {
    //     $response = new ResponseObject;
    //     try {
    //         $userList = User::where('id',32)->select('id', 'fcm_id')->get();
    //         $count = 0;
    //         $fcmIds = [];

    //         $fcmData = (array) [
    //             "userType" =>"user",
    //             "action" =>  "",
    //             "showOnApp" => true,
    //             "bgColor" => null,
    //             "title" =>  $request->title,
    //             "body" =>  $request->message,
    //             "image" => "https://api.bacbonschool.com/uploads/promotions/Promo1616991994.png"
    //         ];

    //         $fcm = new FcmClass();
    //         foreach ($userList as $user) {
    //             if ($user->fcm_id) {
    //                 $fcmIds[] = $user->fcm_id;
    //                 $count++;
    //                 if ($count % 20 == 0) {
    //                   $fcm->sendAdminFCMNotification($fcmIds, $fcmData);
    //                   $fcmIds = [];
    //                 }
    //                 date_default_timezone_set('Asia/Dhaka');
    //                 UserFCMMessage::create([
    //                     'user_id' => $user->id,
    //                     'title' => $request->title,
    //                     'body' => $request->message,
    //                     'image' => "https://api.bacbonschool.com/uploads/promotions/Promo1616991994.png",
    //                     'created_at' => date('Y-m-d H:i:s') //Carbon::now()->toIso8601String()//Carbon::today($date)->setTimezone('UTC') //date('Y-m-d H:i:s')
    //                 ]);

    //             }
    //         }

    //         $fcm->sendAdminFCMNotification($fcmIds, $fcmData);
    //         $response->status = $response::status_ok;
    //         $response->messages = "Message has been sent to users (".$count.")";
    //         $response->result = null;
    //         return FacadeResponse::json($response);

    //     } catch (\Exception $ex) {

    //         $response->status = $response::status_fail;
    //         $response->messages = $ex->getMessage();
    //         $response->result = null;
    //         return FacadeResponse::json($response);

    //     }
    // }




}
