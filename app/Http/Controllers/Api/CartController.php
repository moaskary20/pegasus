<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCartItem;
use App\Models\Product;
use App\Models\StoreCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * محتويات السلة: دورات + منتجات (يتطلب مصادقة)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'courses' => [],
                'cart_products' => [],
                'courses_subtotal' => 0,
                'products_subtotal' => 0,
                'total' => 0,
            ], 200);
        }

        $courseIds = CourseCartItem::where('user_id', $user->id)->pluck('course_id')->all();
        $courses = Course::query()
            ->where('is_published', true)
            ->whereIn('id', $courseIds)
            ->with(['instructor:id,name', 'category:id,name'])
            ->get()
            ->sortBy(fn ($c) => array_search($c->id, $courseIds))
            ->values();

        $storeCart = StoreCart::with('product.category')->where('user_id', $user->id)->get();

        $courseList = $courses->map(fn (Course $c) => $this->formatCourse($c))->all();
        $cartProductsList = $storeCart->map(fn (StoreCart $item) => $this->formatCartProduct($item))->all();

        $coursesSubtotal = (float) $courses->sum(fn ($c) => (float) ($c->offer_price ?? $c->price ?? 0));
        $productsSubtotal = (float) $storeCart->sum(fn ($item) => $item->total);
        $total = $coursesSubtotal + $productsSubtotal;

        return response()->json([
            'courses' => $courseList,
            'cart_products' => $cartProductsList,
            'courses_subtotal' => round($coursesSubtotal, 2),
            'products_subtotal' => round($productsSubtotal, 2),
            'total' => round($total, 2),
        ]);
    }

    public function addCourse(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $course = Course::where('id', $id)->where('is_published', true)->first();
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }
        CourseCartItem::firstOrCreate(['user_id' => $user->id, 'course_id' => $course->id]);
        return response()->json(['success' => true]);
    }

    public function removeCourse(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        CourseCartItem::where('user_id', $user->id)->where('course_id', $id)->delete();
        return response()->json(['success' => true]);
    }

    public function addProduct(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $product = Product::where('id', $id)->where('is_active', true)->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        if (!$product->isInStock()) {
            return response()->json(['message' => 'Product out of stock'], 422);
        }
        $quantity = max(1, (int) $request->input('quantity', 1));
        if ($product->track_quantity && $product->quantity < $quantity) {
            $quantity = $product->quantity;
        }

        $existing = StoreCart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->whereNull('variant_id')
            ->first();

        if ($existing) {
            $newQty = $existing->quantity + $quantity;
            if ($product->track_quantity && $product->quantity < $newQty) {
                $newQty = $product->quantity;
            }
            $existing->update(['quantity' => $newQty]);
        } else {
            StoreCart::create([
                'user_id' => $user->id,
                'session_id' => null,
                'product_id' => $product->id,
                'variant_id' => null,
                'quantity' => $quantity,
            ]);
        }
        return response()->json(['success' => true]);
    }

    /**
     * إزالة عنصر من سلة المتجر (بالـ id الخاص بعنصر السلة)
     */
    public function removeProduct(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        StoreCart::where('user_id', $user->id)->where('id', $id)->delete();
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
            'cover_image' => $coverImage,
            'category' => $course->category ? ['id' => $course->category->id, 'name' => $course->category->name] : null,
            'instructor' => $course->instructor ? ['id' => $course->instructor->id, 'name' => $course->instructor->name] : null,
        ];
    }

    private function formatCartProduct(StoreCart $item): array
    {
        $product = $item->product;
        if (!$product) {
            return ['id' => $item->id, 'product_id' => $item->product_id, 'name' => '', 'slug' => '', 'main_image' => null, 'unit_price' => 0, 'quantity' => $item->quantity, 'total' => 0];
        }
        $unitPrice = (float) $item->unit_price;
        $total = (float) $item->total;

        return [
            'id' => $item->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'main_image' => $this->productImageUrl($product->main_image),
            'unit_price' => round($unitPrice, 2),
            'quantity' => (int) $item->quantity,
            'total' => round($total, 2),
            'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
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
