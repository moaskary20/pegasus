<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\Course;
use App\Policies\CoursePolicy;
use App\Services\PlatformMailConfig;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\CourseRating;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Order;
use App\Observers\CourseRatingObserver;
use App\Observers\EnrollmentObserver;
use App\Observers\LessonObserver;
use App\Observers\OrderObserver;
use App\Listeners\CompleteKashierOrder;
use Asciisd\Kashier\Events\KashierResponseHandled;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Gate::policy(Course::class, CoursePolicy::class);

        $this->app->booted(function () {
            PlatformMailConfig::apply();
        });

        ResetPassword::createUrlUsing(function ($notifiable, $token) {
            return url(route('site.auth.reset-password.form', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });

        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()
                ->numbers();
        });

        CourseRating::observe(CourseRatingObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
        Lesson::observe(LessonObserver::class);
        Order::observe(OrderObserver::class);

        Event::listen(KashierResponseHandled::class, CompleteKashierOrder::class);

        Event::listen(Login::class, function (Login $event): void {
            $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
        });
    }
}
