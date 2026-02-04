<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class QuizController extends Controller
{
    public function show(Course $course, Lesson $lesson): View|RedirectResponse
    {
        abort_unless((bool) $course->is_published, 404);
        abort_unless($lesson->section && (int) $lesson->section->course_id === (int) $course->id, 404);

        $quiz = $lesson->quiz;
        if (!$quiz) {
            return redirect()
                ->route('site.course.lesson.show', [$course, $lesson])
                ->with('notice', ['type' => 'error', 'message' => 'لا يوجد اختبار لهذا الدرس.']);
        }

        if (!auth()->check()) {
            return redirect()
                ->route('site.course.lesson.show', [$course, $lesson])
                ->with('notice', ['type' => 'error', 'message' => 'يجب تسجيل الدخول لأداء الاختبار.']);
        }

        $enrollment = Enrollment::query()
            ->where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return redirect()
                ->route('site.course.lesson.show', [$course, $lesson])
                ->with('notice', ['type' => 'error', 'message' => 'يجب الاشتراك في الدورة لأداء الاختبار.']);
        }

        $quiz->load('questions');

        $previousAttempts = QuizAttempt::where('user_id', auth()->id())
            ->where('quiz_id', $quiz->id)
            ->count();

        $attemptInProgress = QuizAttempt::where('user_id', auth()->id())
            ->where('quiz_id', $quiz->id)
            ->whereNull('submitted_at')
            ->first();

        $lastSubmittedAttempt = QuizAttempt::where('user_id', auth()->id())
            ->where('quiz_id', $quiz->id)
            ->whereNotNull('submitted_at')
            ->latest()
            ->first();

        if ($quiz->max_attempts && $previousAttempts >= $quiz->max_attempts && !$attemptInProgress) {
            return view('courses.quiz-result', [
                'course' => $course,
                'lesson' => $lesson,
                'quiz' => $quiz,
                'attempt' => $lastSubmittedAttempt,
                'maxReached' => true,
            ]);
        }

        if ($attemptInProgress) {
            $attempt = $attemptInProgress;
        } elseif ($lastSubmittedAttempt && !$quiz->allow_retake) {
            return view('courses.quiz-result', [
                'course' => $course,
                'lesson' => $lesson,
                'quiz' => $quiz,
                'attempt' => $lastSubmittedAttempt,
                'maxReached' => false,
            ]);
        } else {
            $attempt = QuizAttempt::create([
                'user_id' => auth()->id(),
                'quiz_id' => $quiz->id,
                'started_at' => now(),
                'answers' => [],
                'attempt_number' => $previousAttempts + 1,
            ]);
        }

        if ($attempt->submitted_at) {
            return view('courses.quiz-result', [
                'course' => $course,
                'lesson' => $lesson,
                'quiz' => $quiz,
                'attempt' => $attempt,
                'maxReached' => false,
            ]);
        }

        $timeRemaining = null;
        if ($quiz->duration_minutes) {
            $startedAt = $attempt->started_at ?? now();
            $durationSeconds = $quiz->duration_minutes * 60;
            $elapsed = now()->diffInSeconds($startedAt);
            $timeRemaining = max(0, $durationSeconds - $elapsed);
        }

        return view('courses.quiz', [
            'course' => $course,
            'lesson' => $lesson,
            'quiz' => $quiz,
            'attempt' => $attempt,
            'timeRemaining' => $timeRemaining,
            'answers' => $attempt->answers ?? [],
        ]);
    }

    public function submit(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless((bool) $course->is_published, 404);
        abort_unless($lesson->section && (int) $lesson->section->course_id === (int) $course->id, 404);

        $quiz = $lesson->quiz;
        if (!$quiz || !auth()->check()) {
            return redirect()->route('site.course.lesson.show', [$course, $lesson]);
        }

        $enrollment = Enrollment::query()
            ->where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return redirect()->route('site.course.lesson.show', [$course, $lesson]);
        }

        $attempt = QuizAttempt::where('user_id', auth()->id())
            ->where('quiz_id', $quiz->id)
            ->whereNull('submitted_at')
            ->first();

        if (!$attempt) {
            return redirect()
                ->route('site.course.quiz.show', [$course, $lesson])
                ->with('notice', ['type' => 'error', 'message' => 'لم يتم العثور على محاولة نشطة.']);
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
            app(\App\Services\PointsService::class)->awardQuizPassed(auth()->user(), $quiz, $earnedCount, $totalQuestions);
        }

        $noticeType = $passed ? 'success' : 'warning';
        $noticeMsg = $passed
            ? 'تهانينا! نجحت في الاختبار. نقاطك: ' . number_format($score, 1) . '%'
            : 'لم تنجح في الاختبار. نقاطك: ' . number_format($score, 1) . '% (المطلوب: ' . $quiz->pass_percentage . '%)';

        return redirect()
            ->route('site.course.quiz.show', [$course, $lesson])
            ->with('notice', ['type' => $noticeType, 'message' => $noticeMsg]);
    }

    public function retake(Course $course, Lesson $lesson): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('site.course.lesson.show', [$course, $lesson]);
        }

        $quiz = $lesson->quiz;
        if (!$quiz || !$quiz->allow_retake) {
            return redirect()->route('site.course.lesson.show', [$course, $lesson]);
        }

        $previousAttempts = QuizAttempt::where('user_id', auth()->id())
            ->where('quiz_id', $quiz->id)
            ->count();

        if ($quiz->max_attempts && $previousAttempts >= $quiz->max_attempts) {
            return redirect()
                ->route('site.course.quiz.show', [$course, $lesson])
                ->with('notice', ['type' => 'error', 'message' => 'لقد استنفدت عدد المحاولات المسموح به.']);
        }

        QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'started_at' => now(),
            'answers' => [],
            'attempt_number' => $previousAttempts + 1,
        ]);

        return redirect()->route('site.course.quiz.show', [$course, $lesson]);
    }
}
