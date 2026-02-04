<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['key' => 'support_email', 'value' => '', 'group' => 'general', 'type' => 'string', 'description' => 'البريد الإلكتروني للدعم'],
            ['key' => 'support_phone', 'value' => '', 'group' => 'general', 'type' => 'string', 'description' => 'رقم هاتف الدعم'],
            ['key' => 'support_phone_2', 'value' => '', 'group' => 'general', 'type' => 'string', 'description' => 'رقم هاتف الدعم الثاني'],
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
        DB::table('platform_settings')->whereIn('key', ['support_email', 'support_phone', 'support_phone_2'])->delete();
    }
};
