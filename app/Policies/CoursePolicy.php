<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function view(User $user, Course $course): bool
    {
        return $this->ownsOrAdmin($user, $course);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function update(User $user, Course $course): bool
    {
        return $this->ownsOrAdmin($user, $course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->ownsOrAdmin($user, $course);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function restore(User $user, Course $course): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Course $course): bool
    {
        return $user->hasRole('admin');
    }

    protected function ownsOrAdmin(User $user, Course $course): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('instructor')
            && (int) $course->user_id === (int) $user->id;
    }
}
