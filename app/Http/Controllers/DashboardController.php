<?php

namespace App\Http\Controllers;
use Auth;
use Excel;
use Exception;
use App\User;
use Carbon\Carbon;
use App\PaidCourse;
use App\PaidCourseParticipant;
use App\UserAllPayment;
use App\PaidCourseCoupon;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Response as FacadeResponse;

class DashboardController extends Controller
{
    public function dashboardCourseAnalytics(Request $request){

        $course = PaidCourse::select('id', 'name', 'name_bn','is_lc_enable', 'sales_amount', 'is_active')
        ->where('is_active', true)
        ->orderby('id', 'desc')
        ->get();

        foreach ($course as $item) {
            $item->no_of_student = PaidCourseParticipant::where('paid_course_id', $item->id)->get()->count();
        }

        $payments = UserAllPayment::select(
            "user_all_payments.*",
            'paid_courses.name as course_name',
            'paid_courses.name_bn as course_name_bn',
            'paid_courses.sales_amount',
            'paid_course_coupons.coupon_code',
            'paid_course_coupons.coupon_value')
        ->where('user_all_payments.item_type', "Paid Course")
        ->where('user_all_payments.transaction_status', "Complete")
        ->leftJoin('paid_courses', 'paid_courses.id', 'user_all_payments.item_id')
        ->leftJoin('paid_course_coupons', 'paid_course_coupons.id', 'user_all_payments.coupon_id')
        ->orderby('user_all_payments.id', 'desc')
        ->limit(5)
        ->get();

        $analytics = ['course' => $course, 'payment' => $payments];

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "Dashboard Analytics";
        $response->result = $analytics;
        return response()->json($response);
    }

    public function paymentAnalytics(Request $request)
    {
        $now = Carbon::now();
        $sixMonthsAgo = $now->copy()->subMonths(8);
        $months = collect();
        for ($i = 0; $i < 12; $i++) {
            $months->push($sixMonthsAgo->copy()->addMonths($i)->format('Y-m'));
        }

        $monthlyPurchases = UserAllPayment::select(
            DB::raw('SUM(payable_amount) as total_amount'),
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month_year')
        )
        ->where('created_at', '>=', $sixMonthsAgo->startOfMonth())
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('month_year', 'asc')
        ->get()
        ->pluck('total_amount', 'month_year');

        $finalData = $months->mapWithKeys(function ($month) use ($monthlyPurchases) {
            return [$month => $monthlyPurchases->get($month, 0)];
        });

        $analytics = ['monthly_purchases' => $finalData];

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "Dashboard Analytics";
        $response->result = $analytics;
        return response()->json($response);
    }

}
