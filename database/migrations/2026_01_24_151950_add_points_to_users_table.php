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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('total_points')->default(0)->after('email');
            $table->integer('available_points')->default(0)->after('total_points');
            $table->string('rank')->default('bronze')->after('available_points'); // bronze, silver, gold, platinum, diamond
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_points', 'available_points', 'rank']);
        });
    }
};
