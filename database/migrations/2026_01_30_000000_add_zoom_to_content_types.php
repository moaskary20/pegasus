<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support ENUM, so we use string column
        // The validation will be done at the application level
        // We'll add a new column if needed or update existing one
        
        // Check if content_type exists
        if (Schema::hasColumn('lessons', 'content_type')) {
            // SQLite: Drop and recreate with new validation in app
            // The enum values will be: 'video', 'image', 'text', 'mixed', 'zoom'
            // Just update any existing constraints in the app model
            
            // For SQLite, we can't modify enum, but the values are stored as strings
            // So we just need to ensure the app accepts 'zoom' as a valid value
        } else {
            // If column doesn't exist, add it
            Schema::table('lessons', function (Blueprint $table) {
                $table->string('content_type')->default('text')->after('content');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite migration - nothing to do since we didn't change schema
        // The enum validation is handled by Laravel model
    }
};

