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
        Schema::create('question_bank_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['mcq', 'fill_blank', 'true_false', 'matching', 'short_answer'])->default('mcq');
            $table->text('question_text');
            $table->json('options')->nullable(); // For MCQ, matching options
            $table->json('correct_answer'); // Answer(s) based on type
            $table->text('explanation')->nullable();
            $table->integer('points')->default(1);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable();
            $table->json('tags')->nullable(); // علامات إضافية
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_bank_questions');
    }
};
