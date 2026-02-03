<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key' => 'site_logo_path',
                'value' => '',
                'group' => 'general',
                'type' => 'string',
                'description' => 'مسار لوجو لوحة التحكم (يظهر في الهيدر)',
            ],
            [
                'key' => 'site_logo_alt',
                'value' => 'Pegasus Academy',
                'group' => 'general',
                'type' => 'string',
                'description' => 'النص البديل للوجو',
            ],
        ];

        foreach ($settings as $setting) {
            $exists = DB::table('platform_settings')->where('key', $setting['key'])->exists();
            if (! $exists) {
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
            'site_logo_path',
            'site_logo_alt',
        ])->delete();
    }
};

