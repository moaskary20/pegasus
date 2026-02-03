<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('manual_receipt_path')->nullable()->after('invoice_url');
            $table->string('manual_receipt_original_name')->nullable()->after('manual_receipt_path');
            $table->timestamp('manual_receipt_uploaded_at')->nullable()->after('manual_receipt_original_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'manual_receipt_path',
                'manual_receipt_original_name',
                'manual_receipt_uploaded_at',
            ]);
        });
    }
};

