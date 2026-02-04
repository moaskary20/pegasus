<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'preview_youtube_url')) {
                $table->string('preview_youtube_url', 500)->nullable();
            }
            if (!Schema::hasColumn('courses', 'preview_lesson_id')) {
                $table->unsignedBigInteger('preview_lesson_id')->nullable();
                $table->foreign('preview_lesson_id')->references('id')->on('lessons')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['preview_lesson_id']);
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['preview_youtube_url', 'preview_lesson_id']);
        });
    }
};
