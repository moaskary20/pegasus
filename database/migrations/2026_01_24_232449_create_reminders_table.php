<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // quiz, message, lesson, coupon, certificate, rating, question, new_course
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable();
            $table->string('color')->default('info'); // info, success, warning, danger
            $table->string('action_url')->nullable();
            $table->string('action_label')->nullable();
            $table->nullableMorphs('remindable'); // polymorphic relation
            $table->timestamp('remind_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_interval')->nullable(); // daily, weekly
            $table->timestamps();
            
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
        });
        
        // User reminder preferences
        Schema::create('reminder_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->boolean('enabled')->default(true);
            $table->boolean('email_enabled')->default(false);
            $table->timestamps();
            
            $table->unique(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_settings');
        Schema::dropIfExists('reminders');
    }
};
