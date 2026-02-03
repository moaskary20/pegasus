<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_global_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('type')->default('number'); // number, percentage, text
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('payout_global_settings')->insert([
            [
                'key' => 'default_commission_rate',
                'value' => '70',
                'type' => 'percentage',
                'label' => 'نسبة عمولة المدرس الافتراضية',
                'description' => 'النسبة المئوية التي يحصل عليها المدرس من كل عملية بيع',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'admin_fee_rate',
                'value' => '5',
                'type' => 'percentage',
                'label' => 'رسوم المعالجة الإدارية',
                'description' => 'نسبة الرسوم الإدارية التي يتم خصمها عند السحب',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'minimum_payout',
                'value' => '100',
                'type' => 'number',
                'label' => 'الحد الأدنى للسحب',
                'description' => 'الحد الأدنى للمبلغ الذي يمكن للمدرس سحبه (بالجنيه المصري)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'payout_processing_days',
                'value' => '7',
                'type' => 'number',
                'label' => 'أيام معالجة السحب',
                'description' => 'عدد الأيام المتوقعة لمعالجة طلبات السحب',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_global_settings');
    }
};
