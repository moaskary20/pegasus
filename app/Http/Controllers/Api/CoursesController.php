<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoursesController extends Controller
{
    /**
     * قائمة الدورات حسب التصنيف. Query params: min_rating, price_type (free/paid), min_price, max_price, sort (newest/rating/price_asc/price_desc)
     */
    public function index(Request $request): JsonResponse
    {
        $categoryId = (int) $request->query('category', 0);
        $subCategoryId = (int) $request->query('sub', 0);
        $minRating = (float) $request->query('min_rating', 0);
        $priceType = (string) $request->query('price_type', '');
        $minPrice = $request->filled('min_price') ? (float) $request->query('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->query('max_price') : null;
        $sort = (string) $request->query('sort', 'newest');

        $priceExpr = DB::raw('COALESCE(offer_price, price)');

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

        if ($minRating > 0) {
            $query->minRating($minRating);
        }

        if ($priceType === 'free') {
            $query->where($priceExpr, '<=', 0);
        } elseif ($priceType === 'paid') {
            $query->where($priceExpr, '>', 0);
        }

        if ($minPrice !== null) {
            $query->where($priceExpr, '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where($priceExpr, '<=', $maxPrice);
        }

        if ($sort === 'rating') {
            $query->orderByDesc('rating')->orderByDesc('reviews_count');
        } elseif ($sort === 'price_asc') {
            $query->orderBy($priceExpr);
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc($priceExpr);
        } else {
            $query->latest();
        }

        $courses = $query->get();

        $list = $courses->map(fn (Course $c) => $this->formatCourse($c))->values()->all();

        return response()->json(['courses' => $list]);
    }

    /**
     * تفاصيل دورة واحدة بالـ slug (لشاشة تفاصيل الدورة)
     * يشمل: التصنيف الفرعي، الإعلان، المستوى، الأقسام والدروس
     * عندما المستخدم مسجّل: is_enrolled, progress_percentage, lesson_progress_map, related_courses, ratings
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $course = Course::query()
            ->where('is_published', true)
            ->where('slug', $slug)
            ->with([
                'instructor:id,name,avatar',
                'category:id,name',
                'subCategory:id,name',
                'sections' => fn ($q) => $q->orderBy('sort_order')->with(['lessons' => fn ($q2) => $q2->orderBy('sort_order')->with('zoomMeeting')]),
            ])
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

        $levelMap = [
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
        ];
        $level = $course->level ?? 'beginner';
        $levelLabel = $levelMap[$level] ?? $level;

        $previewUrl = $course->preview_youtube_url ?: $this->previewVideoUrl($course);
        if ($previewUrl && !str_starts_with((string) $previewUrl, 'http')) {
            $previewUrl = 'https://www.youtube.com/watch?v=' . ltrim((string) $previewUrl, '/');
        }

        $sections = $course->sections->map(function ($section) {
            return [
                'id' => $section->id,
                'title' => $section->title ?? '',
                'sort_order' => (int) ($section->sort_order ?? 0),
                'lessons' => $section->lessons->map(function ($lesson) {
                    $zm = (bool) ($lesson->has_zoom_meeting ?? false) && $lesson->zoomMeeting ? $lesson->zoomMeeting : null;
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title ?? '',
                        'duration_minutes' => (int) ($lesson->duration_minutes ?? 0),
                        'is_free_preview' => (bool) ($lesson->is_free_preview ?? false),
                        'sort_order' => (int) ($lesson->sort_order ?? 0),
                        'has_quiz' => $lesson->quiz()->exists(),
                        'has_zoom_meeting' => $zm !== null,
                        'zoom_join_url' => $zm?->join_url,
                        'zoom_scheduled_at' => $zm?->scheduled_start_time?->toIso8601String(),
                        'zoom_duration' => $zm ? (int) ($zm->duration ?? 0) : null,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $data = [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'description' => $course->description ?? '',
            'objectives' => $course->objectives ?? '',
            'announcement' => $course->announcement ? trim((string) $course->announcement) : null,
            'level' => $level,
            'level_label' => $levelLabel,
            'cover_image' => $this->courseCoverImageUrl($course),
            'preview_video_url' => $previewUrl,
            'price' => round($price, 2),
            'original_price' => $hasDiscount ? round($originalPrice, 2) : null,
            'price_once' => $course->price_once !== null ? round((float) $course->price_once, 2) : null,
            'price_monthly' => $course->price_monthly !== null ? round((float) $course->price_monthly, 2) : null,
            'price_daily' => $course->price_daily !== null ? round((float) $course->price_daily, 2) : null,
            'rating' => round((float) ($course->rating ?? 0), 1),
            'reviews_count' => (int) ($course->reviews_count ?? 0),
            'students_count' => (int) ($course->students_count ?? 0),
            'hours' => $hours,
            'lessons_count' => (int) $course->lessons_count,
            'category' => $course->category ? ['id' => $course->category->id, 'name' => $course->category->name] : null,
            'sub_category' => $course->subCategory ? ['id' => $course->subCategory->id, 'name' => $course->subCategory->name] : null,
            'instructor' => $course->instructor ? [
                'id' => $course->instructor->id,
                'name' => $course->instructor->name,
                'avatar' => $course->instructor->avatar ? asset('storage/' . ltrim($course->instructor->avatar, '/')) : null,
            ] : null,
            'sections' => $sections,
        ];

        $user = $request->user('sanctum');
        if ($user) {
            $enrollment = $course->enrollments()->where('user_id', $user->id)->first();
            $data['is_enrolled'] = $enrollment !== null;
            $data['progress_percentage'] = $enrollment ? (float) ($enrollment->progress_percentage ?? 0) : 0;
            $data['completed_at'] = $enrollment?->completed_at?->toIso8601String();

            $lessonIds = $lessons->pluck('id')->all();
            $progresses = \App\Models\VideoProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->get()
                ->keyBy('lesson_id');

            $lessonProgressMap = [];
            foreach ($progresses as $lessonId => $vp) {
                $lessonProgressMap[$lessonId] = [
                    'completed' => (bool) $vp->completed,
                    'last_position' => (int) ($vp->last_position_seconds ?? 0),
                ];
            }
            $data['lesson_progress_map'] = $lessonProgressMap;

            $relatedCourses = Course::query()
                ->where('is_published', true)
                ->where('id', '!=', $course->id)
                ->when($course->category_id, fn ($q) => $q->where('category_id', $course->category_id))
                ->with(['instructor:id,name', 'category:id,name'])
                ->orderByDesc('rating')
                ->limit(6)
                ->get();

            $data['related_courses'] = $relatedCourses->map(fn (Course $c) => $this->formatCourse($c))->values()->all();

            $ratings = $course->ratings()
                ->latest()
                ->with('user:id,name,avatar')
                ->limit(20)
                ->get();

            $data['ratings'] = $ratings->map(function ($r) {
                $avatar = null;
                if ($r->user && $r->user->avatar) {
                    $avatar = asset('storage/' . ltrim($r->user->avatar, '/'));
                }
                return [
                    'user_name' => $r->user?->name ?? 'مستخدم',
                    'stars' => (int) $r->stars,
                    'review' => $r->review ?? null,
                    'created_at' => $r->created_at?->toIso8601String(),
                    'avatar' => $avatar,
                ];
            })->values()->all();
        }

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
