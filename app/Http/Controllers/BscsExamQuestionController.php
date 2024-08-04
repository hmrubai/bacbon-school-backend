<?php

namespace App\Http\Controllers;

use App\BscsExamQuestion;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

class BscsExamQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function saveBulkQuestion(Request $request)
    {
        $items = $request->items;
        $count = 0;
        foreach ($items as $item) {
            $count++;
            BscsExamQuestion::create($item);
        }

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "Submitted";
        $response->result = $count;
        return FacadeResponse::json($response);



    }
}
