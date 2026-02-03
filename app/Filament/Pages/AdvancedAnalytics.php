<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use App\Models\VideoProgress;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class AdvancedAnalytics extends Page
{
    protected static ?string $navigationLabel = 'التحليلات المتقدمة';
    
    protected static ?string $title = 'التحليلات المتقدمة';
    
    protected static ?int $navigationSort = 22;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    
    protected string $view = 'filament.pages.advanced-analytics';
    
    protected static ?string $slug = 'advanced-analytics';
    
    public static function getNavigationGroup(): ?string
    {
        return 'التقارير';
    }
    
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
    
    public function getPlatformStatsProperty(): array
    {
        return [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_enrollments' => Enrollment::count(),
            'total_revenue' => Order::where('status', 'paid')->sum('total'),
            'active_users_today' => User::whereDate('last_login_at', today())->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }
    
    public function getUserGrowthProperty(): array
    {
        $data = User::selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
        
        return [
            'labels' => array_keys($data),
            'values' => array_values($data),
        ];
    }
    
    public function getRevenueGrowthProperty(): array
    {
        $data = Order::selectRaw("strftime('%Y-%m', created_at) as month, SUM(total) as revenue")
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();
        
        return [
            'labels' => array_keys($data),
            'values' => array_values($data),
        ];
    }
    
    public function getEnrollmentGrowthProperty(): array
    {
        $data = Enrollment::selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
        
        return [
            'labels' => array_keys($data),
            'values' => array_values($data),
        ];
    }
    
    public function getCategoryDistributionProperty(): array
    {
        return Course::select('category_id', DB::raw('COUNT(*) as count'))
            ->with('category')
            ->groupBy('category_id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->category?->name ?? 'غير مصنف' => $item->count])
            ->toArray();
    }
    
    public function getTopInstructorsProperty()
    {
        return User::whereHas('roles', fn($q) => $q->where('name', 'instructor'))
            ->withCount('courses')
            ->withCount(['courses as students_count' => fn($q) => $q->withCount('enrollments')])
            ->orderByDesc('courses_count')
            ->limit(10)
            ->get();
    }
    
    public function getEngagementMetricsProperty(): array
    {
        $totalWatchTime = VideoProgress::sum('watch_time_minutes');
        $avgWatchTime = VideoProgress::avg('watch_time_minutes') ?? 0;
        $completedLessons = VideoProgress::where('completed', true)->count();
        $totalLessons = VideoProgress::count();
        
        return [
            'total_watch_hours' => round($totalWatchTime / 60, 1),
            'avg_watch_minutes' => round($avgWatchTime, 1),
            'lesson_completion_rate' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 1) : 0,
            'completed_lessons' => $completedLessons,
        ];
    }
    
    public function getRecentActivityProperty()
    {
        $enrollments = Enrollment::with(['user', 'course'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($e) => [
                'type' => 'enrollment',
                'user' => $e->user?->name,
                'detail' => $e->course?->title,
                'time' => $e->created_at,
            ]);
        
        $orders = Order::with(['user', 'items.course'])
            ->where('status', 'paid')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($o) => [
                'type' => 'order',
                'user' => $o->user?->name,
                'detail' => number_format($o->total, 2) . ' ج.م',
                'time' => $o->created_at,
            ]);
        
        return $enrollments->merge($orders)
            ->sortByDesc('time')
            ->take(10)
            ->values();
    }
    
    public function getHourlyActivityProperty(): array
    {
        $data = Enrollment::selectRaw("CAST(strftime('%H', created_at) AS INTEGER) as hour, COUNT(*) as count")
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();
        
        // Fill missing hours
        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[$i] = $data[$i] ?? 0;
        }
        
        return $hours;
    }
    
    public function getDayOfWeekActivityProperty(): array
    {
        // SQLite strftime %w returns 0=Sunday, 1=Monday, etc.
        $data = Enrollment::selectRaw("CAST(strftime('%w', created_at) AS INTEGER) as day, COUNT(*) as count")
            ->where('created_at', '>=', now()->subDays(90))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day')
            ->toArray();
        
        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        $result = [];
        
        for ($i = 0; $i <= 6; $i++) {
            $result[$days[$i]] = $data[$i] ?? 0;
        }
        
        return $result;
    }
}
