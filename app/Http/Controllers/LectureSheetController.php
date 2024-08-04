<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\User;
use App\LectureSheet;
use App\LectureSheetFeature;
use App\LectureSheetDescriptionTitle;
use App\PaymentLectureSheet;
use App\UserAllPayment;
use App\UserAllPaymentDetails;
use Illuminate\Http\Request;

class LectureSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getLectureSheetList(Request $request)
    {
        $lecture_sheets = LectureSheet::with('lecture_sheet_feature','lecture_sheet_description_title.lecture_sheet_description_detial')->get();
        foreach ($lecture_sheets as $lecture_sheet) {
            $lecture_sheet->is_bought = false;
            $payment = UserAllPayment::where('item_id', $lecture_sheet->id)->where('user_id', $request->userId)->where('item_type','=','Lecture Sheet')
            ->where('transaction_status','=','Complete')->first();
            if (!is_null($payment)) {
                $lecture_sheet->is_bought = true;
            }
        }
        return FacadeResponse::json($lecture_sheets);
    }

    public function getePurchasedLectureSheetlist($userId)
    {
      $lecture_sheetIds =  UserAllPayment::where('user_all_payments.user_id', $userId)->where('user_all_payments.item_type','=','Lecture Sheet')->where('transaction_status','=','Complete')->pluck('item_id');      
        $lecture_sheets = LectureSheet::whereIn('id', $lecture_sheetIds)->with('lecture_sheet_feature','lecture_sheet_description_title.lecture_sheet_description_detial')->get();       
        return FacadeResponse::json($lecture_sheets);
    }


    public function getLatestLectureSheetListWeb()
    {
          return LectureSheet::where('is_active', true)->with('lecture_sheet_feature')->orderBy('id', 'DESC')->get();
    }


    public function getLectureSheetDetailsWeb(Request $request)
    {
        $response = new ResponseObject;
        $lectureSheetId = $request->lectureSheetId;
        $lectureSheet =  LectureSheet::where('id', $lectureSheetId)->first();
        if (empty($lectureSheet)) {
            $response->status = $response::status_fail;
            $response->messages = "No Lecture Sheet Found";
            $response->result = null;
            return FacadeResponse::json($response);
        }
        $lectureSheet->lecture_sheet_feature = LectureSheetFeature::where('lecture_sheet_id',$lectureSheetId)->get();
        $lectureSheet->lecture_sheet_description_title = LectureSheetDescriptionTitle::where('lecture_sheet_id',$lectureSheetId)->with('lecture_sheet_description_detial')->get();
        $lectureSheet->is_active = $lectureSheet->is_active ? true : false;
        return FacadeResponse::json($lectureSheet);
    }


    public function getLectureSheetDetailsV2(Request $request)
    {
        $lectureSheetId = $request->lectureSheetId;
        $userId = $request->userId;

   
        $check_payment = UserAllPayment::where('user_id', $userId)->where('item_id', $lectureSheetId)
        ->where('item_type','=','Lecture Sheet')
        ->where('transaction_status','=','Complete')->first();

      
        $lectureSheet =  LectureSheet::where('id', $lectureSheetId)->first();      

        $lectureSheet->lecture_sheet_feature = LectureSheetFeature::where('lecture_sheet_id',$lectureSheetId)->get();
        $lectureSheet->lecture_sheet_description_title = LectureSheetDescriptionTitle::where('lecture_sheet_id',$lectureSheetId)->with('lecture_sheet_description_detial')->get();
        
        $lectureSheet->is_active = $lectureSheet->is_active ? true : false;
        $lectureSheet->is_fully_paid = empty($check_payment)  ? false : true;  
        $lectureSheet->url = empty($check_payment)  ? null : $lectureSheet->url;  
        $lectureSheet->payment_status = empty($check_payment) ? null : $check_payment->status;



        return FacadeResponse::json($lectureSheet);
    }
 

    public function createUserLectureSheetPayment(Request $request){
        $response = new ResponseObject;

        $check_payment = UserAllPayment::where('user_id', $request->user_id)->where('item_id', $request->item_id)
        ->where('item_type','=','Lecture Sheet')
        ->where('transaction_status','=','Complete')->first();

        if (!empty($check_payment)) {
            $response->status = $response::status_fail;
            $response->messages = "Already payment done for this lecture sheet";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $user =  User::where('id', $request->user_id)->first();
        $lectureSheet =  LectureSheet::where('id', $request->item_id)->first();

        $payment = UserAllPayment::create([
            'user_id' => $request->user_id,
            'name' => $user->name, 
            'email' => $user->email, 
            'phone' => $user->mobile_number,  
            'address' => $user->address,  
            'currency' => $request->currency,
            'item_id' => $request->item_id,
            'item_name' => $lectureSheet->name,
            'item_type'=> "Lecture Sheet",
            'payable_amount' => $request->amount,
            'paid_amount' => $request->amount,
            'card_type'  => $request->card_type,
            'transaction_id' => $request->transaction_id,
            'transaction_status' => 'Complete',
            'status' => 'Enrolled'
        ]);  
        
        $user_payment_details = UserAllPaymentDetails::create([
            'payment_id' => $payment->id,
            'amount' => $payment->paid_amount
        ]);
            
        $lectureSheet->update([
        'number_of_puchased' => $lectureSheet->number_of_puchased + 1,
        ]);

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = " You have successfully purchased this lecture sheet";
        $verifyController = new VerifyCodeController();
        $response->result = $verifyController->getLoginData($request->user_id);
        return FacadeResponse::json($response);
    }


 
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadLectureSheet($id)
    {
        $lectureSheet = LectureSheet::where('id', $id)->first();
        $file = explode("https://api.bacbonschool.com/", $lectureSheet->url);
        $headers = array(
          'Content-Type: application/pdf',
        );
        // return FacadeResponse::json($file[1]);
        return response()->download($file[1]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeLectureSheet(Request $request)
    {
        foreach($request->items as $item) {
            $url = "https://api.bacbonschool.com/uploads/LectureSheets/".$item['folder']."/".$item['file'];
            $thumbnail = "https://api.bacbonschool.com/uploads/LectureSheets/".$item['folder']."/thumbnails/".$item['thumbnail'];
            LectureSheet::create([               
                'name' => $item['name'],
                'name_bn' => $item['name_bn'],
                'url' => $url,
                'thumbnails' => $thumbnail,
                'url_aws' => null
                ]);
        }
        
        return FacadeResponse::json("done");
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LectureSheet  $lectureSheet
     * @return \Illuminate\Http\Response
     */
    public function show(LectureSheet $lectureSheet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LectureSheet  $lectureSheet
     * @return \Illuminate\Http\Response
     */
    public function edit(LectureSheet $lectureSheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LectureSheet  $lectureSheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LectureSheet $lectureSheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LectureSheet  $lectureSheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(LectureSheet $lectureSheet)
    {
        //
    }
}
