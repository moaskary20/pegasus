<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCartItem;
use App\Models\Coupon;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreCart;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    /**
     * معاينة الطلب (محتويات السلة + الإجمالي)
     */
    public function preview(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $cart = $this->getCartData($user);
        $paymentMethods = $this->getPaymentMethods();

        return response()->json([
            'courses' => $cart['courses'],
            'cart_products' => $cart['cart_products'],
            'courses_subtotal' => $cart['courses_subtotal'],
            'products_subtotal' => $cart['products_subtotal'],
            'total' => $cart['total'],
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * التحقق من الكوبون ومعاينة الخصم
     * POST /api/checkout/validate-coupon
     * body: coupon_code
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $cart = $this->getCartData($user);
        $couponCode = strtoupper(trim((string) $request->input('coupon_code', '')));

        if ($couponCode === '') {
            return response()->json([
                'valid' => false,
                'message' => 'أدخل رمز الكوبون',
                'discount' => 0,
                'total' => $cart['total'],
            ]);
        }

        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'الكوبون غير صالح',
                'discount' => 0,
                'total' => $cart['total'],
            ]);
        }

        if (!$coupon->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'الكوبون منتهي أو غير متاح',
                'discount' => 0,
                'total' => $cart['total'],
            ]);
        }

        $discount = (float) $coupon->calculateDiscount($cart['courses_subtotal']);

        if ($discount <= 0 && $coupon->min_purchase && $cart['courses_subtotal'] < (float) $coupon->min_purchase) {
            return response()->json([
                'valid' => false,
                'message' => 'الحد الأدنى للشراء لهذا الكوبون: ' . number_format($coupon->min_purchase, 0) . ' ر.س',
                'discount' => 0,
                'total' => $cart['total'],
            ]);
        }

        $total = max(0, $cart['courses_subtotal'] - $discount) + $cart['products_subtotal'];

        return response()->json([
            'valid' => true,
            'message' => 'تم تطبيق الكوبون بنجاح',
            'coupon_code' => $couponCode,
            'discount' => round($discount, 2),
            'total' => round($total, 2),
            'courses_subtotal' => $cart['courses_subtotal'],
            'products_subtotal' => $cart['products_subtotal'],
        ]);
    }

    /**
     * إتمام الطلب
     * payment_gateway: kashier | manual
     * coupon_code: اختياري
     * manual_receipt: مطلوب عند manual
     */
    public function process(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
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

        $cart = $this->getCartData($user);

        if ($cart['total'] <= 0) {
            return response()->json([
                'message' => 'السلة فارغة.',
                'errors' => ['cart' => ['أضف عناصر إلى السلة أولاً.']],
            ], 422);
        }

        $couponCode = strtoupper(trim((string) $request->input('coupon_code', '')));
        $discount = 0.0;
        $coupon = null;

        if ($couponCode !== '') {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->isValid()) {
                $discount = (float) $coupon->calculateDiscount($cart['courses_subtotal']);
            } else {
                $couponCode = '';
            }
        }

        $total = max(0, $cart['courses_subtotal'] - $discount) + $cart['products_subtotal'];

        try {
            DB::beginTransaction();

            $receiptPath = null;
            $receiptName = null;
            if ($gateway === 'manual' && $request->hasFile('manual_receipt')) {
                $file = $request->file('manual_receipt');
                $receiptName = $file?->getClientOriginalName();
                $receiptPath = $file?->storePublicly('manual-receipts', 'public');
            }

            $orderId = null;

            if ($cart['courses_count'] > 0) {
                $coursesTotal = max(0, $cart['courses_subtotal'] - $discount);

                $order = Order::create([
                    'user_id' => $user->id,
                    'subtotal' => $cart['courses_subtotal'],
                    'discount' => $discount,
                    'total' => $coursesTotal,
                    'coupon_code' => $couponCode ?: null,
                    'payment_gateway' => $gateway,
                    'status' => 'pending',
                    'manual_receipt_path' => $receiptPath,
                    'manual_receipt_original_name' => $receiptName,
                    'manual_receipt_uploaded_at' => $receiptPath ? now() : null,
                ]);
                $orderId = $order->id;

                foreach ($cart['course_items'] as $item) {
                    $course = $item->course;
                    $price = (float) $item->price;
                    OrderItem::create([
                        'order_id' => $order->id,
                        'course_id' => $course->id,
                        'price' => $price,
                    ]);

                    if ($gateway !== 'manual') {
                        Enrollment::firstOrCreate(
                            [
                                'user_id' => $user->id,
                                'course_id' => $course->id,
                            ],
                            [
                                'order_id' => $order->id,
                                'price_paid' => $price,
                                'enrolled_at' => now(),
                            ]
                        );
                    }
                }

                CourseCartItem::where('user_id', $user->id)->delete();
            }

            $storeOrderId = null;
            if ($cart['products_count'] > 0) {
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
                    'shipping_country' => 'السعودية',
                    'shipping_postal_code' => null,
                    'subtotal' => $cart['products_subtotal'],
                    'shipping_cost' => 0,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total' => $cart['products_subtotal'],
                ]);

                foreach ($cart['store_items'] as $item) {
                    $product = $item->product;
                    if (!$product) {
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
                $storeOrderId = $storeOrder->id;
            }

            if ($coupon && $cart['courses_count'] > 0) {
                $coupon->increment('used_count');
            }

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الطلب بنجاح',
                'order_id' => $orderId,
                'store_order_id' => $storeOrderId,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Checkout API failed', [
                'user_id' => $user->id,
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => config('app.debug') ? 'حدث خطأ: ' . $e->getMessage() : 'حدث خطأ أثناء إنشاء الطلب.',
            ], 500);
        }
    }

    protected function getCartData($user): array
    {
        $cartItems = CourseCartItem::where('user_id', $user->id)
            ->with(['course' => fn ($q) => $q->where('is_published', true)])
            ->get()
            ->filter(fn ($item) => $item->course !== null);

        $coursesSubtotal = 0.0;
        $courseItemsWithPrice = collect();
        foreach ($cartItems as $item) {
            $c = $item->course;
            if (!$c) {
                continue;
            }
            $subType = $item->subscription_type ?? 'once';
            $price = (float) $c->getPriceForSubscriptionType($subType);
            $coursesSubtotal += $price;
            $courseItemsWithPrice->push((object) ['course' => $c, 'price' => $price]);
        }

        $storeCart = StoreCart::with(['product', 'variant'])->where('user_id', $user->id)->get();
        $productsSubtotal = (float) $storeCart->sum(fn ($item) => $item->total);

        return [
            'courses' => $courseItemsWithPrice->map(fn ($item) => [
                'id' => $item->course->id,
                'title' => $item->course->title,
                'slug' => $item->course->slug,
                'price' => round($item->price, 2),
                'cover_image' => $item->course->cover_image,
            ])->all(),
            'cart_products' => $storeCart->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product?->name ?? '',
                'quantity' => $item->quantity,
                'unit_price' => round((float) $item->unit_price, 2),
                'total' => round((float) $item->total, 2),
            ])->all(),
            'course_items' => $courseItemsWithPrice,
            'store_items' => $storeCart,
            'courses_subtotal' => round($coursesSubtotal, 2),
            'products_subtotal' => round($productsSubtotal, 2),
            'total' => round($coursesSubtotal + $productsSubtotal, 2),
            'courses_count' => $courseItemsWithPrice->count(),
            'products_count' => $storeCart->count(),
        ];
    }

    protected function getPaymentMethods(): array
    {
        return [
            ['id' => 'kashier', 'label' => 'الدفع بالفيزا والبطاقات البنكية', 'description' => 'VISA • MasterCard • ميزة'],
            ['id' => 'manual', 'label' => 'تحويل/دفع يدوي', 'description' => 'إرفاق إيصال التحويل'],
        ];
    }
}
