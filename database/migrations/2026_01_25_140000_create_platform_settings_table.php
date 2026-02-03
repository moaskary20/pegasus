<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // general, lessons, security, social, analytics, email, seo
            $table->string('type')->default('string'); // string, integer, boolean, json, text
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $settings = [
            // === Lesson Settings ===
            ['key' => 'max_devices_per_account', 'value' => '3', 'group' => 'lessons', 'type' => 'integer', 'description' => 'الحد الأقصى للأجهزة المسموح بها لكل حساب'],
            ['key' => 'max_views_per_lesson', 'value' => '10', 'group' => 'lessons', 'type' => 'integer', 'description' => 'الحد الأقصى للمشاهدات لكل درس'],
            ['key' => 'enforce_lesson_order', 'value' => 'true', 'group' => 'lessons', 'type' => 'boolean', 'description' => 'منع الانتقال للدرس التالي قبل إتمام الحالي'],
            ['key' => 'require_lesson_completion', 'value' => '80', 'group' => 'lessons', 'type' => 'integer', 'description' => 'نسبة المشاهدة المطلوبة لاعتبار الدرس مكتملاً (%)'],
            ['key' => 'enable_video_watermark', 'value' => 'true', 'group' => 'lessons', 'type' => 'boolean', 'description' => 'تفعيل العلامة المائية على الفيديوهات'],
            ['key' => 'watermark_text', 'value' => '{user_email}', 'group' => 'lessons', 'type' => 'string', 'description' => 'نص العلامة المائية'],
            ['key' => 'prevent_video_download', 'value' => 'true', 'group' => 'lessons', 'type' => 'boolean', 'description' => 'منع تحميل الفيديوهات'],
            ['key' => 'enable_playback_speed', 'value' => 'true', 'group' => 'lessons', 'type' => 'boolean', 'description' => 'السماح بتغيير سرعة التشغيل'],
            ['key' => 'default_video_quality', 'value' => 'auto', 'group' => 'lessons', 'type' => 'string', 'description' => 'جودة الفيديو الافتراضية'],
            ['key' => 'enable_video_resume', 'value' => 'true', 'group' => 'lessons', 'type' => 'boolean', 'description' => 'استئناف الفيديو من آخر نقطة'],
            
            // === Security Settings ===
            ['key' => 'max_failed_login_attempts', 'value' => '5', 'group' => 'security', 'type' => 'integer', 'description' => 'الحد الأقصى لمحاولات الدخول الفاشلة'],
            ['key' => 'lockout_duration_minutes', 'value' => '30', 'group' => 'security', 'type' => 'integer', 'description' => 'مدة قفل الحساب (بالدقائق)'],
            ['key' => 'enable_two_factor_auth', 'value' => 'false', 'group' => 'security', 'type' => 'boolean', 'description' => 'تفعيل المصادقة الثنائية'],
            ['key' => 'session_lifetime_minutes', 'value' => '120', 'group' => 'security', 'type' => 'integer', 'description' => 'مدة الجلسة (بالدقائق)'],
            ['key' => 'force_password_change_days', 'value' => '0', 'group' => 'security', 'type' => 'integer', 'description' => 'إجبار تغيير كلمة المرور كل (أيام) - 0 لتعطيل'],
            ['key' => 'min_password_length', 'value' => '8', 'group' => 'security', 'type' => 'integer', 'description' => 'الحد الأدنى لطول كلمة المرور'],
            ['key' => 'require_password_uppercase', 'value' => 'true', 'group' => 'security', 'type' => 'boolean', 'description' => 'يتطلب حرف كبير في كلمة المرور'],
            ['key' => 'require_password_number', 'value' => 'true', 'group' => 'security', 'type' => 'boolean', 'description' => 'يتطلب رقم في كلمة المرور'],
            ['key' => 'enable_captcha', 'value' => 'false', 'group' => 'security', 'type' => 'boolean', 'description' => 'تفعيل CAPTCHA'],
            ['key' => 'captcha_site_key', 'value' => '', 'group' => 'security', 'type' => 'string', 'description' => 'مفتاح موقع reCAPTCHA'],
            ['key' => 'captcha_secret_key', 'value' => '', 'group' => 'security', 'type' => 'string', 'description' => 'المفتاح السري لـ reCAPTCHA'],
            
            // === Social Login Settings ===
            ['key' => 'enable_google_login', 'value' => 'false', 'group' => 'social', 'type' => 'boolean', 'description' => 'تفعيل تسجيل الدخول بـ Google'],
            ['key' => 'google_client_id', 'value' => '', 'group' => 'social', 'type' => 'string', 'description' => 'معرف عميل Google'],
            ['key' => 'google_client_secret', 'value' => '', 'group' => 'social', 'type' => 'string', 'description' => 'المفتاح السري لـ Google'],
            ['key' => 'enable_facebook_login', 'value' => 'false', 'group' => 'social', 'type' => 'boolean', 'description' => 'تفعيل تسجيل الدخول بـ Facebook'],
            ['key' => 'facebook_app_id', 'value' => '', 'group' => 'social', 'type' => 'string', 'description' => 'معرف تطبيق Facebook'],
            ['key' => 'facebook_app_secret', 'value' => '', 'group' => 'social', 'type' => 'string', 'description' => 'المفتاح السري لـ Facebook'],
            ['key' => 'enable_twitter_login', 'value' => 'false', 'group' => 'social', 'type' => 'boolean', 'description' => 'تفعيل تسجيل الدخول بـ Twitter/X'],
            ['key' => 'twitter_client_id', 'value' => '', 'group' => 'social', 'type' => 'string', 'description' => 'معرف عميل Twitter'],
            ['key' => 'twitter_client_secret', 'value' => '', 'group' => 'social', 'type' => 'string', 'description' => 'المفتاح السري لـ Twitter'],
            
            // === Analytics Settings ===
            ['key' => 'enable_google_analytics', 'value' => 'false', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تفعيل Google Analytics'],
            ['key' => 'google_analytics_id', 'value' => '', 'group' => 'analytics', 'type' => 'string', 'description' => 'معرف Google Analytics (GA4)'],
            ['key' => 'enable_facebook_pixel', 'value' => 'false', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تفعيل Facebook Pixel'],
            ['key' => 'facebook_pixel_id', 'value' => '', 'group' => 'analytics', 'type' => 'string', 'description' => 'معرف Facebook Pixel'],
            ['key' => 'enable_google_tag_manager', 'value' => 'false', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تفعيل Google Tag Manager'],
            ['key' => 'google_tag_manager_id', 'value' => '', 'group' => 'analytics', 'type' => 'string', 'description' => 'معرف Google Tag Manager'],
            ['key' => 'enable_hotjar', 'value' => 'false', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تفعيل Hotjar'],
            ['key' => 'hotjar_site_id', 'value' => '', 'group' => 'analytics', 'type' => 'string', 'description' => 'معرف موقع Hotjar'],
            ['key' => 'enable_clarity', 'value' => 'false', 'group' => 'analytics', 'type' => 'boolean', 'description' => 'تفعيل Microsoft Clarity'],
            ['key' => 'clarity_project_id', 'value' => '', 'group' => 'analytics', 'type' => 'string', 'description' => 'معرف مشروع Clarity'],
            
            // === Email Settings (Brevo/Sendinblue) ===
            ['key' => 'email_provider', 'value' => 'smtp', 'group' => 'email', 'type' => 'string', 'description' => 'مزود خدمة البريد (smtp, brevo, mailgun, ses)'],
            ['key' => 'brevo_api_key', 'value' => '', 'group' => 'email', 'type' => 'string', 'description' => 'مفتاح API لـ Brevo'],
            ['key' => 'brevo_sender_name', 'value' => '', 'group' => 'email', 'type' => 'string', 'description' => 'اسم المرسل في Brevo'],
            ['key' => 'brevo_sender_email', 'value' => '', 'group' => 'email', 'type' => 'string', 'description' => 'بريد المرسل في Brevo'],
            ['key' => 'smtp_host', 'value' => '', 'group' => 'email', 'type' => 'string', 'description' => 'خادم SMTP'],
            ['key' => 'smtp_port', 'value' => '587', 'group' => 'email', 'type' => 'integer', 'description' => 'منفذ SMTP'],
            ['key' => 'smtp_username', 'value' => '', 'group' => 'email', 'type' => 'string', 'description' => 'اسم مستخدم SMTP'],
            ['key' => 'smtp_password', 'value' => '', 'group' => 'email', 'type' => 'string', 'description' => 'كلمة مرور SMTP'],
            ['key' => 'smtp_encryption', 'value' => 'tls', 'group' => 'email', 'type' => 'string', 'description' => 'تشفير SMTP (tls, ssl)'],
            ['key' => 'email_from_address', 'value' => '', 'group' => 'email', 'type' => 'string', 'description' => 'البريد المرسل منه'],
            ['key' => 'email_from_name', 'value' => '', 'group' => 'email', 'type' => 'string', 'description' => 'اسم المرسل'],
            ['key' => 'enable_email_notifications', 'value' => 'true', 'group' => 'email', 'type' => 'boolean', 'description' => 'تفعيل إشعارات البريد'],
            ['key' => 'enable_email_reminders', 'value' => 'true', 'group' => 'email', 'type' => 'boolean', 'description' => 'تفعيل تذكيرات البريد'],
            
            // === SEO Settings ===
            ['key' => 'site_title', 'value' => 'Pegasus Academy', 'group' => 'seo', 'type' => 'string', 'description' => 'عنوان الموقع'],
            ['key' => 'site_description', 'value' => '', 'group' => 'seo', 'type' => 'text', 'description' => 'وصف الموقع'],
            ['key' => 'site_keywords', 'value' => '', 'group' => 'seo', 'type' => 'text', 'description' => 'الكلمات المفتاحية'],
            ['key' => 'enable_sitemap', 'value' => 'true', 'group' => 'seo', 'type' => 'boolean', 'description' => 'تفعيل Sitemap.xml'],
            ['key' => 'sitemap_frequency', 'value' => 'daily', 'group' => 'seo', 'type' => 'string', 'description' => 'تكرار تحديث Sitemap'],
            ['key' => 'robots_txt', 'value' => "User-agent: *\nAllow: /\nDisallow: /admin/\nSitemap: /sitemap.xml", 'group' => 'seo', 'type' => 'text', 'description' => 'محتوى Robots.txt'],
            ['key' => 'enable_structured_data', 'value' => 'true', 'group' => 'seo', 'type' => 'boolean', 'description' => 'تفعيل البيانات المنظمة (Schema.org)'],
            ['key' => 'organization_type', 'value' => 'EducationalOrganization', 'group' => 'seo', 'type' => 'string', 'description' => 'نوع المؤسسة للبيانات المنظمة'],
            ['key' => 'og_image', 'value' => '', 'group' => 'seo', 'type' => 'string', 'description' => 'صورة Open Graph الافتراضية'],
            ['key' => 'twitter_card_type', 'value' => 'summary_large_image', 'group' => 'seo', 'type' => 'string', 'description' => 'نوع بطاقة Twitter'],
            ['key' => 'twitter_site', 'value' => '', 'group' => 'seo', 'type' => 'string', 'description' => 'حساب Twitter للموقع'],
            ['key' => 'enable_lazy_loading', 'value' => 'true', 'group' => 'seo', 'type' => 'boolean', 'description' => 'تفعيل التحميل الكسول للصور'],
            ['key' => 'enable_minification', 'value' => 'true', 'group' => 'seo', 'type' => 'boolean', 'description' => 'تفعيل ضغط CSS/JS'],
            ['key' => 'enable_browser_caching', 'value' => 'true', 'group' => 'seo', 'type' => 'boolean', 'description' => 'تفعيل التخزين المؤقت'],
            ['key' => 'cache_duration_days', 'value' => '30', 'group' => 'seo', 'type' => 'integer', 'description' => 'مدة التخزين المؤقت (أيام)'],
            ['key' => 'enable_mobile_optimization', 'value' => 'true', 'group' => 'seo', 'type' => 'boolean', 'description' => 'تحسين الجوال'],
            ['key' => 'canonical_url', 'value' => '', 'group' => 'seo', 'type' => 'string', 'description' => 'الرابط الأساسي للموقع'],
            
            // === General Settings ===
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'general', 'type' => 'boolean', 'description' => 'وضع الصيانة'],
            ['key' => 'maintenance_message', 'value' => 'الموقع تحت الصيانة، سنعود قريباً', 'group' => 'general', 'type' => 'text', 'description' => 'رسالة الصيانة'],
            ['key' => 'allow_registration', 'value' => 'true', 'group' => 'general', 'type' => 'boolean', 'description' => 'السماح بالتسجيل'],
            ['key' => 'require_email_verification', 'value' => 'true', 'group' => 'general', 'type' => 'boolean', 'description' => 'تأكيد البريد الإلكتروني'],
            ['key' => 'default_user_role', 'value' => 'student', 'group' => 'general', 'type' => 'string', 'description' => 'الدور الافتراضي للمستخدم الجديد'],
            ['key' => 'timezone', 'value' => 'Africa/Cairo', 'group' => 'general', 'type' => 'string', 'description' => 'المنطقة الزمنية'],
            ['key' => 'date_format', 'value' => 'd/m/Y', 'group' => 'general', 'type' => 'string', 'description' => 'تنسيق التاريخ'],
            ['key' => 'time_format', 'value' => 'H:i', 'group' => 'general', 'type' => 'string', 'description' => 'تنسيق الوقت'],
            ['key' => 'currency', 'value' => 'EGP', 'group' => 'general', 'type' => 'string', 'description' => 'العملة'],
            ['key' => 'currency_symbol', 'value' => 'ج.م', 'group' => 'general', 'type' => 'string', 'description' => 'رمز العملة'],
            
            // === Zoom API Settings ===
            ['key' => 'zoom_enabled', 'value' => 'false', 'group' => 'zoom', 'type' => 'boolean', 'description' => 'تفعيل Zoom'],
            ['key' => 'zoom_client_id', 'value' => '', 'group' => 'zoom', 'type' => 'string', 'description' => 'معرف عميل Zoom'],
            ['key' => 'zoom_client_secret', 'value' => '', 'group' => 'zoom', 'type' => 'string', 'description' => 'المفتاح السري لـ Zoom'],
            ['key' => 'zoom_account_id', 'value' => '', 'group' => 'zoom', 'type' => 'string', 'description' => 'معرف حساب Zoom'],
            ['key' => 'zoom_api_key', 'value' => '', 'group' => 'zoom', 'type' => 'string', 'description' => 'مفتاح API لـ Zoom'],
            ['key' => 'zoom_api_secret', 'value' => '', 'group' => 'zoom', 'type' => 'string', 'description' => 'السر الخاص بـ API'],
            ['key' => 'zoom_user_id', 'value' => '', 'group' => 'zoom', 'type' => 'string', 'description' => 'معرف المستخدم في Zoom'],
            ['key' => 'zoom_enable_auto_recording', 'value' => 'true', 'group' => 'zoom', 'type' => 'boolean', 'description' => 'تفعيل التسجيل التلقائي'],
            ['key' => 'zoom_meeting_duration', 'value' => '60', 'group' => 'zoom', 'type' => 'integer', 'description' => 'مدة الاجتماع الافتراضية (دقيقة)'],
            ['key' => 'zoom_require_password', 'value' => 'true', 'group' => 'zoom', 'type' => 'boolean', 'description' => 'مطلوب كلمة مرور للاجتماع'],
            ['key' => 'zoom_waiting_room_enabled', 'value' => 'false', 'group' => 'zoom', 'type' => 'boolean', 'description' => 'تفعيل غرفة الانتظار'],
            ['key' => 'zoom_host_video', 'value' => 'true', 'group' => 'zoom', 'type' => 'boolean', 'description' => 'تفعيل فيديو المضيف'],
            ['key' => 'zoom_participant_video', 'value' => 'true', 'group' => 'zoom', 'type' => 'boolean', 'description' => 'تفعيل فيديو المشاركين'],
            ['key' => 'zoom_audio_type', 'value' => 'both', 'group' => 'zoom', 'type' => 'string', 'description' => 'نوع الصوت (both, voip, telephony)'],
        ];

        foreach ($settings as $setting) {
            DB::table('platform_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
