<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PaymentGatewaysService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (! auth()->check()) {
            session(['url.intended' => route('site.checkout')]);

            return redirect(route('site.auth'));
        }

        $courseCartIds = session('cart', []);
        $courseCartIds = is_array($courseCartIds) ? array_values(array_unique(array_map('intval', $courseCartIds))) : [];

        $courseCart = collect();
        if (count($courseCartIds) > 0) {
            $courseCart = Course::query()
                ->published()
                ->whereIn('id', $courseCartIds)
                ->with(['instructor', 'category', 'subCategory'])
                ->get();
        }

        if ($courseCart->count() === 0) {
            return redirect()->route('site.cart')->with('notice', ['type' => 'error', 'message' => 'السلة فارغة. أضف دورة أولاً.']);
        }

        $couponCode = (string) session('cart_coupon', '');
        $couponCode = strtoupper(trim($couponCode));
        $appliedCoupon = null;
        $discount = 0.0;
        $subtotal = (float) $courseCart->sum(fn ($c) => (float) ($c->offer_price ?? $c->price ?? 0));

        if ($couponCode !== '') {
            $appliedCoupon = Coupon::query()->where('code', $couponCode)->first();
            if ($appliedCoupon && $appliedCoupon->isValid()) {
                $discount = (float) $appliedCoupon->calculateDiscount($subtotal);
            } else {
                session()->forget('cart_coupon');
                $appliedCoupon = null;
                $discount = 0.0;
                $couponCode = '';
            }
        }

        $total = max(0, $subtotal - $discount);
        $paymentMethods = PaymentGatewaysService::getEnabledPaymentMethods();

        if (empty($paymentMethods)) {
            return redirect()->route('site.cart')->with('notice', ['type' => 'error', 'message' => 'لا توجد طرق دفع متاحة حالياً.']);
        }

        return view('checkout.index', [
            'courseCart' => $courseCart,
            'subtotal' => $subtotal,
            'couponCode' => $couponCode,
            'discount' => $discount,
            'total' => $total,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        if (! auth()->check()) {
            session(['url.intended' => route('site.checkout')]);

            return redirect(route('site.auth'));
        }

        $gateway = (string) $request->input('payment_gateway', '');
        $paymentMethods = PaymentGatewaysService::getEnabledPaymentMethods();
        $allowed = array_keys($paymentMethods);

        if (! in_array($gateway, $allowed, true)) {
            return redirect()->route('site.checkout')->with('notice', ['type' => 'error', 'message' => 'يرجى اختيار طريقة دفع صحيحة.']);
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

        $courseCartIds = session('cart', []);
        $courseCartIds = is_array($courseCartIds) ? array_values(array_unique(array_map('intval', $courseCartIds))) : [];

        $courses = Course::query()
            ->published()
            ->whereIn('id', $courseCartIds)
            ->get();

        if ($courses->count() === 0) {
            return redirect()->route('site.cart')->with('notice', ['type' => 'error', 'message' => 'السلة فارغة.']);
        }

        $subtotal = (float) $courses->sum(fn ($c) => (float) ($c->offer_price ?? $c->price ?? 0));

        $couponCode = (string) session('cart_coupon', '');
        $couponCode = strtoupper(trim($couponCode));
        $coupon = null;
        $discount = 0.0;

        if ($couponCode !== '') {
            $coupon = Coupon::query()->where('code', $couponCode)->first();
            if ($coupon && $coupon->isValid()) {
                $discount = (float) $coupon->calculateDiscount($subtotal);
            } else {
                $coupon = null;
                $discount = 0.0;
                $couponCode = '';
                session()->forget('cart_coupon');
            }
        }

        $total = max(0, $subtotal - $discount);

        try {
            DB::beginTransaction();

            $receiptPath = null;
            $receiptName = null;
            if ($gateway === 'manual' && $request->hasFile('manual_receipt')) {
                $file = $request->file('manual_receipt');
                $receiptName = $file?->getClientOriginalName();
                $receiptPath = $file?->storePublicly('manual-receipts', 'public');
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'coupon_code' => $couponCode ?: null,
                'payment_gateway' => $gateway,
                'status' => 'pending',
                'manual_receipt_path' => $receiptPath,
                'manual_receipt_original_name' => $receiptName,
                'manual_receipt_uploaded_at' => $receiptPath ? now() : null,
            ]);

            foreach ($courses as $c) {
                $price = (float) ($c->offer_price ?? $c->price ?? 0);
                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $c->id,
                    'price' => $price,
                ]);
            }

            // الاشتراكات تُنشأ لاحقاً: للدفع اليدوي عند تأكيد الأدمن، للبوابات الإلكترونية عند تأكيد الدفع عبر callback

            if ($gateway === 'manual') {
                if ($coupon) {
                    $coupon->increment('used_count');
                }
                DB::commit();
                session()->forget('cart');
                session()->forget('cart_coupon');

                return redirect()->route('site.checkout.success', ['order' => $order->id]);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            DB::commit();

            // لا نُفرغ السلة هنا؛ تُفرغ عند تأكيد الدفع في الـ callback
            $paymentUrl = PaymentGatewaysService::getPaymentRedirectUrl($order);

            if ($paymentUrl) {
                return redirect()->away($paymentUrl);
            }

            // فشل الحصول على رابط الدفع - حذف الطلب والرجوع مع رسالة خطأ
            if ($coupon) {
                $coupon->decrement('used_count');
            }
            $order->items()->delete();
            $order->delete();

            return redirect()->route('site.checkout')->with('notice', [
                'type' => 'error',
                'message' => 'لم يتم إعداد بيانات بوابة الدفع في لوحة التحكم. يرجى تفعيل البوابة وإدخال بيانات التاجر، أو اختيار طريقة دفع يدوي.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Checkout create order failed', [
                'user_id' => auth()->id(),
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            $message = config('app.debug')
                ? ('حدث خطأ أثناء إنشاء الطلب: '.$e->getMessage())
                : 'حدث خطأ أثناء إنشاء الطلب. حاول مرة أخرى.';

            return redirect()->route('site.checkout')->with('notice', ['type' => 'error', 'message' => $message]);
        }
    }

    public function success(Order $order): View|RedirectResponse
    {
        if (! auth()->check()) {
            session(['url.intended' => route('site.checkout.success', ['order' => $order->id])]);

            return redirect(route('site.auth'));
        }

        if ((int) $order->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $order->load(['items.course']);

        return view('checkout.success', [
            'order' => $order,
        ]);
    }
}
