<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\StoreOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductRatingController extends Controller
{
    /**
     * تقييم منتج (يتطلب مصادقة)
     * POST /api/store/products/{id}/rate
     * body: rating (1-5), comment (اختياري)
     */
    public function store(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $product = Product::where('id', $id)->where('is_active', true)->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $existing = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        $isVerifiedPurchase = $this->hasPurchasedProduct($user->id, $product->id);

        if ($existing) {
            $existing->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'is_verified_purchase' => $isVerifiedPurchase,
            ]);
            $message = 'تم تحديث تقييمك بنجاح.';
        } else {
            ProductReview::create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'is_verified_purchase' => $isVerifiedPurchase,
                'is_approved' => true,
            ]);
            $message = 'تم إضافة تقييمك بنجاح.';
        }

        $product->updateRating();

        return response()->json([
            'success' => true,
            'message' => $message,
            'average_rating' => round((float) ($product->fresh()->average_rating ?? 0), 1),
            'ratings_count' => (int) ($product->fresh()->ratings_count ?? 0),
        ]);
    }

    protected function hasPurchasedProduct(int $userId, int $productId): bool
    {
        return StoreOrder::query()
            ->where('user_id', $userId)
            ->where('payment_status', StoreOrder::PAYMENT_PAID)
            ->whereNotIn('status', [StoreOrder::STATUS_CANCELLED, StoreOrder::STATUS_REFUNDED])
            ->whereHas('items', fn ($q) => $q->where('product_id', $productId))
            ->exists();
    }
}
