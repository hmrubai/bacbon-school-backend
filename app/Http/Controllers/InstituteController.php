<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;


use App\Institute;
use Illuminate\Http\Request;

class InstituteController extends Controller
{

    const MODEL = "App\Institute";

    public function getList () {
        $m = self::MODEL;
        $institutes = $m::select('id','institute_type_id','name','name_bn','keywords')->distinct('name')->get();
        //return $this->respond('done', $divisions);
        return FacadeResponse::json($institutes);
    }

}
