<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseObject;

use DB;
use Validator;
use Carbon\Carbon;
use App\User;
use App\Course;
use App\Chapter;
use App\CourseSubject;
use App\LectureVideo;

use App\Payment;
use App\PaymentCourse;
use App\PaymentSubject;
use App\PaymentChapter;
use App\PaymentLecture;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;


class PaymentController extends Controller
{


    public function checkCourseBought($id, $user_id) {
        $isPaid = PaymentCourse::where('course_id', $id)->where('user_id', $user_id)->first();
        return $isPaid? true: false;
    }
    public function checkSubjectBought($id, $course_id, $user_id) {
        $isPaid = PaymentSubject::where('subject_id', $id)->where('course_id', $course_id)->where('user_id', $user_id)->first();
        return $isPaid? true: false;
    }
    public function checkChapterBought($id, $user_id) {
        $isPaid = PaymentChapter::where('chapter_id', $id)->where('user_id', $user_id)->first();
        return $isPaid? true: false;
    }
    public function checkLectureBought($id, $user_id) {
        $isPaid = PaymentLecture::where('lecture_id', $id)->where('user_id', $user_id)->first();
        return $isPaid? true: false;
    }

    public function getPreviousPaymentOfChapter($chapter_id, $user_id) {
        $givenPrice = PaymentLecture::where('chapter_id', $chapter_id)
        ->where('user_id', $user_id)
        ->where('is_based', true)
        ->groupBy('chapter_id')
        ->select( DB::raw('sum(amount) as sum'))
        ->first();
        if ($givenPrice) {
            return $givenPrice->sum;
        }
        return 0;
    }

    public function getPreviousPaymentOfSubject($subject_id, $course_id, $user_id) {
        $sum = 0;
        $paymentLecture = PaymentLecture::where('subject_id', $subject_id)->where('course_id', $course_id)
        ->where('user_id', $user_id)
        ->groupBy('subject_id', 'course_id')
        ->select( DB::raw('sum(amount) as sum'))
        ->first();
        if ($paymentLecture) {
            $sum += $paymentLecture->sum;
        }
        $paymentChapter = PaymentChapter::where('subject_id', $subject_id)->where('course_id', $course_id)
        ->where('user_id', $user_id)
        ->groupBy('subject_id', 'course_id')
        ->select( DB::raw('sum(amount) as sum'))
        ->first();

        if ($paymentChapter) {
            $sum += $paymentChapter->sum;
        }
        return $sum;
    }

    public function getPreviousPaymentOfCourse($course_id, $user_id) {
        $sum = 0;

        $paymentCourse = PaymentCourse::where('course_id', $course_id)
        ->where('user_id', $user_id)
        ->groupBy('course_id')
        ->select( DB::raw('sum(amount) as sum'))
        ->first();
        if ($paymentCourse) {
            $sum += $paymentCourse->sum;
        }

        $paymentLecture = PaymentLecture::where('course_id', $course_id)
        ->where('user_id', $user_id)
        ->groupBy('subject_id', 'course_id')
        ->select( DB::raw('sum(amount) as sum'))
        ->first();
        if ($paymentLecture) {
            $sum += $paymentLecture->sum;
        }
        $paymentChapter = PaymentChapter::where('course_id', $course_id)
        ->where('user_id', $user_id)
        ->groupBy('subject_id', 'course_id')
        ->select( DB::raw('sum(amount) as sum'))
        ->first();

        if ($paymentChapter) {
            $sum += $paymentChapter->sum;
        }

        $paymentSubject = PaymentSubject::where('course_id', $course_id)
        ->where('user_id', $user_id)
        ->groupBy('subject_id', 'course_id')
        ->select( DB::raw('sum(amount) as sum'))
        ->first();

        if ($paymentSubject) {
            $sum += $paymentSubject->sum;
        }
        return $sum;
    }

