<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\EarningTransaction;
use App\Models\Enrollment;
use App\Models\InstructorPayoutSetting;
use App\Models\PayoutGlobalSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorFinancesController extends Controller
{
    /**
     * بيانات الإدارة المالية للمدرس (يتطلب مصادقة + دور مدرس)
     * GET /api/instructor/finances
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('instructor')) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $userId = $user->id;
        $settings = InstructorPayoutSetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'commission_rate' => PayoutGlobalSetting::getDefaultCommissionRate(),
                'admin_fee_rate' => PayoutGlobalSetting::getAdminFeeRate(),
                'minimum_payout' => PayoutGlobalSetting::getMinimumPayout(),
                'payment_method' => 'bank_transfer',
            ]
        );

        $transactions = EarningTransaction::where('user_id', $userId);
        $totalEarnings = (clone $transactions)->sum('commission_amount');
        $availableBalance = (clone $transactions)->where('status', 'available')->sum('commission_amount');
        $pendingPayout = (clone $transactions)->where('status', 'pending_payout')->sum('commission_amount');
        $paidOut = (clone $transactions)->where('status', 'paid_out')->sum('commission_amount');

        if ($totalEarnings == 0) {
            $courseIds = Course::where('user_id', $userId)->pluck('id');
            $enrollments = Enrollment::whereIn('course_id', $courseIds)->get();
            foreach ($enrollments as $enrollment) {
                $commission = ($enrollment->price_paid * $settings->commission_rate) / 100;
                $totalEarnings += $commission;
                $availableBalance += $commission;
            }
        }

        $courses = Course::where('user_id', $userId)->with('enrollments')->get();
        $courseEarnings = $courses->map(function (Course $course) use ($settings) {
            $enrollments = $course->enrollments;
            $totalSales = $enrollments->sum('price_paid');
            $commission = ($totalSales * $settings->commission_rate) / 100;

            return [
                'id' => $course->id,
                'title' => $course->title,
                'students' => $enrollments->count(),
                'total_sales' => round((float) $totalSales, 2),
                'commission_rate' => (float) $settings->commission_rate,
                'commission_amount' => round($commission, 2),
            ];
        })->values()->all();

        return response()->json([
            'stats' => [
                'total_earnings' => round($totalEarnings, 2),
                'available_balance' => round($availableBalance, 2),
                'pending_payout' => round($pendingPayout, 2),
                'paid_out' => round($paidOut, 2),
                'minimum_payout' => (float) $settings->minimum_payout,
                'commission_rate' => (float) $settings->commission_rate,
            ],
            'courses' => $courseEarnings,
        ]);
    }
}
