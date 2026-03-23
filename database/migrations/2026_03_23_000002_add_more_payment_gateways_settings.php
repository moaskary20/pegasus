<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            // Paymob
            ['key' => 'paymob_enabled', 'value' => 'false', 'group' => 'payment', 'type' => 'boolean', 'description' => 'تفعيل بوابة بايموب'],
            ['key' => 'paymob_mode', 'value' => 'test', 'group' => 'payment', 'type' => 'string', 'description' => 'وضع التشغيل'],
            ['key' => 'paymob_api_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'مفتاح API بايموب'],
            ['key' => 'paymob_integration_id', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'معرف التكامل'],
            ['key' => 'paymob_hmac_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'مفتاح HMAC'],
            ['key' => 'paymob_iframe_id', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'معرف iFrame'],
            // Stripe
            ['key' => 'stripe_enabled', 'value' => 'false', 'group' => 'payment', 'type' => 'boolean', 'description' => 'تفعيل بوابة سترايب'],
            ['key' => 'stripe_mode', 'value' => 'test', 'group' => 'payment', 'type' => 'string', 'description' => 'وضع التشغيل'],
            ['key' => 'stripe_publishable_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'المفتاح العام'],
            ['key' => 'stripe_secret_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'المفتاح السري'],
            // Moyasar
            ['key' => 'moyasar_enabled', 'value' => 'false', 'group' => 'payment', 'type' => 'boolean', 'description' => 'تفعيل بوابة مويَسَر'],
            ['key' => 'moyasar_mode', 'value' => 'test', 'group' => 'payment', 'type' => 'string', 'description' => 'وضع التشغيل'],
            ['key' => 'moyasar_publishable_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'المفتاح العام'],
            ['key' => 'moyasar_secret_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'المفتاح السري'],
            // PayTabs
            ['key' => 'paytabs_enabled', 'value' => 'false', 'group' => 'payment', 'type' => 'boolean', 'description' => 'تفعيل بوابة باي تابس'],
            ['key' => 'paytabs_mode', 'value' => 'test', 'group' => 'payment', 'type' => 'string', 'description' => 'وضع التشغيل'],
            ['key' => 'paytabs_profile_id', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'معرف الملف'],
            ['key' => 'paytabs_server_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'مفتاح الخادم'],
            ['key' => 'paytabs_client_key', 'value' => '', 'group' => 'payment', 'type' => 'string', 'description' => 'مفتاح العميل'],
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
            'paymob_enabled', 'paymob_mode', 'paymob_api_key', 'paymob_integration_id', 'paymob_hmac_key', 'paymob_iframe_id',
            'stripe_enabled', 'stripe_mode', 'stripe_publishable_key', 'stripe_secret_key',
            'moyasar_enabled', 'moyasar_mode', 'moyasar_publishable_key', 'moyasar_secret_key',
            'paytabs_enabled', 'paytabs_mode', 'paytabs_profile_id', 'paytabs_server_key', 'paytabs_client_key',
        ])->delete();
    }
};
