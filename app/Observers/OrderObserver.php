<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     * عند تغيير حالة الطلب إلى "مدفوع" يتم إنشاء الاشتراكات تلقائياً للدورات
     */
    public function updated(Order $order): void
    {
        $originalStatus = $order->getOriginal('status');
        $currentStatus = $order->status;

        // عند التغيير من غير مدفوع إلى مدفوع - إنشاء الاشتراكات
        if ($originalStatus !== 'paid' && $currentStatus === 'paid') {
            $this->createEnrollmentsForOrder($order);
        }
    }

    /**
     * إنشاء اشتراكات للدورات في الطلب
     */
    protected function createEnrollmentsForOrder(Order $order): void
    {
        try {
            $order->load(['items' => fn ($q) => $q->whereNotNull('course_id')]);

            foreach ($order->items as $item) {
                if (!$item->course_id) {
                    continue;
                }

                Enrollment::firstOrCreate(
                    [
                        'user_id' => $order->user_id,
                        'course_id' => $item->course_id,
                    ],
                    [
                        'order_id' => $order->id,
                        'price_paid' => $item->price ?? 0,
                        'enrolled_at' => now(),
                    ]
                );
            }

            Log::info('Enrollments created for paid order', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to create enrollments for paid order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
