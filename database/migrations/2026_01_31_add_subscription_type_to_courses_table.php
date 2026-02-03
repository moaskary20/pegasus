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
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('subscription_type', ['once', 'monthly', 'daily', 'free'])->default('once')->after('offer_price');
            $table->decimal('subscription_price', 10, 2)->nullable()->after('subscription_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['subscription_type', 'subscription_price']);
        });
    }
};
