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
        Schema::create('instructor_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // المدرس
            $table->foreignId('course_id')->constrained()->onDelete('cascade'); // الدورة
            $table->enum('earnings_type', ['percentage', 'fixed'])->default('percentage'); // نوع الأرباح
            $table->decimal('earnings_value', 10, 2)->default(0); // القيمة (نسبة أو مبلغ)
            $table->boolean('is_active')->default(true); // نشط/غير نشط
            $table->timestamps();
            
            // Unique constraint: مدرس واحد لكل دورة
            $table->unique(['user_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_earnings');
    }
};
