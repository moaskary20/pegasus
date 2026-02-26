<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseRating;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseRatingController extends Controller
{
    /**
     * POST rate course: stars, review. Return JSON.
     */
    public function store(Request $request, string $courseSlug): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $course = Course::query()
            ->where('is_published', true)
            ->where('slug', $courseSlug)
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $isEnrolled = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'Must be enrolled to rate'], 403);
        }

        $validated = $request->validate([
            'stars' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);

        $rating = CourseRating::query()
            ->where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        if ($rating) {
            $rating->update($validated);
            $message = 'تم تحديث تقييمك بنجاح.';
        } else {
            CourseRating::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'stars' => $validated['stars'],
                'review' => $validated['review'] ?? null,
            ]);
            $message = 'تم إضافة تقييمك بنجاح.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'rating' => [
                'stars' => (int) $validated['stars'],
                'review' => $validated['review'] ?? null,
            ],
        ]);
    }
}
