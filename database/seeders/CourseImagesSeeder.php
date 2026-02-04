<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CourseImagesSeeder extends Seeder
{
    /**
     * صور افتراضية لكل دورة - يمكن استبدالها بصورك الخاصة
     * ضع صورك في: database/seeders/course-images/
     * بأسماء: 1.jpg, 2.jpg, 3.jpg, 4.jpg ... أو programming.jpg, design.jpg, marketing.jpg, business.jpg
     */
    public function run(): void
    {
        $seedImagesPath = database_path('seeders/course-images');
        $storagePath = 'courses/covers';

        // إنشاء المجلد في storage إذا لم يكن موجوداً
        if (!Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->makeDirectory($storagePath);
        }

        // ألوان مميزة لكل نوع دورة
        $courseThemes = [
            'programming' => ['bg' => [74, 144, 217], 'name' => 'البرمجة'],
            'design' => ['bg' => [155, 89, 182], 'name' => 'التصميم'],
            'marketing' => ['bg' => [46, 204, 113], 'name' => 'التسويق'],
            'business' => ['bg' => [241, 196, 15], 'name' => 'الأعمال'],
        ];

        $courses = Course::whereNull('cover_image')->orWhere('cover_image', '')->get();

        if ($courses->isEmpty()) {
            $courses = Course::all();
        }

        foreach ($courses as $index => $course) {
            $categorySlug = $course->category?->slug ?? 'default';
            $theme = $courseThemes[$categorySlug] ?? $courseThemes['programming'];

            // التحقق من وجود صورة مخصصة في مجلد seed
            $customImage = $this->findCustomImage($seedImagesPath, $course, $index);

            if ($customImage) {
                $savedPath = $this->copyCustomImage($customImage, $storagePath, $course->id);
                if ($savedPath) {
                    $course->update(['cover_image' => $savedPath]);
                    $this->command->info("تم استخدام الصورة المخصصة للدورة: {$course->title}");
                    continue;
                }
            }

            // إنشاء صورة افتراضية برمجياً
            $imagePath = $this->createPlaceholderImage($course, $theme, $storagePath);
            if ($imagePath) {
                $course->update(['cover_image' => $imagePath]);
                $this->command->info("تم إنشاء صورة للدورة: {$course->title}");
            }
        }

        $this->command->info('تم تحديث صور الدورات بنجاح!');
    }

    /**
     * البحث عن صورة مخصصة في مجلد seed
     */
    private function findCustomImage(string $seedPath, Course $course, int $index): ?string
    {
        if (!File::isDirectory($seedPath)) {
            return null;
        }

        $possibleNames = [
            ($index + 1) . '.jpg',
            ($index + 1) . '.png',
            ($index + 1) . '.webp',
            $course->category?->slug . '.jpg',
            $course->category?->slug . '.png',
            $course->category?->slug . '.webp',
            Str::slug($course->title) . '.jpg',
            Str::slug($course->title) . '.png',
        ];

        foreach ($possibleNames as $name) {
            $path = $seedPath . '/' . $name;
            if (File::exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * نسخ الصورة المخصصة إلى storage
     */
    private function copyCustomImage(string $sourcePath, string $storagePath, int $courseId): ?string
    {
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = 'course-' . $courseId . '-' . time() . '.' . $extension;
        $destination = $storagePath . '/' . $filename;

        try {
            $content = File::get($sourcePath);
            Storage::disk('public')->put($destination, $content);
            return $destination;
        } catch (\Exception $e) {
            $this->command->warn("فشل نسخ الصورة: " . $e->getMessage());
            return null;
        }
    }

    /**
     * إنشاء صورة placeholder برمجياً
     */
    private function createPlaceholderImage(Course $course, array $theme, string $storagePath): ?string
    {
        if (!extension_loaded('gd')) {
            $this->command->warn('امتداد GD غير متوفر - تم تخطي إنشاء الصور');
            return null;
        }

        $width = 800;
        $height = 450;

        $image = imagecreatetruecolor($width, $height);
        if (!$image) {
            return null;
        }

        [$r, $g, $b] = $theme['bg'];
        $bgColor = imagecolorallocate($image, $r, $g, $b);
        imagefill($image, 0, 0, $bgColor);

        // إضافة نص بسيط (العنوان بالإنجليزية أو رقم الدورة)
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $label = 'Course #' . $course->id;
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($label);
        $x = (int)(($width - $textWidth) / 2);
        $y = (int)(($height - imagefontheight($fontSize)) / 2);
        imagestring($image, $fontSize, $x, $y, $label, $textColor);

        // حفظ الصورة
        $filename = 'course-' . $course->id . '-' . time() . '.jpg';
        $fullPath = storage_path('app/public/' . $storagePath . '/' . $filename);

        if (!File::isDirectory(dirname($fullPath))) {
            File::makeDirectory(dirname($fullPath), 0755, true);
        }

        $saved = imagejpeg($image, $fullPath, 85);
        imagedestroy($image);

        return $saved ? $storagePath . '/' . $filename : null;
    }
}
