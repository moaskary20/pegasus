<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * بث فيديو معاينة الدورة المرفوع كملف (preview_video_path) دون إظهار مسار التخزين في HTML.
 */
class CoursePreviewVideoStreamController extends Controller
{
    public function __invoke(Course $course): BinaryFileResponse
    {
        abort_unless((bool) $course->is_published, 404);

        $raw = $course->getRawOriginal('preview_video_path');
        $path = str_replace('\\', '/', trim((string) $raw));
        $path = ltrim($path, '/');
        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            abort(404);
        }

        $fullPath = $disk->path($path);
        if (! is_file($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }
}
