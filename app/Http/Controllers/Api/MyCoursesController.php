<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyCoursesController extends Controller
{
    /**
     * قائمة دورات المستخدم المسجل فيها (للموبايل).
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $enrollments = Enrollment::query()
            ->where('user_id', $user->id)
            ->with(['course' => fn ($q) => $q->with(['instructor:id,name', 'category:id,name'])])
            ->orderByDesc('enrolled_at')
            ->get();

        $items = $enrollments->map(function (Enrollment $e) {
            $course = $e->course;
            return [
                'id' => $e->id,
                'course_id' => $course?->id,
                'title' => $course?->title ?? '',
                'slug' => $course?->slug ?? '',
                'cover_image' => $course?->cover_image,
                'instructor_name' => $course?->instructor?->name ?? '',
                'category_name' => $course?->category?->name ?? '',
                'hours' => (float) ($course?->hours ?? 0),
                'progress_percentage' => (float) ($e->progress_percentage ?? 0),
                'enrolled_at' => $e->enrolled_at?->toIso8601String(),
                'completed_at' => $e->completed_at?->toIso8601String(),
                'subscription_type' => $e->subscription_type ?? 'once',
                'access_expires_at' => $e->access_expires_at?->toIso8601String(),
            ];
        })->values()->all();

        $totalCourses = $enrollments->count();
        $completedCount = $enrollments->filter(fn ($e) => $e->completed_at !== null)->count();
        $inProgressCount = $totalCourses - $completedCount;
        $avgProgress = $totalCourses > 0
            ? round($enrollments->avg(fn ($e) => (float) ($e->progress_percentage ?? 0)), 1)
            : 0;
        $totalHours = $enrollments->sum(fn ($e) => (float) ($e->course->hours ?? 0));

        return response()->json([
            'enrollments' => $items,
            'total_courses' => $totalCourses,
            'completed_count' => $completedCount,
            'in_progress_count' => $inProgressCount,
            'avg_progress' => $avgProgress,
            'total_hours' => $totalHours,
        ]);
    }
}
