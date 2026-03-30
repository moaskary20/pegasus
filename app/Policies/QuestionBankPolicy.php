<?php

namespace App\Policies;

use App\Models\QuestionBank;
use App\Models\User;

class QuestionBankPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function view(User $user, QuestionBank $questionBank): bool
    {
        return $this->ownsOrAdmin($user, $questionBank);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'instructor']);
    }

    public function update(User $user, QuestionBank $questionBank): bool
    {
        return $this->ownsOrAdmin($user, $questionBank);
    }

    public function delete(User $user, QuestionBank $questionBank): bool
    {
        return $this->ownsOrAdmin($user, $questionBank);
    }

    protected function ownsOrAdmin(User $user, QuestionBank $questionBank): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('instructor')) {
            return false;
        }

        return (int) $questionBank->user_id === (int) $user->id;
    }
}
