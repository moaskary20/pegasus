<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\JsonResponse;

class CoursesController extends Controller
{
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
