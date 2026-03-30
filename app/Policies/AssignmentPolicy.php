<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return $this->ownsCourseOrAdmin($user, $assignment);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $this->ownsCourseOrAdmin($user, $assignment);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->ownsCourseOrAdmin($user, $assignment);
    }

    protected function ownsCourseOrAdmin(User $user, Assignment $assignment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('instructor')) {
            return false;
        }

        $assignment->loadMissing('course');

        return $assignment->course
            && (int) $assignment->course->user_id === (int) $user->id;
    }
}
