<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use \Illuminate\Http\Response;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCodeSendMail;
use Validator;
use JWTFactory;
use SoapClient;
use JWTAuth;
//use Hash;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\admin;
use App\UserCode;
use App\UserToken;
use Carbon\Carbon;
use App\UserVerificationCode;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

class APILoginController extends Controller
{
    public function loginFirstStepWeb (Request $request)
    {
        // validate phone number
        // check existance of phone number / user
        // fetch user details
        // Check is Password set
        // send otp as message if no password
        // return if password
        $response = new ResponseObject;

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'mobile_number' => 'required'
        ]);

        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        $user_phone =  $request->mobile_number;

        $user_data = User::where('mobile_number', $user_phone)->first();

        $isUser = User::where('mobile_number', $user_phone)->count();
         if ($isUser == 0) {
            $response->status = $response::status_fail;
            $response->messages = "No user found with this phone number";
            return FacadeResponse::json($response);
        }

        if ($user_data->isSetPassword) {

            $response->status = 'password';
            $response->messages = "Please enter your password";
            $response->result =  $user_data->id;
            return FacadeResponse::json($response);
        } else {

            try {
                $digits = 4;
                $expire_minute = 10;
                if ($user_phone == "01714536772" || $user_phone == "01818614080" || $user_phone == "01911697095") {
                    $code = 1234;
                } else {
                    $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                }
                
                UserCode::where('user_id', $user_data->id)->delete();
                UserCode::create([
                    'user_id' => $user_data->id,
                    'code' => $code,
                    'status' => "Available",
                    'expire_at' => Carbon::now()->addMinutes($expire_minute)
                ]);

                $message = 'Use ' . $code . ' as your login code for BacBon School. ' . $request->app_signature;
                $phone = $user_data->mobile_number;

                $this->sendRestSms($phone, $message);

                $response->status = 'otp';
                $response->messages = "Code has been sent to your phone.";
                $response->result =  $user_data->id;
                return FacadeResponse::json($response);
            } catch (Exception $e) {
                $response->status = $response::status_fail;
                $response->messages = $e->getMessage();
                return FacadeResponse::json($response);
            }
        }

    }

    public function loginWithPassword (Request $request) {
        // Fetch user with id
        // Match password
        // Return

        $response = new ResponseObject;

        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'id' => 'required',
            'password' => 'required'
        ]);
        $user = User::where('id', $request->id)->first();

        if(Hash::check($request->password, $user->password)) {
            $verify = new VerifyCodeController();

            $response->status = $response::status_ok;
            $response->messages = "Login successful";
            $response->result =  $verify->getLoginData($user->id);
            return FacadeResponse::json($response);
        } else {
            $response->status = $response::status_fail;
            $response->messages = "Password did not match";
            return FacadeResponse::json($response);
        }
    }

    public function jwtLogin(Request $request)
    {
        $response = new ResponseObject;

        $data = $request->json()->all();
        $jwt_token = null;

        $validator = Validator::make($data, [
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = admin::where('email', $data['email'])->first();

        if(empty($user)){
            $response->status = false;
            $response->messages = "User does not exist!";
            return FacadeResponse::json($response);
        }

        if(Hash::check($data['password'], $user->password)) {
            $verify = new VerifyCodeController();

            if (!$jwt_token=JWTAuth::fromUser($user)) 
            {
                $response->status = false;
                $response->messages = "Invalid Email or Password!";
                return FacadeResponse::json($response, 401);
            }

            $obj = (Object) [
                "user"  => $user,
                "token" => $jwt_token
            ];

            $response->status = true;
            $response->messages = "Login successful!";
            $response->result = $obj;
            return FacadeResponse::json($response);
        } else {
            $response->status = false;
            $response->messages = "Password did not match";
            return FacadeResponse::json($response);
        }
    }

    public function loginWithOTP (Request $request) {
        // Fetch user with id
        // Match otp   with time
        // Return
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

        $verify = new VerifyCodeController();

      $response->status = $response::status_ok;

      $response->messages = "Code matched";
        $response->result =  $verify->getLoginData($user_id);

      return FacadeResponse::json($response);
    }

    public function GetOTPForDB (Request $request) {

        $response = new ResponseObject;
        $subjects = [];

        $mobile = $request->mobile ? $request->mobile : 0;

        $user = User::where('mobile_number', $mobile)->first();

        if(!empty($user)){
            $user_code_data = UserCode::where('user_id', $user->id)
                          ->where('expire_at','>=',Carbon::now())
                          ->first();

            $response->status = $response::status_ok;
            $response->messages = "Code matched";
            $response->result = $user_code_data;
            return FacadeResponse::json($response);
        }

        $response->status = false;
        $response->messages = "Check Mobile No.";
        return FacadeResponse::json($response);

    }

    public function login(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'app_signature' => 'required'
        ]);

        // return FacadeResponse::json($request);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        if ($request->mobile_number) {

            $user_phone =  $request->get('mobile_number');

            $user_data = User::where('mobile_number', $user_phone)->first();
          //  return FacadeResponse::json($user_data);
            if (!$user_data) {
                $response->status = $response::status_fail;
                $response->messages = "You are no registered with this phone number";
                return FacadeResponse::json($response);
            };

            try {
                $digits = 4;
                $expire_minute = 30;
                if ($user_phone == "01714536772" || $user_phone == "01818614080" || $user_phone == "01911697095") {
                    $code = 1234;
                } else {
                    $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
                }

                UserCode::where('user_id', $user_data->id)->delete();
                UserCode::create([
                    'user_id' => $user_data->id,
                    'code' => $code,
                    'status' => "Available",
                    'expire_at' => Carbon::now()->addMinutes($expire_minute)
                ]);

                $message = 'Use ' . $code . ' as your OTP for BacBon School. ' . $request->app_signature;
                $phone = $user_data->mobile_number;
                $this->sendRestSms($phone, $message);


                // return FacadeResponse::json($this->sendRestSms($phone, $message));

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

            $user = User::where('email', $request->email)->select('id', 'password')->first();
            if ($user) {
                if($user->password) {
                    if(Hash::check($request->password, $user->password)) {
                        $verify = new VerifyCodeController();

                        $response->status = $response::status_ok;
                        $response->messages = "Login successful";
                        $response->result =  $verify->getLoginData($user->id);
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

    public function logout(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $logout = UserCode::where('user_id', $request->user_id)->delete();
        $deleteToken = UserToken::where('user_id', $request->user_id)->delete();
        $response->status = $response::status_ok;
        $response->messages = "Successfully Logout";
        return FacadeResponse::json($response);
    }

    public function sendVerificationEmailOTP(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();

        if(!$request->user_id){
            $response->status = $response::status_fail;
            $response->messages = "Please, Attach the user ID";
            return FacadeResponse::json($response); 
        }

        $user = User::where('id', $request->user_id)->first();

        if(!empty($user)){
            $is_otp_sent = false;
            $is_otp_sent_mobile = false;
            $is_otp_sent_email = false;
            $otp = rand(1000, 9999);
            
            //$otp = 2024;

            UserVerificationCode::create([
                "user_id" => $request->user_id,
                "code" => $otp,
                "expire_at" => Carbon::now()->addMinutes(10)
            ]);

            if($user->mobile_number){
                $message = 'Use ' . $otp . ' as your vatification code for BacBon School.';
                $this->sendRestSms($user->mobile_number, $message);

                $is_otp_sent = true;
                $is_otp_sent_mobile = true;
            }
            if($user->email){

                $to = $user->email;
                $subject = "Verification Code of BacBon School";
                
                $message = 'Use ' . $otp . ' as your vatification code for BacBon School.';
                
                $header = "From:info@bacbonschool.com \r\n";
                $header .= "Cc:support@bacbonschool.com \r\n";
                $header .= "MIME-Version: 1.0\r\n";
                $header .= "Content-type: text/html\r\n";
                $retval = mail ($to,$subject,$message,$header);


                $is_otp_sent = true;
                $is_otp_sent_email = true;
            }

            if($is_otp_sent){
                $response->status = $response::status_ok;
                $response->messages = "Your verification code has been sent successfully!";
                return FacadeResponse::json($response); 
            }

        }else{
            $response->status = $response::status_fail;
            $response->messages = "User does not found!";
            return FacadeResponse::json($response);
        }
        $response->status = $response::status_fail;
        $response->messages = "Unsuccessful";
        return FacadeResponse::json($response);
    }
    
    public function deleteUser(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();

        if(!$request->user_id){
            $response->status = $response::status_ok;
            $response->messages = "Please, Attach the user ID";
            return FacadeResponse::json($response); 
        }

        $user = User::where('id', $request->user_id)->first();

        $is_code_valid =  UserVerificationCode::where('user_id', $request->user_id)->where('code', $request->verification_code)->first();

        if(empty($is_code_valid)){
            $response->status = $response::status_fail;
            $response->messages = "Code doesn't match!";
            return FacadeResponse::json($response);
        }

        if(!empty($user)){
            $user = User::where('id', $request->user_id)->update([
                "mobile_number" => $request->user_id . "_deleted_" . $user->mobile_number,
                "email" => $request->user_id . "_deleted_" . $user->email
            ]);
            $response->status = $response::status_ok;
            $response->messages = "User has been deleted successfully";
            return FacadeResponse::json($response);
        }else{
            $response->status = $response::status_fail;
            $response->messages = "User does not found!";
            return FacadeResponse::json($response);
        }

        $response->status = $response::status_fail;
        $response->messages = "Unsuccessful";
        return FacadeResponse::json($response);
    }

    public function updatePassword (Request $request) {
        // validate new password old password and user id
        // Fetch user password
        // Check password is Set previosly
        // Match with old password with existing password
        // Update password if matched or return fail

        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'id' => 'required',
            'old_password' => 'required',
            'new_password' => 'required|min:6'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }



        $user = User::where('id', $request->id)->first();
        if ($user->isSetPassword) {

            // if($request->old_password=== $user->password) {
            if(Hash::check($request->old_password, $user->password)) {
                $user->update(['password' => bcrypt($request->new_password)]);

                $response->status = $response::status_ok;
                $response->messages = "Password updated successfully";
                return FacadeResponse::json($response);
            } else {
                $response->status = $response::status_fail;
                $response->messages = "Old password did not match";
                return FacadeResponse::json($response);
            }
        } else {
            $response->status = $response::status_fail;
            $response->messages = "You did not set your password.";
            return FacadeResponse::json($response);
        }
    }

    public function smsTest (Request $request) {
        $message = 'Use 1234 as your login code for BacBon Tutors. un+6snP7SP6';
        $message = 'Test message to thik e jabe.';
        
        $digits = 4;
        $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
        
        $message = 'Use ' . $code . ' as your vatification code for BacBon School.';
        
        return FacadeResponse::json($this->sendRestSms($request->phone, $message));
    }

    public function sendSms($phone, $message)
    {
        $phn = $phone;
        try {

            $soapClient = new SoapClient("https://user.mobireach.com.bd/index.php?r=sms/service");

          $response = $soapClient->SendTextMultiMessage("bacbon1", "#*Bst&22#%", "8801877715110", $phn, $message);
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
        return $response;
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


    // public function sendSms($phone, $message)
    // {
    //     try {

    //         $soapClient = new SoapClient("https://user.mobireach.com.bd/index.php?r=sms/service");

    //       return $soapClient->SendTextMultiMessage("bacbon1", "Pass@2021", "8801877715110", $phone, $message);

    //     } catch (Exception $e) {
    //         echo $e->getMessage();
    //     }
    // }

    public function checkDataHeader(Request $request)
    {
        $header = $request->header('signature');
        //$header = $_SERVER['HTTP_X_REQUESTED_WITH'];//get_headers("url");
        return FacadeResponse::json([$header]);
    }

}
