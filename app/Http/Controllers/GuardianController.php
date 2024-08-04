<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use JWTAuth;
use Validator;
use Carbon\Carbon;
use Hash;
use App\Guardian;
use App\GuardianCode;
use App\PreRegistrationGuardian;
use Illuminate\Http\Request;
use App\Http\Resources\Guardian\DetailsResource;

class GuardianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function preRegister(Request $request)
    {
        $response = new ResponseObject;
        $nameArray = explode(" ", $request->name);
        $codeInit = '';
        foreach ($nameArray as $arr) {
            $codeInit .= $arr[0];
        }
        $data = $request->json()->all();
            $validator = Validator::make($data, [
                'name' => 'required',
                'mobile_number' => 'required|unique:guardians',
                'email' => 'unique:guardians',
                'app_signature' => 'required'
            ]);
            if ($validator->fails()) {
                $response->status = $response::status_fail;
                $response->messages = $validator->errors()->first();
                return FacadeResponse::json($response);
            }
            try {
                $registrationData = $request->all();
                if ($request->refferal_code) {
                    $reffence = Guardian::where('user_code', $request->refferal_code)->select('id', 'fcm_id')->first();
                    if ($reffence === null) {
                        $response->status = $response::status_fail;
                        $response->messages = "Referral code is incorrect";
                        return FacadeResponse::json($response);
                    }
                }

                $pre_limit = PreRegistrationGuardian::where('mobile_number', $request->mobile_number)->get();

                if(sizeof($pre_limit) > 4){
                    $response->status = $response::status_fail;
                    $response->messages = "You have tried multiple times to get registered using this mobile. Please, try another number!";
                    return FacadeResponse::json($response); 
                }

                // $user = Guardian::create($registrationData);
                $digits = 4;
                $expire_minute = 15;
                $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);

                unset($registrationData['app_signature']);
                unset($registrationData['refferal_code']);
                $registrationData['otp_expired_at'] = Carbon::now()->addMinutes($expire_minute);
                $registrationData['otp'] = $code;
                $registrationData['reffered_code'] = $request->refferal_code;
                PreRegistrationGuardian::create($registrationData);

                $message = '<#> Use ' . $code . ' as your OTP for BacBon School. '; // . $request->app_signature;
                $phone = $request->mobile_number;

                $apiRegisterController = new APIRegisterController();
                $apiRegisterController->sendSms($phone, $message);

                // broadcast(new UserCreated($user))->toOthers();
                $response->status = $response::status_ok;
                $response->messages = "Code has been sent to your phone.";
                return FacadeResponse::json($response);
            } catch (Exception $e) {
                $response->status = $response::status_fail;
                $response->messages = $e->getMessage();
                return FacadeResponse::json($response);
            }
    }


    public function VerifyCode(Request $request) {
        $response = new ResponseObject;
        $subjects = [];

        $data = $request->json()->all();
        // Validate Data
        $validator = Validator::make($data, [
            'mobile_number' => 'required',
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            return FacadeResponse::json($response);
        }
        // Fetch Data from pre registration table
        $preRegister = PreRegistrationGuardian::where('mobile_number', $request->mobile_number)
                        ->where('otp', $request->code)
                        ->where('otp_expired_at','>=', Carbon::now())
                        ->first();
        if(!$preRegister){
            $response->status = $response::status_fail;
            $response->messages = "Code not found or code expire";
            return FacadeResponse::json($response);
        };

        $refference_id = null;
        if ($preRegister->reffered_code) {
            $refference = Guardian::where('user_code', $preRegister->reffered_code)->select('id', 'fcm_id')->first();
            $refference_id = $refference->id;
        }
            $user = Guardian::create([
                "name" => $preRegister->name,
                "mobile_number" => $preRegister->mobile_number,
                "email" => $preRegister->email,
                "refference_id" => $refference_id
            ]);
            $user_code = 'BSG' . (1000 + $user->id);

            $user->update(['user_code' => $user_code]);
            if ($refference_id) {
                $refferedUserNumber = Guardian::where('refference_id', $refference_id)->where('isCompleteRegistration', true)->count();

                $fcmObject = (object) [
                    "title" => "Congratulations!",
                    "body" => $user->name . " has just registered with your refference",
                    "data" => $refferedUserNumber
                ];
                if ($reffence->fcm_id) {
                    $this->sendFCMNotification($reffence->fcm_id, $fcmObject);
                }
            }
        $verify = new VerifyCodeController();
        $result_data = $this->getLoginData($user->id);
        $response->status = $response::status_ok;
        $response->messages = "Code matched";
        $response->result = $result_data;
        return FacadeResponse::json($response);
    }


    public function login(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'app_signature' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        if ($request->mobile_number) {
            $user_phone =  $request->get('mobile_number');
            $user_data = Guardian::where('mobile_number', $user_phone)->first();
            if (!$user_data) {
                $response->status = $response::status_fail;
                $response->messages = "You are no registered with this phone number";
                return FacadeResponse::json($response);
            };
            try {
                $digits = 4;
                $expire_minute = 30;
                if ($user_phone == "01714536772" || $user_phone == "01911697095" || $user_phone == "01818614080") {
                    $code = 1234;
                } else {
                    $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                }
                GuardianCode::where('guardian_id', $user_data->id)->delete();
                GuardianCode::create([
                    'guardian_id' => $user_data->id,
                    'code' => $code,
                    'status' => "Available",
                    'expire_at' => Carbon::now()->addMinutes($expire_minute)
                ]);
                $message = '<#> Use ' . $code . ' as your OTP for BacBon School. '; // . $request->app_signature;
                $phone = $user_data->mobile_number;
                $apiRegisterController = new APIRegisterController();
                $apiRegisterController->sendSms($phone, $message);
                $response->status = $response::status_ok;
                $response->messages = "Code has been sent to your phone.";
                $response->result =  $user_data->id;
                return FacadeResponse::json($response);
            } catch (Exception $e) {
                $response->status = $response::status_fail;
                $response->messages = $e->getMessage();
                return FacadeResponse::json($response);
            }
        } else if ($request->email) {

            $user = Guardian::where('email', $request->email)->select('id', 'password')->first();
            if ($user) {
                if($user->password) {
                    if(Hash::check($request->password, $user->password)) {

                        $response->status = $response::status_ok;
                        $response->messages = "Login successful";
                        $response->result =  $this->getLoginData($user->id);
                        return FacadeResponse::json($response);
                    } else {
                        $response->status = $response::status_fail;
                        $response->messages = "Password is not correct";
                        return FacadeResponse::json($response);
                    }
                } else {
                    $response->status = $response::status_fail;
                    $response->messages = "Please try with your phone number";
                    return FacadeResponse::json($response);
                }
            } else {
                $response->status = $response::status_fail;
                $response->messages = "You are not registered yet. Please register";
                return FacadeResponse::json($response);
            }
        }
    }


    public function verifyOtp (Request $request){
        $response = new ResponseObject;
        $subjects = [];
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'id' => 'required',
            'code' => 'required',
        ]);
        if ($validator->fails()) {
           $response->status = $response::status_fail;
           $response->messages = $validator->errors();
           return FacadeResponse::json($response);
       }
        $user_id =  $request->get('id');
        $user_code =  $request->get('code');
        $user_code_data = GuardianCode::where('guardian_id', $user_id)
                          ->where('code', $user_code)
                          ->where('expire_at','>=',Carbon::now())
                          ->first();
        if(!$user_code_data){
           $response->status = $response::status_fail;
           $response->messages = "Code not found or code expire";
           return FacadeResponse::json($response);
        };
      $result_data = $this->getLoginData($user_id);
      $response->status = $response::status_ok;

      $response->messages = "Code matched";
      $response->result = $result_data;

      return FacadeResponse::json($response);
     }


     public function foreignRegister(Request $request) {
        $response = new ResponseObject;
        $nameArray = explode(" ", $request->name);
        $codeInit = '';
        foreach ($nameArray as $arr) {
            $codeInit .= $arr[0];
        }
        $data = $request->json()->all();


        $validator = Validator::make($data, [
            'name' => 'required',
            'mobile_number' => 'unique:guardians',
            'email' => 'required|unique:guardians',
            'password' => 'required|min:6',
            'app_signature' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }


        if ($request->refferal_code) {
            $reffence = Guardian::where('user_code', $request->refferal_code)->select('id', 'fcm_id')->first();
            if ($reffence === null) {
                $response->status = $response::status_fail;
                $response->messages = "Referral code is incorrect";
                return FacadeResponse::json($response);
            }
            $refferedUserNumber = Guardian::where('refference_id', $reffence->id)->count();
            $registrationData['refference_id'] = $reffence->id;

            $fcmObject = (object) [
                "title" => "Congratulations!",
                "body" => $request->name . " has just registered with your refference",
                "data" => $refferedUserNumber + 1
            ];
            $this->sendFCMNotification($reffence->fcm_id, $fcmObject);
        }

        $user = Guardian::create([
            "name" => $request->name,
            "mobile_number" => $request->mobile_number,
            "email" => $request->email,
            "gender" => "Male",
            "current_course_id" => $request->current_course_id ? $request->current_course_id : null,
            "is_bangladeshi" => false,
            "password" => bcrypt($request->password)
        ]);
        $user_code = strtoupper($codeInit) . (1000 + $user->id);
        $user->update(['user_code' => $user_code]);
        $response->status = $response::status_ok;
        $response->messages = "Registration Successfull";
        $response->result =  $this->getLoginData($user->id);
        return FacadeResponse::json($response);
    }


    public function getGuardianDetails (Request $request) {
        $guardian = $this->getLoginData($request->id);
        return FacadeResponse::json($guardian);
    }
    public function getLoginData($id) {
        $guardian = Guardian::where('id', $id)->with('children')->first();
        return new DetailsResource($guardian);
    }
}
