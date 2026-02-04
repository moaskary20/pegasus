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
        if (! Schema::hasColumn('users', 'academic_history')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('academic_history')->nullable()->after('interests');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'academic_history')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('academic_history');
            });
        }
    }
};
