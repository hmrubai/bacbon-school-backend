<?php
namespace App\Custom;
use App\LectureVideo;
use App\PaymentLecture;
use App\PaymentDetail;
use App\Payment;

class GenPayment {

    public function savePaymentDetail ($data) {
        // Insert Into Payment Details Table
        $paymentDetail = PaymentDetail::save([
            'user_id' => $data['user_id'],
            'lecture_id' => $data['lecture_id'],
            'payment_id' => $data['payment_id'],
            'amount' => $data['amount']
        ]);
        $this->savePaymentLecture($data);
    }

    public function savePaymentLecture ($data) {

        $getLectureInfo = LectureVideo::where('id', $data['lecture_id'])->first();

        // Insert Into Payment lecture Table
        $paidAmount = $this->getAllPaymentByLectureId($data['lecture_id'], $data['user_id']);
        $isPaid = $paidAmount < $data['amount'] ? false : true;
        if ($data['due']) {
            $isOpen = true;
        } else {
            $isOpen = $paidAmount < $data['amount'] ? false : true;
        }
        $previousPayment = PaymentLecture::where('user_id', $data['user_id'])->where('lecture_id', $data['lecture_id'])->first();
        if($previousPayment) {
            PaymentLecture::where('id', $previousPayment->id)->update([
                'user_id' => $data['user_id'],
                'lecture_id' => $data['lecture_id'],
                'isPaid' => $isPaid,
                'isOpen' => $isOpen,
                'actual_price' => $getLectureInfo->price,
                'discount' => $data['discount'],
                'due' => $data['due']
            ]);
        } else {
            PaymentLecture::save([
                'user_id' => $data['user_id'],
                'lecture_id' => $data['lecture_id'],
                'isPaid' => $isPaid,
                'isOpen' => $isOpen,
                'actual_price' => $getLectureInfo->price,
                'discount' => $data['discount'],
                'due' => $data['due']
            ]);
        }
    }

    public function getAllPaymentByLectureId ($lecture_id, $user_id) {
        return PaymentDetail::where('lecture_id', $lecture_id)->where('user_id', $user_id)->groupBy('lecture_id')->sum('amount');
    }
}
