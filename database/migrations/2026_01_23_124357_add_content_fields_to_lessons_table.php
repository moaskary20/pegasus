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
            $table->string('video_path')->nullable()->after('description');
            $table->string('image_path')->nullable()->after('video_path');
            $table->longText('content')->nullable()->after('image_path'); // محتوى نصي للدرس
            $table->enum('content_type', ['video', 'image', 'text', 'mixed'])->default('text')->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['video_path', 'image_path', 'content', 'content_type']);
        });
    }
};
