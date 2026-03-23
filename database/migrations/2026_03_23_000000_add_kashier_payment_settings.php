<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['key' => 'kashier_enabled', 'value' => 'false', 'group' => 'payment', 'type' => 'boolean', 'description' => 'تفعيل بوابة كاشير'],
            ['key' => 'kashier_mode', 'value' => 'test', 'group' => 'payment', 'type' => 'string', 'description' => 'وضع التشغيل (test | live)'],
            ['key' => 'kashier_merchant_id', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'معرف التاجر في كاشير'],
            ['key' => 'kashier_api_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'مفتاح API لكاشير'],
            ['key' => 'kashier_encryption_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'مفتاح التشفير HMAC لكاشير'],
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
            'kashier_enabled',
            'kashier_mode',
            'kashier_merchant_id',
            'kashier_api_key',
            'kashier_encryption_key',
        ])->delete();
    }
};
