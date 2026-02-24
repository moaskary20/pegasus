<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    /**
     * قائمة الدورات حسب التصنيف (category_id واختياري sub للفرعي)
     */
    public function index(Request $request): JsonResponse
    {
        $categoryId = (int) $request->query('category', 0);
        $subCategoryId = (int) $request->query('sub', 0);

        $query = Course::query()
            ->where('is_published', true)
            ->with(['instructor:id,name', 'category:id,name']);

        if ($subCategoryId > 0) {
            $query->where(function ($q) use ($subCategoryId) {
                $q->where('category_id', $subCategoryId)->orWhere('sub_category_id', $subCategoryId);
            });
        } elseif ($categoryId > 0) {
            $childIds = Category::query()->where('parent_id', $categoryId)->pluck('id')->all();
            $query->where(function ($q) use ($categoryId, $childIds) {
                $q->where('category_id', $categoryId);
                if (count($childIds) > 0) {
                    $q->orWhereIn('sub_category_id', $childIds);
                }
            });
        }

        $courses = $query->orderByDesc('rating')->orderByDesc('reviews_count')->latest()->get();

        $list = $courses->map(fn (Course $c) => $this->formatCourse($c))->values()->all();

        return response()->json(['courses' => $list]);
    }

    /**
     * تفاصيل دورة واحدة بالـ slug (لشاشة تفاصيل الدورة)
     */
    public function show(string $slug): JsonResponse
    {
        $course = Course::query()
            ->where('is_published', true)
            ->where('slug', $slug)
            ->with(['instructor:id,name,avatar', 'category:id,name', 'subCategory:id,name'])
            ->withCount(['lessons'])
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $lessons = $course->lessons()->get();
        $totalMinutes = (int) $lessons->sum(fn ($l) => (int) ($l->duration_minutes ?? 0));
        $hours = (int) ($course->hours ?? 0) ?: (int) round($totalMinutes / 60);

        $price = (float) ($course->offer_price ?? $course->price ?? 0);
        $originalPrice = (float) ($course->price ?? 0);
        $hasDiscount = $course->offer_price !== null && (float) $course->offer_price < $originalPrice;

        $data = [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'description' => $course->description ?? '',
            'objectives' => $course->objectives ?? '',
            'cover_image' => $this->courseCoverImageUrl($course),
            'preview_video_url' => $course->preview_youtube_url ?: $this->previewVideoUrl($course),
            'price' => round($price, 2),
            'original_price' => $hasDiscount ? round($originalPrice, 2) : null,
            'rating' => round((float) ($course->rating ?? 0), 1),
            'reviews_count' => (int) ($course->reviews_count ?? 0),
            'students_count' => (int) ($course->students_count ?? 0),
            'hours' => $hours,
            'lessons_count' => (int) $course->lessons_count,
            'category' => $course->category ? ['id' => $course->category->id, 'name' => $course->category->name] : null,
            'instructor' => $course->instructor ? [
                'id' => $course->instructor->id,
                'name' => $course->instructor->name,
                'avatar' => $course->instructor->avatar ? asset('storage/' . ltrim($course->instructor->avatar, '/')) : null,
            ] : null,
        ];

        return response()->json($data);
    }

    private function previewVideoUrl(Course $course): ?string
    {
        $path = $course->getRawOriginal('preview_video_path');
        if (is_string($path) && trim($path) !== '') {
            return $this->absoluteUrl('storage/' . ltrim($path, '/'));
        }
        return null;
    }

    private function formatCourse(Course $course): array
    {
        $price = (float) ($course->offer_price ?? $course->price ?? 0);
        $originalPrice = (float) ($course->price ?? 0);
        $hasDiscount = $course->offer_price !== null && (float) $course->offer_price < $originalPrice;
        $coverImage = $this->courseCoverImageUrl($course);

        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'price' => round($price, 2),
            'original_price' => $hasDiscount ? round($originalPrice, 2) : null,
            'rating' => round((float) ($course->rating ?? 0), 1),
            'reviews_count' => (int) ($course->reviews_count ?? 0),
            'students_count' => (int) ($course->students_count ?? 0),
            'cover_image' => $coverImage,
            'category' => $course->category ? ['id' => $course->category->id, 'name' => $course->category->name] : null,
            'instructor' => $course->instructor ? ['id' => $course->instructor->id, 'name' => $course->instructor->name] : null,
        ];
    }

    private function courseCoverImageUrl(Course $course): ?string
    {
        $raw = $course->getRawOriginal('cover_image');
        if (is_string($raw) && trim($raw) !== '') {
            return $this->absoluteUrl('storage/' . ltrim($raw, '/'));
        }
        $accessor = $course->cover_image;
        return $accessor ? $this->absoluteUrl($accessor) : null;
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (empty(trim($path ?? ''))) {
            return null;
        }
        $path = ltrim(trim($path), '/');
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $base = rtrim(config('app.url', request()->getSchemeAndHttpHost() ?? 'https://academypegasus.com'), '/');
        return $base . '/' . $path;
    }

    /**
     * تصنيفات الدورات من إدارة الدورات التدريبية (Category)
     * التصنيفات الرئيسية مع الفرعية وعدد الدورات المنشورة
     */
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $data = $categories->map(function (Category $cat) {
            $children = $cat->children->map(function (Category $child) {
                $count = Course::query()->where('is_published', true)
                    ->where(function ($q) use ($child) {
                        $q->where('category_id', $child->id)->orWhere('sub_category_id', $child->id);
                    })->count();
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'image' => $child->image_url,
                    'icon' => $child->icon,
                    'published_courses_count' => $count,
                ];
            })->values()->all();

            $childIds = $cat->children->pluck('id')->all();
            $directCount = Course::query()->where('is_published', true)->where('category_id', $cat->id)->count();
            $subCount = $childIds
                ? Course::query()->where('is_published', true)
                    ->where(function ($q) use ($childIds) {
                        $q->whereIn('category_id', $childIds)->orWhereIn('sub_category_id', $childIds);
                    })->count()
                : 0;
            $publishedCoursesCount = $directCount + $subCount;

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'image' => $cat->image_url,
                'icon' => $cat->icon,
                'description' => $cat->description,
                'published_courses_count' => $publishedCoursesCount,
                'children' => $children,
            ];
        })->values()->all();

        return response()->json(['categories' => $data]);
    }
}
