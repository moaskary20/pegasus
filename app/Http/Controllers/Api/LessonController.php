<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\VideoProgress;
use App\Services\LessonAccessService;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function __construct(
        protected LessonAccessService $accessService
    ) {}

    /**
     * GET lesson details for mobile: id, title, video_url, duration_minutes, is_free_preview, prev_lesson, next_lesson, can_access
     */
    public function show(string $courseSlug, int $lessonId): JsonResponse
    {
        $course = Course::query()
            ->where('is_published', true)
            ->where('slug', $courseSlug)
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $lesson = Lesson::query()
            ->whereHas('section', fn ($q) => $q->where('course_id', $course->id))
            ->with(['video', 'section', 'files', 'zoomMeeting'])
            ->find($lessonId);

        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 404);
        }

        $user = auth('sanctum')->user();
        $isEnrolled = $user && $course->enrollments()->where('user_id', $user->id)->exists();
        $canAccess = false;

        if ($isEnrolled && $user) {
            $canAccess = $this->accessService->canAccessLesson($user, $lesson);
        } else {
            $canAccess = (bool) ($lesson->is_free ?? false) || (bool) ($lesson->is_free_preview ?? false);
        }

        if (!$canAccess) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $course->load([
            'sections' => fn ($q) => $q->orderBy('sort_order')->with(['lessons' => fn ($q2) => $q2->orderBy('sort_order')]),
        ]);
        $allLessons = $course->sections->flatMap(fn ($s) => $s->lessons->sortBy('sort_order'))->values();
        $idx = $allLessons->search(fn ($l) => (int) $l->id === (int) $lesson->id);

        $prevLesson = null;
        $nextLesson = null;
        if ($idx !== false && $idx > 0) {
            $prev = $allLessons[$idx - 1];
            $prevLesson = ['id' => $prev->id, 'title' => $prev->title ?? ''];
        }
        if ($idx !== false && $idx < $allLessons->count() - 1) {
            $next = $allLessons[$idx + 1];
            $nextLesson = ['id' => $next->id, 'title' => $next->title ?? ''];
        }

        $videoUrl = $lesson->video_url;
        if ($lesson->isYoutubeVideo()) {
            $videoUrl = $lesson->youtube_embed_url;
        } elseif ($lesson->video) {
            if ($lesson->video->hls_path) {
                $videoUrl = $lesson->video->hls_path;
            } elseif ($lesson->video->path) {
                $videoUrl = asset('storage/' . ltrim($lesson->video->path, '/'));
            }
        } elseif ($lesson->video_path) {
            $videoUrl = asset('storage/' . ltrim($lesson->video_path, '/'));
        }

        $baseUrl = rtrim(config('app.url', request()->getSchemeAndHttpHost()), '/');
        if ($videoUrl && !str_starts_with($videoUrl, 'http')) {
            $videoUrl = $baseUrl . '/' . ltrim($videoUrl, '/');
        }

        $contentType = $lesson->content_type ?? '';
        $hasContent = in_array($contentType, ['text', 'mixed']) && !empty(trim((string) ($lesson->content ?? '')));

        $files = $lesson->files->map(function ($f) use ($baseUrl) {
            $url = $f->path ? asset('storage/' . ltrim($f->path, '/')) : null;
            if ($url && !str_starts_with((string) $url, 'http')) {
                $url = $baseUrl . '/' . ltrim((string) $url, '/');
            }
            return [
                'id' => $f->id,
                'name' => $f->name ?? '',
                'url' => $url,
                'size' => (int) ($f->size ?? 0),
            ];
        })->values()->all();

        $zoomMeeting = null;
        if ((bool) ($lesson->has_zoom_meeting ?? false) && $lesson->zoomMeeting) {
            $zm = $lesson->zoomMeeting;
            $zoomMeeting = [
                'join_url' => $zm->join_url ?? null,
                'scheduled_start_time' => $zm->scheduled_start_time?->toIso8601String(),
                'duration' => (int) ($zm->duration ?? 0),
                'topic' => $zm->topic ?? null,
            ];
        }

        $data = [
            'id' => $lesson->id,
            'title' => $lesson->title ?? '',
            'video_url' => $videoUrl,
            'duration_minutes' => (int) ($lesson->duration_minutes ?? 0),
            'is_free_preview' => (bool) ($lesson->is_free_preview ?? false),
            'prev_lesson' => $prevLesson,
            'next_lesson' => $nextLesson,
            'can_access' => $canAccess,
            'has_quiz' => $lesson->quiz()->exists(),
            'content' => $hasContent ? $lesson->content : null,
            'content_type' => $contentType ?: null,
            'files' => $files,
            'zoom_meeting' => $zoomMeeting,
        ];

        return response()->json($data);
    }

    /**
     * POST save progress (position, duration). Reuses logic from web route.
     */
    public function saveProgress(Request $request, string $courseSlug, int $lessonId): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['ok' => false], 401);
        }

        $course = Course::query()->where('is_published', true)->where('slug', $courseSlug)->first();
        if (!$course) {
            return response()->json(['ok' => false], 404);
        }

        $lesson = Lesson::query()
            ->whereHas('section', fn ($q) => $q->where('course_id', $course->id))
            ->find($lessonId);

        if (!$lesson) {
            return response()->json(['ok' => false], 404);
        }

        $enrollment = $course->enrollments()->where('user_id', $user->id)->exists();
        if (!$enrollment) {
            return response()->json(['ok' => false], 403);
        }

        if (!$this->accessService->canAccessLesson($user, $lesson)) {
            return response()->json(['ok' => false], 403);
        }

        $position = (int) ($request->input('position') ?? $request->json('position') ?? 0);
        $duration = (int) ($request->input('duration') ?? $request->json('duration') ?? 0);

        $progress = VideoProgress::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['last_position_seconds' => 0, 'completed' => false, 'watch_time_minutes' => 0]
        );

        $wasCompleted = (bool) $progress->completed;
        $isCompleted = $duration > 0 && $position >= (int) ($duration * 0.9);

        $progress->update([
            'last_position_seconds' => $position,
            'last_watched_at' => now(),
            'completed' => $isCompleted,
        ]);

        if ($isCompleted && !$wasCompleted) {
            app(PointsService::class)->awardLessonCompleted($user, $lesson);
        }

        $enrollmentModel = $course->enrollments()->where('user_id', $user->id)->first();
        if ($enrollmentModel) {
            $course->load(['sections' => fn ($q) => $q->withCount('lessons')]);
            $totalLessons = $course->sections->sum('lessons_count');
            $completedCount = VideoProgress::where('user_id', $user->id)
                ->whereHas('lesson.section', fn ($q) => $q->where('course_id', $course->id))
                ->where('completed', true)
                ->count();
            $enrollmentModel->update([
                'progress_percentage' => $totalLessons > 0 ? ($completedCount / $totalLessons) * 100 : 0,
                'completed_at' => $completedCount >= $totalLessons ? now() : null,
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
