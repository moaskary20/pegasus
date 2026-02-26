<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'discount_type')) {
                $table->string('discount_type')->default('percentage')->after('code');
            }
            if (!Schema::hasColumn('vouchers', 'description')) {
                $table->text('description')->nullable()->after('code');
            }
            if (!Schema::hasColumn('vouchers', 'max_usage_count')) {
                $table->integer('max_usage_count')->nullable()->after('discount_amount');
            }
            if (!Schema::hasColumn('vouchers', 'usage_count')) {
                $table->integer('usage_count')->default(0)->after('max_usage_count');
            }
            if (!Schema::hasColumn('vouchers', 'start_date')) {
                $table->dateTime('start_date')->nullable()->after('usage_count');
            }
            if (!Schema::hasColumn('vouchers', 'end_date')) {
                $table->dateTime('end_date')->nullable()->after('start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $columns = ['discount_type', 'description', 'max_usage_count', 'usage_count', 'start_date', 'end_date'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('vouchers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
