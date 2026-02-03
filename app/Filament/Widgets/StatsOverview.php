<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $isInstructor = $user->hasRole('instructor');
        
        $stats = [];
        
        if ($isAdmin) {
            $stats = [
                Stat::make('إجمالي المستخدمين', User::count())
                    ->description('جميع المستخدمين المسجلين')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('success'),
                Stat::make('إجمالي الدورات', Course::count())
                    ->description('جميع الدورات')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('info'),
                Stat::make('الدورات المنشورة', Course::where('is_published', true)->count())
                    ->description('الدورات المتاحة للطلاب')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),
                Stat::make('إجمالي المدرسين', User::role('instructor')->count())
                    ->description('المدرسون المسجلون')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('warning'),
            ];
        } elseif ($isInstructor) {
            $instructorCourses = Course::where('user_id', $user->id);
            $stats = [
                Stat::make('دوراتي', $instructorCourses->count())
                    ->description('إجمالي الدورات')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('info'),
                Stat::make('الدورات المنشورة', $instructorCourses->where('is_published', true)->count())
                    ->description('متاحة للطلاب')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),
                Stat::make('إجمالي الطلاب', $instructorCourses->sum('students_count'))
                    ->description('طلاب مسجلون في دوراتي')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('success'),
            ];
        } else {
            // Student stats (will be added later)
            $stats = [
                Stat::make('مرحباً', $user->name)
                    ->description('لوحة تحكم الطالب')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('info'),
            ];
        }
        
        return $stats;
    }
}
