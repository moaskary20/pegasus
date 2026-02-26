<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentSubscription;
use App\Models\SubscriptionPlan;
use App\Models\Voucher;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionsController extends Controller
{
    /**
     * قائمة خطط الاشتراك المتاحة
     */
    public function plans(Request $request): JsonResponse
    {
        $plans = SubscriptionPlan::query()
            ->orderBy('price')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'type' => $p->type,
                'type_label' => $p->getTypeArabic(),
                'price' => round((float) $p->price, 2),
                'duration_days' => (int) $p->duration_days,
                'max_lessons' => $p->max_lessons,
            ]);

        return response()->json([
            'plans' => $plans,
        ]);
    }

    /**
     * اشتراكات المستخدم الحالية (فعالة + منتهية)
     */
    public function my(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $subscriptions = StudentSubscription::query()
            ->where('user_id', $user->id)
            ->with('subscriptionPlan')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'plan_id' => $s->subscription_plan_id,
                'plan_name' => $s->subscriptionPlan?->name ?? '',
                'plan_type' => $s->subscriptionPlan?->type ?? 'once',
                'plan_type_label' => $s->subscriptionPlan?->getTypeArabic() ?? '',
                'start_date' => $s->start_date?->format('Y-m-d'),
                'end_date' => $s->end_date?->format('Y-m-d'),
                'status' => $s->status,
                'is_active' => $s->isActive(),
                'final_price' => round((float) $s->final_price, 2),
            ]);

        return response()->json([
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * الاشتراك في خطة
     * plan_id, payment_gateway (kashier|manual), voucher_code?, manual_receipt (مطلوب عند manual)
     */
    public function subscribe(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $planId = (int) $request->input('plan_id', 0);
        $plan = SubscriptionPlan::find($planId);
        if (!$plan) {
            return response()->json([
                'message' => 'خطة الاشتراك غير موجودة.',
                'errors' => ['plan_id' => ['اختر خطة صحيحة.']],
            ], 422);
        }

        $gateway = (string) $request->input('payment_gateway', '');
        $allowed = ['kashier', 'manual'];
        if (!in_array($gateway, $allowed, true)) {
            return response()->json([
                'message' => 'يرجى اختيار طريقة دفع صحيحة.',
                'errors' => ['payment_gateway' => ['طريقة الدفع غير صحيحة.']],
            ], 422);
        }

        if ($gateway === 'manual') {
            $request->validate([
                'manual_receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ], [
                'manual_receipt.required' => 'يرجى إرفاق إيصال الدفع.',
                'manual_receipt.mimes' => 'صيغة الإيصال يجب أن تكون JPG/PNG/PDF.',
                'manual_receipt.max' => 'حجم الإيصال كبير جداً (الحد 5MB).',
            ]);
        }

        $voucherCode = strtoupper(trim((string) $request->input('voucher_code', '')));
        $voucherId = null;
        if ($voucherCode !== '') {
            $voucher = Voucher::where('code', $voucherCode)->first();
            if ($voucher && $voucher->isValid()) {
                $voucherId = $voucher->id;
            }
        }

        try {
            DB::beginTransaction();

            $receiptPath = null;
            $receiptName = null;
            if ($gateway === 'manual' && $request->hasFile('manual_receipt')) {
                $file = $request->file('manual_receipt');
                $receiptName = $file?->getClientOriginalName();
                $receiptPath = $file?->storePublicly('subscription-receipts', 'public');
            }

            $service = app(SubscriptionService::class);
            $subscription = $service->createSubscription($user->id, $plan->id, $voucherId);

            if ($receiptPath) {
                $subscription->update([
                    'manual_receipt_path' => $receiptPath,
                    'manual_receipt_original_name' => $receiptName,
                    'manual_receipt_uploaded_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'تم الاشتراك بنجاح',
                'subscription_id' => $subscription->id,
                'end_date' => $subscription->end_date?->format('Y-m-d'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Subscription API failed', [
                'user_id' => $user->id,
                'plan_id' => $planId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => config('app.debug')
                    ? ('حدث خطأ: ' . $e->getMessage())
                    : 'حدث خطأ أثناء إنشاء الاشتراك.',
            ], 500);
        }
    }
}
