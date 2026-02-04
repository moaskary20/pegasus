<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class SalesReports extends Page
{
    protected static ?string $navigationLabel = 'تقارير المبيعات';
    
    protected static ?string $title = 'تقارير المبيعات';
    
    protected static ?int $navigationSort = 20;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
    
    protected string $view = 'filament.pages.sales-reports';
    
    protected static ?string $slug = 'sales-reports';
    
    public string $period = 'month'; // week, month, quarter, year, all
    public string $courseFilter = 'all';
    
    public static function getNavigationGroup(): ?string
    {
        return 'التقارير';
    }
    
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('admin') || $user->hasRole('instructor'));
    }
    
    public function getDateRangeProperty(): array
    {
        return match ($this->period) {
            'week' => [now()->subWeek(), now()],
            'month' => [now()->subMonth(), now()],
            'quarter' => [now()->subQuarter(), now()],
            'year' => [now()->subYear(), now()],
            default => [null, null],
        };
    }
    
    public function getCoursesProperty()
    {
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            return Course::orderBy('title')->get();
        }
        
        return Course::where('instructor_id', $user->id)->orderBy('title')->get();
    }
    
    public function getTotalRevenueProperty(): float
    {
        $query = $this->getBaseOrderQuery();
        return $query->sum('total');
    }
    
    public function getTotalOrdersProperty(): int
    {
        return $this->getBaseOrderQuery()->count();
    }
    
    public function getTotalEnrollmentsProperty(): int
    {
        $query = $this->getBaseEnrollmentQuery();
        return $query->count();
    }
    
    public function getAverageOrderValueProperty(): float
    {
        $total = $this->totalRevenue;
        $count = $this->totalOrders;
        return $count > 0 ? $total / $count : 0;
    }
    
    public function getRevenueChartDataProperty(): array
    {
        [$startDate, $endDate] = $this->dateRange;
        
        $query = $this->getBaseOrderQuery();
        
        // Database-agnostic date format (SQLite & MySQL)
        $format = $this->period === 'year' ? \App\Support\DatabaseDateHelper::yearMonth() : \App\Support\DatabaseDateHelper::date();
        $data = $query->selectRaw("{$format} as date, SUM(total) as revenue")
            ->groupBy('date')
            ->orderBy('date')
            ->when(!$startDate, fn($q) => $q->limit(12))
            ->pluck('revenue', 'date')
            ->toArray();
        
        return [
            'labels' => array_keys($data),
            'values' => array_values($data),
        ];
    }
    
    public function getTopCoursesProperty()
    {
        $user = auth()->user();
        [$startDate, $endDate] = $this->dateRange;
        
        $query = Enrollment::query()
            ->select('course_id', DB::raw('COUNT(*) as enrollments_count'))
            ->whereHas('course', function ($q) use ($user) {
                if (!$user->hasRole('admin')) {
                    $q->where('instructor_id', $user->id);
                }
            });
        
        if ($startDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->groupBy('course_id')
            ->orderByDesc('enrollments_count')
            ->limit(10)
            ->with('course')
            ->get();
    }
    
    public function getRecentOrdersProperty()
    {
        return $this->getBaseOrderQuery()
            ->with(['user', 'items.course'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }
    
    public function getMonthlyGrowthProperty(): float
    {
        $thisMonth = Order::query()
            ->where('status', 'paid')
            ->when(!auth()->user()->hasRole('admin'), fn($q) => 
                $q->whereHas('items.course', fn($c) => $c->where('instructor_id', auth()->id()))
            )
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('total');
        
        $lastMonth = Order::query()
            ->where('status', 'paid')
            ->when(!auth()->user()->hasRole('admin'), fn($q) => 
                $q->whereHas('items.course', fn($c) => $c->where('instructor_id', auth()->id()))
            )
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('total');
        
        if ($lastMonth == 0) return $thisMonth > 0 ? 100 : 0;
        
        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }
    
    protected function getBaseOrderQuery()
    {
        $user = auth()->user();
        [$startDate, $endDate] = $this->dateRange;
        
        $query = Order::query()
            ->where('status', 'paid');
        
        if (!$user->hasRole('admin')) {
            $query->whereHas('items.course', fn($q) => $q->where('instructor_id', $user->id));
        }
        
        if ($this->courseFilter !== 'all') {
            $query->whereHas('items', fn($q) => $q->where('course_id', $this->courseFilter));
        }
        
        if ($startDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query;
    }
    
    protected function getBaseEnrollmentQuery()
    {
        $user = auth()->user();
        [$startDate, $endDate] = $this->dateRange;
        
        $query = Enrollment::query();
        
        if (!$user->hasRole('admin')) {
            $query->whereHas('course', fn($q) => $q->where('instructor_id', $user->id));
        }
        
        if ($this->courseFilter !== 'all') {
            $query->where('course_id', $this->courseFilter);
        }
        
        if ($startDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query;
    }
    
    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }
    
    public function exportExcel()
    {
        [$startDate, $endDate] = $this->dateRange;
        
        return app(\App\Services\ReportExportService::class)->exportSalesToCsv([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'course_id' => $this->courseFilter,
        ]);
    }
}
