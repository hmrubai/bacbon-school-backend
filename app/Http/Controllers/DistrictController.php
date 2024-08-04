<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use Validator;
use App\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{

    public function getDistrictListByDivision($id)
    {
        $districts = District::where('division_id', $id)->get();
        return FacadeResponse::json($districts);
    }


    public function updateDistrict (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, District::$updateRules);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        $district = District::where('id', $request->id)->first();
        if (!$district) {

            $response->status = $response::status_fail;
            $response->messages = "No District found";
            return FacadeResponse::json($response);
        }
        $district->update($data);
        $response->status = $response::status_ok;
        $response->messages = "District has been updated";
        $response->result = $district;

        return FacadeResponse::json($response);
    }
    public function storeDistrict (Request $request)
    {
        $response = new ResponseObject;
        $data = $request->json()->all();
        $validator = Validator::make($data, District::$rules);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        $district = District::create($data);

        $response->status = $response::status_ok;
        $response->messages = "District has been uploaded";
        $response->result = $district;

        return FacadeResponse::json($response);
    }

    public function deleteDistrict(Request $request)
    {
        $response = new ResponseObject;
        $district = District::where('id', $request->id)->first();
        if (!$district) {

            $response->status = $response::status_fail;
            $response->messages = "No District found";
            return FacadeResponse::json($response);
        }
        $district->delete();
        $response->status = $response::status_ok;
        $response->messages = "District has been deleted";
        $response->result = $district;

        return FacadeResponse::json($response);
    }
}
