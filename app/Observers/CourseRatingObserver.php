<?php

namespace App\Observers;

use App\Models\CourseRating;

class CourseRatingObserver
{
    public function created(CourseRating $rating): void
    {
        $this->refreshCourseStats($rating);
    }

    public function updated(CourseRating $rating): void
    {
        $this->refreshCourseStats($rating);
    }

    public function deleted(CourseRating $rating): void
    {
        $this->refreshCourseStats($rating);
    }

    protected function refreshCourseStats(CourseRating $rating): void
    {
        $course = $rating->course;
        if (!$course) {
            return;
        }

        $stats = CourseRating::query()
            ->where('course_id', $course->id)
            ->selectRaw('AVG(stars) as avg_rating, COUNT(*) as reviews_count')
            ->first();

        $course->updateQuietly([
            'rating' => round((float) ($stats->avg_rating ?? 0), 2),
            'reviews_count' => (int) ($stats->reviews_count ?? 0),
        ]);
    }
}
