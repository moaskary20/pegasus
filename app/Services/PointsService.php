<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\PointTransaction;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * Award points to user
     */
    public function awardPoints(
        User $user,
        int $points,
        string $type,
        string $description,
        $pointable = null,
        array $metadata = []
    ): PointTransaction {
        return DB::transaction(function () use ($user, $points, $type, $description, $pointable, $metadata) {
            // Create transaction
            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'points' => $points,
                'type' => $type,
                'description' => $description,
                'pointable_type' => $pointable ? get_class($pointable) : null,
                'pointable_id' => $pointable?->id,
                'metadata' => $metadata,
            ]);

            // Update user points
            $user->increment('total_points', $points);
            $user->increment('available_points', $points);

            // Update rank
            $user->updateRank();

            return $transaction;
        });
    }

    /**
     * Deduct points from user
     */
    public function deductPoints(
        User $user,
        int $points,
        string $type,
        string $description,
        $pointable = null,
        array $metadata = []
    ): ?PointTransaction {
        if ($user->available_points < $points) {
            return null;
        }

        return DB::transaction(function () use ($user, $points, $type, $description, $pointable, $metadata) {
            // Create transaction (negative points)
            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'points' => -$points,
                'type' => $type,
                'description' => $description,
                'pointable_type' => $pointable ? get_class($pointable) : null,
                'pointable_id' => $pointable?->id,
                'metadata' => $metadata,
            ]);

            // Update user points
            $user->decrement('available_points', $points);

            return $transaction;
        });
    }

    /**
     * Award points for completing a lesson
     */
    public function awardLessonCompleted(User $user, Lesson $lesson): ?PointTransaction
    {
        // Check if already awarded
        $exists = PointTransaction::where('user_id', $user->id)
            ->where('type', PointTransaction::TYPE_LESSON_COMPLETED)
            ->where('pointable_type', Lesson::class)
            ->where('pointable_id', $lesson->id)
            ->exists();

        if ($exists) {
            return null;
        }

        return $this->awardPoints(
            $user,
            PointTransaction::POINTS_LESSON_COMPLETED,
            PointTransaction::TYPE_LESSON_COMPLETED,
            "إكمال درس: {$lesson->title}",
            $lesson,
            ['course_id' => $lesson->course_id]
        );
    }

    /**
     * Award points for passing a quiz
     */
    public function awardQuizPassed(User $user, $quiz, int $score, int $totalQuestions): ?PointTransaction
    {
        // Check if already awarded
        $exists = PointTransaction::where('user_id', $user->id)
            ->where('type', PointTransaction::TYPE_QUIZ_PASSED)
            ->where('pointable_type', get_class($quiz))
            ->where('pointable_id', $quiz->id)
            ->exists();

        if ($exists) {
            return null;
        }

        // Perfect score gets bonus points
        $points = ($score === $totalQuestions)
            ? PointTransaction::POINTS_QUIZ_PERFECT
            : PointTransaction::POINTS_QUIZ_PASSED;

        $description = ($score === $totalQuestions)
            ? "اجتياز اختبار بدرجة كاملة!"
            : "اجتياز اختبار بنجاح";

        return $this->awardPoints(
            $user,
            $points,
            PointTransaction::TYPE_QUIZ_PASSED,
            $description,
            $quiz,
            ['score' => $score, 'total' => $totalQuestions]
        );
    }

    /**
     * Award points for completing a course
     */
    public function awardCourseCompleted(User $user, $course): ?PointTransaction
    {
        // Check if already awarded
        $exists = PointTransaction::where('user_id', $user->id)
            ->where('type', PointTransaction::TYPE_COURSE_COMPLETED)
            ->where('pointable_type', get_class($course))
            ->where('pointable_id', $course->id)
            ->exists();

        if ($exists) {
            return null;
        }

        return $this->awardPoints(
            $user,
            PointTransaction::POINTS_COURSE_COMPLETED,
            PointTransaction::TYPE_COURSE_COMPLETED,
            "إكمال دورة: {$course->title}",
            $course
        );
    }

    /**
     * Award bonus points
     */
    public function awardBonus(User $user, int $points, string $reason): PointTransaction
    {
        return $this->awardPoints(
            $user,
            $points,
            PointTransaction::TYPE_BONUS,
            $reason
        );
    }

    /**
     * Redeem a reward
     */
    public function redeemReward(User $user, Reward $reward): ?RewardRedemption
    {
        // Check if reward is available
        if (!$reward->isAvailable()) {
            return null;
        }

        // Check if user has enough points
        if ($user->available_points < $reward->points_required) {
            return null;
        }

        return DB::transaction(function () use ($user, $reward) {
            // Deduct points
            $this->deductPoints(
                $user,
                $reward->points_required,
                PointTransaction::TYPE_REWARD_REDEEMED,
                "استبدال مكافأة: {$reward->name}",
                $reward
            );

            // Create redemption
            $redemption = RewardRedemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->points_required,
                'status' => RewardRedemption::STATUS_COMPLETED,
            ]);

            // Increment redeemed count
            $reward->increment('redeemed_count');

            // If it's a free course, enroll user
            if ($reward->type === Reward::TYPE_FREE_COURSE && $reward->course_id) {
                $this->enrollUserInCourse($user, $reward->course_id);
            }

            return $redemption;
        });
    }

    /**
     * Enroll user in course for free
     */
    protected function enrollUserInCourse(User $user, int $courseId): void
    {
        // Check if not already enrolled
        $exists = \App\Models\Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->exists();

        if (!$exists) {
            \App\Models\Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'enrolled_at' => now(),
            ]);
        }
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return User::orderByDesc('total_points')
            ->where('total_points', '>', 0)
            ->limit($limit)
            ->get();
    }

    /**
     * Get user rank position
     */
    public function getUserRankPosition(User $user): int
    {
        return User::where('total_points', '>', $user->total_points)->count() + 1;
    }

    /**
     * Get points needed for next rank
     */
    public function getPointsForNextRank(User $user): ?int
    {
        $thresholds = [
            'bronze' => 500,
            'silver' => 2000,
            'gold' => 5000,
            'platinum' => 10000,
            'diamond' => null,
        ];

        $nextThreshold = $thresholds[$user->rank ?? 'bronze'] ?? null;

        if ($nextThreshold === null) {
            return null;
        }

        return max(0, $nextThreshold - $user->total_points);
    }
}
