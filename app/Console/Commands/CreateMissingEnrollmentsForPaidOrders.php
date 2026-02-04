<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Console\Command;

class CreateMissingEnrollmentsForPaidOrders extends Command
{
    protected $signature = 'orders:create-missing-enrollments';

    protected $description = 'إنشاء اشتراكات للطلبات المدفوعة التي لا تملك اشتراكات (معالجة الطلبات اليدوية القديمة)';

    public function handle(): int
    {
        $orders = Order::where('status', 'paid')
            ->with(['items' => fn ($q) => $q->whereNotNull('course_id')])
            ->get();

        $created = 0;

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if (!$item->course_id) {
                    continue;
                }

                $exists = Enrollment::where('user_id', $order->user_id)
                    ->where('course_id', $item->course_id)
                    ->exists();

                if (!$exists) {
                    Enrollment::create([
                        'user_id' => $order->user_id,
                        'course_id' => $item->course_id,
                        'order_id' => $order->id,
                        'price_paid' => $item->price ?? 0,
                        'enrolled_at' => now(),
                    ]);
                    $created++;
                    $this->info("تم إنشاء اشتراك: الطلب #{$order->order_number} - المستخدم #{$order->user_id} - الدورة #{$item->course_id}");
                }
            }
        }

        $this->info("تم إنشاء {$created} اشتراك.");
        return self::SUCCESS;
    }
}
