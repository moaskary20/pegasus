<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use App\Models\SubmissionFile;
use App\Notifications\AssignmentSubmittedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyAssignmentsController extends Controller
{
    /**
     * قائمة واجبات المستخدم (دورات مسجل فيها) مع إحصائيات (للموبايل).
     */
    public function index(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $enrolledCourseIds = Enrollment::where('user_id', $user->id)->pluck('course_id');

        $assignments = Assignment::query()
            ->whereIn('course_id', $enrolledCourseIds)
            ->where('is_published', true)
            ->with(['course:id,title,slug', 'lesson:id,title', 'submissions' => fn ($q) => $q->where('user_id', $user->id)->orderByDesc('created_at')->limit(1)])
            ->orderByDesc('created_at')
            ->get();

        $pending = $assignments->filter(fn ($a) => $a->submissions->isEmpty() || $a->submissions->last()?->status === 'resubmit_requested');
        $submitted = $assignments->filter(fn ($a) => $a->submissions->isNotEmpty() && $a->submissions->last()?->status === 'submitted');
        $graded = $assignments->filter(fn ($a) => $a->submissions->isNotEmpty() && $a->submissions->last()?->status === 'graded');

        $items = $assignments->map(function (Assignment $a) use ($user) {
            $lastSubmission = $a->submissions->first();
            return [
                'id' => $a->id,
                'title' => $a->title,
                'type' => $a->type ?? 'assignment',
                'due_date' => $a->due_date?->toIso8601String(),
                'max_score' => (int) ($a->max_score ?? 0),
                'course_id' => $a->course_id,
                'course_title' => $a->course?->title ?? '',
                'course_slug' => $a->course?->slug ?? '',
                'lesson_title' => $a->lesson?->title ?? '',
                'status' => $this->assignmentStatus($a),
                'last_submission_status' => $lastSubmission?->status,
                'score' => $lastSubmission && $lastSubmission->status === 'graded' ? $lastSubmission->score : null,
            ];
        })->values()->all();

        return response()->json([
            'assignments' => $items,
            'stats' => [
                'total' => $assignments->count(),
                'pending' => $pending->count(),
                'submitted' => $submitted->count(),
                'graded' => $graded->count(),
            ],
        ]);
    }

    private function assignmentStatus(Assignment $a): string
    {
        if ($a->submissions->isEmpty()) {
            return 'pending';
        }
        $last = $a->submissions->last();
        if ($last->status === 'graded') {
            return 'graded';
        }
        if ($last->status === 'submitted') {
            return 'submitted';
        }
        if ($last->status === 'resubmit_requested') {
            return 'resubmit_requested';
        }
        return 'pending';
    }

    /**
     * تفاصيل واجب واحد (للموبايل — لعرض نموذج التسليم).
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $assignment = Assignment::query()
            ->where('is_published', true)
            ->with(['course:id,title,slug', 'lesson:id,title'])
            ->find($id);

        if (! $assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $assignment->course_id)
            ->exists();

        if (! $isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }

        $lastSubmission = $assignment->submissions()
            ->where('user_id', $user->id)
            ->with('files')
            ->latest()
            ->first();

        return response()->json([
            'id' => $assignment->id,
            'title' => $assignment->title,
            'description' => $assignment->description ?? '',
            'instructions' => $assignment->instructions ?? '',
            'type' => $assignment->type ?? 'assignment',
            'max_score' => (int) ($assignment->max_score ?? 0),
            'due_date' => $assignment->due_date?->toIso8601String(),
            'is_overdue' => $assignment->isOverdue(),
            'allowed_file_types' => $assignment->allowed_file_types ?? [],
            'max_file_size_mb' => (float) ($assignment->max_file_size_mb ?? 10),
            'can_submit' => $assignment->canSubmit($user),
            'course_title' => $assignment->course?->title ?? '',
            'course_slug' => $assignment->course?->slug ?? '',
            'lesson_title' => $assignment->lesson?->title ?? '',
            'last_submission' => $lastSubmission ? [
                'id' => $lastSubmission->id,
                'status' => $lastSubmission->status,
                'score' => $lastSubmission->score,
                'feedback' => $lastSubmission->feedback,
                'submitted_at' => $lastSubmission->submitted_at?->toIso8601String(),
                'files' => $lastSubmission->files->map(fn ($f) => [
                    'id' => $f->id,
                    'file_name' => $f->file_name,
                    'file_path' => $f->file_path,
                ])->values()->all(),
            ] : null,
        ]);
    }

    /**
     * تسليم واجب (للموبايل).
     * POST multipart: content (string), files[] (file)
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $assignment = Assignment::query()
            ->where('is_published', true)
            ->find($id);

        if (! $assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        if (! $assignment->canSubmit($user)) {
            return response()->json([
                'message' => 'لا يمكنك تسليم هذا الواجب. تأكد من التسجيل في الدورة ومواعيد التسليم.',
            ], 403);
        }

        $content = trim((string) $request->input('content', ''));
        $files = $request->file('files');
        if (! is_array($files)) {
            $files = $files ? [$files] : [];
        }
        $files = array_values(array_filter($files));

        if (empty($content) && empty($files)) {
            return response()->json([
                'message' => 'أضف محتوى نصياً أو ملفات للتسليم',
            ], 422);
        }

        $maxSizeMb = (float) ($assignment->max_file_size_mb ?? 10);
        $allowedTypes = $assignment->allowed_file_types ?? ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
        foreach ($files as $file) {
            if ($file->getSize() > $maxSizeMb * 1024 * 1024) {
                return response()->json([
                    'message' => "حجم الملف {$file->getClientOriginalName()} يتجاوز الحد المسموح ({$maxSizeMb} ميجابايت)",
                ], 422);
            }
            $ext = strtolower($file->getClientOriginalExtension());
            if (! empty($allowedTypes) && ! in_array($ext, $allowedTypes, true)) {
                return response()->json([
                    'message' => "نوع الملف {$file->getClientOriginalName()} غير مسموح. الأنواع: " . implode(', ', $allowedTypes),
                ], 422);
            }
        }

        $attemptNumber = $assignment->submissions()
            ->where('user_id', $user->id)
            ->count() + 1;

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => $content,
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_late' => $assignment->isOverdue(),
            'attempt_number' => $attemptNumber,
        ]);

        foreach ($files as $file) {
            $path = $file->store('submissions/' . $submission->id, 'public');
            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);
        }

        try {
            $submission->load(['assignment.course.instructor', 'user']);
            $submission->assignment->course?->instructor?->notify(new AssignmentSubmittedNotification($submission));
        } catch (\Throwable $e) {
            // ignore notification errors
        }

        return response()->json([
            'message' => 'تم تسليم الواجب بنجاح',
            'submission_id' => $submission->id,
        ], 201);
    }
}
