<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\CourseRating;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Observers\CourseRatingObserver;
use App\Observers\EnrollmentObserver;
use App\Observers\LessonObserver;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
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
