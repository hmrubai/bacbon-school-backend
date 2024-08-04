<?php namespace App\Http\Controllers;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class FcmClass
{

public function sendAdminFCMNotification ($token, $data) {
    $optionBuilder = new OptionsBuilder();
    $optionBuilder->setTimeToLive(60*20);
    $optionBuilder->setPriority('high');

    $notificationBuilder = new PayloadNotificationBuilder($data['title']);
    $notificationBuilder->setClickAction('FLUTTER_NOTIFICATION_CLICK');
    $notificationBuilder->setBody($data['body']);
    $notificationBuilder->setIcon($data['image']);
    $notificationBuilder->setSound('default');

    $dataBuilder = new PayloadDataBuilder();
    $dataBuilder->addData($data);

    $option = $optionBuilder->build();
    $notification = $notificationBuilder->build();
    $data = $dataBuilder->build();

    FCM::sendTo($token, $option, $notification, $data);

}


}
