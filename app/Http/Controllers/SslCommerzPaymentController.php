<?php

namespace App\Http\Controllers;

use App\CourseSubject;
use App\LectureVideoParticipant;

use App\CrashCourse;
use App\CrashCourseMaterial;
use App\CrashCourseParticipant;
use App\CrashCourseParticipantQuizAccess;
use App\CrashCourseSubject;
use App\LectureSheet;
use App\Library\SslCommerz\SslCommerzNotification;
use App\PaidCourse;
use App\PaidCourseMaterial;
use App\PaidCourseParticipant;
use App\PaidCourseParticipantQuizAccess;
use App\User;
use App\UserAllPayment;
use App\UserAllPaymentDetails;
use App\PaidCourseCoupon;
use App\PaidCourseApplyCoupon;
use DB;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

class SslCommerzPaymentController extends Controller
{

    public function exampleEasyCheckout()
    {
        return view('exampleEasycheckout');
    }

    public function exampleHostedCheckout()
    {
        return view('exampleHosted');
    }

    public function index(Request $request)
    {
        # Here you have to receive all the order data to initate the payment.
        # Let's say, your oder transaction informations are saving in a table called "orders"
        # In "orders" table, order unique identity is "transaction_id". "status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $post_data = array();
        $post_data['total_amount'] = '10'; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = 'Customer Name';
        $post_data['cus_email'] = 'customer@mail.com';
        $post_data['cus_add1'] = 'Customer Address';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = '8801XXXXXXXXX';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        #Before  going to initiate the payment order status need to insert or update as Pending.
        $update_product = DB::table('user_all_payments')
            ->where('transaction_id', $post_data['tran_id'])
            ->updateOrInsert([
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'amount' => $post_data['total_amount'],
                'transaction_status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency'],
            ]);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'hosted');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function purchaseLectureVideo(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();

        $course_subject = CourseSubject::select('course_subjects.*', 'courses.name as course_name', 'subjects.name as subject_name')
                    ->leftJoin('courses', 'courses.id', 'course_subjects.course_id')
                    ->leftJoin('subjects', 'subjects.id', 'course_subjects.subject_id')
                    ->where('course_subjects.course_id', $request->course_id)
                    ->where('course_subjects.subject_id', $request->subject_id)
                    ->first();

        if(empty($course_subject)){
            $result = ["messages" => "Unsuccessful! Subject Not found!", "status" => 'FAIL'];
            return FacadeResponse::json($result);
        }

        if(empty($user)){
            $result = ["messages" => "Unsuccessful! User Not found!", "status" => 'FAIL'];
            return FacadeResponse::json($result);
        }

        $already_purchased = LectureVideoParticipant::where('user_id', $request->user_id)
            ->where('course_subject_id', $course_subject->id)
            ->where('payment_status', 'completed')
            ->first();

        if(!empty($already_purchased)){
            $result = ["messages" => "Already purchased!", "status" => 'FAIL', 'data' => $already_purchased];
            return FacadeResponse::json($result);
        }

        $is_exist = LectureVideoParticipant::where('user_id', $request->user_id)
            ->where('course_subject_id', $course_subject->id)
            ->where('payment_status', 'pending')
            ->first();

        if(empty($is_exist)){
            LectureVideoParticipant::create([
                'user_id'           => $request->user_id, 
                'course_id'         => $request->course_id, 
                'subject_id'        => $request->subject_id, 
                'course_subject_id' => $course_subject->id, 
                'total_amount'      => $course_subject->price,
                'paid_amount'       => $course_subject->price, 
                'is_fully_paid'     => 1, 
                'is_trial_taken'    => 0, 
                'is_active'         => 1, 
                'payment_status'    => "pending"
            ]);
        }

        $post_data = array();
        $post_data['total_amount'] = $course_subject->price; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $user->name;
        $post_data['cus_email'] = $user->email ? $user->email : '';
        $post_data['cus_add1'] = "Bangladesh";
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = $user->mobile_number;
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = $course_subject->course_name . ' - ' . $course_subject->subject_name;
        $post_data['product_category'] = "vitual";
        $post_data['product_profile'] = "vitual-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        #Before  going to initiate the payment order status need to update as Pending.
        $user_payment = UserAllPayment::where('transaction_id', $post_data['tran_id'])
            ->updateOrCreate([
                'user_id' => $user->id,
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'item_id' => $course_subject->id,
                'item_name' => $course_subject->course_name . ' - ' . $course_subject->subject_name,
                'item_type' => "Lecture Videos",
                'payable_amount' => $post_data['total_amount'],
                'paid_amount' => $post_data['total_amount'],
                'discount' => 0,
                'transaction_status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency'],
                'status' => 'Pending',
            ]);

        $user_payment_details = UserAllPaymentDetails::updateOrCreate([
            'payment_id' => $user_payment->id,
            'amount' => $user_payment->paid_amount,
        ]);

        //return FacadeResponse::json("Response from Backend!");

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        return $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }
    }

    public function purchasePaidCourse(Request $request)
    {

        # Here you have to receive all the order data to initate the payment.
        # Lets your oder trnsaction informations are saving in a table called "orders"
        # In orders table order uniq identity is "transaction_id","status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $user = User::where('id', $request->user_id)->first();
        $course = PaidCourse::where('id', $request->item_id)->first();

        $coupon_code = $request->coupon_code ? $request->coupon_code : '';
        $coupon_price = 0;
        $coupon_id = null;

        if($coupon_code){
            $coupon = PaidCourseCoupon::where('coupon_code', $coupon_code)->first();
            $coupon_price = $coupon->coupon_value;
            $coupon_id = $coupon->id;
        }

        $post_data = array();
        $post_data['total_amount'] = $course->sales_amount - $coupon_price; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $user->name;
        $post_data['cus_email'] = $user->email ? $user->email : '';
        $post_data['cus_add1'] = "Bangladesh";
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = $user->mobile_number;
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = $course->name;
        $post_data['product_category'] = "vitual";
        $post_data['product_profile'] = "vitual-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        #Before  going to initiate the payment order status need to update as Pending.
        $user_payment = UserAllPayment::where('transaction_id', $post_data['tran_id'])
            ->updateOrCreate([
                'user_id' => $user->id,
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'item_id' => $course->id,
                'item_name' => $course->name,
                'item_type' => "Paid Course",
                'coupon_id' => $coupon_id,
                'payable_amount' => $course->sales_amount,
                'paid_amount' => $post_data['total_amount'],
                'discount' => $coupon_price,
                'transaction_status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency'],
                'status' => 'Pending',
            ]);

        $user_payment_details = UserAllPaymentDetails::updateOrCreate([
            'payment_id' => $user_payment->id,
            'amount' => $user_payment->paid_amount,
        ]);

        //return FacadeResponse::json("Response from Backend!");

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        return $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }
    }
    
    public function purchasePaidCourseWithCoupon(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $course = PaidCourse::where('id', $request->item_id)->first();

        $coupon_code = $request->coupon_code ? $request->coupon_code : '';
        $coupon_price = 0;
        $coupon_id = null;

        if($coupon_code){
            $coupon = PaidCourseCoupon::where('coupon_code', $coupon_code)->first();
            $coupon_price = $coupon->coupon_value;
            $coupon_id = $coupon->id;
        }

        $post_data = array();
        $post_data['total_amount'] = $course->sales_amount - $coupon_price; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid();

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $user->name;
        $post_data['cus_email'] = $user->email ? $user->email : '';
        $post_data['cus_add1'] = "Bangladesh";
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = $user->mobile_number;
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = $course->name;
        $post_data['product_category'] = "vitual";
        $post_data['product_profile'] = "vitual-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        $user_payment = UserAllPayment::where('transaction_id', $post_data['tran_id'])
            ->updateOrCreate([
                'user_id' => $user->id,
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'item_id' => $course->id,
                'item_name' => $course->name,
                'item_type' => "Paid Course",
                'coupon_id' => $coupon_id,
                'payable_amount' => $course->sales_amount,
                'paid_amount' => $post_data['total_amount'],
                'discount' => $coupon_price,
                'payment_status' => 'Full Paid',
                'transaction_status' => 'Complete',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency'],
                'card_type' => 'Coupon',
                'status' => 'Enrolled',
            ]);

        $user_payment_details = UserAllPaymentDetails::updateOrCreate([
            'payment_id' => $user_payment->id,
            'amount' => $course->sales_amount,
        ]);


        $checkParticipant = PaidCourseParticipant::where('paid_course_id', $request->item_id)->where('user_id', $request->user_id)->first();

        //$coupon_id = $order_detials->coupon_id ? $order_detials->coupon_id : 0;
        
        if($coupon_id){
            $coupon_details = PaidCourseApplyCoupon::where('coupon_id', $coupon_id)
                ->where('user_id', $request->user_id)
                ->where('paid_course_id', $request->item_id)
                ->where('applied_status', 'panding')
                ->first();

            if(!empty($coupon_details)){
                PaidCourseApplyCoupon::where('id', $coupon_details->id)->update([
                    'applied_status' => 'successful'
                ]);
            }
        }

        if (empty($checkParticipant)) {
            PaidCourseParticipant::create([
                'user_id' => $request->user_id,
                'paid_course_id' => $request->item_id,
                'course_amount' => $course->sales_amount,
                'paid_amount' => $user_payment->paid_amount,
                'is_fully_paid' => true,
            ]);
        } else {
            $checkParticipant->update([
                'paid_amount' => $user_payment->paid_amount,
                'is_active' => true,
                'is_fully_paid' => true,
            ]);
        }

        $course->update([
            'number_of_students_enrolled' => $course->number_of_students_enrolled + 1,
        ]);

        $material_exam_list = PaidCourseMaterial::where('paid_course_id', $request->item_id)->get();
        foreach ($material_exam_list as $material) {
            $quizAccess = PaidCourseParticipantQuizAccess::where('user_id', $request->user_id)
                ->where('paid_course_material_id', $material->id)->first();

            if (empty($quizAccess)) {
                if($material->test_type == "RevisionTest"){
                    PaidCourseParticipantQuizAccess::create([
                        "paid_course_material_id" => $material->id,
                        "user_id" => $request->user_id,
                        "access_count" => 100,
                    ]);
                }
                if($material->test_type == "ModelTest"){
                    PaidCourseParticipantQuizAccess::create([
                        "paid_course_material_id" => $material->id,
                        "user_id" => $request->user_id,
                        "access_count" => 1,
                    ]);
                }
                if($material->test_type == "WeeklyTest"){
                    PaidCourseParticipantQuizAccess::create([
                        "paid_course_material_id" => $material->id,
                        "user_id" => $request->user_id,
                        "access_count" => 1,
                    ]);
                }

            } else {
                $quizAccess->update([
                    'access_count' => 100,
                ]);
            }
        }

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "You have successfully enrolled this course";
        $response->result = "";
        return FacadeResponse::json($response);
    }

    public function ManuallyEnrollIntoPaidCourseWithCoupon(Request $request)
    {
        $response = new ResponseObject;

        $user = User::where('mobile_number', $request->mobile)->first();
        if(empty($user)){
            $response->status = $response::status_fail;
            $response->result = [];
            $response->messages = "User not found";
            return FacadeResponse::json($response);
        }else{
            $user_id = $user->id;
        }

        $is_purchased = PaidCourseParticipant::where('paid_course_id', $request->item_id)->where('user_id', $user_id)->first();
        if(!empty($is_purchased)){
            $response->status = $response::status_fail;
            $response->result = [];
            $response->messages = "Already Purchased!";
            return FacadeResponse::json($response);
        }

        $course = PaidCourse::where('id', $request->item_id)->first();

        $coupon_code = $request->coupon_code ? $request->coupon_code : '';
        $coupon_price = 0;
        $coupon_id = null;

        if($coupon_code){
            $coupon = PaidCourseCoupon::where('coupon_code', $coupon_code)->first();
            $coupon_price = $coupon->coupon_value;
            $coupon_id = $coupon->id;
        }

        $post_data = array();
        $post_data['total_amount'] = $course->sales_amount - $coupon_price; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid();

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $user->name;
        $post_data['cus_email'] = $user->email ? $user->email : '';
        $post_data['cus_add1'] = "Bangladesh";
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = $user->mobile_number;
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = $course->name;
        $post_data['product_category'] = "vitual";
        $post_data['product_profile'] = "vitual-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        $user_payment = UserAllPayment::where('transaction_id', $post_data['tran_id'])
            ->updateOrCreate([
                'user_id' => $user->id,
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'item_id' => $course->id,
                'item_name' => $course->name,
                'item_type' => "Paid Course",
                'coupon_id' => $coupon_id,
                'payable_amount' => $course->sales_amount,
                'paid_amount' => $post_data['total_amount'],
                'discount' => $coupon_price,
                'payment_status' => 'Full Paid',
                'transaction_status' => 'Complete',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency'],
                'card_type' => 'Coupon',
                'status' => 'Enrolled',
            ]);

        $user_payment_details = UserAllPaymentDetails::updateOrCreate([
            'payment_id' => $user_payment->id,
            'amount' => $course->sales_amount,
        ]);


        $checkParticipant = PaidCourseParticipant::where('paid_course_id', $request->item_id)->where('user_id', $user_id)->first();

        //$coupon_id = $order_detials->coupon_id ? $order_detials->coupon_id : 0;
        
        if($coupon_id){
            PaidCourseApplyCoupon::create([
                'user_id' => $user_id,
                'paid_course_id' => $request->item_id,
                'coupon_id' => $coupon_id,
                'applied_from' => 'mobile',
                'applied_status' => 'successful'
            ]);
        }

        if (empty($checkParticipant)) {
            PaidCourseParticipant::create([
                'user_id' => $user_id,
                'paid_course_id' => $request->item_id,
                'course_amount' => $course->sales_amount,
                'paid_amount' => $user_payment->paid_amount,
                'is_fully_paid' => true,
            ]);
        } else {
            $checkParticipant->update([
                'paid_amount' => $user_payment->paid_amount,
                'is_active' => true,
                'is_fully_paid' => true,
            ]);
        }

        $course->update([
            'number_of_students_enrolled' => $course->number_of_students_enrolled + 1,
        ]);

        $material_exam_list = PaidCourseMaterial::where('paid_course_id', $request->item_id)->get();
        foreach ($material_exam_list as $material) {
            $quizAccess = PaidCourseParticipantQuizAccess::where('user_id', $request->user_id)
                ->where('paid_course_material_id', $material->id)->first();

            if (empty($quizAccess)) {
                if($material->test_type == "RevisionTest"){
                    PaidCourseParticipantQuizAccess::create([
                        "paid_course_material_id" => $material->id,
                        "user_id" => $request->user_id,
                        "access_count" => 100,
                    ]);
                }
                if($material->test_type == "ModelTest"){
                    PaidCourseParticipantQuizAccess::create([
                        "paid_course_material_id" => $material->id,
                        "user_id" => $request->user_id,
                        "access_count" => 1,
                    ]);
                }
                if($material->test_type == "WeeklyTest"){
                    PaidCourseParticipantQuizAccess::create([
                        "paid_course_material_id" => $material->id,
                        "user_id" => $request->user_id,
                        "access_count" => 1,
                    ]);
                }

            } else {
                $quizAccess->update([
                    'access_count' => 100,
                ]);
            }
        }

        $response->status = $response::status_ok;
        $response->messages = "You have successfully enrolled this course";
        $response->result = "";
        return FacadeResponse::json($response);
    }

    public function purchaseCrashCourse(Request $request)
    {

        # Here you have to receive all the order data to initate the payment.
        # Lets your oder trnsaction informations are saving in a table called "orders"
        # In orders table order uniq identity is "transaction_id","status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $user = User::where('id', $request->user_id)->first();
        $course = CrashCourse::where('id', $request->item_id)->first();

        $post_data = array();
        $post_data['total_amount'] = $course->sales_amount . ''; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $user->name;
        $post_data['cus_email'] = $user->email ? $user->email : '';
        $post_data['cus_add1'] = "Bangladesh";
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = $user->mobile_number;
        $post_data['cus_fax'] = "";

        // # CUSTOMER INFORMATION
        // $post_data['cus_name'] = $user->name;
        // $post_data['cus_email'] = $user->email;
        // $post_data['cus_add1'] = $user->address;
        // $post_data['cus_add2'] = "";
        // $post_data['cus_city'] = "";
        // $post_data['cus_state'] = "";
        // $post_data['cus_postcode'] = "";
        // $post_data['cus_country'] = "Bangladesh";
        // $post_data['cus_phone'] = $user->mobile_number;
        // $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = $course->name;
        $post_data['product_category'] = "vitual";
        $post_data['product_profile'] = "vitual-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        #Before  going to initiate the payment order status need to update as Pending.
        $user_payment = UserAllPayment::where('transaction_id', $post_data['tran_id'])
            ->updateOrCreate([
                'user_id' => $user->id,
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'item_id' => $course->id,
                'item_name' => $course->name,
                'item_type' => "Crash Course",
                'payable_amount' => $post_data['total_amount'],
                'paid_amount' => $post_data['total_amount'],
                'transaction_status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency'],
                'status' => 'Pending',
            ]);

        $user_payment_details = UserAllPaymentDetails::updateOrCreate([
            'payment_id' => $user_payment->id,
            'amount' => $user_payment->paid_amount,
        ]);

        //return FacadeResponse::json("Response from Backend!");

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        return $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');
        // return FacadeResponse::json($payment_options);

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function purchaseLectureSheet(Request $request)
    {

        # Here you have to receive all the order data to initate the payment.
        # Lets your oder trnsaction informations are saving in a table called "orders"
        # In orders table order uniq identity is "transaction_id","status" field contain status of the transaction, "amount" is the order amount to be paid and "currency" is for storing Site Currency which will be checked with paid currency.

        $user = User::where('id', $request->user_id)->first();
        $lectureSheet = LectureSheet::where('id', $request->item_id)->first();

        $post_data = array();
        $post_data['total_amount'] = $lectureSheet->price . ''; # You cant not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid(); // tran_id must be unique

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $user->name;
        $post_data['cus_email'] = $user->email ? $user->email : '';
        $post_data['cus_add1'] = "Bangladesh";
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = $user->mobile_number;
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = $lectureSheet->name;
        $post_data['product_category'] = "vitual";
        $post_data['product_profile'] = "vitual-goods";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        #Before  going to initiate the payment order status need to update as Pending.
        $user_payment = UserAllPayment::where('transaction_id', $post_data['tran_id'])
            ->updateOrCreate([
                'user_id' => $user->id,
                'name' => $post_data['cus_name'],
                'email' => $post_data['cus_email'],
                'phone' => $post_data['cus_phone'],
                'item_id' => $lectureSheet->id,
                'item_name' => $lectureSheet->name,
                'item_type' => "Lecture Sheet",
                'payable_amount' => $post_data['total_amount'],
                'paid_amount' => $post_data['total_amount'],
                'transaction_status' => 'Pending',
                'address' => $post_data['cus_add1'],
                'transaction_id' => $post_data['tran_id'],
                'currency' => $post_data['currency'],
                'status' => 'Pending',
            ]);

        $user_payment_details = UserAllPaymentDetails::updateOrCreate([
            'payment_id' => $user_payment->id,
            'amount' => $user_payment->paid_amount,
        ]);

        //return FacadeResponse::json("Response from Backend!");

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        return $payment_options = $sslc->makePayment($post_data, 'checkout', 'json');
        // return FacadeResponse::json($payment_options);

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }

    }

    public function success(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        $sslc = new SslCommerzNotification();

        #Check order status in order tabel against the transaction id or order id.
        $order_detials = DB::table('user_all_payments')
            ->where('transaction_id', $tran_id)->first();

        if ($order_detials->transaction_status == 'Pending') {
            $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, $currency);

            if ($validation == true) 
            {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel. Here you need to update order status
                in order table as Processing or Complete.
                Here you can also sent sms or email for successfull transaction to customer
                 */
                $payment_status = '';
                $payment_amount = UserAllPaymentDetails::where('payment_id', $order_detials->id)->sum('amount');

                if ($order_detials->paid_amount == $payment_amount) {
                    $payment_status = "Full Paid";
                } else {
                    $payment_status = "Partially Paid";
                }

                $update_product = DB::table('user_all_payments')
                    ->where('transaction_id', $tran_id)
                    ->update([
                        'transaction_status' => 'Complete',
                        'payment_status' => $payment_status,
                        'card_type' => $request->input('card_type'),
                        'status' => 'Enrolled',
                    ]);

                /// deleting incomplete date
                $delete_product = UserAllPayment::where('user_id', $order_detials->user_id)
                    ->where('item_id', $order_detials->item_id)
                    ->where('transaction_status', '!=', 'Complete')
                    ->delete();

                if ($order_detials->item_type == "Crash Course") {

                    $course = CrashCourse::where('id', $order_detials->item_id)->first();
                    $checkParticipant = CrashCourseParticipant::where('crash_course_id', $order_detials->item_id)->where('user_id', $order_detials->user_id)->first();

                    if (empty($checkParticipant)) {
                        CrashCourseParticipant::create([
                            'user_id' => $order_detials->user_id,
                            'crash_course_id' => $order_detials->item_id,
                            'course_amount' => $order_detials->payable_amount,
                            'paid_amount' => $order_detials->paid_amount,
                            'is_fully_paid' => true,
                        ]);
                    } else {
                        $checkParticipant->update([
                            'paid_amount' => $order_detials->paid_amount,
                            'is_active' => true,
                            'is_fully_paid' => true,
                        ]);
                    }

                    $course->update([
                        'number_of_students_enrolled' => $course->number_of_students_enrolled + 1,
                    ]);

                    $course_subjects = CrashCourseSubject::where('crash_course_id', $order_detials->item_id)->get();
                    foreach ($course_subjects as $subject) {
                        $materialIds = CrashCourseMaterial::where('crash_course_subject_id', $subject->id)->where('type', '=', 'quiz')->pluck('id');
                        foreach ($materialIds as $materialId) {
                            $quizAccess = CrashCourseParticipantQuizAccess::where('user_id', $order_detials->user_id)
                                ->where('crash_course_material_id', $materialId)->first();

                            if (empty($quizAccess)) {
                                CrashCourseParticipantQuizAccess::create([
                                    'user_id' => $order_detials->user_id,
                                    'crash_course_material_id' => $materialId,
                                    'access_count' => 100,
                                ]);
                            } else {
                                $quizAccess->update([
                                    'access_count' => 100,
                                ]);
                            }
                        }
                    }
                    echo "<script> window.location = 'https://bacbonschool.com/crash-course/" . $order_detials->item_id . "';</script>";
                } else if ($order_detials->item_type == "Paid Course") {

                    $course = PaidCourse::where('id', $order_detials->item_id)->first();
                    $checkParticipant = PaidCourseParticipant::where('paid_course_id', $order_detials->item_id)->where('user_id', $order_detials->user_id)->first();

                    $coupon_id = $order_detials->coupon_id ? $order_detials->coupon_id : 0;
                    
                    if($coupon_id){
                        $coupon = PaidCourseCoupon::where('id', $coupon_id)->first();
                        $coupon_price = $coupon->coupon_value;
                        $coupon_id = $coupon->id;

                        $coupon_details = PaidCourseApplyCoupon::where('coupon_id', $coupon->id)
                            ->where('user_id', $order_detials->user_id)
                            ->where('paid_course_id', $order_detials->item_id)
                            ->where('applied_status', 'panding')
                            ->first();

                        if(!empty($coupon_details)){
                            PaidCourseApplyCoupon::where('id', $coupon_details->id)->update([
                                'applied_status' => 'successful'
                            ]);
                        }
                    }

                    if (empty($checkParticipant)) {
                        PaidCourseParticipant::create([
                            'user_id' => $order_detials->user_id,
                            'paid_course_id' => $order_detials->item_id,
                            'course_amount' => $course->sales_amount,
                            'paid_amount' => $order_detials->paid_amount,
                            'is_fully_paid' => true,
                        ]);
                    } else {
                        $checkParticipant->update([
                            'paid_amount' => $order_detials->paid_amount,
                            'is_active' => true,
                            'is_fully_paid' => true,
                        ]);
                    }

                    $course->update([
                        'number_of_students_enrolled' => $course->number_of_students_enrolled + 1,
                    ]);

                    $materialIds = PaidCourseMaterial::where('paid_course_id', $order_detials->item_id)->pluck('id');
                    foreach ($materialIds as $materialId) {
                        $quizAccess = PaidCourseParticipantQuizAccess::where('user_id', $order_detials->user_id)
                            ->where('paid_course_material_id', $materialId)->first();

                        if (empty($quizAccess)) {
                            PaidCourseParticipantQuizAccess::create([
                                'user_id' => $order_detials->user_id,
                                'paid_course_material_id' => $materialId,
                                'access_count' => 100,
                            ]);
                        } else {
                            $quizAccess->update([
                                'access_count' => 100,
                            ]);
                        }
                    }

                    //echo "<script> window.location = 'http://test.bacbonschool.com/paid-course-details/" . $order_detials->item_id . "';</script>";
                    echo "<script> window.location = 'https://bacbonschool.com/paid-course-details/" . $order_detials->item_id . "';</script>";
                    //echo "<script> window.location = 'http://localhost:4200/paid-course-details/" . $order_detials->item_id . "';</script>";
                } else if ($order_detials->item_type == "Lecture Sheet") {

                    $lectureSheet = LectureSheet::where('id', $order_detials->item_id)->first();
                    $lectureSheet->update([
                        'number_of_puchased' => $lectureSheet->number_of_puchased + 1,
                    ]);
                    //  echo "<br >Transaction is successfully Completed";
                    echo "<script> window.location = 'https://bacbonschool.com/lecture-sheet/" . $order_detials->item_id . "';</script>";
                }else if ($order_detials->item_type == "Lecture Videos") {

                    //$lectureVideos = LectureSheet::where('id', $order_detials->item_id)->first();
                    $lectureVideos = LectureVideoParticipant::where('user_id', $order_detials->user_id)
                        ->where('course_subject_id', $order_detials->item_id)
                        ->where('payment_status', 'pending')
                        ->first();

                    $lectureVideos->update([
                        'payment_status' => "completed",
                    ]);

                    //  echo "<br >Transaction is successfully Completed"; 
                    echo "<script> window.location = 'https://bacbonschool.com/lesson-list/" . $lectureVideos->course_id . "/" . $lectureVideos->subject_id . "';</script>";
                    //echo "<script> window.location = 'http://localhost:4200/lesson-list/" . $lectureVideos->course_id . "/" . $lectureVideos->subject_id . "';</script>";
                }

            } else {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel and Transation validation failed.
                Here you need to update order status as Failed in order table.
                 */
                $update_product = DB::table('user_all_payments')
                    ->where('transaction_id', $tran_id)
                    ->update(['transaction_status' => 'Failed']);
                echo "validation Fail";
            }
        } else if ($order_detials->transaction_status == 'Processing' || $order_detials->transaction_status == 'Complete') {
            /*
            That means through IPN Order status already updated. Now you can just show the customer that transaction is completed. No need to udate database.
             */
            //  echo "Transaction is successfully Completed";

            if ($order_detials->item_type == "Crash Course") {
                echo "<script>window.location = 'https://bacbonschool.com/crash-course/" . $order_detials->item_id . "';</script>";
            } else if ($order_detials->item_type == "Paid Course") {
                echo "<script>window.location = 'https://bacbonschool.com/paid-course-details/" . $order_detials->item_id . "';</script>";
                //echo "<script> window.location = 'http://localhost:4200/paid-course-details/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Lecture Videos") {
                $lectureVideos = LectureVideoParticipant::where('user_id', $order_detials->user_id)
                        ->where('course_subject_id', $order_detials->item_id)
                        ->where('payment_status', 'pending')
                        ->first();
                echo "<script> window.location = 'https://bacbonschool.com/lesson-list/" . $lectureVideos->course_id . "/" . $lectureVideos->subject_id . "';</script>";
            } else {
                echo "<script>window.location = 'https://bacbonschool.com/lecture-sheet/" . $order_detials->item_id . "';</script>";
            }

        } else {
            #That means something wrong happened. You can redirect customer to your product page.
            echo "Invalid Transaction";
        }

    }

    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_detials = DB::table('user_all_payments')
            ->where('transaction_id', $tran_id)->first();

        if ($order_detials->transaction_status == 'Pending') {
            $update_product = DB::table('user_all_payments')
                ->where('transaction_id', $tran_id)
                ->update(['transaction_status' => 'Failed']);
            // echo "Transaction is Falied";
            if ($order_detials->item_type == "Crash Course") {
                echo "<script>window.location = 'https://bacbonschool.com/crash-course/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Paid Course") {
                //echo "<script> window.location = 'http://localhost:4200/paid-course-details/" . $order_detials->item_id . "';</script>";
                echo "<script>window.location = 'https://bacbonschool.com/paid-course-details/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Lecture Videos") {
                $lectureVideos = LectureVideoParticipant::where('user_id', $order_detials->user_id)
                        ->where('course_subject_id', $order_detials->item_id)
                        ->where('payment_status', 'pending')
                        ->first();
                echo "<script> window.location = 'https://bacbonschool.com/lesson-list/" . $lectureVideos->course_id . "/" . $lectureVideos->subject_id . "';</script>";
            }else {
                echo "<script>window.location = 'https://bacbonschool.com/lecture-sheet/" . $order_detials->item_id . "';</script>";
            }
        } else if ($order_detials->transaction_status == 'Processing' || $order_detials->transaction_status == 'Complete') {
            // echo "Transaction is already Successful";
            if ($order_detials->item_type == "Crash Course") {
                echo "<script>window.location = 'https://bacbonschool.com/crash-course/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Paid Course") {
                //echo "<script> window.location = 'http://localhost:4200/paid-course-details/" . $order_detials->item_id . "';</script>";
                echo "<script>window.location = 'https://bacbonschool.com/paid-course-details/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Lecture Videos") {
                $lectureVideos = LectureVideoParticipant::where('user_id', $order_detials->user_id)
                        ->where('course_subject_id', $order_detials->item_id)
                        ->where('payment_status', 'pending')
                        ->first();
                echo "<script> window.location = 'https://bacbonschool.com/lesson-list/" . $lectureVideos->course_id . "/" . $lectureVideos->subject_id . "';</script>";
            } else {
                echo "<script>window.location = 'https://bacbonschool.com/lecture-sheet/" . $order_detials->item_id . "';</script>";
            }
        } else {
            // echo "Transaction is Invalid";
            if ($order_detials->item_type == "Crash Course") {
                echo "<script>window.location = 'https://bacbonschool.com/crash-course/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Paid Course") {
                //echo "<script> window.location = 'http://localhost:4200/paid-course-details/" . $order_detials->item_id . "';</script>";
                echo "<script>window.location = 'https://bacbonschool.com/paid-course-details/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Lecture Videos") {
                $lectureVideos = LectureVideoParticipant::where('user_id', $order_detials->user_id)
                        ->where('course_subject_id', $order_detials->item_id)
                        ->where('payment_status', 'pending')
                        ->first();
                echo "<script> window.location = 'https://bacbonschool.com/lesson-list/" . $lectureVideos->course_id . "/" . $lectureVideos->subject_id . "';</script>";
            }
            else {
                echo "<script>window.location = 'https://bacbonschool.com/lecture-sheet/" . $order_detials->item_id . "';</script>";
            }
        }

    }

    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_detials = DB::table('user_all_payments')
            ->where('transaction_id', $tran_id)->first();

        if ($order_detials->transaction_status == 'Pending') {
            $update_product = DB::table('user_all_payments')
                ->where('transaction_id', $tran_id)
                ->update(['transaction_status' => 'Canceled']);
            //  echo "Transaction is Cancel";
            if ($order_detials->item_type == "Crash Course") {
                echo "<script>window.location = 'https://bacbonschool.com/crash-course/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Paid Course") {
                //echo "<script> window.location = 'http://localhost:4200/paid-course-details/" . $order_detials->item_id . "';</script>";
                echo "<script>window.location = 'https://bacbonschool.com/paid-course-details/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Lecture Videos") {
                $lectureVideos = LectureVideoParticipant::where('user_id', $order_detials->user_id)
                        ->where('course_subject_id', $order_detials->item_id)
                        ->where('payment_status', 'pending')
                        ->first();
                echo "<script> window.location = 'https://bacbonschool.com/lesson-list/" . $lectureVideos->course_id . "/" . $lectureVideos->subject_id . "';</script>";
            } else {
                echo "<script>window.location = 'https://bacbonschool.com/lecture-sheet/" . $order_detials->item_id . "';</script>";
            }
        } else if ($order_detials->transaction_status == 'Processing' || $order_detials->transaction_status == 'Complete') {
            //  echo "Transaction is already Successful";
            if ($order_detials->item_type == "Crash Course") {
                echo "<script>window.location = 'https://bacbonschool.com/crash-course/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Paid Course") {
                //echo "<script> window.location = 'http://localhost:4200/paid-course-details/" . $order_detials->item_id . "';</script>";
                echo "<script>window.location = 'https://bacbonschool.com/paid-course-details/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Lecture Videos") {
                $lectureVideos = LectureVideoParticipant::where('user_id', $order_detials->user_id)
                        ->where('course_subject_id', $order_detials->item_id)
                        ->where('payment_status', 'pending')
                        ->first();
                echo "<script> window.location = 'https://bacbonschool.com/lesson-list/" . $lectureVideos->course_id . "/" . $lectureVideos->subject_id . "';</script>";
            } else {
                echo "<script>window.location = 'https://bacbonschool.com/lecture-sheet/" . $order_detials->item_id . "';</script>";
            }
        } else {
            // echo "Transaction is Invalid";
            if ($order_detials->item_type == "Crash Course") {
                echo "<script>window.location = 'https://bacbonschool.com/crash-course/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Paid Course") {
                //echo "<script> window.location = 'http://localhost:4200/paid-course-details/" . $order_detials->item_id . "';</script>";
                echo "<script>window.location = 'https://bacbonschool.com/paid-course-details/" . $order_detials->item_id . "';</script>";
            }
            else if ($order_detials->item_type == "Lecture Videos") {
                $lectureVideos = LectureVideoParticipant::where('user_id', $order_detials->user_id)
                        ->where('course_subject_id', $order_detials->item_id)
                        ->where('payment_status', 'pending')
                        ->first();
                echo "<script> window.location = 'https://bacbonschool.com/lesson-list/" . $lectureVideos->course_id . "/" . $lectureVideos->subject_id . "';</script>";
            } else {
                echo "<script>window.location = 'https://bacbonschool.com/lecture-sheet/" . $order_detials->item_id . "';</script>";
            }
        }

    }

    public function ipn(Request $request)
    {
        #Received all the payement information from the gateway
        if ($request->input('tran_id')) #Check transation id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order tabel against the transaction id or order id.
            $order_details = DB::table('user_all_payments')
                ->where('transaction_id', $tran_id)
                ->select('transaction_id', 'transaction_status', 'currency', 'payable_amount', 'paid_amount')->first();

            if ($order_details->transaction_status == 'Pending') {
                $sslc = new SslCommerzNotification();
                $validation = $sslc->orderValidate($request->all(), $tran_id, $order_details->paid_amount, $order_details->currency);
                if ($validation == true) {
                    /*
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successful transaction to customer
                     */
                    $update_product = DB::table('user_all_payments')
                        ->where('transaction_id', $tran_id)
                        ->update(['transaction_status' => 'Complete']);

                    echo "Transaction is successfully Completed";
                } else {
                    /*
                    That means IPN worked, but Transation validation failed.
                    Here you need to update order status as Failed in order table.
                     */
                    $update_product = DB::table('user_all_payments')
                        ->where('transaction_id', $tran_id)
                        ->update(['transaction_status' => 'Failed']);

                    echo "validation Fail";
                }

            } else if ($order_details->transaction_status == 'Processing' || $order_details->transaction_status == 'Complete') {

                #That means Order status already updated. No need to udate database.

                echo "Transaction is already successfully Completed";
            } else {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
            }
        } else {
            echo "Invalid Data";
        }
    }

}
