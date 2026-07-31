<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\LessonAccessService;
use App\Services\LessonVideoStreamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * بث فيديو الدرس المرفوع عبر مسار التطبيق حتى لا يظهر رابط الملف المباشر في مصدر الصفحة.
 */
class LessonVideoStreamController extends Controller
{
    public function __invoke(
        Request $request,
        Course $course,
        Lesson $lesson,
        LessonAccessService $access,
        LessonVideoStreamService $stream
    ): BinaryFileResponse|RedirectResponse {
        abort_unless((bool) $course->is_published, 404);
        abort_unless($lesson->section && (int) $lesson->section->course_id === (int) $course->id, 404);

        if ($lesson->isYoutubeVideo()) {
            abort(404);
        }

        $isDesignatedPreviewLesson = (int) ($course->preview_lesson_id ?? 0) === (int) $lesson->id;
        if (! $isDesignatedPreviewLesson) {
            abort_unless($access->canStreamLessonVideo($request->user(), $course, $lesson), 403);
        }

        return $stream->streamLessonFile($lesson);
    }
}
