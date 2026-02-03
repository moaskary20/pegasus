<?php

namespace App\Services;

use App\Models\StudentSubscription;
use App\Models\DailyLessonAccess;
use App\Models\VoucherUsage;
use App\Models\Voucher;
use App\Models\SubscriptionPlan;

class SubscriptionService
{
    public function createSubscription(
        int $userId,
        int $subscriptionPlanId,
        ?int $voucherId = null
    ): StudentSubscription {
        $plan = SubscriptionPlan::findOrFail($subscriptionPlanId);
        
        $finalPrice = $plan->price;
        
        if ($voucherId) {
            $discount = $this->getVoucherDiscount($voucherId, $plan->price);
            $finalPrice -= $discount;
        }

        $subscription = StudentSubscription::create([
            'user_id' => $userId,
            'subscription_plan_id' => $subscriptionPlanId,
            'voucher_id' => $voucherId,
            'start_date' => now(),
            'end_date' => now()->addDays($plan->duration_days),
            'status' => 'active',
            'final_price' => max(0, $finalPrice),
        ]);

        if ($voucherId) {
            $this->useVoucher($userId, $voucherId);
        }

        return $subscription;
    }

    public function grantLessonAccess(int $userId, int $lessonId): DailyLessonAccess
    {
        return DailyLessonAccess::create([
            'user_id' => $userId,
            'lesson_id' => $lessonId,
            'purchased_at' => now(),
            'expires_at' => now()->addDay(),
        ]);
    }

    public function useVoucher(int $userId, int $voucherId): void
    {
        VoucherUsage::create([
            'user_id' => $userId,
            'voucher_id' => $voucherId,
            'used_at' => now(),
        ]);

        Voucher::findOrFail($voucherId)->increment('usage_count');
    }

    public function checkAccess(int $userId, int $lessonId): bool
    {
        $hasSubscription = StudentSubscription::where([
            ['user_id', $userId],
            ['status', 'active'],
        ])
            ->where('end_date', '>=', now())
            ->exists();

        if ($hasSubscription) {
            return true;
        }

        $hasAccess = DailyLessonAccess::where([
            ['user_id', $userId],
            ['lesson_id', $lessonId],
        ])
            ->where('expires_at', '>=', now())
            ->exists();

        return $hasAccess;
    }

    public function updateExpiredSubscriptions(): int
    {
        return StudentSubscription::where('status', 'active')
            ->where('end_date', '<', now())
            ->update(['status' => 'expired']);
    }

    public function getVoucherDiscount(int $voucherId, float $price): float
    {
        $voucher = Voucher::findOrFail($voucherId);

        if (!$voucher->isValid()) {
            return 0;
        }

        if ($voucher->discount_type === 'percentage') {
            return ($price * $voucher->discount_percentage) / 100;
        }

        return $voucher->discount_amount ?? 0;
    }

    public function getFinalPrice(float $originalPrice, ?int $voucherId = null): float
    {
        $discount = 0;
        
        if ($voucherId) {
            $discount = $this->getVoucherDiscount($voucherId, $originalPrice);
        }

        return max(0, $originalPrice - $discount);
    }
}
