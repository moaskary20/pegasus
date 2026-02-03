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
            // Remove old fields
            $table->dropColumn(['subscription_type', 'subscription_price']);
        });

        Schema::table('courses', function (Blueprint $table) {
            // Add separate prices for each subscription type
            $table->decimal('price_once', 10, 2)->nullable()->after('offer_price');
            $table->decimal('price_monthly', 10, 2)->nullable()->after('price_once');
            $table->decimal('price_daily', 10, 2)->nullable()->after('price_monthly');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['price_once', 'price_monthly', 'price_daily']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->enum('subscription_type', ['once', 'monthly', 'daily', 'free'])->default('once')->after('offer_price');
            $table->decimal('subscription_price', 10, 2)->nullable()->after('subscription_type');
        });
    }
};
