<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\VideoProgress;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class StudentProgressReports extends Page
{
    protected static ?string $navigationLabel = 'تقارير تقدم الطلاب';
    
    protected static ?string $title = 'تقارير تقدم الطلاب';
    
    protected static ?int $navigationSort = 21;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    
    protected string $view = 'filament.pages.student-progress-reports';
    
    protected static ?string $slug = 'student-progress-reports';
    
    public string $courseFilter = 'all';
    public string $progressFilter = 'all'; // all, completed, in_progress, not_started
    public string $search = '';
    
    public static function getNavigationGroup(): ?string
    {
        return 'التقارير';
    }
    
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('admin') || $user->hasRole('instructor'));
    }
    
    public function getCoursesProperty()
    {
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            return Course::orderBy('title')->get();
        }
        
        return Course::where('instructor_id', $user->id)->orderBy('title')->get();
    }
    
    public function getEnrollmentsProperty()
    {
        $user = auth()->user();
        
        $query = Enrollment::with(['user', 'course'])
            ->whereHas('course', function ($q) use ($user) {
                if (!$user->hasRole('admin')) {
                    $q->where('instructor_id', $user->id);
                }
            });
        
        if ($this->courseFilter !== 'all') {
            $query->where('course_id', $this->courseFilter);
        }
        
        if ($this->progressFilter !== 'all') {
            $query->where(function ($q) {
                match ($this->progressFilter) {
                    'completed' => $q->whereNotNull('completed_at'),
                    'in_progress' => $q->whereNull('completed_at')->where('progress_percentage', '>', 0),
                    'not_started' => $q->where('progress_percentage', 0)->orWhereNull('progress_percentage'),
                    default => null,
                };
            });
        }
        
        if ($this->search) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
        }
        
        return $query->orderByDesc('updated_at')->paginate(20);
    }
    
    public function getOverallStatsProperty(): array
    {
        $user = auth()->user();
        
        $baseQuery = Enrollment::whereHas('course', function ($q) use ($user) {
            if (!$user->hasRole('admin')) {
                $q->where('instructor_id', $user->id);
            }
        });
        
        $total = (clone $baseQuery)->count();
        $completed = (clone $baseQuery)->whereNotNull('completed_at')->count();
        $inProgress = (clone $baseQuery)->whereNull('completed_at')->where('progress_percentage', '>', 0)->count();
        $notStarted = (clone $baseQuery)->where(fn($q) => $q->where('progress_percentage', 0)->orWhereNull('progress_percentage'))->count();
        
        $avgProgress = (clone $baseQuery)->avg('progress_percentage') ?? 0;
        
        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'not_started' => $notStarted,
            'avg_progress' => round($avgProgress, 1),
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }
    
    public function getProgressDistributionProperty(): array
    {
        $user = auth()->user();
        
        $query = Enrollment::whereHas('course', function ($q) use ($user) {
            if (!$user->hasRole('admin')) {
                $q->where('instructor_id', $user->id);
            }
        });
        
        $distribution = [
            '0%' => (clone $query)->where(fn($q) => $q->where('progress_percentage', 0)->orWhereNull('progress_percentage'))->count(),
            '1-25%' => (clone $query)->whereBetween('progress_percentage', [1, 25])->count(),
            '26-50%' => (clone $query)->whereBetween('progress_percentage', [26, 50])->count(),
            '51-75%' => (clone $query)->whereBetween('progress_percentage', [51, 75])->count(),
            '76-99%' => (clone $query)->whereBetween('progress_percentage', [76, 99])->count(),
            '100%' => (clone $query)->where('progress_percentage', 100)->count(),
        ];
        
        return $distribution;
    }
    
    public function getTopStudentsProperty()
    {
        $user = auth()->user();
        
        return Enrollment::with(['user', 'course'])
            ->whereHas('course', function ($q) use ($user) {
                if (!$user->hasRole('admin')) {
                    $q->where('instructor_id', $user->id);
                }
            })
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();
    }
    
    public function getCoursesStatsProperty()
    {
        $user = auth()->user();
        
        return Course::withCount([
                'enrollments',
                'enrollments as completed_count' => fn($q) => $q->whereNotNull('completed_at'),
            ])
            ->withAvg('enrollments', 'progress_percentage')
            ->when(!$user->hasRole('admin'), fn($q) => $q->where('instructor_id', $user->id))
            ->orderByDesc('enrollments_count')
            ->limit(10)
            ->get();
    }
    
    public function setProgressFilter(string $filter): void
    {
        $this->progressFilter = $filter;
        $this->resetPage();
    }
    
    public function resetPage(): void
    {
        $this->dispatch('resetPage');
    }
    
    public function exportExcel()
    {
        return app(\App\Services\ReportExportService::class)->exportProgressToCsv([
            'course_id' => $this->courseFilter,
            'progress_filter' => $this->progressFilter,
        ]);
    }
}
