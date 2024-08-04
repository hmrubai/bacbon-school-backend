<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use \Illuminate\Http\Response;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCodeSendMail;
use App\User;
use App\PreRegistration;
use App\UserCode;
use SoapClient;
use JWTFactory;
use JWTAuth;
use Validator;
use App\Events\UserCreated;
use GuzzleHttp\Client;

use Carbon\Carbon;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class APIRegisterController extends Controller
{





    public function sendWebUserOTP (Request $request) {
    // Validate input data
    // Generate user unique refferal code
    // Check Phone number Unique
    // check isCompleteRegister
    // Save user information
    // Generate OTP
    // Save otp
    // Send OTP Message

        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'name' => 'required',
            'mobile_number' => 'required',
            'email' => 'unique:users',
            'password' => 'required|min:6',
        ]);


        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        if (User::where('mobile_number', $request->mobile_number)->count()) {
            $response->status = $response::status_fail;
            $response->messages = "You are already created account with us, please log in instead";
            return FacadeResponse::json($response);
        }

        $nameArray = explode(" ", $request->name);
        $codeInit = '';
        foreach ($nameArray as $arr) {
            $codeInit .= $arr[0];
        }

        try {
            $registrationData = $request->all();
            $registrationData['gender'] = "Male";


            if ($request->refferal_code) {
                $reffence = User::where('user_code', $request->refferal_code)->select('id', 'fcm_id')->first();
                if ($reffence === null) {
                    $response->status = $response::status_fail;
                    $response->messages = "Referral code is incorrect";
                    return FacadeResponse::json($response);
                }
                $refferedUserNumber = User::where('refference_id', $reffence->id)->count();
                $registrationData['refference_id'] = $reffence->id;

            }

            $user = User::create([
                "name" => $request->name,
                "mobile_number" => $request->mobile_number,
                "email" => $request->email,
                "gender" => "Male",
                "current_course_id" => $request->current_course_id ? $request->current_course_id : null,
                "password" => bcrypt($request->password),
                "isSetPassword" => true

            ]);

            $digits = 4;
            $expire_minute = 3;
            $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
            //$code = 2024;

            $registrationData['user_code'] = 'BS' . (1000 + $user->id);

            $user->update(['user_code' => $registrationData['user_code']]);
            UserCode::create([
                'user_id' => $user->id,
                'code' => $code,
                'status' => "Available",
                'expire_at' => Carbon::now()->addMinutes($expire_minute)
            ]);

            $message = 'Use ' . $code . ' as your login code for BacBon School. Thank you';
            $phone = $user->mobile_number;

            $this->sendRestSms($phone, $message);

            broadcast(new UserCreated($user))->toOthers();
            $response->status = $response::status_ok;
            $response->messages = "OTP has been sent to your phone.";
            $response->result =  $user->id;
            return FacadeResponse::json($response);
        } catch (Exception $e) {
            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();
            return FacadeResponse::json($response);
        }
    }



    public function completeRegistration (Request $request) {
        // Validate input data
        // Check otp with data and expiration
        // get user details
        // Update complete registration user
        // generate token
        // return data to user
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


        $user_code_data = UserCode::where('user_id', $user_id)
                        ->where('code', $user_code)
                        ->where('expire_at','>=',Carbon::now())
                        ->first();
        if(!$user_code_data){
            $response->status = $response::status_fail;
            $response->messages = "Code not found or code expire";
            return FacadeResponse::json($response);
        };

        $update = User::where('id', $user_id)->update([
            'isCompleteRegistration' => true
        ]);


        // $fcmObject = (object) [
        //     "title" => "Congratulations!",
        //     "body" => $request->name . " has just registered with your refference",
        //     "data" => $refferedUserNumber + 1
        // ];
        // if ($reffence->fcm_id)
        //     $this->sendFCMNotification($reffence->fcm_id, $fcmObject);


        $verifyCodeController = new VerifyCodeController();

        $result_data = $verifyCodeController->getLoginData($user_id);
        $response->status = $response::status_ok;

        $response->messages = "Code matched";
        $response->result = $result_data;

        return FacadeResponse::json($response);
    }

    public function register(Request $request)
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
                'mobile_number' => 'required|unique:users',
                'email' => 'unique:users',
                'app_signature' => 'required',
                // 'verification_hash' => 'required',
            ]);
            if ($validator->fails()) {
                $response->status = $response::status_fail;
                $response->messages = $validator->errors()->first();
                return FacadeResponse::json($response);
            }

            try {
                $fourDigit = 4;
                $registrationData = $request->all();
                $registrationData['gender'] = "Male";


                if ($request->refferal_code) {
                    $reffence = User::where('user_code', $request->refferal_code)->select('id', 'fcm_id')->first();
                    if ($reffence === null) {
                        $response->status = $response::status_fail;
                        $response->messages = "Referral code is incorrect";
                        return FacadeResponse::json($response);
                    }
                    $registrationData['refference_id'] = $reffence->id;
                }

                $user = User::create($registrationData);
                $digits = 4;
                $expire_minute = 30;
                $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                //$code = 2024;

                $registrationData['user_code'] = 'BS' . (1000 + $user->id);

                $user->update(['user_code' => $registrationData['user_code']]);
                UserCode::create([
                    'user_id' => $user->id,
                    'code' => $code,
                    'status' => "Available",
                    'expire_at' => Carbon::now()->addMinutes($expire_minute)
                ]);

                $message = 'Use ' . $code . ' as your OTP for BacBon School. '; // . $request->app_signature;
                $phone = $user->mobile_number;

                $this->sendRestSms($phone, $message);

                broadcast(new UserCreated($user))->toOthers();
                $response->status = $response::status_ok;
                $response->messages = "Code has been sent to your phone.";
                $response->result =  $user->id;
                return FacadeResponse::json($response);
            } catch (Exception $e) {
                $response->status = $response::status_fail;
                $response->messages = $e->getMessage();
                return FacadeResponse::json($response);
            }

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
            'mobile_number' => 'unique:users',
            'email' => 'required|unique:users',
            'password' => 'required|min:6',
            'app_signature' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }


        if ($request->refferal_code) {
            $reffence = User::where('user_code', $request->refferal_code)->select('id', 'fcm_id')->first();
            if ($reffence === null) {
                $response->status = $response::status_fail;
                $response->messages = "Referral code is incorrect";
                return FacadeResponse::json($response);
            }

            if($request->device_id){
                $isDeviceIdExists = User::where('device_id',$request->device_id)->count();
                   if($isDeviceIdExists){
                       $response->status = $response::status_fail;
                       $response->messages = "This device has been already used. That's why this device can't be used for referral registration";
                       return FacadeResponse::json($response);
                   }
            }


            $refferedUserNumber = User::where('refference_id', $reffence->id)->count();
            $registrationData['refference_id'] = $reffence->id;

            $fcmObject = (object) [
                "title" => "Congratulations!",
                "body" => $request->name . " has just registered with your refference",
                "data" => $refferedUserNumber + 1
            ];
            $this->sendFCMNotification($reffence->fcm_id, $fcmObject);
        }

        $user = User::create([
            "name" => $request->name,
            "mobile_number" => $request->mobile_number,
            "email" => $request->email,
            "gender" => "Male",
            "current_course_id" => $request->current_course_id ? $request->current_course_id : null,
            "isBangladeshi" => false,
            "password" => bcrypt($request->password)
        ]);


        $user_code = strtoupper($codeInit) . (1000 + $user->id);

        $user->update(['user_code' => $user_code]);

        $verify = new VerifyCodeController();

        //broadcast(new UserCreated($user))->toOthers();
        $response->status = $response::status_ok;
        $response->messages = "Registration Successfull";
        $response->result =  $verify->getLoginData($user->id);
        return FacadeResponse::json($response);
    }
    public function createReferralCode($previous_code, $id)
    {
        preg_match_all('!\d+!', $previous_code, $matches);
        return $matches[0][0] + $id;
    }
    // public function sendSms($phone, $message)
    // {
    //     try {
    //         $soapClient = new SoapClient("https://api2.onnorokomSMS.com/sendSMS.asmx?wsdl");
    //         $paramArray = array(
    //             'userName' => "01835510247",
    //             'userPassword' => "Mamun.15321",
    //             'mobileNumber' => $phone,
    //             'smsText' => $message,
    //             'type' => "TEXT",
    //             'maskName' => 'DemoMask',
    //             'campaignName' => '',
    //         );
    //         $value = $soapClient->__call("OneToOne", array($paramArray));
    //         // echo $value->OneToOneResult;
    //     } catch (Exception $e) {
    //         echo $e->getMessage();
    //     }
    // }


    //  public function sendSms($phone, $message)
    // {
    //     try {

    //         $soapClient = new SoapClient("https://user.mobireach.com.bd/index.php?r=sms/service");

    //       $response = $soapClient->SendTextMultiMessage("bacbon1", "Pass@2021", "8801877715110", $phone, $message);
    //       $result = $response[0];
    //       if ($result->CurrentCredit % 1000 < 1) {
    //           $notifyPhone = "01784882464";
    //           $notifyMessage = "Mobireach BacBon Account current balance is " . $result->CurrentCredit. ' BDT';
    //           $response = $soapClient->SendTextMultiMessage("bacbon1", "Pass@2021", "8801877715110", $notifyPhone, $notifyMessage);
    //       } else if ($result->CurrentCredit < 50 && $result->CurrentCredit % 10 < 1) {
    //           $notifyPhone = "01784882464";
    //           $notifyMessage = "Mobireach BacBon Account current balance is " . $result->CurrentCredit. ' BDT. Please recharge now';
    //           $response = $soapClient->SendTextMultiMessage("bacbon1", "Pass@2021", "8801877715110", $notifyPhone, $notifyMessage);
    //       }
    //     return $response;
    //     } catch (Exception $e) {
    //         echo $e->getMessage();
    //     }
    // }

    public function sendSms($phone, $message)
    {
        try {

            $soapClient = new SoapClient("https://user.mobireach.com.bd/index.php?r=sms/service");

          $response = $soapClient->SendTextMultiMessage("bacbon1", "#*Bst&22#%", "8801877715110", $phone, $message);
          $result = $response[0];
          if ($result->CurrentCredit % 1000 < 1) {
              $notifyPhone = "01714536772";
              $notifyMessage = "Mobireach BacBon Account current balance is " . $result->CurrentCredit. ' BDT';
              $response = $soapClient->SendTextMultiMessage("bacbon1", "#*Bst&22#%", "8801877715110", $notifyPhone, $notifyMessage);
          } else if ($result->CurrentCredit < 50 && $result->CurrentCredit % 10 < 1) {
              $notifyPhone = "01714536772";
              $notifyMessage = "Mobireach BacBon Account current balance is " . $result->CurrentCredit. ' BDT. Please recharge now';
              $response = $soapClient->SendTextMultiMessage("bacbon1", "#*Bst&22#%", "8801877715110", $notifyPhone, $notifyMessage);
          }
        return $result;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function sendRestSms($phone, $message)
    {
        try {
            $response = new ResponseObject;
            
            $client = new Client();
            $url = "https://api.mobireach.com.bd/SendTextMessage?Username=bacbon1&Password=BBSft@2024&From=8801877715110&To=". $phone. "&Message=" . $message;
            return $res = $client->request('GET', $url);
            
            // $response->status = $response::status_ok;
            // $response->messages = "Message sent successfully";
            // $response->result = $res->getBody();
            // return FacadeResponse::json($response);

          //return $soapClient->SendTextMultiMessage("bacbon1", "Pass@2021", "8801877715110", $phone, $message);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function sendSmsRobi()
    {
        try {

            $soapClient = new SoapClient("https://user.mobireach.com.bd/index.php?r=sms/service");

            $phone = "01714536772";
            $message = "Hello, I am sending this message from our new sms client";
            $response = $soapClient->SendTextMultiMessage("bacbon1", "#*Bst&22#%", "8801877715110", $phone, $message);
            $result = $response[0];
            if ($result->CurrentCredit % 1000 < 1) {
                $notifyPhone = "01714536772";
                $notifyMessage = "Mobireach BacBon Account current balance is " . $result->CurrentCredit. ' BDT';
                $response = $soapClient->SendTextMultiMessage("bacbon1", "#*Bst&22#%", "8801877715110", $notifyPhone, $notifyMessage);
            } else if ($result->CurrentCredit < 50 && $result->CurrentCredit % 10 < 1) {
                $notifyPhone = "01714536772";
                $notifyMessage = "Mobireach BacBon Account current balance is " . $result->CurrentCredit. ' BDT. Please recharge now';
                $response = $soapClient->SendTextMultiMessage("bacbon1", "#*Bst&22#%", "8801877715110", $notifyPhone, $notifyMessage);
            }
            return FacadeResponse::json($result->CurrentCredit);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }



    public function sendMail() {
        $data = array('name'=>"Mehedi Rueen");
        return Mail::send(['html'=>'emails/user_code_send'], $data, function($message) {
            $message->to('rubai.mobarak@gmail.com', 'BacBon School')->subject
               ('BacBon School Registration Mail');
            $message->from('rubai.mobarak@gmail.com','Mehedi Rueen');
         });
    }
    public function demoFCM()
    {
        $token = 'cJLYejCHYlg:APA91bG71AoSqPgi4GdjxqyPWAVrzhqLxt7CaIqMjkA0Txye62PATivvkk9FFGywQcAlP1fYCYw-5XGMPMVFTplInla-ojmQSpO9M9LljfDgHs7UeIDKzpfirmTiHZ32bWcK6SFkuWFJ';
        $fcmObject = (object) [
            "title" => "Congratulations!",
            "body" => "has just registered with your refference",
            "data" => "Hello"
        ];
        $this->sendFCMNotification($token, $fcmObject);
        return 'Success';
    }





    public function sendFCMNotification($token, $obj)
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder($obj->title);
        $notificationBuilder->setBody($obj->body)
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['refferedUserNumber' => $obj->data]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();


        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        $downstreamResponse->tokensToDelete();


        $downstreamResponse->tokensToModify();

        $downstreamResponse->tokensToRetry();
    }

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
                'mobile_number' => 'required|unique:users',
                'email' => 'unique:users',
                'app_signature' => 'required'
            ]);

            if ($validator->fails()) {
                $response->status = $response::status_fail;
                $response->messages = $validator->errors()->first();
                return FacadeResponse::json($response);
            }

            // For Puktimara hacker
            if($request->email == 'msnrpp0165@detectu.com'){
                $response->status = $response::status_ok;
                $response->messages = "Code has been sent to your phone.";
                return FacadeResponse::json($response);
            }

            $pre_limit = PreRegistration::where('mobile_number', $request->mobile_number)->get();

            if(sizeof($pre_limit) > 50){
                $response->status = $response::status_fail;
                $response->messages = "You have tried multiple times to get registered using this mobile. Please, try another number!";
                return FacadeResponse::json($response); 
            }

            try {
                $registrationData = $request->all();

                if ($request->refferal_code) {
                    $reffence = User::where('user_code', $request->refferal_code)->select('id', 'fcm_id')->first();
                    if ($reffence === null) {
                        $response->status = $response::status_fail;
                        $response->messages = "Referral code is incorrect";
                        return FacadeResponse::json($response);
                    }

                    if($request->device_id){
                     $isDeviceIdExists = User::where('device_id',$request->device_id)->count();
                        if($isDeviceIdExists){
                            $response->status = $response::status_fail;
                            $response->messages = "This device has been already used. That's why this device can't be used for referral registration";
                            return FacadeResponse::json($response);
                        }
                    }
                }
                
                // $user = User::create($registrationData);
                $digits = 4;
                $expire_minute = 15;
                $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                //$code = 2024;



                unset($registrationData['app_signature']);
              //  unset($registrationData['refferal_code']);
                $registrationData['otp_expired_at'] = Carbon::now()->addMinutes($expire_minute);
                $registrationData['otp'] = $code;
                $registrationData['referred_code'] = $request->refferal_code;
                PreRegistration::create($registrationData);

                $message = 'Use ' . $code . ' as your OTP for BacBon School. '; // . $request->app_signature;
                $phone = $request->mobile_number;

                $this->sendRestSms($phone, $message);

                // broadcast(new UserCreated($user))->toOthers();
                $response->status = $response::status_ok;
                //$response->messages = "Code has been sent to your phone.";
                $response->messages = "Use 2024 as your OTP for BacBon School.";
                //$response->messages = 'Use ' . $code . ' as your OTP for BacBon School. ';
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
        $preRegister = PreRegistration::where('mobile_number', $request->mobile_number)
                        ->where('otp', $request->code)
                        ->where('otp_expired_at','>=', Carbon::now())
                        ->first();
        if(!$preRegister){
            $response->status = $response::status_fail;
            $response->messages = "Code not found or code expire";
            return FacadeResponse::json($response);
        };

        $refference_id = null;
        if ($preRegister->referred_code) {
            $refference = User::where('user_code', $preRegister->referred_code)->select('id', 'fcm_id')->first();
            $refference_id = $refference->id;
        }

        // $registrationData['user_code'] = 'BS' . (1000 + $user->id);
            $user = User::create([
                "name" => $preRegister->name,
                "mobile_number" => $preRegister->mobile_number,
                "email" => $preRegister->email,
                "device_id" => $preRegister->device_id,
                "isCompleteRegistration" => true,
                "refference_id" => $refference_id,
                "current_course_id" => $preRegister->current_course_id
            ]);

            $user_code = 'BS' . (1000 + $user->id);

            $user->update(['user_code' => $user_code]);
            if ($refference_id) {
                $refferedUserNumber = User::where('refference_id', $refference_id)->where('isCompleteRegistration', true)->count();

                $fcmObject = (object) [
                    "title" => "Congratulations!",
                    "body" => $user->name . " has just registered with your refference",
                    "data" => $refferedUserNumber
                ];
                if ($refference->fcm_id) {
                    $this->sendFCMNotification($refference->fcm_id, $fcmObject);
                }
            }
        $verify = new VerifyCodeController();
        $result_data = $verify->getLoginData($user->id);
        $response->status = $response::status_ok;

        $response->messages = "Code matched";
        $response->result = $result_data;

        return FacadeResponse::json($response);
    }
}
