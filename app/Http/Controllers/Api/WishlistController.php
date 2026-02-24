<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseWishlist;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    /**
     * قائمة المفضلة: دورات + منتجات (يتطلب مصادقة)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['courses' => [], 'products' => [], 'wishlist_course_ids' => [], 'wishlist_product_ids' => []], 200);
        }

        $courseIds = CourseWishlist::where('user_id', $user->id)->pluck('course_id')->all();
        $productIds = Wishlist::where('user_id', $user->id)->pluck('product_id')->all();

        $courses = Course::query()
            ->where('is_published', true)
            ->whereIn('id', $courseIds)
            ->with(['instructor:id,name', 'category:id,name'])
            ->get()
            ->sortBy(fn ($c) => array_search($c->id, $courseIds))
            ->values();

        $products = Product::query()
            ->active()
            ->whereIn('id', $productIds)
            ->with('category:id,name')
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $productIds))
            ->values();

        $courseList = $courses->map(fn (Course $c) => $this->formatCourse($c))->all();
        $productList = $products->map(fn (Product $p) => $this->formatProduct($p))->all();

        return response()->json([
            'courses' => $courseList,
            'products' => $productList,
            'wishlist_course_ids' => $courseIds,
            'wishlist_product_ids' => $productIds,
        ]);
    }

    public function addCourse(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        try {
            $course = Course::where('id', $id)->where('is_published', true)->first();
            if (!$course) {
                return response()->json(['message' => 'Course not found'], 404);
            }
            CourseWishlist::firstOrCreate(['user_id' => $user->id, 'course_id' => $course->id]);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('WishlistController::addCourse', ['id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    public function removeCourse(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        CourseWishlist::where('user_id', $user->id)->where('course_id', $id)->delete();
        return response()->json(['success' => true]);
    }

    public function addProduct(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        try {
            $product = Product::where('id', $id)->where('is_active', true)->first();
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }
            Wishlist::firstOrCreate(['user_id' => $user->id, 'product_id' => $product->id]);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('WishlistController::addProduct', ['id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    public function removeProduct(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        Wishlist::where('user_id', $user->id)->where('product_id', $id)->delete();
        return response()->json(['success' => true]);
    }

    private function formatCourse(Course $course): array
    {
        $price = (float) ($course->offer_price ?? $course->price ?? 0);
        $originalPrice = (float) ($course->price ?? 0);
        $hasDiscount = $course->offer_price !== null && (float) $course->offer_price < $originalPrice;
        $coverImage = $this->absoluteUrl($course->getRawOriginal('cover_image') ? 'storage/' . ltrim($course->getRawOriginal('cover_image'), '/') : $course->cover_image);

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

    private function formatProduct(Product $p): array
    {
        $comparePrice = (float) ($p->compare_price ?? 0);
        $price = (float) ($p->price ?? 0);
        $hasDiscount = $comparePrice > 0 && $comparePrice > $price;
        $mainImage = $this->productImageUrl($p->main_image);

        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'price' => round($price, 2),
            'compare_price' => $hasDiscount ? round($comparePrice, 2) : null,
            'main_image' => $mainImage,
            'category' => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
            'average_rating' => round((float) ($p->average_rating ?? 0), 1),
            'ratings_count' => (int) ($p->ratings_count ?? 0),
            'sales_count' => (int) ($p->sales_count ?? 0),
        ];
    }

    private function productImageUrl(?string $path): ?string
    {
        if (empty(trim($path ?? ''))) {
            return null;
        }
        $path = ltrim(trim($path), '/');
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        return asset('storage/' . $path);
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (empty(trim($path ?? ''))) {
            return null;
        }
        $path = ltrim(trim($path), '/');
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        $base = rtrim(config('app.url', request()->getSchemeAndHttpHost() ?? 'https://academypegasus.com'), '/');
        return $base . '/' . $path;
    }
}
