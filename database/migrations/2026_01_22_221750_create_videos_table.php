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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->string('disk')->default('local');
            $table->string('path')->nullable(); // Original video path before encoding
            $table->string('hls_path')->nullable(); // Master m3u8 path
            $table->enum('status', ['pending', 'processing', 'ready', 'failed'])->default('pending');
            $table->integer('duration_seconds')->default(0);
            $table->string('encoding_job_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
