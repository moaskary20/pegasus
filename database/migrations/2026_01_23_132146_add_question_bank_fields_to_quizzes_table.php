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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('question_bank_id')->nullable()->after('lesson_id')->constrained()->onDelete('set null');
            $table->integer('questions_count')->nullable()->after('question_bank_id'); // عدد الأسئلة المطلوبة من البنك
            $table->boolean('randomize_questions')->default(true)->after('questions_count'); // هل يتم اختيار الأسئلة عشوائياً
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['question_bank_id']);
            $table->dropColumn(['question_bank_id', 'questions_count', 'randomize_questions']);
        });
    }
};
