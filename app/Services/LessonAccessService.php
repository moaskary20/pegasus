<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\VideoProgress;

class LessonAccessService
{
    /**
     * زائر بدون حساب: لا يُسمح بمشاهدة دروس المعاينة إلا إذا فُعّل إعداد المنصة allow_anonymous_lesson_preview.
     */
    public function canAnonymousUserAccessLesson(Lesson $lesson): bool
    {
        if (! (bool) PlatformSetting::get('allow_anonymous_lesson_preview', false)) {
            return false;
        }

        return (bool) ($lesson->is_free ?? false) || (bool) ($lesson->is_free_preview ?? false);
    }

    /**
     * هل يُسمح ببث فيديو الدرس (مسجّل أو زائر عند تفعيل معاينة مجهولة).
     */
    public function canStreamLessonVideo(?User $user, Course $course, Lesson $lesson): bool
    {
        if (! $lesson->section || (int) $lesson->section->course_id !== (int) $course->id) {
            return false;
        }

        $isEnrolled = $user && Enrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($isEnrolled) {
            return $this->canAccessLesson($user, $lesson);
        }

        if (! $user) {
            return $this->canAnonymousUserAccessLesson($lesson);
        }

        return (bool) ($lesson->is_free ?? false) || (bool) ($lesson->is_free_preview ?? false);
    }

    public function canAccessLesson(User $user, Lesson $lesson): bool
    {
        // Check if user is enrolled in the course
        $enrollment = $lesson->section->course->enrollments()
            ->where('user_id', $user->id)
            ->exists();
        
        if (!$enrollment) {
            return false;
        }

        $enforceOrder = (bool) PlatformSetting::get('enforce_lesson_order', true);
        if (!$enforceOrder && $lesson->can_unlock_without_completion) {
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
