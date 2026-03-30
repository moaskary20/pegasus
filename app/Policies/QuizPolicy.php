<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function view(User $user, Quiz $quiz): bool
    {
        return $this->ownsCourseOrAdmin($user, $quiz);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $this->ownsCourseOrAdmin($user, $quiz);
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->ownsCourseOrAdmin($user, $quiz);
    }

    protected function ownsCourseOrAdmin(User $user, Quiz $quiz): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('instructor')) {
            return false;
        }

        $quiz->loadMissing('lesson.section.course');
        $course = $quiz->lesson?->section?->course;

        return $course && (int) $course->user_id === (int) $user->id;
    }
}
