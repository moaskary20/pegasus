<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled tasks
Schedule::command('reminders:send')->dailyAt('09:00')->name('send-daily-reminders');
Schedule::command('reminders:send --type=coupon')->dailyAt('08:00')->name('send-coupon-reminders');
Schedule::command('reminders:send --type=question')->twiceDaily(10, 16)->name('send-question-reminders');
