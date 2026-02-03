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
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points'); // can be positive or negative
            $table->string('type'); // lesson_completed, quiz_passed, reward_redeemed, bonus, etc.
            $table->string('description');
            $table->morphs('pointable'); // polymorphic relation to lesson, quiz, reward, etc.
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
