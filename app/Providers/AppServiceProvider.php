<?php

namespace App\Providers;

use App\Models\CourseRating;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Observers\CourseRatingObserver;
use App\Observers\EnrollmentObserver;
use App\Observers\LessonObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        CourseRating::observe(CourseRatingObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
        Lesson::observe(LessonObserver::class);
    }
}
