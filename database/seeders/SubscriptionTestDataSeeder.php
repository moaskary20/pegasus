<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

/**
 * بيانات وهمية لتجربة الاشتراكات والـ Voucher
 */
class SubscriptionTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // خطة اشتراك تجريبية
        $plan = SubscriptionPlan::firstOrCreate(
            ['name' => 'خطة تجريبية للاختبار'],
            [
                'description' => 'خطة للاختبار والتجربة مع Voucher',
                'type' => 'once',
                'price' => 100,
                'duration_days' => 30,
            ]
        );

        // Voucher يغطي 100% — للتجربة بدون دفع
        Voucher::firstOrCreate(
            ['code' => 'TEST100'],
            [
                'type' => 'once',
                'description' => 'كود تجريبي — خصم 100% للاختبار',
                'discount_type' => 'percentage',
                'discount_percentage' => 100,
                'discount_amount' => null,
                'max_usage_count' => 100,
                'usage_count' => 0,
                'start_date' => now()->subDays(1),
                'end_date' => now()->addYear(),
                'is_active' => true,
            ]
        );

        // Voucher بدل مبلغ ثابت 1000 ر.س — يغطي معظم الخطط
        Voucher::firstOrCreate(
            ['code' => 'FREE1000'],
            [
                'type' => 'once',
                'description' => 'كود تجريبي — خصم 1000 ر.س',
                'discount_type' => 'fixed',
                'discount_percentage' => null,
                'discount_amount' => 1000,
                'max_usage_count' => 50,
                'usage_count' => 0,
                'start_date' => now()->subDays(1),
                'end_date' => now()->addYear(),
                'is_active' => true,
            ]
        );

        $this->command->info('تم إضافة بيانات تجريبية للاشتراكات والـ Voucher');
        $this->command->info('خطط الاشتراك: ' . SubscriptionPlan::count());
        $this->command->info('أكواد التجربة: TEST100 (100% خصم), FREE1000 (1000 ر.س خصم)');
    }
}
