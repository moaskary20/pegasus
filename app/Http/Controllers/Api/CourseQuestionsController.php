<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\Lesson;
use App\Services\LessonAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseQuestionsController extends Controller
{
    public function __construct(
        protected LessonAccessService $accessService
    ) {}

    /**
     * GET questions for a lesson (Q&A). Requires access to lesson.
     */
    public function index(string $courseSlug, int $lessonId): JsonResponse
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

        $questions = CourseQuestion::query()
            ->where('lesson_id', $lesson->id)
            ->with(['user:id,name', 'answers' => fn ($q) => $q->with('user:id,name')->orderBy('created_at')])
            ->orderByDesc('created_at')
            ->get();

        $data = $questions->map(fn ($q) => [
            'id' => $q->id,
            'question' => $q->question,
            'user_name' => $q->user?->name ?? '',
            'is_answered' => $q->is_answered,
            'created_at' => $q->created_at?->toIso8601String(),
            'answers' => $q->answers->map(fn ($a) => [
                'id' => $a->id,
                'answer' => $a->answer,
                'user_name' => $a->user?->name ?? '',
                'created_at' => $a->created_at?->toIso8601String(),
            ])->values()->all(),
        ]);

        return response()->json(['questions' => $data]);
    }

    /**
     * POST add new question for a lesson. Auth required, must be enrolled.
     */
    public function store(Request $request, string $courseSlug, int $lessonId): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $course = Course::query()->where('is_published', true)->where('slug', $courseSlug)->first();
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $lesson = Lesson::query()
            ->whereHas('section', fn ($q) => $q->where('course_id', $course->id))
            ->find($lessonId);

        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 404);
        }

        $enrollment = $course->enrollments()->where('user_id', $user->id)->exists();
        if (!$enrollment) {
            return response()->json(['message' => 'Must be enrolled to ask questions'], 403);
        }

        if (!$this->accessService->canAccessLesson($user, $lesson)) {
            return response()->json(['message' => 'Access denied to this lesson'], 403);
        }

        $questionText = trim((string) ($request->input('question') ?? $request->json('question') ?? ''));
        if ($questionText === '') {
            return response()->json(['message' => 'Question is required'], 422);
        }

        $question = CourseQuestion::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'question' => $questionText,
            'is_answered' => false,
        ]);

        $course->load('instructor');
        $instructor = $course->instructor;
        if ($instructor && $instructor->id !== $user->id) {
            $instructor->notify(new \App\Notifications\CourseQuestionAnsweredNotification($question, 'new_question'));
        }

        return response()->json([
            'id' => $question->id,
            'question' => $question->question,
            'user_name' => $user->name,
            'is_answered' => false,
            'created_at' => $question->created_at?->toIso8601String(),
            'answers' => [],
        ], 201);
    }
}
