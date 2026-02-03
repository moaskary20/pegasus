<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assignments table - assignments created by instructors for lessons
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->string('type')->default('assignment'); // assignment, project
            $table->integer('max_score')->default(100);
            $table->integer('passing_score')->default(60);
            $table->timestamp('due_date')->nullable();
            $table->boolean('allow_late_submission')->default(true);
            $table->integer('late_penalty_percent')->default(10);
            $table->boolean('allow_resubmission')->default(true);
            $table->integer('max_submissions')->nullable();
            $table->json('allowed_file_types')->nullable(); // ['pdf', 'doc', 'zip']
            $table->integer('max_file_size_mb')->default(10);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            
            $table->index(['course_id', 'is_published']);
            $table->index(['lesson_id']);
        });
        
        // Assignment submissions - student submissions
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content')->nullable(); // Text content if any
            $table->string('status')->default('submitted'); // submitted, graded, returned, resubmit_requested
            $table->integer('score')->nullable();
            $table->text('feedback')->nullable(); // Instructor feedback
            $table->timestamp('submitted_at');
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_late')->default(false);
            $table->integer('attempt_number')->default(1);
            $table->timestamps();
            
            $table->index(['assignment_id', 'user_id']);
            $table->index(['status']);
        });
        
        // Submission files - files attached to submissions
        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('assignment_submissions')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size'); // in bytes
            $table->timestamps();
        });
        
        // Assignment comments - comments on submissions
        Schema::create('assignment_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('assignment_submissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('assignment_comments')->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_private')->default(false); // Private notes for instructor only
            $table->timestamps();
            
            $table->index(['submission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_comments');
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
