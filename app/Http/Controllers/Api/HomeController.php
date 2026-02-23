<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * بيانات الصفحة الرئيسية: دورات مميزة + أحدث الدورات + الأقسام مع دوراتها
     */
    public function __invoke(Request $request): JsonResponse
    {
        $topCourses = Course::query()
            ->where('is_published', true)
            ->with(['instructor:id,name', 'category:id,name'])
            ->orderByDesc('rating')
            ->orderByDesc('reviews_count')
            ->limit(10)
            ->get();

        $recentCourses = Course::query()
            ->where('is_published', true)
            ->with(['instructor:id,name', 'category:id,name'])
            ->latest()
            ->limit(10)
            ->get();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->withCount(['courses as published_courses_count' => fn ($q) => $q->where('is_published', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(10)
            ->get();

        $categoriesWithCourses = $categories->map(function (Category $cat) {
            $childIds = Category::query()->where('parent_id', $cat->id)->pluck('id')->all();
            $courses = Course::query()
                ->where('is_published', true)
                ->where(function ($q) use ($cat, $childIds) {
                    $q->where('category_id', $cat->id);
                    if (count($childIds) > 0) {
                        $q->orWhereIn('sub_category_id', $childIds);
                    }
                })
                ->with(['instructor:id,name', 'category:id,name'])
                ->orderByDesc('rating')
                ->limit(8)
                ->get();

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'published_courses_count' => (int) ($cat->published_courses_count ?? 0),
                'courses' => $courses->map(fn ($c) => $this->formatCourse($c)),
            ];
        });

        return response()->json([
            'top_courses' => $topCourses->map(fn ($c) => $this->formatCourse($c)),
            'recent_courses' => $recentCourses->map(fn ($c) => $this->formatCourse($c)),
            'categories' => $categoriesWithCourses,
            'wishlist_ids' => [],
        ]);
    }

    private function formatCourse(Course $course): array
    {
        $price = (float) ($course->offer_price ?? $course->price ?? 0);
        $originalPrice = (float) ($course->price ?? 0);
        $hasDiscount = $course->offer_price !== null && (float) $course->offer_price < $originalPrice;

        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'price' => round($price, 2),
            'original_price' => $hasDiscount ? round($originalPrice, 2) : null,
            'rating' => round((float) ($course->rating ?? 0), 1),
            'reviews_count' => (int) ($course->reviews_count ?? 0),
            'students_count' => (int) ($course->students_count ?? 0),
            'cover_image' => $course->cover_image,
            'category' => $course->category ? ['id' => $course->category->id, 'name' => $course->category->name] : null,
            'instructor' => $course->instructor ? ['id' => $course->instructor->id, 'name' => $course->instructor->name] : null,
        ];
    }
}
