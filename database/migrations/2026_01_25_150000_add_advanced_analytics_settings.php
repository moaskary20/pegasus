<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            // Google Analytics Advanced
            ['key' => 'ga_property_id', 'value' => '', 'group' => 'analytics', 'type' => 'string', 'description' => 'معرف الموقع في Google Analytics'],
            ['key' => 'ga_enhanced_ecommerce', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تتبع التجارة الإلكترونية المحسّن'],
            ['key' => 'ga_track_pageviews', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تتبع مشاهدات الصفحات'],
            ['key' => 'ga_debug_mode', 'value' => 'false', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'وضع الاختبار'],
            
            // Facebook Pixel Advanced
            ['key' => 'fb_test_event_code', 'value' => '', 'group' => 'analytics', 'type' => 'string', 'description' => 'رمز اختبار أحداث Facebook'],
            ['key' => 'fb_access_token', 'value' => '', 'group' => 'analytics', 'type' => 'string', 'description' => 'Access Token للـ Conversions API'],
            ['key' => 'fb_track_pageview', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تتبع PageView'],
            ['key' => 'fb_track_purchase', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تتبع Purchase'],
            ['key' => 'fb_track_add_to_cart', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تتبع AddToCart'],
            ['key' => 'fb_track_registration', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تتبع CompleteRegistration'],
            ['key' => 'fb_track_lead', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تتبع Lead'],
            
            // Google Tag Manager Advanced
            ['key' => 'gtm_environment', 'value' => 'live', 'group' => 'analytics', 'type' => 'string', 'description' => 'بيئة GTM'],
            ['key' => 'gtm_enable_datalayer', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تفعيل Data Layer'],
            ['key' => 'gtm_ecommerce_events', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'أحداث التجارة الإلكترونية'],
            ['key' => 'gtm_auth_token', 'value' => '', 'group' => 'analytics', 'type' => 'string', 'description' => 'Auth Token للبيئة'],
            ['key' => 'gtm_preview_id', 'value' => '', 'group' => 'analytics', 'type' => 'string', 'description' => 'Preview ID'],
            
            // Hotjar Advanced
            ['key' => 'hotjar_version', 'value' => '6', 'group' => 'analytics', 'type' => 'string', 'description' => 'إصدار Hotjar'],
            ['key' => 'hotjar_recordings', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تسجيل الجلسات'],
            ['key' => 'hotjar_heatmaps', 'value' => 'true', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'خرائط الحرارة'],
            ['key' => 'hotjar_surveys', 'value' => 'false', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'الاستطلاعات'],
            ['key' => 'hotjar_feedback', 'value' => 'false', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'التغذية الراجعة'],
        ];

        foreach ($settings as $setting) {
            // Check if setting already exists
            $exists = DB::table('platform_settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('platform_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        $keys = [
            'ga_property_id', 'ga_enhanced_ecommerce', 'ga_track_pageviews', 'ga_debug_mode',
            'fb_test_event_code', 'fb_access_token', 'fb_track_pageview', 'fb_track_purchase',
            'fb_track_add_to_cart', 'fb_track_registration', 'fb_track_lead',
            'gtm_environment', 'gtm_enable_datalayer', 'gtm_ecommerce_events', 'gtm_auth_token', 'gtm_preview_id',
            'hotjar_version', 'hotjar_recordings', 'hotjar_heatmaps', 'hotjar_surveys', 'hotjar_feedback',
        ];

        DB::table('platform_settings')->whereIn('key', $keys)->delete();
    }
};
