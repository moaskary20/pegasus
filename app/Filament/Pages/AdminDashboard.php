<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class AdminDashboard extends Page
{
    protected static ?string $navigationLabel = 'لوحة التحكم';
    
    protected static ?string $title = 'لوحة التحكم';
    
    protected static ?int $navigationSort = 0;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected string $view = 'filament.pages.admin-dashboard';
    
    public function getStatsProperty(): array
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        
        if ($isAdmin) {
            return [
                'users' => User::count(),
                'instructors' => User::whereHas('roles', fn($q) => $q->where('name', 'instructor'))->count(),
                'students' => User::whereHas('roles', fn($q) => $q->where('name', 'student'))->count(),
                'courses' => Course::count(),
                'published_courses' => Course::where('is_published', true)->count(),
                'enrollments' => Enrollment::count(),
                'categories' => Category::count(),
                'revenue' => Order::where('status', 'paid')->sum('total'),
                'orders' => Order::where('status', 'paid')->count(),
                'completed_courses' => Enrollment::whereNotNull('completed_at')->count(),
            ];
        } else {
            // Instructor
            $courseIds = Course::where('instructor_id', $user->id)->pluck('id');
            return [
                'courses' => Course::where('instructor_id', $user->id)->count(),
                'published_courses' => Course::where('instructor_id', $user->id)->where('is_published', true)->count(),
                'enrollments' => Enrollment::whereIn('course_id', $courseIds)->count(),
                'completed_courses' => Enrollment::whereIn('course_id', $courseIds)->whereNotNull('completed_at')->count(),
                'revenue' => Order::whereHas('items', fn($q) => $q->whereIn('course_id', $courseIds))->where('status', 'paid')->sum('total'),
            ];
        }
    }
    
    public function getRecentEnrollmentsProperty()
    {
        $user = auth()->user();
        $query = Enrollment::with(['user', 'course']);
        
        if (!$user->hasRole('admin')) {
            $courseIds = Course::where('instructor_id', $user->id)->pluck('id');
            $query->whereIn('course_id', $courseIds);
        }
        
        return $query->orderByDesc('created_at')->limit(5)->get();
    }
    
    public function getTopCoursesProperty()
    {
        $user = auth()->user();
        $query = Course::withCount('enrollments');
        
        if (!$user->hasRole('admin')) {
            $query->where('instructor_id', $user->id);
        }
        
        return $query->orderByDesc('enrollments_count')->limit(5)->get();
    }
    
    public function getEnrollmentChartProperty(): array
    {
        $user = auth()->user();
        
        $query = Enrollment::selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(6));
        
        if (!$user->hasRole('admin')) {
            $courseIds = Course::where('instructor_id', $user->id)->pluck('id');
            $query->whereIn('course_id', $courseIds);
        }
        
        $data = $query->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
        
        return [
            'labels' => array_keys($data),
            'values' => array_values($data),
        ];
    }
    
    public function getIsAdminProperty(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
