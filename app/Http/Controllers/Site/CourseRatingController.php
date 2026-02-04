<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseRating;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseRatingController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless((bool) $course->is_published, 404);

        if (!auth()->check()) {
            return redirect()
                ->route('site.course.show', $course)
                ->with('notice', ['type' => 'error', 'message' => 'يجب تسجيل الدخول لتقييم الدورة.']);
        }

        $isEnrolled = Enrollment::query()
            ->where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->exists();

        if (!$isEnrolled) {
            return redirect()
                ->route('site.course.show', $course)
                ->with('notice', ['type' => 'error', 'message' => 'يجب الاشتراك في الدورة لتقييمها.']);
        }

        $validated = $request->validate([
            'stars' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);

        $rating = CourseRating::query()
            ->where('course_id', $course->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($rating) {
            $rating->update($validated);
            $message = 'تم تحديث تقييمك بنجاح.';
        } else {
            CourseRating::create([
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'stars' => $validated['stars'],
                'review' => $validated['review'] ?? null,
            ]);
            $message = 'تم إضافة تقييمك بنجاح.';
        }

        return redirect()
            ->route('site.course.show', $course)
            ->with('notice', ['type' => 'success', 'message' => $message])
            ->withFragment('reviews');
    }
}
