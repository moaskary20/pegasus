<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\InstructorEarnings;
use App\Models\InstructorEarning;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EarningsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $isAdmin = $user?->hasRole('admin');
        $isInstructor = $user?->hasRole('instructor') && !$isAdmin;
        
        $query = \App\Models\InstructorEarning::with(['user', 'course.enrollments'])
            ->where('is_active', true);
        
        if ($isInstructor) {
            $query->where('user_id', $user->id);
        }
        
        $earningsData = $query->get();
        $totalEarnings = $earningsData->sum(fn ($earning) => $earning->calculateTotalEarnings());
        $totalPayments = $earningsData->sum(fn ($earning) => $earning->getTotalPayments());
        $coursesCount = $earningsData->count();
        $totalStudents = $earningsData->sum(fn ($earning) => $earning->getStudentsCount());
        $pendingEarnings = $totalEarnings - $totalPayments;

        return [
            Stat::make('إجمالي الأرباح', number_format($totalEarnings, 2) . ' ج.م')
                ->description('إجمالي الأرباح المحسوبة')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5])
                ->icon('heroicon-o-banknotes'),
            
            Stat::make('إجمالي المدفوعات', number_format($totalPayments, 2) . ' ج.م')
                ->description('المبالغ المدفوعة فعلياً')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info')
                ->chart([3, 2, 4, 3, 4, 2, 3])
                ->icon('heroicon-o-credit-card'),
            
            Stat::make('الأرباح المعلقة', number_format($pendingEarnings, 2) . ' ج.م')
                ->description('الأرباح غير المدفوعة بعد')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingEarnings > 0 ? 'warning' : 'success')
                ->chart([2, 1, 2, 1, 2, 1, 2])
                ->icon('heroicon-o-clock'),
            
            Stat::make('عدد الدورات', $coursesCount)
                ->description('الدورات التي تحقق أرباح')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary')
                ->chart([1, 2, 1, 2, 1, 2, 1])
                ->icon('heroicon-o-book-open'),
            
            Stat::make('إجمالي الطلاب', $totalStudents)
                ->description('الطلاب المسجلين في الدورات')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([5, 4, 6, 5, 7, 4, 6])
                ->icon('heroicon-o-user-group'),
        ];
    }
}
