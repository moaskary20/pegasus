<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreCart;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
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

        $user = auth()->user();
        $this->mergeSessionStoreCartToUser($user);

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

        $storeCart = StoreCart::with(['product', 'variant'])->where('user_id', $user->id)->get();

        $coursesCount = $courseCart->count();
        $storeCount = $storeCart->count();
        if ($coursesCount === 0 && $storeCount === 0) {
            return redirect()->route('site.cart')->with('notice', ['type' => 'error', 'message' => 'السلة فارغة. أضف دورة أو منتج من المتجر أولاً.']);
        }

        $couponCode = (string) session('cart_coupon', '');
        $couponCode = strtoupper(trim($couponCode));
        $appliedCoupon = null;
        $discount = 0.0;
        $coursesSubtotal = (float) $courseCart->sum(fn ($c) => (float) ($c->offer_price ?? $c->price ?? 0));
        $storeSubtotal = (float) $storeCart->sum(fn ($i) => (float) ($i->total ?? 0));

        if ($couponCode !== '' && $coursesSubtotal > 0) {
            $appliedCoupon = Coupon::query()->where('code', $couponCode)->first();
            if ($appliedCoupon && $appliedCoupon->isValid()) {
                $discount = (float) $appliedCoupon->calculateDiscount($coursesSubtotal);
            } else {
                session()->forget('cart_coupon');
                $appliedCoupon = null;
                $discount = 0.0;
                $couponCode = '';
            }
        }

        $total = max(0, $coursesSubtotal - $discount) + $storeSubtotal;
        $paymentMethods = PaymentGatewaysService::getEnabledPaymentMethods();

        if (empty($paymentMethods)) {
            return redirect()->route('site.cart')->with('notice', ['type' => 'error', 'message' => 'لا توجد طرق دفع متاحة حالياً.']);
        }

        return view('checkout.index', [
            'courseCart' => $courseCart,
            'storeCart' => $storeCart,
            'coursesSubtotal' => $coursesSubtotal,
            'storeSubtotal' => $storeSubtotal,
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

        $user = auth()->user();
        $this->mergeSessionStoreCartToUser($user);

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

        $storeCart = StoreCart::with(['product', 'variant'])->where('user_id', $user->id)->get();

        if ($courses->count() === 0 && $storeCart->count() === 0) {
            return redirect()->route('site.cart')->with('notice', ['type' => 'error', 'message' => 'السلة فارغة.']);
        }

        $coursesSubtotal = (float) $courses->sum(fn ($c) => (float) ($c->offer_price ?? $c->price ?? 0));
        $storeSubtotal = (float) $storeCart->sum(fn ($i) => (float) ($i->total ?? 0));

        $couponCode = (string) session('cart_coupon', '');
        $couponCode = strtoupper(trim($couponCode));
        $coupon = null;
        $discount = 0.0;

        if ($couponCode !== '' && $coursesSubtotal > 0) {
            $coupon = Coupon::query()->where('code', $couponCode)->first();
            if ($coupon && $coupon->isValid()) {
                $discount = (float) $coupon->calculateDiscount($coursesSubtotal);
            } else {
                $coupon = null;
                $discount = 0.0;
                $couponCode = '';
                session()->forget('cart_coupon');
            }
        }

        $total = max(0, $coursesSubtotal - $discount) + $storeSubtotal;

        try {
            DB::beginTransaction();

            $receiptPath = null;
            $receiptName = null;
            if ($gateway === 'manual' && $request->hasFile('manual_receipt')) {
                $file = $request->file('manual_receipt');
                $receiptName = $file?->getClientOriginalName();
                $receiptPath = $file?->storePublicly('manual-receipts', 'public');
            }

            $storeOrder = null;
            if ($storeCart->count() > 0) {
                $storeOrder = StoreOrder::create([
                    'user_id' => $user->id,
                    'status' => StoreOrder::STATUS_PENDING,
                    'payment_status' => StoreOrder::PAYMENT_PENDING,
                    'payment_method' => $gateway,
                    'customer_name' => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone' => $user->phone ?? 'سيتم التواصل',
                    'shipping_address' => 'سيتم التواصل',
                    'shipping_city' => $user->city ?? 'غير محدد',
                    'shipping_state' => null,
                    'shipping_country' => 'مصر',
                    'shipping_postal_code' => null,
                    'subtotal' => $storeSubtotal,
                    'shipping_cost' => 0,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total' => $storeSubtotal,
                ]);

                foreach ($storeCart as $item) {
                    $product = $item->product;
                    if (! $product) {
                        continue;
                    }
                    $unitPrice = (float) $item->unit_price;
                    $qty = (int) $item->quantity;
                    $itemTotal = $unitPrice * $qty;

                    StoreOrderItem::create([
                        'store_order_id' => $storeOrder->id,
                        'product_id' => $product->id,
                        'variant_id' => $item->variant_id,
                        'product_name' => $product->name,
                        'variant_name' => $item->variant?->name,
                        'sku' => $product->sku,
                        'price' => $unitPrice,
                        'quantity' => $qty,
                        'total' => $itemTotal,
                    ]);

                    if ($product->track_quantity) {
                        $product->decrement('quantity', $qty);
                    }
                }

                StoreCart::where('user_id', $user->id)->delete();
            }

            $coursesSubtotalForOrder = $coursesSubtotal;
            $coursesDiscountForOrder = $discount;
            $orderTotal = max(0, $coursesSubtotalForOrder - $coursesDiscountForOrder) + $storeSubtotal;

            $order = Order::create([
                'user_id' => $user->id,
                'store_order_id' => $storeOrder?->id,
                'subtotal' => $coursesSubtotalForOrder + $storeSubtotal,
                'discount' => $coursesDiscountForOrder,
                'total' => $orderTotal,
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

            if ($gateway === 'manual') {
                if ($coupon) {
                    $coupon->increment('used_count');
                }
                if ($storeOrder) {
                    $storeOrder->markAsPaid();
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

            $paymentUrl = PaymentGatewaysService::getPaymentRedirectUrl($order);

            if ($paymentUrl) {
                return redirect()->away($paymentUrl);
            }

            if ($coupon) {
                $coupon->decrement('used_count');
            }
            if ($storeOrder) {
                foreach ($storeOrder->items as $soItem) {
                    if ($soItem->product && $soItem->product->track_quantity) {
                        $soItem->product->increment('quantity', $soItem->quantity);
                    }
                }
                $storeOrder->delete();
            }
            $order->items()->delete();
            $order->delete();

            $reason = PaymentGatewaysService::$lastFailureReason ?? '';
            $message = match (true) {
                str_starts_with($reason, 'zero_amount') => 'المبلغ الإجمالي صفر. يرجى التحقق من السلة أو الكوبون.',
                str_starts_with($reason, 'missing_credentials') => 'لم يتم إعداد بيانات بوابة الدفع في لوحة التحكم. يرجى تفعيل كاشير وإدخال Merchant ID ومفتاح التشفير، أو اختيار طريقة دفع يدوي.',
                str_starts_with($reason, 'exception:') => 'حدث خطأ تقني أثناء الاتصال ببوابة الدفع. يرجى المحاولة لاحقاً أو اختيار طريقة الدفع اليدوي.',
                default => 'لم يتم إعداد بيانات بوابة الدفع في لوحة التحكم. يرجى تفعيل البوابة وإدخال بيانات التاجر، أو اختيار طريقة دفع يدوي.',
            };

            return redirect()->route('site.checkout')->with('notice', [
                'type' => 'error',
                'message' => $message,
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

    protected function mergeSessionStoreCartToUser($user): void
    {
        if (! $user) {
            return;
        }
        $sessionId = session()->getId();
        StoreCart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id, 'session_id' => null]);
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

        $order->load(['items.course', 'storeOrder.items.product']);

        return view('checkout.success', [
            'order' => $order,
        ]);
    }
}
