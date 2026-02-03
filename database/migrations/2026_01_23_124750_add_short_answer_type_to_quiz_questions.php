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
        // SQLite doesn't support ENUM, so we just ensure the column accepts the new value
        // The validation will be handled at the application level
        if (\DB::getDriverName() !== 'sqlite') {
            \DB::statement("ALTER TABLE quiz_questions MODIFY COLUMN type ENUM('mcq', 'fill_blank', 'true_false', 'matching', 'short_answer') DEFAULT 'mcq'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\DB::getDriverName() !== 'sqlite') {
            \DB::statement("ALTER TABLE quiz_questions MODIFY COLUMN type ENUM('mcq', 'fill_blank', 'true_false', 'matching') DEFAULT 'mcq'");
        }
    }
};
