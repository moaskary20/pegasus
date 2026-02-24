<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * قائمة المنتجات حسب التصنيف (category واختياري sub للفرعي)
     */
    public function products(Request $request): JsonResponse
    {
        $categoryId = (int) $request->query('category', 0);
        $subCategoryId = (int) $request->query('sub', 0);

        $query = Product::query()
            ->active()
            ->inStock()
            ->with('category:id,name');

        if ($subCategoryId > 0) {
            $query->where('category_id', $subCategoryId);
        } elseif ($categoryId > 0) {
            $childIds = ProductCategory::where('parent_id', $categoryId)->pluck('id');
            $query->where(function ($q) use ($categoryId, $childIds) {
                $q->where('category_id', $categoryId)
                    ->orWhereIn('category_id', $childIds);
            });
        }

        $products = $query->latest()->get();

        $list = $products->map(fn (Product $p) => $this->formatProductListItem($p))->values()->all();

        return response()->json(['products' => $list]);
    }

    /**
     * تفاصيل منتج واحد بالـ slug
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->with(['category:id,name', 'images'])
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->increment('views_count');

        $mainImage = $this->productImageUrl($product->main_image);
        $images = $product->images->map(fn ($img) => $this->productImageUrl($img->image) ?? $img->url)->values()->all();
        if ($mainImage && !in_array($mainImage, $images)) {
            array_unshift($images, $mainImage);
        }
        if (empty($images) && $mainImage) {
            $images = [$mainImage];
        }

        $comparePrice = (float) ($product->compare_price ?? 0);
        $price = (float) ($product->price ?? 0);
        $hasDiscount = $comparePrice > 0 && $comparePrice > $price;
        $discountPercent = $hasDiscount ? round((($comparePrice - $price) / $comparePrice) * 100, 1) : null;

        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'short_description' => $product->short_description ?? '',
            'description' => $product->description ?? '',
            'price' => round($price, 2),
            'compare_price' => $hasDiscount ? round($comparePrice, 2) : null,
            'discount_percentage' => $discountPercent,
            'main_image' => $mainImage,
            'images' => $images,
            'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
            'average_rating' => round((float) ($product->average_rating ?? 0), 1),
            'ratings_count' => (int) ($product->ratings_count ?? 0),
            'sales_count' => (int) ($product->sales_count ?? 0),
            'quantity' => $product->track_quantity ? (int) $product->quantity : null,
            'track_quantity' => $product->track_quantity,
            'stock_status' => $product->stock_status,
            'stock_status_label' => $product->stock_status_label,
            'weight' => $product->weight ? (float) $product->weight : null,
            'dimensions' => $product->dimensions,
            'is_digital' => (bool) $product->is_digital,
            'requires_shipping' => (bool) $product->requires_shipping,
        ];

        return response()->json($data);
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

    private function formatProductListItem(Product $p): array
    {
        $comparePrice = (float) ($p->compare_price ?? 0);
        $price = (float) ($p->price ?? 0);
        $hasDiscount = $comparePrice > 0 && $comparePrice > $price;

        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'price' => round($price, 2),
            'compare_price' => $hasDiscount ? round($comparePrice, 2) : null,
            'main_image' => $this->productImageUrl($p->main_image),
            'category' => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
            'average_rating' => round((float) ($p->average_rating ?? 0), 1),
            'ratings_count' => (int) ($p->ratings_count ?? 0),
            'sales_count' => (int) ($p->sales_count ?? 0),
        ];
    }

    /**
     * تصنيفات المتجر من إدارة المتجر (ProductCategory)
     * تُرجع التصنيفات الرئيسية مع التصنيفات الفرعية وعدد المنتجات
     */
    public function categories(): JsonResponse
    {
        $categories = ProductCategory::query()
            ->active()
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $data = $categories->map(function (ProductCategory $cat) {
            $children = $cat->children->map(function (ProductCategory $child) {
                $productsCount = Product::query()->active()->where('category_id', $child->id)->count();
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'image' => $child->image ? asset('storage/' . ltrim($child->image, '/')) : null,
                    'products_count' => $productsCount,
                ];
            })->values()->all();

            $directCount = Product::query()->active()->where('category_id', $cat->id)->count();
            $childrenIds = $cat->children->pluck('id')->all();
            $subCount = $childrenIds
                ? Product::query()->active()->whereIn('category_id', $childrenIds)->count()
                : 0;
            $productsCount = $directCount + $subCount;

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'image' => $cat->image ? asset('storage/' . ltrim($cat->image, '/')) : null,
                'products_count' => $productsCount,
                'children' => $children,
            ];
        })->values()->all();

        return response()->json(['categories' => $data]);
    }
}
