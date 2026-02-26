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
        Schema::table('student_subscriptions', function (Blueprint $table) {
            $table->string('manual_receipt_path')->nullable()->after('final_price');
            $table->string('manual_receipt_original_name')->nullable()->after('manual_receipt_path');
            $table->timestamp('manual_receipt_uploaded_at')->nullable()->after('manual_receipt_original_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['manual_receipt_path', 'manual_receipt_original_name', 'manual_receipt_uploaded_at']);
        });
    }
};
