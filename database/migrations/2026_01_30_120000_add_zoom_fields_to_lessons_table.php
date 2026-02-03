<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // إضافة حقول Zoom إلى جدول lessons
            $table->boolean('has_zoom_meeting')
                ->default(false)
                ->after('content_type')
                ->comment('هل الدرس يحتوي على اجتماع Zoom');
            
            $table->dateTime('zoom_scheduled_time')
                ->nullable()
                ->after('has_zoom_meeting')
                ->comment('موعد اجتماع Zoom');
            
            $table->integer('zoom_duration')
                ->default(60)
                ->after('zoom_scheduled_time')
                ->comment('مدة اجتماع Zoom بالدقائق');
            
            $table->string('zoom_password')
                ->nullable()
                ->after('zoom_duration')
                ->comment('كلمة مرور اجتماع Zoom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['has_zoom_meeting', 'zoom_scheduled_time', 'zoom_duration', 'zoom_password']);
        });
    }
};
