<?php

namespace App\Policies;

use App\Models\CourseQuestion;
use App\Models\User;

class CourseQuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function view(User $user, CourseQuestion $courseQuestion): bool
    {
        return $this->ownsCourseOrAdmin($user, $courseQuestion);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function update(User $user, CourseQuestion $courseQuestion): bool
    {
        return $this->ownsCourseOrAdmin($user, $courseQuestion);
    }

    public function delete(User $user, CourseQuestion $courseQuestion): bool
    {
        return $this->ownsCourseOrAdmin($user, $courseQuestion);
    }

    protected function ownsCourseOrAdmin(User $user, CourseQuestion $courseQuestion): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('instructor')) {
            return false;
        }

        $courseQuestion->loadMissing('course');

        return $courseQuestion->course
            && (int) $courseQuestion->course->user_id === (int) $user->id;
    }
}
