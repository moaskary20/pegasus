<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\LessonAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * بث فيديو الدرس المرفوع عبر مسار التطبيق حتى لا يظهر رابط الملف المباشر في مصدر الصفحة.
 */
class LessonVideoStreamController extends Controller
{
    public function __invoke(Request $request, Course $course, Lesson $lesson, LessonAccessService $access): BinaryFileResponse|RedirectResponse
    {
        abort_unless((bool) $course->is_published, 404);
        abort_unless($lesson->section && (int) $lesson->section->course_id === (int) $course->id, 404);

        if ($lesson->isYoutubeVideo()) {
            abort(404);
        }

        $isDesignatedPreviewLesson = (int) ($course->preview_lesson_id ?? 0) === (int) $lesson->id;
        if (! $isDesignatedPreviewLesson) {
            abort_unless($access->canStreamLessonVideo($request->user(), $course, $lesson), 403);
        }

        $lesson->loadMissing('video');

        if ($lesson->video && $lesson->video->path) {
            return $this->streamFromDisk((string) ($lesson->video->disk ?? 'local'), $lesson->video->path);
        }

        if ($lesson->video_path) {
            return $this->streamFromDisk('public', $this->sanitizeRelativePath($lesson->video_path));
        }

        abort(404);
    }

    private function streamFromDisk(string $diskName, string $relativePath): BinaryFileResponse|RedirectResponse
    {
        $relativePath = $this->sanitizeRelativePath($relativePath);
        $disk = Storage::disk($diskName);

        if (! $disk->exists($relativePath)) {
            abort(404);
        }

        if (config("filesystems.disks.{$diskName}.driver") === 's3') {
            return redirect()->away($disk->temporaryUrl($relativePath, now()->addHours(2)));
        }

        $fullPath = $disk->path($relativePath);
        if (! is_file($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }

    private function sanitizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');
        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        return $path;
    }
}
