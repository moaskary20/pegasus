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
        Schema::table('certificates', function (Blueprint $table) {
            $table->text('intro_text')->nullable()->after('pdf_path');
            $table->text('completion_text')->nullable()->after('intro_text');
            $table->text('award_text')->nullable()->after('completion_text');
            $table->string('director_name')->nullable()->after('award_text');
            $table->string('academic_director_name')->nullable()->after('director_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn([
                'intro_text',
                'completion_text',
                'award_text',
                'director_name',
                'academic_director_name',
            ]);
        });
    }
};
