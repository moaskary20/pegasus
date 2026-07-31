<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * بث فيديوهات الدروس عبر مسارات التطبيق دون كشف مسار التخزين المباشر.
 */
class LessonVideoStreamService
{
    public function hasStreamableFile(Lesson $lesson): bool
    {
        $lesson->loadMissing('video');

        if ($lesson->isYoutubeVideo()) {
            return false;
        }

        if ($lesson->video && ! empty($lesson->video->path) && empty($lesson->video->hls_path)) {
            return true;
        }

        return ! empty($lesson->video_path);
    }

    public function hasHls(Lesson $lesson): bool
    {
        $lesson->loadMissing('video');

        return (bool) ($lesson->video && ! empty($lesson->video->hls_path));
    }

    /**
     * رابط بث الموقع (جلسة ويب) — لا يكشف مسار الملف.
     */
    public function siteStreamUrl(Course $course, Lesson $lesson): ?string
    {
        if (! $this->hasStreamableFile($lesson)) {
            return null;
        }

        return route('site.course.lesson.video.stream', [$course, $lesson]);
    }

    /**
     * رابط بث موقّت للموبايل/API (لا يحتاج Authorization في مشغّل الفيديو).
     */
    public function temporaryApiStreamUrl(Course $course, Lesson $lesson, int $hours = 2): ?string
    {
        if (! $this->hasStreamableFile($lesson)) {
            return null;
        }

        return URL::temporarySignedRoute(
            'api.courses.lessons.video.stream',
            now()->addHours(max(1, $hours)),
            [
                'courseSlug' => $course->slug,
                'lessonId' => $lesson->id,
            ]
        );
    }

    public function streamLessonFile(Lesson $lesson): BinaryFileResponse|RedirectResponse
    {
        $lesson->loadMissing('video');

        if ($lesson->video && $lesson->video->path && empty($lesson->video->hls_path)) {
            return $this->streamFromDisk((string) ($lesson->video->disk ?? 'local'), $lesson->video->path);
        }

        if ($lesson->video_path) {
            return $this->streamFromDisk('public', $this->sanitizeRelativePath($lesson->video_path));
        }

        abort(404);
    }

    public function streamFromDisk(string $diskName, string $relativePath): BinaryFileResponse|RedirectResponse
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

        return response()->file($fullPath, [
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
        ]);
    }

    public function sanitizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');
        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        return $path;
    }

    /**
     * هل المسار يبدو ملف فيديو؟ يُستخدم لمنع التحميل المباشر عبر /storage.
     */
    public static function isVideoStoragePath(string $path): bool
    {
        $path = strtolower($path);
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        return in_array($ext, [
            'mp4', 'webm', 'mov', 'm4v', 'mkv', 'avi', 'mpeg', 'mpg', 'm3u8', 'ts',
        ], true);
    }
}
