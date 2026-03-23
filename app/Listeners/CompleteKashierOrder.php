<?php

namespace App\Listeners;

use App\Models\Enrollment;
use App\Models\Order;
use Asciisd\Kashier\Enums\OrderStatus as KashierOrderStatus;
use Asciisd\Kashier\Events\KashierResponseHandled;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompleteKashierOrder
{
    public function handle(KashierResponseHandled $event): void
    {
        $payload = $event->payload;
        $status = (string) ($payload['paymentStatus'] ?? '');
        $merchantOrderId = (string) ($payload['merchantOrderId'] ?? '');

        if ($merchantOrderId === '') {
            return;
        }

        $kashierStatus = KashierOrderStatus::tryFrom($status);
        if (! $kashierStatus || ! $kashierStatus->isSuccess()) {
            Log::info('Kashier response: payment not successful', [
                'merchantOrderId' => $merchantOrderId,
                'paymentStatus' => $status,
            ]);

            return;
        }

        $order = Order::query()->where('order_number', $merchantOrderId)->first();
        if (! $order || $order->payment_gateway !== 'kashier') {
            return;
        }

        if ($order->status === 'paid') {
            return;
        }

        try {
            DB::beginTransaction();

            $order->update(['status' => 'paid']);

            foreach ($order->items as $item) {
                Enrollment::firstOrCreate(
                    [
                        'user_id' => $order->user_id,
                        'course_id' => $item->course_id,
                    ],
                    [
                        'order_id' => $order->id,
                        'price_paid' => (float) $item->price,
                        'enrolled_at' => now(),
                    ]
                );
            }

            DB::commit();

            session()->forget('cart');
            session()->forget('cart_coupon');

            Log::info('Kashier order completed', ['order_id' => $order->id, 'order_number' => $merchantOrderId]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('CompleteKashierOrder failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
