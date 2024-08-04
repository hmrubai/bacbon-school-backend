<?php

namespace App\Console;

use App\LectureVideo;
use App\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function () {


            $datetime = date("Y-m-d H:i:s");
            $timestamp = strtotime($datetime);
            if (date('H') < 13) {
                $time = $timestamp - (18 * 60 * 60);
            } else {
                $time = $timestamp - (6 * 60 * 60);
            }
            $fromTime = date("Y-m-d H:i:s", $time);


            $lectureCount = LectureVideo::whereBetween('created_at', [$fromTime, date("Y-m-d H:i:s")])->count();
            if ($lectureCount > 1) {


            // $users = User::whereNotNull('fcm_id')->select('id', 'fcm_id')->get();
            $user = User::where('user_id', 1)->select('id', 'fcm_id')->first();
            // foreach ($users as $user) {
                $this->sendNotification($user->fcm_id, $lectureCount);
            // }
            }
        })->twiceDaily(12, 18)
        ->runInBackground();
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    public function sendNotification($token, $recentVideoNumber)
    {
        // $token = 'cJLYejCHYlg:APA91bG71AoSqPgi4GdjxqyPWAVrzhqLxt7CaIqMjkA0Txye62PATivvkk9FFGywQcAlP1fYCYw-5XGMPMVFTplInla-ojmQSpO9M9LljfDgHs7UeIDKzpfirmTiHZ32bWcK6SFkuWFJ';
        $fcmObject = (object) [
            "title" => "BacBon School",
            "body" => "There has been uploaded ".$recentVideoNumber. " lecttures recently",
            "data" => "Hello"
        ];
        $this->sendFCMNotification($token, $fcmObject);
        return 'Success';
    }
    public function sendFCMNotification($token, $obj)
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder("$obj->title");
        $notificationBuilder->setBody($obj->body)
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['refferedUserNumber' => $obj->data]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();


        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        $downstreamResponse->tokensToDelete();


        $downstreamResponse->tokensToModify();


        $downstreamResponse->tokensToRetry();
    }

}
