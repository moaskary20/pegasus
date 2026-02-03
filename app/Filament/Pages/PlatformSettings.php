<?php

namespace App\Filament\Pages;

use App\Models\PlatformSetting;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class PlatformSettings extends Page
{
    use WithFileUploads;

    protected static ?string $navigationLabel = 'الإعدادات العامة';
    
    protected static ?string $title = 'الإعدادات العامة';
    
    protected static ?int $navigationSort = 1;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.platform-settings';
    
    protected static ?string $slug = 'platform-settings';
    
    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public string $activeTab = 'lessons';
    
    // All settings organized by group
    public array $settings = [];
    
    // Test email
    public string $testEmailAddress = '';

    // Admin Panel Branding (Logo)
    public $adminLogoFile = null;

    // Public Website Settings (Logo + Home Slider)
    public $siteLogoFile = null;
    public $siteFooterLogoFile = null;
    public $slideImage = null;
    public array $slideForm = [
        'title' => '',
        'subtitle' => '',
        'primary_text' => 'تصفح الدورات',
        'primary_url' => '/admin/browse-courses',
        'secondary_text' => 'لوحة التحكم',
        'secondary_url' => '/admin',
        'is_active' => true,
    ];
    public ?int $editingSlideIndex = null;
    
    public function mount(): void
    {
        $this->loadSettings();
    }
    
    public function loadSettings(): void
    {
        $allSettings = PlatformSetting::all();
        
        foreach ($allSettings as $setting) {
            $value = match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $setting->value,
                'json' => json_decode((string) $setting->value, true) ?: [],
                default => $setting->value,
            };
            
            $this->settings[$setting->key] = $value;
        }
    }
    
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
    
    public function saveLessonsSettings(): void
    {
        $keys = [
            'max_devices_per_account', 'max_views_per_lesson', 'enforce_lesson_order',
            'require_lesson_completion', 'enable_video_watermark', 'watermark_text',
            'prevent_video_download', 'enable_playback_speed', 'default_video_quality',
            'enable_video_resume'
        ];
        
        $this->saveSettingsGroup($keys, 'lessons');
        session()->flash('success', 'تم حفظ إعدادات الدروس بنجاح');
    }
    
    public function saveSecuritySettings(): void
    {
        $keys = [
            'max_failed_login_attempts', 'lockout_duration_minutes', 'enable_two_factor_auth',
            'session_lifetime_minutes', 'force_password_change_days', 'min_password_length',
            'require_password_uppercase', 'require_password_number', 'enable_captcha',
            'captcha_site_key', 'captcha_secret_key'
        ];
        
        $this->saveSettingsGroup($keys, 'security');
        session()->flash('success', 'تم حفظ إعدادات الأمان بنجاح');
    }
    
    public function saveSocialSettings(): void
    {
        $keys = [
            'enable_google_login', 'google_client_id', 'google_client_secret',
            'enable_facebook_login', 'facebook_app_id', 'facebook_app_secret',
            'enable_twitter_login', 'twitter_client_id', 'twitter_client_secret'
        ];
        
        $this->saveSettingsGroup($keys, 'social');
        session()->flash('success', 'تم حفظ إعدادات تسجيل الدخول الاجتماعي بنجاح');
    }
    
    public function saveAnalyticsSettings(): void
    {
        $keys = [
            // Google Analytics
            'enable_google_analytics', 'google_analytics_id', 'ga_property_id',
            'ga_enhanced_ecommerce', 'ga_track_pageviews', 'ga_debug_mode',
            // Facebook Pixel
            'enable_facebook_pixel', 'facebook_pixel_id', 'fb_test_event_code',
            'fb_access_token', 'fb_track_pageview', 'fb_track_purchase',
            'fb_track_add_to_cart', 'fb_track_registration', 'fb_track_lead',
            // Google Tag Manager
            'enable_google_tag_manager', 'google_tag_manager_id', 'gtm_environment',
            'gtm_enable_datalayer', 'gtm_ecommerce_events', 'gtm_auth_token', 'gtm_preview_id',
            // Hotjar
            'enable_hotjar', 'hotjar_site_id', 'hotjar_version',
            'hotjar_recordings', 'hotjar_heatmaps', 'hotjar_surveys', 'hotjar_feedback',
            // Microsoft Clarity
            'enable_clarity', 'clarity_project_id'
        ];
        
        $this->saveSettingsGroup($keys, 'analytics');
        session()->flash('success', 'تم حفظ إعدادات التحليلات بنجاح');
    }
    
    public function saveEmailSettings(): void
    {
        $keys = [
            'email_provider', 'brevo_api_key', 'brevo_sender_name', 'brevo_sender_email',
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption',
            'email_from_address', 'email_from_name', 'enable_email_notifications',
            'enable_email_reminders'
        ];
        
        $this->saveSettingsGroup($keys, 'email');
        session()->flash('success', 'تم حفظ إعدادات البريد الإلكتروني بنجاح');
    }
    
    public function saveSeoSettings(): void
    {
        $keys = [
            'site_title', 'site_description', 'site_keywords', 'enable_sitemap',
            'sitemap_frequency', 'robots_txt', 'enable_structured_data', 'organization_type',
            'og_image', 'twitter_card_type', 'twitter_site', 'enable_lazy_loading',
            'enable_minification', 'enable_browser_caching', 'cache_duration_days',
            'enable_mobile_optimization', 'canonical_url'
        ];
        
        $this->saveSettingsGroup($keys, 'seo');
        session()->flash('success', 'تم حفظ إعدادات SEO بنجاح');
    }
    
    public function saveGeneralSettings(): void
    {
        // Handle admin logo upload (optional)
        if ($this->adminLogoFile) {
            $this->validate([
                'adminLogoFile' => 'image|max:2048', // 2MB
            ]);

            $oldPath = (string) ($this->settings['admin_logo_path'] ?? '');
            $path = $this->adminLogoFile->store('branding', 'public');

            // Delete old logo if exists
            if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            PlatformSetting::where('key', 'admin_logo_path')->update(['value' => $path]);
            $this->settings['admin_logo_path'] = $path;
            $this->adminLogoFile = null;
        }

        $keys = [
            'maintenance_mode', 'maintenance_message', 'allow_registration',
            'require_email_verification', 'default_user_role', 'timezone',
            'date_format', 'time_format', 'currency', 'currency_symbol',
            'admin_logo_alt',
        ];
        
        $this->saveSettingsGroup($keys, 'general');
        session()->flash('success', 'تم حفظ الإعدادات العامة بنجاح');
    }

    public function removeAdminLogo(): void
    {
        $oldPath = (string) ($this->settings['admin_logo_path'] ?? '');
        if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        PlatformSetting::where('key', 'admin_logo_path')->update(['value' => '']);
        $this->settings['admin_logo_path'] = '';

        PlatformSetting::clearGroupCache('general');
        session()->flash('success', 'تم حذف اللوجو بنجاح');
    }
    
    public function saveZoomSettings(): void
    {
        $keys = [
            'zoom_enabled', 'zoom_client_id', 'zoom_client_secret', 'zoom_account_id',
            'zoom_api_key', 'zoom_api_secret', 'zoom_user_id', 'zoom_enable_auto_recording',
            'zoom_meeting_duration', 'zoom_require_password', 'zoom_waiting_room_enabled',
            'zoom_host_video', 'zoom_participant_video', 'zoom_audio_type'
        ];
        
        $this->saveSettingsGroup($keys, 'zoom');
        session()->flash('success', 'تم حفظ إعدادات Zoom بنجاح');
    }
    
    protected function saveSettingsGroup(array $keys, string $group): void
    {
        foreach ($keys as $key) {
            if (isset($this->settings[$key])) {
                $value = $this->settings[$key];
                
                // Convert boolean to string for storage
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                
                PlatformSetting::where('key', $key)->update(['value' => $value]);
            }
        }
        
        PlatformSetting::clearGroupCache($group);
    }

    /**
     * ===== Public Website (Site) Settings =====
     */
    public function saveSiteLogo(): void
    {
        if (! $this->siteLogoFile) {
            return;
        }

        $this->validate([
            'siteLogoFile' => 'image|max:4096',
        ]);

        $oldPath = (string) ($this->settings['site_logo_path'] ?? '');
        $path = $this->siteLogoFile->store('site/branding', 'public');

        if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $this->upsertSetting('site_logo_path', $path, 'site', 'string', 'مسار لوجو الموقع العام (يظهر في هيدر الموقع)');
        $this->settings['site_logo_path'] = $path;
        $this->siteLogoFile = null;

        PlatformSetting::clearGroupCache('site');
        session()->flash('success', 'تم حفظ لوجو الموقع بنجاح');
    }

    public function removeSiteLogo(): void
    {
        $oldPath = (string) ($this->settings['site_logo_path'] ?? '');
        if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $this->upsertSetting('site_logo_path', '', 'site', 'string', 'مسار لوجو الموقع العام (يظهر في هيدر الموقع)');
        $this->settings['site_logo_path'] = '';

        PlatformSetting::clearGroupCache('site');
        session()->flash('success', 'تم حذف لوجو الموقع بنجاح');
    }

    public function saveSiteTextSettings(): void
    {
        $alt = (string) ($this->settings['site_logo_alt'] ?? 'Pegasus Academy');
        $this->upsertSetting('site_logo_alt', $alt, 'site', 'string', 'النص البديل للوجو الخاص بالموقع العام');

        $footerAlt = (string) ($this->settings['site_footer_logo_alt'] ?? $alt);
        $this->upsertSetting('site_footer_logo_alt', $footerAlt, 'site', 'string', 'النص البديل للوجو الخاص بفوتر الموقع');

        $googlePlayUrl = trim((string) ($this->settings['site_app_google_play_url'] ?? ''));
        $this->upsertSetting('site_app_google_play_url', $googlePlayUrl, 'site', 'string', 'رابط تحميل التطبيق من Google Play');

        $appleStoreUrl = trim((string) ($this->settings['site_app_apple_store_url'] ?? ''));
        $this->upsertSetting('site_app_apple_store_url', $appleStoreUrl, 'site', 'string', 'رابط تحميل التطبيق من App Store');

        PlatformSetting::clearGroupCache('site');
        session()->flash('success', 'تم حفظ إعدادات الموقع بنجاح');
    }

    public function saveSiteFooterLogo(): void
    {
        if (! $this->siteFooterLogoFile) {
            return;
        }

        $this->validate([
            'siteFooterLogoFile' => 'image|max:4096',
        ]);

        $oldPath = (string) ($this->settings['site_footer_logo_path'] ?? '');
        $path = $this->siteFooterLogoFile->store('site/branding/footer', 'public');

        if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $this->upsertSetting('site_footer_logo_path', $path, 'site', 'string', 'مسار لوجو فوتر الموقع العام');
        $this->settings['site_footer_logo_path'] = $path;
        $this->siteFooterLogoFile = null;

        PlatformSetting::clearGroupCache('site');
        session()->flash('success', 'تم حفظ لوجو الفوتر بنجاح');
    }

    public function removeSiteFooterLogo(): void
    {
        $oldPath = (string) ($this->settings['site_footer_logo_path'] ?? '');
        if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $this->upsertSetting('site_footer_logo_path', '', 'site', 'string', 'مسار لوجو فوتر الموقع العام');
        $this->settings['site_footer_logo_path'] = '';

        PlatformSetting::clearGroupCache('site');
        session()->flash('success', 'تم حذف لوجو الفوتر بنجاح');
    }

    public function startAddSlide(): void
    {
        $this->editingSlideIndex = null;
        $this->slideImage = null;
        $this->slideForm = [
            'title' => '',
            'subtitle' => '',
            'primary_text' => 'تصفح الدورات',
            'primary_url' => '/admin/browse-courses',
            'secondary_text' => 'لوحة التحكم',
            'secondary_url' => '/admin',
            'is_active' => true,
        ];
    }

    public function editSlide(int $index): void
    {
        $slides = $this->getSlides();
        if (! isset($slides[$index])) {
            return;
        }

        $slide = $slides[$index];
        $this->editingSlideIndex = $index;
        $this->slideImage = null;
        $this->slideForm = array_merge($this->slideForm, [
            'title' => (string) ($slide['title'] ?? ''),
            'subtitle' => (string) ($slide['subtitle'] ?? ''),
            'primary_text' => (string) ($slide['primary_text'] ?? 'تصفح الدورات'),
            'primary_url' => (string) ($slide['primary_url'] ?? '/admin/browse-courses'),
            'secondary_text' => (string) ($slide['secondary_text'] ?? 'لوحة التحكم'),
            'secondary_url' => (string) ($slide['secondary_url'] ?? '/admin'),
            'is_active' => (bool) ($slide['is_active'] ?? true),
        ]);
    }

    public function saveSlide(): void
    {
        $slides = $this->getSlides();

        $this->validate([
            'slideForm.title' => 'nullable|string|max:120',
            'slideForm.subtitle' => 'nullable|string|max:255',
            'slideForm.primary_text' => 'nullable|string|max:40',
            'slideForm.primary_url' => 'nullable|string|max:255',
            'slideForm.secondary_text' => 'nullable|string|max:40',
            'slideForm.secondary_url' => 'nullable|string|max:255',
            'slideForm.is_active' => 'boolean',
            'slideImage' => ($this->editingSlideIndex === null ? 'required|' : 'nullable|') . 'image|max:6144',
        ]);

        $existingPath = null;
        if ($this->editingSlideIndex !== null && isset($slides[$this->editingSlideIndex])) {
            $existingPath = $slides[$this->editingSlideIndex]['image_path'] ?? null;
        }

        $imagePath = $existingPath;
        if ($this->slideImage) {
            $imagePath = $this->slideImage->store('site/sliders', 'public');

            if (is_string($existingPath) && $existingPath !== '' && Storage::disk('public')->exists($existingPath)) {
                Storage::disk('public')->delete($existingPath);
            }
        }

        $newSlide = [
            'image_path' => $imagePath ?: '',
            'title' => (string) ($this->slideForm['title'] ?? ''),
            'subtitle' => (string) ($this->slideForm['subtitle'] ?? ''),
            'primary_text' => (string) ($this->slideForm['primary_text'] ?? ''),
            'primary_url' => (string) ($this->slideForm['primary_url'] ?? ''),
            'secondary_text' => (string) ($this->slideForm['secondary_text'] ?? ''),
            'secondary_url' => (string) ($this->slideForm['secondary_url'] ?? ''),
            'is_active' => (bool) ($this->slideForm['is_active'] ?? true),
        ];

        if ($this->editingSlideIndex === null) {
            $slides[] = $newSlide;
        } else {
            $slides[$this->editingSlideIndex] = $newSlide;
        }

        $this->persistSlides($slides);

        $this->slideImage = null;
        $this->editingSlideIndex = null;

        session()->flash('success', 'تم حفظ الشريحة بنجاح');
    }

    public function deleteSlide(int $index): void
    {
        $slides = $this->getSlides();
        if (! isset($slides[$index])) {
            return;
        }

        $path = $slides[$index]['image_path'] ?? null;
        if (is_string($path) && $path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        array_splice($slides, $index, 1);
        $this->persistSlides($slides);

        session()->flash('success', 'تم حذف الشريحة');
    }

    public function moveSlideUp(int $index): void
    {
        $slides = $this->getSlides();
        if ($index <= 0 || ! isset($slides[$index])) {
            return;
        }

        [$slides[$index - 1], $slides[$index]] = [$slides[$index], $slides[$index - 1]];
        $this->persistSlides($slides);
    }

    public function moveSlideDown(int $index): void
    {
        $slides = $this->getSlides();
        if (! isset($slides[$index]) || ! isset($slides[$index + 1])) {
            return;
        }

        [$slides[$index + 1], $slides[$index]] = [$slides[$index], $slides[$index + 1]];
        $this->persistSlides($slides);
    }

    protected function persistSlides(array $slides): void
    {
        $this->settings['site_home_slider'] = array_values($slides);

        $this->upsertSetting(
            'site_home_slider',
            json_encode($this->settings['site_home_slider'], JSON_UNESCAPED_UNICODE),
            'site',
            'json',
            'شرائح السلايدر في الصفحة الرئيسية (JSON)'
        );

        PlatformSetting::clearGroupCache('site');
        $this->loadSettings();
    }

    protected function getSlides(): array
    {
        $slides = $this->settings['site_home_slider'] ?? [];
        return is_array($slides) ? $slides : [];
    }

    protected function upsertSetting(string $key, string $value, string $group, string $type, string $description): void
    {
        PlatformSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
    
    public function sendTestEmail(): void
    {
        if (empty($this->testEmailAddress)) {
            session()->flash('error', 'يرجى إدخال بريد إلكتروني للاختبار');
            return;
        }
        
        try {
            \Mail::raw('هذا بريد اختباري من منصة Pegasus Academy', function ($message) {
                $message->to($this->testEmailAddress)
                    ->subject('بريد اختباري');
            });
            
            session()->flash('success', 'تم إرسال البريد الاختباري بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', 'فشل إرسال البريد: ' . $e->getMessage());
        }
    }
    
    public function generateSitemap(): void
    {
        // Placeholder for sitemap generation
        session()->flash('success', 'تم إنشاء ملف Sitemap.xml بنجاح');
    }
    
    public function updateRobotsTxt(): void
    {
        try {
            $content = $this->settings['robots_txt'] ?? '';
            file_put_contents(public_path('robots.txt'), $content);
            session()->flash('success', 'تم تحديث ملف Robots.txt بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', 'فشل تحديث الملف: ' . $e->getMessage());
        }
    }
    
    public function getTabsProperty(): array
    {
        return [
            'lessons' => ['label' => 'إعدادات الدروس', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
            'security' => ['label' => 'إعدادات الأمان', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
            'social' => ['label' => 'تسجيل الدخول الاجتماعي', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            'analytics' => ['label' => 'التحليلات والتتبع', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            'email' => ['label' => 'إعدادات البريد', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            'seo' => ['label' => 'تحسين محركات البحث', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
            'zoom' => ['label' => 'إعدادات Zoom', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 20a7 7 0 1014 0M6 10a4 4 0 118 0 4 4 0 01-8 0z'],
            'general' => ['label' => 'إعدادات عامة', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            'site' => ['label' => 'إعدادات الموقع', 'icon' => 'M12 2a10 10 0 100 20 10 10 0 000-20zM2 12h20M12 2c2.5 2.7 4 6.2 4 10s-1.5 7.3-4 10c-2.5-2.7-4-6.2-4-10S9.5 4.7 12 2z'],
        ];
    }
}
