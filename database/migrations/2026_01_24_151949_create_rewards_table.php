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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // discount, free_course, badge, certificate
            $table->integer('points_required');
            $table->integer('value')->nullable(); // discount percentage or course_id
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('quantity')->nullable(); // null = unlimited
            $table->integer('redeemed_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('points_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
