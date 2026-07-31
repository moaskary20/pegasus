<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\LessonAccessService;
use App\Services\LessonVideoStreamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * بث فيديو الدرس للموبايل عبر رابط موقّت — بدون كشف مسار التخزين في JSON.
 */
class LessonVideoStreamController extends Controller
{
    public function __invoke(
        Request $request,
        string $courseSlug,
        int $lessonId,
        LessonAccessService $access,
        LessonVideoStreamService $stream
    ): BinaryFileResponse|RedirectResponse {
        $hasValidSignature = $request->hasValidSignature();
        $user = $request->user('sanctum') ?? $request->user();

        if (! $hasValidSignature && ! $user) {
            abort(403);
        }

        $course = Course::query()
            ->where('is_published', true)
            ->where('slug', $courseSlug)
            ->firstOrFail();

        $lesson = Lesson::query()
            ->whereHas('section', fn ($q) => $q->where('course_id', $course->id))
            ->with(['video', 'section'])
            ->findOrFail($lessonId);

        if ($lesson->isYoutubeVideo()) {
            abort(404);
        }

        $isDesignatedPreviewLesson = (int) ($course->preview_lesson_id ?? 0) === (int) $lesson->id;

        if (! $hasValidSignature) {
            abort_unless($access->canStreamLessonVideo($user, $course, $lesson) || $isDesignatedPreviewLesson, 403);
        } elseif (! $isDesignatedPreviewLesson && $user) {
            // التوقيع صالح؛ إن وُجد مستخدم نعيد التحقق من الصلاحية عند الإمكان
            abort_unless($access->canStreamLessonVideo($user, $course, $lesson), 403);
        }

        return $stream->streamLessonFile($lesson);
    }
}