    public function purchaseList (Request $request) {
        $boughtItem = "";
        $totalPayablePrice = 0;
        $response = new ResponseObject;
        $data = $request->json()->all();
        $current_date_time = Carbon::now();
        $count = 0;

        $user = User::where('id', $request->user_id)->first();

        foreach ($request->items as $item) {
            if ($item['lecture_id']) {
                $lecture = LectureVideo::where('id', $item['lecture_id'])->first();
                $totalPayablePrice +=  $this->checkLectureBought($item['lecture_id'], $request->user_id) ? 0 : $lecture['price'];

            } else if ($item['chapter_id']) {

                $chapter = Chapter::where('id', $item['chapter_id'])->first();
                $totalPayablePrice += $chapter['price'] - $this->getPreviousPaymentOfChapter($item['chapter_id'], $request->user_id);

            }  else if ($item['subject_id']) {

                $subject = CourseSubject::where('id', $item['subject_id'])->first();
                $totalPayablePrice += $subject['price'] - $this->getPreviousPaymentOfSubject($subject['subject_id'], $item['course_id'], $request->user_id);

            } else {
                $course = Course::where('id', $item['course_id'])->first();
                $totalPayablePrice += $course['price'] - $this->getPreviousPaymentOfCourse($item['course_id'], $request->user_id);
            }
        }

        if ( ($user->balance + $request->amount +  $request->discount) < $totalPayablePrice) {
            $response->status = $response::status_fail;
            $response->messages = "insufficient balance";
            return FacadeResponse::json($response);
        } else {
            $amount_from_balance = 0;
            $amount_to_balance = 0;
            if ( ( $request->amount + $request->discount ) <  $totalPayablePrice) {
                $amount_from_balance = $totalPayablePrice - ( $request->amount + $request->discount );
                $balance = $user->balance - $amount_from_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            } else {
                $amount_to_balance =  ( $request->amount + $request->discount) - $totalPayablePrice ;
                $balance = $user->balance + $amount_to_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            }

            $payment = Payment::create([
                'user_id' => $request->user_id,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'amount_from_balance' => $amount_from_balance,
                'amount_to_balance' => $amount_to_balance,
                'payment_date' => $current_date_time,
                'discount' => $request->discount
            ]);

            foreach ($request->items as $item) {
                if ($item['lecture_id']) {
                    if (!$this->checkLectureBought($item['lecture_id'], $request->user_id) ) {
                        $lecture = LectureVideo::where('id', $item['lecture_id'])->first();
                        $this->purchaseLecture($request->user_id, $payment->id, $lecture);
                    }
                } else if ($item['chapter_id']) {
                    if (!$this->checkChapterBought($item['chapter_id'], $request->user_id) ) {
                        $chapter = Chapter::where('id', $item['chapter_id'])->first();
                        $chapterPrice = $chapter['price'] - $this->getPreviousPaymentOfChapter($item['chapter_id'], $request->user_id);
                        $this->purchaseChapter($request->user_id, $payment->id, $chapter, $chapterPrice);
                    }

                }  else if ($item['subject_id']) {
                    if (!$this->checkSubjectBought($item['subject_id'], $item['course_id'], $request->user_id) ) {
                        $subject = CourseSubject::where('id', $item['subject_id'])->first();
                        $subjectPrice = $subject['price'] - $this->getPreviousPaymentOfSubject($subject['subject_id'], $item['course_id'], $request->user_id);
                        $this->purchaseSubject($request->user_id, $payment->id, $subject, $subjectPrice);
                    }
                } else {
                    if (!$this->checkCourseBought($item['course_id'], $request->user_id) ) {
                        $course = Course::where('id', $item['course_id'])->first();
                        $coursePrice = $course['price'] - $this->getPreviousPaymentOfCourse($item['course_id'], $request->user_id);
                        $this->purchaseCourse($request->user_id, $payment->id, $course, $coursePrice);
                    }
                }
            }
            $this->sendFCMNotification($user->fcm_id);
            $response->status = $response::status_ok;
            $response->messages = "Successfully Bought";
            return FacadeResponse::json($response);
        }
    }

    public function purchaseLecture ($user_id, $payment_id, $item) {
        PaymentLecture::create([
            'payment_id' => $payment_id,
            'user_id' => $user_id,
            'course_id' => $item->course_id,
            'subject_id' => $item->subject_id,
            'chapter_id' => $item->chapter_id,
            'lecture_id' => $item->id,
            'amount' => $item->price
        ]);
    }
    public function purchaseChapter ($user_id, $payment_id, $item, $chapterPrice) {
        PaymentChapter::create([
            'payment_id' => $payment_id,
            'user_id' => $user_id,
            'course_id' => $item->course_id,
            'subject_id' => $item->subject_id,
            'chapter_id' => $item->id,
            'amount' => $chapterPrice
        ]);
    }
    public function purchaseSubject ($user_id, $payment_id, $item, $subjectPrice) {
        PaymentSubject::create([
            'payment_id' => $payment_id,
            'user_id' => $user_id,
            'course_id' => $item->course_id,
            'subject_id' => $item->id,
            'amount' => $subjectPrice
        ]);
    }
    public function purchaseCourse ($user_id, $payment_id, $item, $coursePrice) {
        PaymentCourse::create([
            'payment_id' => $payment_id,
            'user_id' => $user_id,
            'course_id' => $item->id,
            'amount' => $coursePrice
        ]);
    }



    public function getChapterPrice($chapterId, $userId) {
        $sum = 0;
        $paid = PaymentLecture::where('user_id', $userId)
        ->where('isPaid', true)->select('lecture_id')->get();

        $lectureListToPay = LectureVideo::where('chapter_id',  $chapterId)
        ->whereNotIn('id', $paid)->get();
        $unPaid = 0;
        $partiallyPaid = 0;
        foreach($lectureListToPay as $lecture){
            $pl = PaymentLecture::where('user_id', $userId)
            ->where('lecture_id', $lecture->id)
            ->first();
            if ($pl) {
                $unPaid += $pl->actual_price - $pl->amount;
                $partiallyPaid += $pl->amount;
            }
        }

        $sum = $lectureListToPay->sum('price');
        return $sum - $partiallyPaid;
    }


    public function sendFCMNotification ($token) {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder('Congratulation!');
        $notificationBuilder->setBody('Your payment successfull')
                            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();


        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        //return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();

        //return Array (key : oldToken, value : new token - you must change the token in your database )
        $downstreamResponse->tokensToModify();

        //return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();

    }


}
