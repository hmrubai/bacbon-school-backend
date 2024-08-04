<?php namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseObject;
use App\User;

class PromotionController extends Controller {

    const MODEL = "App\Promotion";

    public function add(Request $request)
    {
        $response = new ResponseObject;
        $model = null;
        $m = self::MODEL;
        $destinationPath = 'uploads/promotions/';

        $data = json_decode($request->data, true);

        if ($request->has('file') && $data["title"]) {


            $file = $request->file('file');
            $fileName = "Promo" . time().'.'.$file->getClientOriginalExtension();
            $destinationPath = 'uploads/promotions';


            if ($file->move($destinationPath, $fileName)) {
                 $fullUrl = 'https://api.bacbonschool.com/api/uploads/promotions/'.$fileName;
                // $fullUrl = 'http://localhost:8000/uploads/promotions/'.$fileName;
                 if ($data['id'] != null) {
                     $document = $m::where('id', $data['id'])->first();
                     if (!is_null($document)) {
                        $imageUrlArray = (array) explode('/',$document->promo_image_url,-1);
                         unlink('uploads/promotions/' .end($imageUrlArray));
                         $model=  $m::where('id', $data['id'])->update([
                             "title" => $data['title'],
                             "navigate_to_web_url" => $data['navigate_to_web_url'],
                             "navigate_to_app_location" => $data['navigate_to_app_location'],
                             "data" => $data['data'],
                             "should_cache" => $data['should_cache'],
                             "is_active" => $data['is_active'],
                             "type" => $data['type'],
                             "promo_image_url" => $fullUrl
                         ]);
                     }
                 } else {
                    $model=   $m::create([
                         "title" => $data['title'],
                         "promo_image_url" => $fullUrl,
                         "navigate_to_web_url" => $data['navigate_to_web_url'],
                         "navigate_to_app_location" => $data['navigate_to_app_location'],
                         "should_cache" => $data['should_cache'],
                         "is_active" => $data['is_active'],
                         "type" => $data['type'],
                         "data" => $data['data']
                     ]);
                 }
             }
        } else {

            $response->status = $response::status_fail;
            $response->messages = "No file or title has been added";
            $response->result =  null;
            return FacadeResponse::json($response);

        }

         $response->status = $response::status_ok;
         $response->messages = "Promo has been uploaded";
         $response->result =  $model;
        return FacadeResponse::json($response);
    }


    public function all () {
        $m = self::MODEL;
        return FacadeResponse::json($m::get());
    }

    public function getRandomPromotionForUser () {

        $m = self::MODEL;
        $promo = $m::where('is_active', true)->where('type', 'User')->inRandomOrder()->first();
        return FacadeResponse::json($promo);

    }

    public function getRandomPromotionForGuardian () {
        $m = self::MODEL;
        $promo = $m::where('is_active', true)->where('type', 'Guardian')->inRandomOrder()->first();
        return FacadeResponse::json($promo);
    }

    public function get (Request $request) {
        $m = self::MODEL;
        $promo = $m::find($request->id);
        return FacadeResponse::json($promo);
    }


	public function remove(Request $request)
	{
        $response = new ResponseObject;
        $m = self::MODEL;
        $document = $m::find($request->id);

		if(is_null($document)){

            $response->status = $response::status_fail;
            $response->messages = "No file found to delete";
            $response->result =  null;
            return FacadeResponse::json($response);

        }

       $imageUrlArray = (array) explode('/',$document->promo_image_url,-1);
        if (file_exists('uploads/promotions/'.end($imageUrlArray)))
            unlink('uploads/promotions/'.end($imageUrlArray));
		$m::destroy($request->id);

         $response->status = $response::status_ok;
         $response->messages = "Successfully deleted";
         $response->result =  $m::get();
         return FacadeResponse::json($response);
	}


    public function getRefDetails(Request $request) {

        $obj = (object) [
            "award" => "500",
            "award_bn" => "৫০০",
            "participant_count" => "100",
            "participant_count_bn" => "১০০",
            "total_referred" => User::where('refference_id', $request->user_id)->count()
        ];
         return FacadeResponse::json($obj);
     }


}
