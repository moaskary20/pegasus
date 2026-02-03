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
            // Remove old fields if they exist (add_subscription_type may not have run yet)
            if (Schema::hasColumn('courses', 'subscription_type')) {
                $table->dropColumn('subscription_type');
            }
            if (Schema::hasColumn('courses', 'subscription_price')) {
                $table->dropColumn('subscription_price');
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            // Add separate prices for each subscription type (skip if already exist)
            if (!Schema::hasColumn('courses', 'price_once')) {
                $table->decimal('price_once', 10, 2)->nullable()->after('offer_price');
            }
            if (!Schema::hasColumn('courses', 'price_monthly')) {
                $table->decimal('price_monthly', 10, 2)->nullable()->after('price_once');
            }
            if (!Schema::hasColumn('courses', 'price_daily')) {
                $table->decimal('price_daily', 10, 2)->nullable()->after('price_monthly');
            }
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
