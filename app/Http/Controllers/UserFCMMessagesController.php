<?php namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
class UserFCMMessagesController extends Controller {

    const MODEL = "App\UserFCMMessage";


    public function messageList($user_id) {
        $m = self::MODEL;
        $data = $m::where('user_id',$user_id)->orderBy('id', 'desc')->get();
        return FacadeResponse::json($data);
    }

    public function deleteMessage(Request $request) {
        $response = new ResponseObject;
        $m = self::MODEL;
        $data = $m::where('id',$request->id)->where('user_id',$request->user_id)->delete();
        $unseen_count = $m::where('user_id',$request->user_id)->where('seen',false)->count();
        
        $result = ["unseen_count" => $unseen_count];

        $response->status = $response::status_ok;
        $response->messages = "Message has been deleted";
        $response->result = $result;
        return FacadeResponse::json($response);

    }
    public function getMessageDetails($id) {
        $m = self::MODEL;
        $data = $m::where('id',$id)->first();
        return FacadeResponse::json($data);

    }
    public function markAsSeen(Request $request) {
        $response = new ResponseObject;
        $m = self::MODEL;
        $data = $m::where('id',$request->id)->update([
            'seen' => true
        ]);
        $unseen_count = $m::where('user_id',$request->user_id)->where('seen',false)->count();
        //$data->unseen_count = $m::where('user_id',$request->user_id)->where('seen',false)->count();
        $data = ['unseen_count' => $unseen_count];
        $response->status = $response::status_ok;
        $response->messages = "Message has been seened";
        $response->result = $data;
        return FacadeResponse::json($response);

    }

    
    public function deleteUserMessageByDate(Request $request){
        $response = new ResponseObject;
        $m = self::MODEL;
  
        $from = date('Y-m-d', strtotime($request->from));
        $to = date('Y-m-d', strtotime("+2 day", strtotime($request->to)));
     
      
        $list =  $m::whereBetween('created_at', [$from, $to])->delete();
        //  $list =  $m::where('created_at', '>=', '2021-04-20')->where('created_at', '<=', '2021-05-20')->get();
      //  $list->delete();
        $response->status = $response::status_ok;
        $response->messages = "Message has been deleted";
        $response->result = $list;
        return FacadeResponse::json($response);
    }
}
