<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('course_cart_items', 'subscription_type')) {
            Schema::table('course_cart_items', function (Blueprint $table) {
                $table->string('subscription_type', 20)->default('once')->after('course_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('course_cart_items', function (Blueprint $table) {
            $table->dropColumn('subscription_type');
        });
    }
};
