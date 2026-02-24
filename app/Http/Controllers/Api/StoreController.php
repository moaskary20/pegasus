<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
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
