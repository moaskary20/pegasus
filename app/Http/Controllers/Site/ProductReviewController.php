<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\StoreOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        if (! $product->is_active) {
            abort(404);
        }

        if (! auth()->check()) {
            return redirect()
                ->route('site.store.product', $product)
                ->with('notice', ['type' => 'error', 'message' => 'يجب تسجيل الدخول لتقييم المنتج.']);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $existing = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('user_id', auth()->id())
            ->first();

        $isVerifiedPurchase = $this->hasPurchasedProduct(auth()->id(), $product->id);

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
                'user_id' => auth()->id(),
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'is_verified_purchase' => $isVerifiedPurchase,
                'is_approved' => true,
            ]);
            $message = 'تم إضافة تقييمك بنجاح.';
        }

        $product->updateRating();

        return redirect()
            ->route('site.store.product', $product)
            ->with('notice', ['type' => 'success', 'message' => $message])
            ->withFragment('reviews');
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
