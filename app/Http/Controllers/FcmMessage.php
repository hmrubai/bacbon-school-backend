<?php namespace App\Http\Controllers;
use Benwilkins\FCM\FcmMessage;


class FcmMessageClass
{

// public function sendAdminFCMNotification ($token, $data) {
//     $optionBuilder = new OptionsBuilder();
//     $optionBuilder->setTimeToLive(60*20);
//     $optionBuilder->setPriority('high');

//     $notificationBuilder = new PayloadNotificationBuilder($data['title']);
//     $notificationBuilder->setClickAction('FLUTTER_NOTIFICATION_CLICK');
//     $notificationBuilder->setBody($data['body']);
//     $notificationBuilder->setIcon($data['image']);
//     $notificationBuilder->setSound('default');

//     $dataBuilder = new PayloadDataBuilder();
//     $dataBuilder->addData($data);

//     $option = $optionBuilder->build();
//     $notification = $notificationBuilder->build();
//     $data = $dataBuilder->build();

//     FCM::sendTo($token, $option, $notification, $data);

// }

    public function toFcm($notifiable) 
    {
        $message = new FcmMessage();
        $message->setHeaders([
        'project_id'    =>  "843876597604"   // FCM sender_id
         ])->content([
            'title'        => 'Foo', 
            'body'         => 'Bar', 
            'sound'        => '', // Optional 
            'icon'         => '', // Optional
            'click_action' => '' // Optional
        ])->data([
            'param1' => 'baz' // Optional
        ])->priority(FcmMessage::PRIORITY_HIGH); // Optional - Default is 'normal'.
        
        return $message;
    }


}
