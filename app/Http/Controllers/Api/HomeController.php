<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseWishlist;
use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class HomeController extends Controller
{
    /**
     * بيانات الصفحة الرئيسية: دورات مميزة + أحدث الدورات + الأقسام مع دوراتها
     * كل قسم يُحمّل بشكل مستقل حتى لا يفشل استعلام واحد في إفراغ كل البيانات.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $this->resolveOptionalSanctumUser($request);

        $topCourses = $this->loadTopCourses();
        $recentCourses = $this->loadRecentCourses();
        $categoriesWithCourses = $this->loadCategoriesWithCourses();
        $homeSlider = $this->getHomeSlider();
        $wishlistIds = $this->loadWishlistIds();
        $blogPosts = $this->loadBlogPosts();

        return response()->json([
            'home_slider' => $homeSlider,
            'top_courses' => $topCourses,
            'recent_courses' => $recentCourses,
            'categories' => $categoriesWithCourses,
            'blog_posts' => $blogPosts,
            'wishlist_ids' => array_values($wishlistIds),
        ]);
    }

    private function loadTopCourses(): array
    {
        try {
            $topCourses = Course::query()
                ->where('is_published', true)
                ->with(['instructor:id,name', 'category:id,name'])
                ->orderByDesc('rating')
                ->orderByDesc('reviews_count')
                ->limit(10)
                ->get();
            return $topCourses->map(fn ($c) => $this->formatCourse($c))->values()->all();
        } catch (\Throwable $e) {
            Log::warning('HomeController: loadTopCourses failed', ['message' => $e->getMessage()]);
            return [];
        }
    }

    private function loadRecentCourses(): array
    {
        try {
            $recentCourses = Course::query()
                ->where('is_published', true)
                ->with(['instructor:id,name', 'category:id,name'])
                ->latest()
                ->limit(10)
                ->get();
            return $recentCourses->map(fn ($c) => $this->formatCourse($c))->values()->all();
        } catch (\Throwable $e) {
            Log::warning('HomeController: loadRecentCourses failed', ['message' => $e->getMessage()]);
            return [];
        }
    }

    private function loadCategoriesWithCourses(): array
    {
        try {
            $categories = Category::query()
                ->whereNull('parent_id')
                ->where(fn ($q) => $q->where('is_active', true)->orWhereNull('is_active'))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(10)
                ->get();

            try {
                $categories->loadCount(['courses as published_courses_count' => fn ($q) => $q->where('is_published', true)]);
            } catch (\Throwable $e) {
                Log::warning('HomeController: categories withCount failed', ['message' => $e->getMessage()]);
            }

            return $categories->map(function (Category $cat) {
                $count = (int) ($cat->published_courses_count ?? 0);
                try {
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
                    $courseList = $courses->map(fn ($c) => $this->formatCourse($c))->values()->all();
                } catch (\Throwable $e) {
                    Log::warning('HomeController: category courses failed for cat ' . $cat->id, ['message' => $e->getMessage()]);
                    $courseList = [];
                }
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'published_courses_count' => $count,
                    'courses' => $courseList,
                ];
            })->values()->all();
        } catch (\Throwable $e) {
            Log::warning('HomeController: loadCategoriesWithCourses failed', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * عند إرسال Bearer token في الطلب نحدد المستخدم حتى يُرجَع wishlist_ids دون اشتراط middleware.
     */
    private function resolveOptionalSanctumUser(Request $request): void
    {
        $token = $request->bearerToken();
        if (empty($token)) {
            return;
        }
        $accessToken = PersonalAccessToken::findToken($token);
        if ($accessToken) {
            auth('sanctum')->setUser($accessToken->tokenable);
        }
    }

    private function loadBlogPosts(): array
    {
        try {
            $posts = BlogPost::query()
                ->published()
                ->with('author:id,name,avatar')
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->limit(6)
                ->get();

            return $posts->map(function ($post) {
                $coverUrl = null;
                if ($post->cover_image) {
                    $coverUrl = $this->absoluteCoverUrl('storage/' . ltrim($post->cover_image, '/'));
                }
                $authorAvatar = null;
                if ($post->author && $post->author->avatar) {
                    $authorAvatar = $this->absoluteCoverUrl('storage/' . ltrim($post->author->avatar, '/'));
                }
                return [
                    'id' => $post->id,
                    'title' => $post->title ?? '',
                    'slug' => $post->slug ?? '',
                    'excerpt' => $post->excerpt ?? '',
                    'cover_image' => $coverUrl,
                    'published_at' => $post->published_at?->toIso8601String(),
                    'author' => $post->author ? [
                        'id' => $post->author->id,
                        'name' => $post->author->name ?? '',
                        'avatar' => $authorAvatar,
                    ] : null,
                ];
            })->values()->all();
        } catch (\Throwable $e) {
            Log::warning('HomeController: loadBlogPosts failed', ['message' => $e->getMessage()]);
            return [];
        }
    }

    private function loadWishlistIds(): array
    {
        try {
            if (! auth('sanctum')->check()) {
                return [];
            }
            return CourseWishlist::where('user_id', auth('sanctum')->id())->pluck('course_id')->all();
        } catch (\Throwable $e) {
            Log::warning('HomeController: loadWishlistIds failed', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /** شرائح السلايدر من إعدادات الموقع (يُتحكم بها من لوحة التحكم) */
    private function getHomeSlider(): array
    {
        try {
            $rawSlides = PlatformSetting::get('site_home_slider', []);
        } catch (\Throwable $e) {
            return [];
        }
        if (is_string($rawSlides)) {
            $rawSlides = json_decode($rawSlides, true) ?: [];
        }
        $slides = collect(is_array($rawSlides) ? $rawSlides : [])
            ->filter(fn ($s) => is_array($s) && ! empty($s['image_path'] ?? '') && ((bool) ($s['is_active'] ?? true)))
            ->values()
            ->all();

        return array_map(function ($s) {
            $imagePath = ltrim((string) ($s['image_path'] ?? ''), '/');
            if ($imagePath === '') {
                return [
                    'image_url' => null,
                    'title' => (string) ($s['title'] ?? ''),
                    'subtitle' => (string) ($s['subtitle'] ?? ''),
                    'primary_text' => (string) ($s['primary_text'] ?? ''),
                    'primary_url' => (string) ($s['primary_url'] ?? ''),
                    'secondary_text' => (string) ($s['secondary_text'] ?? ''),
                    'secondary_url' => (string) ($s['secondary_url'] ?? ''),
                ];
            }
            $storagePath = str_starts_with($imagePath, 'storage/') ? $imagePath : 'storage/' . $imagePath;
            $imageUrl = $this->absoluteCoverUrl($storagePath);

            return [
                'image_url' => $imageUrl,
                'title' => (string) ($s['title'] ?? ''),
                'subtitle' => (string) ($s['subtitle'] ?? ''),
                'primary_text' => (string) ($s['primary_text'] ?? ''),
                'primary_url' => (string) ($s['primary_url'] ?? ''),
                'secondary_text' => (string) ($s['secondary_text'] ?? ''),
                'secondary_url' => (string) ($s['secondary_url'] ?? ''),
            ];
        }, $slides);
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

    /** رابط صورة غلاف الدورة (من الحقل الخام أو الـ accessor) */
    private function courseCoverImageUrl(Course $course): ?string
    {
        $raw = $course->getRawOriginal('cover_image');
        if (is_string($raw) && trim($raw) !== '') {
            $path = 'storage/' . ltrim($raw, '/');
            return $this->absoluteCoverUrl($path);
        }
        $fromAccessor = $course->cover_image;
        return $this->absoluteCoverUrl($fromAccessor);
    }

    /** تأكد من إرجاع رابط صورة مطلق للتطبيق */
    private function absoluteCoverUrl(?string $coverImage): ?string
    {
        if (empty($coverImage)) {
            return null;
        }
        $coverImage = trim($coverImage);
        if (str_starts_with($coverImage, 'http://') || str_starts_with($coverImage, 'https://')) {
            return $coverImage;
        }
        $base = rtrim(config('app.url', ''), '/');
        if ($base === '') {
            $base = request()->getSchemeAndHttpHost() ?? 'https://academypegasus.com';
        }
        return $base . '/' . ltrim($coverImage, '/');
    }
}
