<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StoreCart;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = (int) $request->query('category', 0);
        $subCategoryId = (int) $request->query('sub', 0);
        $minPrice = $request->filled('min_price') ? (float) $request->query('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->query('max_price') : null;
        $priceType = (string) $request->query('price_type', '');
        $sort = (string) $request->query('sort', 'newest');
        $featured = $request->boolean('featured');

        $query = Product::query()
            ->active()
            ->inStock()
            ->with(['category', 'images']);

        if ($subCategoryId > 0) {
            $query->where('category_id', $subCategoryId);
        } elseif ($categoryId > 0) {
            $childIds = ProductCategory::where('parent_id', $categoryId)->pluck('id');
            $query->where(function ($q) use ($categoryId, $childIds) {
                $q->where('category_id', $categoryId)
                    ->orWhereIn('category_id', $childIds);
            });
        }

        if ($featured) {
            $query->featured();
        }

        if ($priceType === 'free') {
            $query->where('price', '<=', 0);
        } elseif ($priceType === 'paid') {
            $query->where('price', '>', 0);
        }

        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        if ($sort === 'price_asc') {
            $query->orderBy('price');
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc('price');
        } elseif ($sort === 'rating') {
            $query->orderByDesc('average_rating')->orderByDesc('ratings_count');
        } elseif ($sort === 'popular') {
            $query->orderByDesc('sales_count');
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        $categoriesTree = ProductCategory::query()
            ->active()
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return view('pages.store.index', [
            'products' => $products,
            'categoriesTree' => $categoriesTree,
            'categoryId' => $categoryId,
            'subCategoryId' => $subCategoryId,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'priceType' => $priceType,
            'sort' => $sort,
            'featured' => $featured,
        ]);
    }

    public function show(Product $product): View|RedirectResponse
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load(['category', 'images', 'approvedReviews.user']);
        $product->increment('views_count');

        $user = auth()->user();
        $inWishlist = $user
            ? Wishlist::where('user_id', $user->id)->where('product_id', $product->id)->exists()
            : false;

        $sessionId = session()->getId();
        $cartItems = StoreCart::getCart($user?->id, $user ? null : $sessionId);
        $inCart = $cartItems->contains('product_id', $product->id);

        return view('pages.store.show', [
            'product' => $product,
            'inWishlist' => $inWishlist,
            'inCart' => $inCart,
        ]);
    }

    public function addToCart(Request $request, Product $product): RedirectResponse
    {
        if (!$product->is_active || !$product->isInStock()) {
            return redirect()->route('site.store')->with('notice', ['type' => 'error', 'message' => 'المنتج غير متوفر.']);
        }

        $quantity = max(1, (int) $request->input('quantity', 1));

        if ($product->track_quantity && $product->quantity < $quantity) {
            return redirect()->back()->with('notice', ['type' => 'error', 'message' => 'الكمية المتاحة غير كافية.']);
        }

        $user = auth()->user();
        $sessionId = session()->getId();

        $existing = StoreCart::query()
            ->where('product_id', $product->id)
            ->whereNull('variant_id')
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn ($q) => $q->where('session_id', $sessionId))
            ->first();

        if ($existing) {
            $newQty = $existing->quantity + $quantity;
            if ($product->track_quantity && $product->quantity < $newQty) {
                $newQty = $product->quantity;
            }
            $existing->update(['quantity' => $newQty]);
        } else {
            StoreCart::create([
                'user_id' => $user?->id,
                'session_id' => $user ? null : $sessionId,
                'product_id' => $product->id,
                'variant_id' => null,
                'quantity' => $quantity,
            ]);
        }

        return redirect()->route('site.cart')->with('notice', ['type' => 'success', 'message' => 'تمت إضافة المنتج إلى السلة.']);
    }
}
