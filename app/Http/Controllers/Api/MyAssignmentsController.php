<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
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
}
