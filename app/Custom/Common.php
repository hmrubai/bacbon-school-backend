<?php
namespace App\Custom;
use App\LectureVideo;
use App\LectureQuestion;
use App\PaymentLecture;
use App\Payment;
class Common {

    public function lecturePaymentGlobal ($data, $payment, $lecture) {

        $lecturePrice = $lecture->price - $data['discount'];

        if ($data['amount'] > $lecture->price) {
            $restAmount = $data['amount'] - $lecture->price;


            $unpaidLecturesCount = PaymentLecture::where('user_id', $data['user_id'])->where('isPaid', false)->count();
            $unpaidLectures = PaymentLecture::where('user_id', $data['user_id'])->where('isPaid', false)->get();
            if ($unpaidLecturesCount > 0 ) {
                foreach($unpaidLectures as $ul) {
                    $ulAmount = $ul->actual_price - $ul->amount;
                    if ($restAmount >= $ulAmount) {
                        PaymentLecture::where('id', $ul->id)->update([
                            'amount' => $ul->actual_price,
                            'isPaid' => true,
                            ]);
                    } else {
                        PaymentLecture::where('id', $ul->id)->update([
                            'amount' => $restAmount,
                        ]);

                        break;
                    }
                    $restAmount = $restAmount - $ulAmount;
                }

                $paymentLectureObj = (array)[
                    "user_id" => $data['user_id'],
                    "lecture_id" => $data['lecture_id'],
                    "payment_id" => $payment->id,
                    "amount" => $lecture->price + $restAmount,
                    "actual_price" =>$lecture->price,
                    "isPaid" =>$data['amount'] >= $lecturePrice? true : false,
                ];
                PaymentLecture::create($paymentLectureObj);
            } else {



                $paymentLectureObj = (array)[
                    "user_id" => $data['user_id'],
                    "lecture_id" => $data['lecture_id'],
                    "payment_id" => $payment->id,
                    "amount" => $data['amount'],
                    "actual_price" =>$lecture->price,
                    "isPaid" =>$data['amount'] >= $lecturePrice? true : false,
                 ];
                 PaymentLecture::create($paymentLectureObj);




            }
        } else {
            $totalBalance = $data['amount'] + $payment->balance;
            if($payment->balance > 0) {
                $paymentLectureObj = (array)[
                    "user_id" => $data['user_id'],
                    "lecture_id" => $data['lecture_id'],
                    "payment_id" => $payment->id,
                    "amount" => $data['amount'],
                    "actual_price" =>$lecture->price,
                    "isPaid" => $totalBalance >= $lecturePrice? true : false,
                 ];
                 PaymentLecture::create($paymentLectureObj);

            } else {
                $paymentLectureObj = (array)[
                    "user_id" => $data['user_id'],
                    "lecture_id" => $data['lecture_id'],
                    "payment_id" => $payment->id,
                    "amount" => $data['amount'],
                    "actual_price" =>$lecture->price,
                    "isPaid" =>$totalBalance >= $lecturePrice? true : false,
                 ];
                 PaymentLecture::create($paymentLectureObj);
            }
        }
        return true;
    }


    public function getChapterPrice($chapterId, $userId) {
        $sum = 0;
        $paid = PaymentLecture::where('user_id', $userId)
        ->where('isPaid', true)->select('lecture_id')->get();

        $lectureListToPay = LectureVideo::where('chapter_id',  $chapterId)
        ->whereNotIn('id', $paid)->get();
        // $unPaid = 0;
        $partiallyPaid = 0;
        foreach($lectureListToPay as $lecture){
            $pl = PaymentLecture::where('user_id', $userId)
            ->where('lecture_id', $lecture->id)
            ->first();
            if ($pl) {
                // $unPaid += $pl->actual_price - $pl->amount;
                $partiallyPaid += $pl->amount;
            }
        }

        $sum = $lectureListToPay->sum('price');
        return $sum - $partiallyPaid;
    }


    public function PaymentLecturesOfChapter ($data, $payment, $paymentAmount) {

        $paid = PaymentLecture::where('user_id', $data['user_id'])
        ->where('isPaid', true)->select('lecture_id')->get();
        $lectureListToPay = LectureVideo::where('chapter_id',  $data['chapter_id'])
        ->whereNotIn('id', $paid)->get();

        foreach($lectureListToPay as $lecture) {
            if ($paymentAmount) {
                $paymentAmountForLecture = 0;
                $pl = PaymentLecture::where('user_id', $data['user_id'])
                ->where('lecture_id', $lecture->id)
                ->first();
                if ($pl) {
                    if ($paymentAmount >= ($pl->actual_price - $pl->amount)) {
                        // $paymentAmountForLecture = $pl->actual_price;
                        PaymentLecture::where('id', $pl->id)->update([
                            // "amount" => $paymentAmountForLecture,
                            "isPaid" => true
                        ]);
                    }
                } else {

                    $paymentAmountForLecture = $paymentAmount > $lecture->price? $lecture->price : $paymentAmount;
                    $paymentLectureObj = (array)[
                        "user_id" => $data['user_id'],
                        "lecture_id" => $lecture->id,
                        "payment_id" => $payment->id,
                        "amount" => $paymentAmountForLecture,
                        "actual_price" =>$lecture->price,
                        "isPaid" => true,
                     ];
                     PaymentLecture::create($paymentLectureObj);
                }
            }
        }
        return true;
    }

}
