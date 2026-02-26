<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * GET quiz: questions (without correct_answer), duration_minutes, pass_percentage, attempt info.
     * Create attempt if needed.
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
            ->with('quiz')
            ->find($lessonId);

        if (!$lesson) {
            return response()->json(['message' => 'Lesson not found'], 404);
        }

        $quiz = $lesson->quiz;
        if (!$quiz) {
            return response()->json(['message' => 'No quiz for this lesson'], 404);
        }

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Must be enrolled to take quiz'], 403);
        }

        $quiz->load('questions');
        $previousAttempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->count();

        $attemptInProgress = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->whereNull('submitted_at')
            ->first();

        $lastSubmittedAttempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->whereNotNull('submitted_at')
            ->latest()
            ->first();

        if ($quiz->max_attempts && $previousAttempts >= $quiz->max_attempts && !$attemptInProgress) {
            return response()->json([
                'max_reached' => true,
                'quiz' => $this->formatQuiz($quiz, []),
                'last_attempt' => $lastSubmittedAttempt ? $this->formatAttempt($lastSubmittedAttempt) : null,
            ]);
        }

        if ($attemptInProgress) {
            $attempt = $attemptInProgress;
        } elseif ($lastSubmittedAttempt && !$quiz->allow_retake) {
            return response()->json([
                'already_submitted' => true,
                'quiz' => $this->formatQuiz($quiz, []),
                'attempt' => $this->formatAttempt($lastSubmittedAttempt),
            ]);
        } else {
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'started_at' => now(),
                'answers' => [],
                'attempt_number' => $previousAttempts + 1,
            ]);
        }

        if ($attempt->submitted_at) {
            return response()->json([
                'already_submitted' => true,
                'quiz' => $this->formatQuiz($quiz, []),
                'attempt' => $this->formatAttempt($attempt),
            ]);
        }

        $timeRemaining = null;
        if ($quiz->duration_minutes) {
            $startedAt = $attempt->started_at ?? now();
            $durationSeconds = $quiz->duration_minutes * 60;
            $elapsed = now()->diffInSeconds($startedAt);
            $timeRemaining = max(0, (int) ($durationSeconds - $elapsed));
        }

        $questions = $quiz->questions->map(fn ($q) => [
            'id' => $q->id,
            'type' => $q->type,
            'question_text' => $q->question_text,
            'options' => $q->options,
            'points' => (float) ($q->points ?? 1),
            'sort_order' => (int) ($q->sort_order ?? 0),
            // correct_answer excluded for client
        ])->values()->all();

        return response()->json([
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title ?? '',
                'duration_minutes' => (int) ($quiz->duration_minutes ?? 0),
                'pass_percentage' => (float) ($quiz->pass_percentage ?? 0),
                'allow_retake' => (bool) ($quiz->allow_retake ?? false),
                'max_attempts' => $quiz->max_attempts ? (int) $quiz->max_attempts : null,
                'questions' => $questions,
            ],
            'attempt' => [
                'id' => $attempt->id,
                'attempt_number' => (int) $attempt->attempt_number,
                'started_at' => $attempt->started_at?->toIso8601String(),
                'time_remaining_seconds' => $timeRemaining,
                'answers' => $attempt->answers ?? [],
            ],
        ]);
    }

    /**
     * POST submit answers. Returns score, passed, result JSON.
     */
    public function submit(Request $request, string $courseSlug, int $lessonId): JsonResponse
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
            ->with('quiz')
            ->find($lessonId);

        if (!$lesson || !$lesson->quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Must be enrolled'], 403);
        }

        $quiz = $lesson->quiz;
        $attempt = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->whereNull('submitted_at')
            ->first();

        if (!$attempt) {
            return response()->json(['message' => 'No active attempt found'], 400);
        }

        $answers = $request->input('answers', []);
        if (!is_array($answers)) {
            $answers = [];
        }

        $quiz->load('questions');
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($quiz->questions as $question) {
            $totalPoints += (float) ($question->points ?? 1);
            $userAnswer = $answers[$question->id] ?? null;

            if ($userAnswer !== null && $userAnswer !== '') {
                $correctAnswer = $question->correct_answer;

                switch ($question->type) {
                    case 'mcq':
                    case 'true_false':
                        if (is_array($correctAnswer) && in_array($userAnswer, $correctAnswer)) {
                            $earnedPoints += (float) ($question->points ?? 1);
                        }
                        break;

                    case 'fill_blank':
                    case 'short_answer':
                        if (is_array($correctAnswer)) {
                            $userAnswerLower = strtolower(trim((string) $userAnswer));
                            foreach ($correctAnswer as $correct) {
                                if (strtolower(trim((string) $correct)) === $userAnswerLower) {
                                    $earnedPoints += (float) ($question->points ?? 1);
                                    break;
                                }
                            }
                        }
                        break;

                    case 'matching':
                        if (is_array($correctAnswer) && is_string($userAnswer)) {
                            $userAnswerArray = json_decode($userAnswer, true);
                            if (is_array($userAnswerArray)) {
                                $matches = 0;
                                foreach ($correctAnswer as $key => $value) {
                                    if (isset($userAnswerArray[$key]) && (string) $userAnswerArray[$key] === (string) $value) {
                                        $matches++;
                                    }
                                }
                                if ($matches === count($correctAnswer)) {
                                    $earnedPoints += (float) ($question->points ?? 1);
                                }
                            }
                        }
                        break;
                }
            }
        }

        $score = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        $passed = $score >= (float) $quiz->pass_percentage;

        $attempt->update([
            'answers' => $answers,
            'score' => $score,
            'passed' => $passed,
            'submitted_at' => now(),
        ]);

        if ($passed) {
            $totalQuestions = $quiz->questions->count();
            $earnedCount = (int) round(($score / 100) * $totalQuestions);
            app(\App\Services\PointsService::class)->awardQuizPassed($user, $quiz, $earnedCount, $totalQuestions);
        }

        return response()->json([
            'score' => round($score, 1),
            'passed' => $passed,
            'pass_percentage' => (float) $quiz->pass_percentage,
            'attempt' => $this->formatAttempt($attempt),
        ]);
    }

    /**
     * POST create new attempt (retake).
     */
    public function retake(string $courseSlug, int $lessonId): JsonResponse
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
            ->with('quiz')
            ->find($lessonId);

        if (!$lesson || !$lesson->quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $quiz = $lesson->quiz;
        if (!$quiz->allow_retake) {
            return response()->json(['message' => 'Retake not allowed'], 422);
        }

        $previousAttempts = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->count();

        if ($quiz->max_attempts && $previousAttempts >= $quiz->max_attempts) {
            return response()->json(['message' => 'Max attempts reached'], 422);
        }

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'started_at' => now(),
            'answers' => [],
            'attempt_number' => $previousAttempts + 1,
        ]);

        return response()->json([
            'success' => true,
            'attempt' => [
                'id' => $attempt->id,
                'attempt_number' => (int) $attempt->attempt_number,
                'started_at' => $attempt->started_at->toIso8601String(),
                'time_remaining_seconds' => $quiz->duration_minutes ? $quiz->duration_minutes * 60 : null,
            ],
        ]);
    }

    private function formatQuiz(Quiz $quiz, array $questions): array
    {
        return [
            'id' => $quiz->id,
            'title' => $quiz->title ?? '',
            'duration_minutes' => (int) ($quiz->duration_minutes ?? 0),
            'pass_percentage' => (float) ($quiz->pass_percentage ?? 0),
            'allow_retake' => (bool) ($quiz->allow_retake ?? false),
            'max_attempts' => $quiz->max_attempts ? (int) $quiz->max_attempts : null,
            'questions' => $questions,
        ];
    }

    private function formatAttempt(QuizAttempt $attempt): array
    {
        return [
            'id' => $attempt->id,
            'score' => $attempt->score !== null ? round((float) $attempt->score, 1) : null,
            'passed' => (bool) $attempt->passed,
            'submitted_at' => $attempt->submitted_at?->toIso8601String(),
            'attempt_number' => (int) $attempt->attempt_number,
        ];
    }
}
