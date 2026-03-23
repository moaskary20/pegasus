<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
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
use Illuminate\Support\Facades\Event;
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
    }
}
