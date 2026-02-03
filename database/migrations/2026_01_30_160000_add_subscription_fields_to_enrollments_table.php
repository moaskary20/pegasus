<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('subscription_type')->default('once_4_months'); // once_4_months, monthly, per_session
            $table->timestamp('access_expires_at')->nullable();

            $table->unsignedInteger('sessions_total')->nullable();
            $table->unsignedInteger('sessions_remaining')->nullable();

            $table->string('payment_method')->default('manual'); // manual, order, voucher
            $table->string('voucher_code')->nullable();
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();

            $table->index(['subscription_type', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex(['subscription_type', 'payment_method']);
            $table->dropConstrainedForeignId('voucher_id');
            $table->dropColumn([
                'subscription_type',
                'access_expires_at',
                'sessions_total',
                'sessions_remaining',
                'payment_method',
                'voucher_code',
            ]);
        });
    }
};

