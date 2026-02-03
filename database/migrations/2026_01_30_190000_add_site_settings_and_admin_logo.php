<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure admin logo keys exist (Admin panel branding)
        $adminKeys = [
            [
                'key' => 'admin_logo_path',
                'value' => '',
                'group' => 'general',
                'type' => 'string',
                'description' => 'مسار لوجو لوحة التحكم (Admin Panel) يظهر في الهيدر',
            ],
            [
                'key' => 'admin_logo_alt',
                'value' => 'Pegasus Academy',
                'group' => 'general',
                'type' => 'string',
                'description' => 'النص البديل للوجو الخاص بلوحة التحكم',
            ],
        ];

        foreach ($adminKeys as $setting) {
            $exists = DB::table('platform_settings')->where('key', $setting['key'])->exists();
            if (! $exists) {
                DB::table('platform_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Ensure site keys exist (Public website branding)
        $siteKeys = [
            [
                'key' => 'site_logo_path',
                'value' => '',
                'group' => 'site',
                'type' => 'string',
                'description' => 'مسار لوجو الموقع العام (يظهر في هيدر الموقع)',
            ],
            [
                'key' => 'site_logo_alt',
                'value' => 'Pegasus Academy',
                'group' => 'site',
                'type' => 'string',
                'description' => 'النص البديل للوجو الخاص بالموقع العام',
            ],
            [
                'key' => 'site_home_slider',
                'value' => '[]',
                'group' => 'site',
                'type' => 'json',
                'description' => 'شرائح السلايدر في الصفحة الرئيسية (JSON)',
            ],
        ];

        foreach ($siteKeys as $setting) {
            $exists = DB::table('platform_settings')->where('key', $setting['key'])->exists();
            if (! $exists) {
                DB::table('platform_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Move existing logo (previously saved under site_logo_*) to admin logo.
        $oldSiteLogoPath = (string) (DB::table('platform_settings')->where('key', 'site_logo_path')->value('value') ?? '');
        $oldSiteLogoAlt = (string) (DB::table('platform_settings')->where('key', 'site_logo_alt')->value('value') ?? 'Pegasus Academy');

        $adminLogoPath = (string) (DB::table('platform_settings')->where('key', 'admin_logo_path')->value('value') ?? '');

        if ($adminLogoPath === '' && $oldSiteLogoPath !== '') {
            DB::table('platform_settings')->where('key', 'admin_logo_path')->update([
                'value' => $oldSiteLogoPath,
                'updated_at' => now(),
            ]);

            DB::table('platform_settings')->where('key', 'admin_logo_alt')->update([
                'value' => $oldSiteLogoAlt ?: 'Pegasus Academy',
                'updated_at' => now(),
            ]);

            // Clear site logo for public website (admin will still have the migrated logo)
            DB::table('platform_settings')->where('key', 'site_logo_path')->update([
                'value' => '',
                'updated_at' => now(),
            ]);
        }

        // Ensure site logo keys are in "site" group (even if they existed earlier)
        DB::table('platform_settings')->whereIn('key', ['site_logo_path', 'site_logo_alt', 'site_home_slider'])->update([
            'group' => 'site',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Keep data-safe: do not delete keys to avoid losing uploaded files refs.
        // Only revert groups back to "general" for the keys we changed.
        DB::table('platform_settings')->whereIn('key', ['site_logo_path', 'site_logo_alt', 'site_home_slider'])->update([
            'group' => 'general',
            'updated_at' => now(),
        ]);
    }
};

