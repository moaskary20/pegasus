<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governorate_shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar'); // Arabic name
            $table->string('name_en'); // English name
            $table->string('code')->unique(); // Governorate code
            $table->string('region')->nullable(); // Region (صعيد, دلتا, إلخ)
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable(); // Min order for free shipping
            $table->integer('estimated_days_min')->nullable();
            $table->integer('estimated_days_max')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('cash_on_delivery')->default(true); // الدفع عند الاستلام
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        
        // Insert all Egyptian governorates
        $this->insertGovernorates();
    }
    
    protected function insertGovernorates(): void
    {
        $governorates = [
            // القاهرة الكبرى
            ['name_ar' => 'القاهرة', 'name_en' => 'Cairo', 'code' => 'CAI', 'region' => 'القاهرة الكبرى', 'shipping_cost' => 40, 'estimated_days_min' => 1, 'estimated_days_max' => 2, 'sort_order' => 1],
            ['name_ar' => 'الجيزة', 'name_en' => 'Giza', 'code' => 'GIZ', 'region' => 'القاهرة الكبرى', 'shipping_cost' => 40, 'estimated_days_min' => 1, 'estimated_days_max' => 2, 'sort_order' => 2],
            ['name_ar' => 'القليوبية', 'name_en' => 'Qalyubia', 'code' => 'QLY', 'region' => 'القاهرة الكبرى', 'shipping_cost' => 45, 'estimated_days_min' => 1, 'estimated_days_max' => 3, 'sort_order' => 3],
            
            // الإسكندرية
            ['name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria', 'code' => 'ALX', 'region' => 'الساحل الشمالي', 'shipping_cost' => 50, 'estimated_days_min' => 2, 'estimated_days_max' => 3, 'sort_order' => 4],
            
            // الدلتا
            ['name_ar' => 'البحيرة', 'name_en' => 'Beheira', 'code' => 'BHR', 'region' => 'الدلتا', 'shipping_cost' => 55, 'estimated_days_min' => 2, 'estimated_days_max' => 4, 'sort_order' => 5],
            ['name_ar' => 'الغربية', 'name_en' => 'Gharbia', 'code' => 'GHR', 'region' => 'الدلتا', 'shipping_cost' => 55, 'estimated_days_min' => 2, 'estimated_days_max' => 4, 'sort_order' => 6],
            ['name_ar' => 'كفر الشيخ', 'name_en' => 'Kafr El Sheikh', 'code' => 'KFS', 'region' => 'الدلتا', 'shipping_cost' => 55, 'estimated_days_min' => 2, 'estimated_days_max' => 4, 'sort_order' => 7],
            ['name_ar' => 'الدقهلية', 'name_en' => 'Dakahlia', 'code' => 'DKH', 'region' => 'الدلتا', 'shipping_cost' => 55, 'estimated_days_min' => 2, 'estimated_days_max' => 4, 'sort_order' => 8],
            ['name_ar' => 'دمياط', 'name_en' => 'Damietta', 'code' => 'DMT', 'region' => 'الدلتا', 'shipping_cost' => 55, 'estimated_days_min' => 2, 'estimated_days_max' => 4, 'sort_order' => 9],
            ['name_ar' => 'الشرقية', 'name_en' => 'Sharqia', 'code' => 'SHR', 'region' => 'الدلتا', 'shipping_cost' => 50, 'estimated_days_min' => 2, 'estimated_days_max' => 3, 'sort_order' => 10],
            ['name_ar' => 'المنوفية', 'name_en' => 'Monufia', 'code' => 'MNF', 'region' => 'الدلتا', 'shipping_cost' => 50, 'estimated_days_min' => 2, 'estimated_days_max' => 3, 'sort_order' => 11],
            
            // القناة
            ['name_ar' => 'بورسعيد', 'name_en' => 'Port Said', 'code' => 'PTS', 'region' => 'القناة', 'shipping_cost' => 55, 'estimated_days_min' => 2, 'estimated_days_max' => 4, 'sort_order' => 12],
            ['name_ar' => 'الإسماعيلية', 'name_en' => 'Ismailia', 'code' => 'ISM', 'region' => 'القناة', 'shipping_cost' => 55, 'estimated_days_min' => 2, 'estimated_days_max' => 4, 'sort_order' => 13],
            ['name_ar' => 'السويس', 'name_en' => 'Suez', 'code' => 'SUZ', 'region' => 'القناة', 'shipping_cost' => 55, 'estimated_days_min' => 2, 'estimated_days_max' => 4, 'sort_order' => 14],
            
            // صعيد مصر
            ['name_ar' => 'الفيوم', 'name_en' => 'Fayoum', 'code' => 'FYM', 'region' => 'الصعيد', 'shipping_cost' => 60, 'estimated_days_min' => 3, 'estimated_days_max' => 5, 'sort_order' => 15],
            ['name_ar' => 'بني سويف', 'name_en' => 'Beni Suef', 'code' => 'BNS', 'region' => 'الصعيد', 'shipping_cost' => 60, 'estimated_days_min' => 3, 'estimated_days_max' => 5, 'sort_order' => 16],
            ['name_ar' => 'المنيا', 'name_en' => 'Minya', 'code' => 'MNY', 'region' => 'الصعيد', 'shipping_cost' => 65, 'estimated_days_min' => 3, 'estimated_days_max' => 5, 'sort_order' => 17],
            ['name_ar' => 'أسيوط', 'name_en' => 'Asyut', 'code' => 'AST', 'region' => 'الصعيد', 'shipping_cost' => 70, 'estimated_days_min' => 3, 'estimated_days_max' => 5, 'sort_order' => 18],
            ['name_ar' => 'سوهاج', 'name_en' => 'Sohag', 'code' => 'SHG', 'region' => 'الصعيد', 'shipping_cost' => 70, 'estimated_days_min' => 3, 'estimated_days_max' => 5, 'sort_order' => 19],
            ['name_ar' => 'قنا', 'name_en' => 'Qena', 'code' => 'QNA', 'region' => 'الصعيد', 'shipping_cost' => 75, 'estimated_days_min' => 4, 'estimated_days_max' => 6, 'sort_order' => 20],
            ['name_ar' => 'الأقصر', 'name_en' => 'Luxor', 'code' => 'LXR', 'region' => 'الصعيد', 'shipping_cost' => 80, 'estimated_days_min' => 4, 'estimated_days_max' => 6, 'sort_order' => 21],
            ['name_ar' => 'أسوان', 'name_en' => 'Aswan', 'code' => 'ASW', 'region' => 'الصعيد', 'shipping_cost' => 85, 'estimated_days_min' => 4, 'estimated_days_max' => 7, 'sort_order' => 22],
            
            // البحر الأحمر وسيناء
            ['name_ar' => 'البحر الأحمر', 'name_en' => 'Red Sea', 'code' => 'RDS', 'region' => 'البحر الأحمر', 'shipping_cost' => 80, 'estimated_days_min' => 4, 'estimated_days_max' => 6, 'sort_order' => 23],
            ['name_ar' => 'شمال سيناء', 'name_en' => 'North Sinai', 'code' => 'NSN', 'region' => 'سيناء', 'shipping_cost' => 90, 'estimated_days_min' => 4, 'estimated_days_max' => 7, 'sort_order' => 24],
            ['name_ar' => 'جنوب سيناء', 'name_en' => 'South Sinai', 'code' => 'SSN', 'region' => 'سيناء', 'shipping_cost' => 90, 'estimated_days_min' => 4, 'estimated_days_max' => 7, 'sort_order' => 25],
            
            // المحافظات الحدودية
            ['name_ar' => 'مطروح', 'name_en' => 'Matrouh', 'code' => 'MTR', 'region' => 'الساحل الشمالي', 'shipping_cost' => 85, 'estimated_days_min' => 4, 'estimated_days_max' => 6, 'sort_order' => 26],
            ['name_ar' => 'الوادي الجديد', 'name_en' => 'New Valley', 'code' => 'NVL', 'region' => 'الصحراء الغربية', 'shipping_cost' => 100, 'estimated_days_min' => 5, 'estimated_days_max' => 7, 'sort_order' => 27],
        ];
        
        $now = now();
        foreach ($governorates as &$gov) {
            $gov['is_active'] = true;
            $gov['cash_on_delivery'] = true;
            $gov['created_at'] = $now;
            $gov['updated_at'] = $now;
        }
        
        DB::table('governorate_shipping_rates')->insert($governorates);
    }

    public function down(): void
    {
        Schema::dropIfExists('governorate_shipping_rates');
    }
};
