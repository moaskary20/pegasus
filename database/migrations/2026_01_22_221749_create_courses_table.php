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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Instructor
            $table->string('cover_image')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable(); // JSON or text
            $table->integer('hours')->default(0);
            $table->integer('lectures_count')->default(0);
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('offer_price', 10, 2)->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('sub_category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->boolean('is_published')->default(false);
            $table->integer('students_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
