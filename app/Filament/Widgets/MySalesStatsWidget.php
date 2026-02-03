<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MySalesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $courses = Course::where('user_id', $user->id);
        $totalStudents = $courses->sum('students_count');
        $totalRevenue = 0; // TODO: Calculate from orders
        $totalCourses = $courses->count();
        
        return [
            Stat::make('إجمالي الطلاب', $totalStudents)
                ->description('طلاب مسجلون في دوراتي')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('إجمالي الأرباح', '$' . number_format($totalRevenue, 2))
                ->description('إجمالي الإيرادات')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            Stat::make('عدد الدورات', $totalCourses)
                ->description('دورات منشورة')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
        ];
    }
}
