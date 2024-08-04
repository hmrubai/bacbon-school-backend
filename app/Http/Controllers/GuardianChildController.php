<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use Validator;
use App\Guardian;
use App\User;
use App\GuardianChild;
use Illuminate\Http\Request;
class GuardianChildController extends Controller
{
    /**
     * Connect child with guardian
     * @method Post
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     *
     */
    public function connectStudentWithGuardian (Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();
            $validator = Validator::make($data, [
                'guardian_id' => 'required',
                'mobile_number' => 'required',
                'code' => 'required'
            ]);
            if ($validator->fails()) {
                $response->status = $response::status_fail;
                $response->messages = $validator->errors()->first();
                return FacadeResponse::json($response);
            }
        $user = User::where('mobile_number', $request->mobile_number)->where('user_code', $request->code)->first();
        if (is_null($user)) {
            $response->status = $response::status_fail;
            $response->messages = "Phone number and code didn't match";
            return FacadeResponse::json($response);
        }
        $isChildExisted = GuardianChild::where('guardian_id', $request->guardian_id)->where('user_id', $user->id)->first();
        if (!is_null($isChildExisted)) {
            $response->status = $response::status_fail;
            $response->messages = "You are already connected with this student";
            return FacadeResponse::json($response);
        }
        $connection = GuardianChild::create([
            "user_id" => $user->id,
            "guardian_id" => $request->guardian_id,
            "is_accepted_by_student" => true, // This line will be deleted after implement child approval
            "relation" => $request->relation
        ]);
        $guardianController = new GuardianController();
        $response->status = $response::status_ok;
        $response->messages = "You are already connected with this student";
        $response->result = $guardianController->getLoginData($request->guardian_id);
        return FacadeResponse::json($response);
    }

}
