<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // تشغيل RoleSeeder أولاً
        $this->call(RoleSeeder::class);
        
        // تشغيل SampleDataSeeder لإنشاء البيانات التجريبية
        $this->call(SampleDataSeeder::class);
        
        // إضافة صور للدورات
        $this->call(CourseImagesSeeder::class);

        // بيانات المتجر (أقسام ومنتجات)
        $this->call(StoreSeeder::class);
    }
}
