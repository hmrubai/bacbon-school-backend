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
use App\Subject;
use App\Chapter;
use App\CourseSubject;
use App\LectureVideo;

use App\Payment;
use App\PaymentCourse;
use App\PaymentSubject;
use App\PaymentChapter;
use App\PaymentLecture;
use PhpParser\Node\Expr\Cast\Object_;

class SearchController extends Controller
{


    public function searchByCode ($search, $user_id) {
        $item = Course::where('code', $search)->first();
        if ($item) {
            $item['type'] = "Course";
            $item['payable_amount'] = $item['price'] - $this->getPreviousPaymentOfCourse($item->id, $user_id);
        }
        if (!$item) {
            $item = CourseSubject::where('code', $search)
            ->join('subjects', 'course_subjects.subject_id', 'subjects.id')
            ->select('subjects.id as id', 'subjects.name as name', 'subjects.name_bn as name_bn', 'course_subjects.code as code', 'course_subjects.course_id as course_id', 'course_subjects.price as price')
            ->with('course')
            ->first();
            if ($item) {
                $item['type'] = "Subject";
                // return $item;
                if ($this->checkCourseBought($item->course_id, $user_id)) {
                    $item['payable_amount'] = 0;
                } else if ($this->checkSubjectBought($item->id, $item->course_id, $user_id)) {
                    $item['payable_amount'] = 0;
                }  else {
                    $item['payable_amount'] = $item['price'] - $this->getPreviousPaymentOfSubject($item->id, $item->course_id, $user_id);
                }
            }
        }
        if (!$item) {
            $item = Chapter::where('code', $search)
            ->with('course', 'subject')
            ->first();
            if ($item) {
                if ($this->checkCourseBought($item->course_id, $user_id)) {
                    $item['payable_amount'] = 0;
                } else if ($this->checkSubjectBought($item->subject_id, $item->course_id, $user_id)) {
                    $item['payable_amount'] = 0;
                } else if ($this->checkChapterBought($item->id, $user_id)) {
                    $item['payable_amount'] = 0;
                } else {
                    $item['payable_amount'] = $item['price'] - $this->getPreviousPaymentOfChapter($item->id, $user_id);
                }

                $item['type'] = "Chapter";
            }
        }

        if (!$item) {
            $item = LectureVideo::where('code', $search)
            ->select('id', 'title as name', 'code', 'course_id', 'subject_id', 'chapter_id', 'price')
            ->with('course', 'subject', 'chapter')
            ->first();
            if ($item) {
                if ($this->checkCourseBought($item->course_id, $user_id)) {
                    $item['payable_amount'] = 0;
                } else if ($this->checkSubjectBought($item->subject_id, $item->course_id, $user_id)) {
                    $item['payable_amount'] = 0;
                } else if ($this->checkChapterBought($item->chapter_id, $user_id)) {
                    $item['payable_amount'] = 0;
                } else if ($this->checkLectureBought($item->id, $user_id)){
                    $item['payable_amount'] = 0;
                } else {
                    $item['payable_amount'] = $item['price'];
                }
                $item['type'] = "Lecture";
            }
        }
        return $item;
    }

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




    public function purchaseLecture (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'lecture_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }
        $current_date_time = Carbon::now();

        $isPrevioslyBought = PaymentLecture::where('user_id', $request->user_id)->where('lecture_id', $request->lecture_id)->count();
        if ($isPrevioslyBought) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this Lecture";
            return FacadeResponse::json($response);
        }

        $user = User::where('id', $request->user_id)->first();
        $lecture = LectureVideo::where('id', $request->lecture_id)->first();


