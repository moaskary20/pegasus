<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PlatformSetting;
use App\Models\Product;
use App\Models\StoreCart;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $buildId = now()->format('Ymd-His');

        // Stats (public)
        $stats = [
            'courses_count' => Course::where('is_published', true)->count(),
            'students_count' => User::whereHas('roles', fn ($q) => $q->where('name', 'student'))->count(),
            'products_count' => Product::active()->count(),
            'enrollments_count' => Enrollment::count(),
            'completed_count' => Enrollment::whereNotNull('completed_at')->count(),
        ];

        // Featured courses
        $featuredCourses = Course::query()
            ->where('is_published', true)
            ->with(['instructor', 'category'])
            ->orderByDesc('rating')
            ->limit(8)
            ->get();

        // New courses
        $newCourses = Course::query()
            ->where('is_published', true)
            ->with(['instructor', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        // Categories (with courses count for homepage)
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->withCount(['courses as published_courses_count' => fn ($q) => $q->where('is_published', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(10)
            ->get();

        // Top instructors (أشهر المدرسين)
        $topInstructors = User::query()
            ->whereHas('courses', fn ($q) => $q->where('is_published', true))
            ->withCount(['courses as published_courses_count' => fn ($q) => $q->where('is_published', true)])
            ->withSum('courses as total_students', 'students_count')
            ->orderByDesc('published_courses_count')
            ->orderByDesc('total_students')
            ->limit(12)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar' => $u->avatar_url,
                'courses_count' => (int) ($u->published_courses_count ?? 0),
                'students_count' => (int) ($u->total_students ?? 0),
            ]);

        // Featured products
        $featuredProducts = Product::query()
            ->with(['category', 'images'])
            ->active()
            ->featured()
            ->latest()
            ->limit(8)
            ->get();

        $productWishlistIds = auth()->check()
            ? Wishlist::where('user_id', auth()->id())->pluck('product_id')->toArray()
            : [];

        // Home slider
        $rawSlides = PlatformSetting::get('site_home_slider', []);
        if (is_string($rawSlides)) {
            $rawSlides = json_decode($rawSlides, true) ?: [];
        }
        $homeSlides = collect(is_array($rawSlides) ? $rawSlides : [])
            ->filter(fn ($s) => is_array($s) && ! empty($s['image_path'] ?? '') && ((bool) ($s['is_active'] ?? true)))
            ->values()
            ->all();

        // Quick links counts (cart, wishlist)
        $courseCartIds = session('cart', []);
        $courseCartCount = is_array($courseCartIds) ? count($courseCartIds) : 0;
        $sessionId = session()->getId();
        $storeCart = StoreCart::getCart(auth()->id(), auth()->check() ? null : $sessionId);
        $storeCartCount = $storeCart->count();
        $cartCount = $courseCartCount + $storeCartCount;

        $courseWishlistIds = session('course_wishlist', []);
        $courseWishlistCount = is_array($courseWishlistIds) ? count($courseWishlistIds) : 0;
        $storeWishlistCount = auth()->check() ? Wishlist::where('user_id', auth()->id())->count() : 0;
        $wishlistCount = $courseWishlistCount + $storeWishlistCount;

        $quickLinksData = [
            'cartCount' => $cartCount,
            'wishlistCount' => $wishlistCount,
            'unreadMessages' => 0,
            'unreadNotifications' => 0,
        ];

        if (auth()->check()) {
            $user = auth()->user();
            $quickLinksData['unreadMessages'] = $user->unread_messages_count ?? 0;
            $quickLinksData['unreadNotifications'] = $user->unreadNotifications()->count();
        }

        $data = [
            '__build_id' => $buildId,
            'stats' => $stats,
            'featuredCourses' => $featuredCourses,
            'newCourses' => $newCourses,
            'categories' => $categories,
            'topInstructors' => $topInstructors,
            'featuredProducts' => $featuredProducts,
            'productWishlistIds' => $productWishlistIds,
            'homeSlides' => $homeSlides,
            'quickLinksData' => $quickLinksData,
        ];

        return response()
            ->view('home', $data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('X-Pegasus-Home-Build', $buildId);
    }
}
