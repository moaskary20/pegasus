<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Subscription Plans Table
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['once', 'monthly', 'daily']);
            $table->decimal('price', 10, 2);
            $table->integer('duration_days');
            $table->integer('max_lessons')->nullable();
            $table->timestamps();
        });

        // Vouchers Table
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->integer('max_usage_count')->nullable();
            $table->integer('usage_count')->default(0);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Student Subscriptions Table
        Schema::create('student_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onDelete('set null');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->decimal('final_price', 10, 2);
            $table->timestamps();
        });

        // Daily Lesson Access Table
        Schema::create('daily_lesson_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->dateTime('purchased_at');
            $table->dateTime('expires_at');
            $table->timestamps();
        });

        // Voucher Usage Table
        Schema::create('voucher_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->dateTime('used_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_usage');
        Schema::dropIfExists('daily_lesson_access');
        Schema::dropIfExists('student_subscriptions');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('subscription_plans');
    }
};
