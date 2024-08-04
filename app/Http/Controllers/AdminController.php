<?php

namespace App\Http\Controllers;

use App\admin;
use Validator;
use Hash;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function userList(){
        return FacadeResponse::json(admin::get());
    }

    public function register(Request $request)

    {

        $response = new ResponseObject;

        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'name' => 'required',
            'username' => 'required|max:50|min:5|unique:admins',
            'email' => 'required|max:50|min:5|unique:admins',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }


        $admin = admin::create($request->all());
         $user = (Object) [
             "id" => $admin->id,
             "name" => $admin->name,
             "username" => $admin->username,
             "email" => $admin->email,
             "role" => $admin->role,
             "gender" => $admin->gender
         ];

        return response()->json([
            'status'   => 'OK',
            'message'   => 'Admin created successfull',
            'data'   => $user,
        ]);
    }


    public function login(Request $request)
    {
        $admin = admin::where('username', $request->username)->first();
        if (is_null($admin)) {

            return response()->json([
                'status'   => 'NotOK',
                'message'   => 'User not found'
            ]);
        }
        $user = (Object) [
            "id" => $admin->id,
            "name" => $admin->name,
            "username" => $admin->username,
            "email" => $admin->email,
            "role" => $admin->role,
            "role_sequence" => $admin->role_sequence,
            "gender" => $admin->gender
        ];

        if(Hash::check($request->password,$admin->password)) {
            $token = JWTAuth::fromUser($admin);
            return response()->json([
                'status'   => 'OK',
                'message'   => 'Login successfull',
                'data'   => $user,
                'token' => $token
            ]);
        } else {
            return response()->json([
                'status'   => 'NotOK',
                'message'   => 'Username or password does not match',
            ]);
        }

    }
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }

    protected function sendResult($message,$data,$errors = [],$status = true)
    {
        $errorCode = $status ? 200 : 422;
        $result = [
            "message" => $message,
            "status" => $status,
            "data" => $data,
            "errors" => $errors
        ];
        return response()->json($result,$errorCode);
    }


    public function changePassword (Request $request) {

        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, [
            'id' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        $admin = admin::where('id', $request->id)->first();
        if ($admin->password != null) {


                $admin->update(['password' => bcrypt($request->password)]);
                $response->status = $response::status_ok;
                $response->messages = "Password changed successfully";
                return FacadeResponse::json($response);


            // if(!Hash::check($request->password, $admin->password)) {

            //     $pass = bcrypt($request->password);
            //     $admin->update(['password' => $pass]);
            //     $response->status = $response::status_ok;
            //     $response->messages = "Password changed successfully";
            //     return FacadeResponse::json($response);

            // } else {
            //     $response->status = $response::status_fail;
            //     $response->messages = "You inserted old password";
            //     return FacadeResponse::json($response);
            // }
        } else {
            $response->status = $response::status_fail;
            $response->messages = "You did not set your password.";
            return FacadeResponse::json($response);
        }
    }


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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function show(admin $admin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function edit(admin $admin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, admin $admin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(admin $admin)
    {
        //
    }
}
