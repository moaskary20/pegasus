<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['key' => 'paypal_enabled', 'value' => 'false', 'group' => 'payment', 'type' => 'boolean', 'description' => 'تفعيل بوابة باي بال'],
            ['key' => 'paypal_mode', 'value' => 'sandbox', 'group' => 'payment', 'type' => 'string', 'description' => 'وضع التشغيل (sandbox | live)'],
            ['key' => 'paypal_client_id', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'معرف العميل في PayPal'],
            ['key' => 'paypal_client_secret', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'المفتاح السري في PayPal'],
        ];

        foreach ($settings as $setting) {
            if (! DB::table('platform_settings')->where('key', $setting['key'])->exists()) {
                DB::table('platform_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('platform_settings')->whereIn('key', [
            'paypal_enabled',
            'paypal_mode',
            'paypal_client_id',
            'paypal_client_secret',
        ])->delete();
    }
};