        if ($this->checkCourseBought($lecture->course_id, $request->user_id) ) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this full Course";
            return FacadeResponse::json($response);
        }

        if ($this->checkSubjectBought($lecture->subject_id, $lecture->course_id, $request->user_id) ) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this full Subject";
            return FacadeResponse::json($response);
        }
        if ($this->checkChapterBought($lecture->chapter_id, $request->user_id) ) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this full Chapter";
            return FacadeResponse::json($response);
        }

        if ($this->checkLectureBought($lecture->id, $request->user_id) ) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this Lecture";
            return FacadeResponse::json($response);
        }


        if ( ($user->balance + $request->amount +  $request->discount) < $lecture->price) {
            $response->status = $response::status_fail;
            $response->messages = "insufficient balance";
            return FacadeResponse::json($response);
        } else {
            $paying_amount = $request->amount;
            $amount_from_balance = 0;
            $amount_to_balance = 0;
            if ( ( $request->amount + $request->discount ) <  $lecture->price) {
                $amount_from_balance = $lecture->price - ( $request->amount + $request->discount );
                $balance = $user->balance - $amount_from_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            } else {
                $amount_to_balance =  ( $request->amount + $request->discount) - $lecture->price ;
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

            $paymentLecture = PaymentLecture::create([
                'payment_id' => $payment->id,
                'user_id' => $request->user_id,
                'course_id' => $lecture->course_id,
                'subject_id' => $lecture->subject_id,
                'chapter_id' => $lecture->chapter_id,
                'lecture_id' => $lecture->id,
                'amount' => $lecture->price
            ]);
            $response->status = $response::status_ok;
            $response->messages = "Successfully Bought";
            return FacadeResponse::json($response);
        }
    }


    public function purchaseChapter (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'chapter_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        $user = User::where('id', $request->user_id)->first();
        $chapter = Chapter::where('id', $request->chapter_id)->first();

        if ($this->checkCourseBought($chapter->course_id, $request->user_id) ) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this full Course";
            return FacadeResponse::json($response);
        }

        if ($this->checkSubjectBought($chapter->subject_id, $chapter->course_id, $request->user_id) ) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this full Subject";
            return FacadeResponse::json($response);
        }
        if ($this->checkChapterBought($chapter->id, $request->user_id) ) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this Chapter";
            return FacadeResponse::json($response);
        }
        $chapterPrice = $chapter->price - $this->getPreviousPaymentOfChapter($request->chapter_id, $request->user_id);

        if ( ($user->balance + $request->amount +  $request->discount) < $chapterPrice) {
            $response->status = $response::status_fail;
            $response->messages = "insufficient balance";
            return FacadeResponse::json($response);
        } else {
            $amount_from_balance = 0;
            $amount_to_balance = 0;
            if ( ( $request->amount + $request->discount ) <  $chapterPrice) {
                $amount_from_balance = $chapterPrice - ( $request->amount + $request->discount );
                $balance = $user->balance - $amount_from_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            } else {
                $amount_to_balance =  ( $request->amount + $request->discount) - $chapterPrice ;
                $balance = $user->balance + $amount_to_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            }

            $current_date_time = Carbon::now();
            $payment = Payment::create([
                'user_id' => $request->user_id,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'amount_from_balance' => $amount_from_balance,
                'amount_to_balance' => $amount_to_balance,
                'payment_date' => $current_date_time,
                'discount' => $request->discount
            ]);

            $paymentLecture = PaymentChapter::create([
                'payment_id' => $payment->id,
                'user_id' => $request->user_id,
                'course_id' => $chapter->course_id,
                'subject_id' => $chapter->subject_id,
                'chapter_id' => $chapter->id,
                'amount' => $chapterPrice
            ]);
            $response->status = $response::status_ok;
            $response->messages = "Successfully Bought";
            return FacadeResponse::json($response);
        }
    }



    public function purchaseSubject (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'subject_id' => 'required',
            'course_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        $user = User::where('id', $request->user_id)->first();
        $subject = CourseSubject::where('subject_id', $request->subject_id)->where('course_id', $request->course_id)->first();

        if ($this->checkCourseBought($subject->course_id, $request->user_id) ) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this full Course";
            return FacadeResponse::json($response);
        }

        if ($this->checkSubjectBought($subject->subject_id, $subject->course_id, $request->user_id) ) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this full Subject";
            return FacadeResponse::json($response);
        }

        $subjectPrice = $subject->price - $this->getPreviousPaymentOfSubject($request->subject_id, $request->course_id, $request->user_id);

        if ( ($user->balance + $request->amount +  $request->discount) < $subjectPrice) {
            $response->status = $response::status_fail;
            $response->messages = "insufficient balance";
            return FacadeResponse::json($response);
        } else {
            $amount_from_balance = 0;
            $amount_to_balance = 0;
            if ( ( $request->amount + $request->discount ) <  $subjectPrice) {
                $amount_from_balance = $subjectPrice - ( $request->amount + $request->discount );
                $balance = $user->balance - $amount_from_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            } else {
                $amount_to_balance =  ( $request->amount + $request->discount) - $subjectPrice ;
                $balance = $user->balance + $amount_to_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            }

            $current_date_time = Carbon::now();
            $payment = Payment::create([
                'user_id' => $request->user_id,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'amount_from_balance' => $amount_from_balance,
                'amount_to_balance' => $amount_to_balance,
                'payment_date' => $current_date_time,
                'discount' => $request->discount
            ]);

            $paymentLecture = PaymentSubject::create([
                'payment_id' => $payment->id,
                'user_id' => $request->user_id,
                'course_id' => $subject->course_id,
                'subject_id' => $subject->subject_id,
                'amount' => $subjectPrice
            ]);
            $response->status = $response::status_ok;
            $response->messages = "Successfully Bought";
            return FacadeResponse::json($response);
        }
    }



    public function purchaseCourse (Request $request) {
        $response = new ResponseObject;
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'user_id' => 'required',
            'course_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            return FacadeResponse::json($response);
        }

        $user = User::where('id', $request->user_id)->first();
        $course = Course::where('id', $request->course_id)->first();

        if ($this->checkCourseBought($course->course_id, $request->user_id) ) {
            $response->status = $response::status_fail;
            $response->messages = "This user has already bought this full Course";
            return FacadeResponse::json($response);
        }

        $coursePrice = $course->price - $this->getPreviousPaymentOfCourse($request->course_id, $request->user_id);

        if ( ($user->balance + $request->amount +  $request->discount) < $coursePrice) {
            $response->status = $response::status_fail;
            $response->messages = "insufficient balance";
            return FacadeResponse::json($response);
        } else {
            $amount_from_balance = 0;
            $amount_to_balance = 0;
            if ( ( $request->amount + $request->discount ) <  $coursePrice) {
                $amount_from_balance = $coursePrice - ( $request->amount + $request->discount );
                $balance = $user->balance - $amount_from_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            } else {
                $amount_to_balance =  ( $request->amount + $request->discount) - $coursePrice ;
                $balance = $user->balance + $amount_to_balance;
                User::where('id', $request->user_id)->update(['balance' => $balance]);
            }

            $current_date_time = Carbon::now();
            $payment = Payment::create([
                'user_id' => $request->user_id,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'amount_from_balance' => $amount_from_balance,
                'amount_to_balance' => $amount_to_balance,
                'payment_date' => $current_date_time,
                'discount' => $request->discount
            ]);

            $paymentLecture = PaymentCourse::create([
                'payment_id' => $payment->id,
                'user_id' => $request->user_id,
                'course_id' => $course->id,
                'amount' => $coursePrice
            ]);
            $response->status = $response::status_ok;
            $response->messages = "Successfully Bought";
            return FacadeResponse::json($response);
        }
    }

}
