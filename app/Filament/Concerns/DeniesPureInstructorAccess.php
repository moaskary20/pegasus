<?php

namespace App\Filament\Concerns;

trait DeniesPureInstructorAccess
{
    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->hasRole('instructor') && ! $user->hasRole('admin')) {
            return false;
        }

        return true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
