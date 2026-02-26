<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('platform_settings')->where('key', 'prevent_screen_capture')->exists()) {
            DB::table('platform_settings')->insert([
                'key' => 'prevent_screen_capture',
                'value' => 'true',
                'group' => 'lessons',
                'type' => 'boolean',
                'description' => 'منع تصوير الشاشة والتقاط لقطات الشاشة',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('video_progress') && !Schema::hasColumn('video_progress', 'view_count')) {
            Schema::table('video_progress', function (Blueprint $table) {
                $table->unsignedInteger('view_count')->default(1)->after('last_watched_at');
            });
        }
    }

    public function down(): void
    {
        DB::table('platform_settings')->where('key', 'prevent_screen_capture')->delete();

        if (Schema::hasTable('video_progress') && Schema::hasColumn('video_progress', 'view_count')) {
            Schema::table('video_progress', function (Blueprint $table) {
                $table->dropColumn('view_count');
            });
        }
    }
};
