<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('platform_settings')->where('key', 'manual_payment_enabled')->exists()) {
            DB::table('platform_settings')->insert([
                'key' => 'manual_payment_enabled',
                'value' => 'true',
                'group' => 'payment',
                'type' => 'boolean',
                'description' => 'تفعيل خيار تحويل/دفع يدوي في صفحة الدفع',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('platform_settings')->where('key', 'manual_payment_enabled')->delete();
    }
};
