<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class InstructorController extends Controller
{
    /**
     * عرض ملف المدرب ودوراته
     * GET /api/instructors/{id}
     */
    public function show(int $id): JsonResponse
    {
        $instructor = User::find($id);
        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found'], 404);
        }

        $hasPublishedCourses = Course::query()
            ->where('user_id', $instructor->id)
            ->where('is_published', true)
            ->exists();

        if (!$hasPublishedCourses) {
            return response()->json(['message' => 'Instructor not found'], 404);
        }

        $courses = Course::query()
            ->where('user_id', $instructor->id)
            ->where('is_published', true)
            ->with(['category:id,name', 'subCategory:id,name'])
            ->orderByDesc('rating')
            ->orderByDesc('created_at')
            ->get();

        $coursesList = $courses->map(fn (Course $c) => [
            'id' => $c->id,
            'title' => $c->title,
            'slug' => $c->slug,
            'cover_image' => $c->cover_image ? asset('storage/' . ltrim($c->cover_image, '/')) : null,
            'price' => round((float) ($c->offer_price ?? $c->price ?? 0), 2),
            'original_price' => $c->offer_price ? round((float) ($c->price ?? 0), 2) : null,
            'rating' => round((float) ($c->rating ?? 0), 1),
            'reviews_count' => (int) ($c->reviews_count ?? 0),
            'students_count' => (int) ($c->students_count ?? 0),
            'category' => $c->category ? ['id' => $c->category->id, 'name' => $c->category->name] : null,
        ])->all();

        $totalStudents = (int) $courses->sum('students_count');

        return response()->json([
            'instructor' => [
                'id' => $instructor->id,
                'name' => $instructor->name,
                'avatar' => $instructor->avatar ? asset('storage/' . ltrim($instructor->avatar, '/')) : null,
                'bio' => $instructor->bio ?? null,
            ],
            'courses' => $coursesList,
            'courses_count' => count($coursesList),
            'total_students' => $totalStudents,
        ]);
    }
}
