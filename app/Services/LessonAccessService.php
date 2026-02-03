<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\User;
use App\Models\VideoProgress;

class LessonAccessService
{
    /**
     * Check if a user can access a lesson
     * Checks enrollment, prerequisite lessons, and unlock settings
     */
    public function canAccessLesson(User $user, Lesson $lesson): bool
    {
        // Check if user is enrolled in the course
        $enrollment = $lesson->section->course->enrollments()
            ->where('user_id', $user->id)
            ->exists();
        
        if (!$enrollment) {
            return false;
        }
        
        // If lesson allows unlocking without completion, user can access it
        if ($lesson->can_unlock_without_completion) {
            return true;
        }
        
        // If lesson is free, user can access it
        if ($lesson->is_free || $lesson->is_free_preview) {
            return true;
        }
        
        // Check if previous lessons are completed
        return $this->areAllPreviousLessonsCompleted($user, $lesson);
    }
    
    /**
     * Check if all previous lessons in the section are completed
     */
    public function areAllPreviousLessonsCompleted(User $user, Lesson $lesson): bool
    {
        $previousLessons = $lesson->section
            ->lessons()
            ->where('sort_order', '<', $lesson->sort_order)
            ->get();
        
        if ($previousLessons->isEmpty()) {
            return true;
        }
        
        // Check if all previous lessons are completed
        foreach ($previousLessons as $previousLesson) {
            $isCompleted = VideoProgress::where('user_id', $user->id)
                ->where('lesson_id', $previousLesson->id)
                ->where('completed', true)
                ->exists();
            
            if (!$isCompleted) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get the first incomplete previous lesson (if any)
     */
    public function getFirstIncompleteLesson(User $user, Lesson $lesson): ?Lesson
    {
        $previousLessons = $lesson->section
            ->lessons()
            ->where('sort_order', '<', $lesson->sort_order)
            ->orderBy('sort_order', 'asc')
            ->get();
        
        foreach ($previousLessons as $previousLesson) {
            $isCompleted = VideoProgress::where('user_id', $user->id)
                ->where('lesson_id', $previousLesson->id)
                ->where('completed', true)
                ->exists();
            
            if (!$isCompleted) {
                return $previousLesson;
            }
        }
        
        return null;
    }
}
