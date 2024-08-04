<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\User;
use App\eBook;
use App\PaymentEBook;
use App\UserAllPayment;
use App\UserAllPaymentDetails;
use Illuminate\Http\Request;

class EBookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function geteBooklistByCourseId($courseId)
    {
        $ebooks = eBook::where('course_id', $courseId)->get();
        return FacadeResponse::json($ebooks);
    }

    public function UpdateeBookGPlayID(Request $request)
    {
        $ebooks = eBook::all();
        
        foreach ($ebooks as $item) {
            $gpid = "gp_eb_" . str_pad($item->id, 2, '0', STR_PAD_LEFT); 

            eBook::where('id', $item->id)->update([
                "gp_product_id" => $gpid
            ]);
        }

        $list = eBook::all();
        return FacadeResponse::json($list);
    }

    public function geteBooklistByCourseIdV2(Request $request)
    {
        $ebooks = eBook::where('course_id', $request->courseId)->get();
        foreach ($ebooks as $ebook) {
            $ebook->is_bought = false;
            $payment = PaymentEBook::where('e_book_id', $ebook->id)->where('user_id', $request->userId)->where('is_complete', 1)->first();
            if (!is_null($payment)) {
                $ebook->is_bought = true;
            }
        }
        return FacadeResponse::json($ebooks);
    }

    public function geteBooklistByCourseIdV3(Request $request)
    {
        $ebooks = eBook::where('course_id', $request->courseId)->with('e_book_feature','e_book_description_title.e_book_description_detial')->get();
        foreach ($ebooks as $ebook) {
            $ebook->is_bought = false;
            $payment = UserAllPayment::where('item_id', $ebook->id)->where('user_id', $request->userId)->where('item_type','=','E-Book')
            ->where('transaction_status','=','Complete')->first();
            if (!is_null($payment)) {
                $ebook->is_bought = true;
            }
        }
        return FacadeResponse::json($ebooks);
    }

    public function getePurchasedeBooklist($userId)
    {
      $ebookIds =  UserAllPayment::where('user_all_payments.user_id', $userId)->where('user_all_payments.item_type','=','E-Book')->pluck('item_id');      
        $ebooks = eBook::whereIn('id', $ebookIds)->with('e_book_feature','e_book_description_title.e_book_description_detial')->get();       
        return FacadeResponse::json($ebooks);
    }

 

    public function createUserEBookPayment(Request $request){
        $response = new ResponseObject;

        $check_payment = UserAllPayment::where('user_id', $request->user_id)->where('item_id', $request->item_id)
        ->where('item_type','=','E-Book')
        ->where('transaction_status','=','Complete')->first();

        if (!empty($check_payment)) {
            $response->status = $response::status_fail;
            $response->messages = "Already payment done for this book";
            $response->result = null;
            return FacadeResponse::json($response);
        }

        $user =  User::where('id', $request->user_id)->first();
        $eBook =  eBook::where('id', $request->item_id)->first();

        $payment = UserAllPayment::create([
            'user_id' => $request->user_id,
            'name' => $user->name, 
            'email' => $user->email, 
            'phone' => $user->mobile_number,  
            'address' => $user->address,  
            'currency' => $request->currency,
            'item_id' => $request->item_id,
            'item_name' => $eBook->name,
            'item_type'=> "E-Book",
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
            
        $eBook->update([
        'number_of_puchased' => $eBook->number_of_puchased + 1,
        ]);

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = " You have successfully purchased this book";
        $verifyController = new VerifyCodeController();
        $response->result = $verifyController->getLoginData($request->user_id);
        return FacadeResponse::json($response);
    }


 
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadEbook($id)
    {
        $ebook = eBook::where('id', $id)->first();
        $file = explode("https://api.bacbonschool.com/", $ebook->e_book_url);
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
    public function storeEbook(Request $request)
    {
        foreach($request->items as $item) {
            $url = "https://api.bacbonschool.com/uploads/ebooks/".$item['folder']."/".$item['file'];
            $thumbnail = "https://api.bacbonschool.com/uploads/ebooks/".$item['folder']."/thumbnails/".$item['thumbnail'];
            eBook::create([
                'course_id' => $item['course_id'],
                'name' => $item['name'],
                'name_bn' => $item['name_bn'],
                'e_book_url' => $url,
                'thumbnails' => $thumbnail,
                'e_book_url_aws' => null
                ]);
        }
        
        return FacadeResponse::json("done");
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\eBook  $eBook
     * @return \Illuminate\Http\Response
     */
    public function show(eBook $eBook)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\eBook  $eBook
     * @return \Illuminate\Http\Response
     */
    public function edit(eBook $eBook)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\eBook  $eBook
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, eBook $eBook)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\eBook  $eBook
     * @return \Illuminate\Http\Response
     */
    public function destroy(eBook $eBook)
    {
        //
    }
}
