<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة دورة')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getHeader(): ?View
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        
        $coursesQuery = Course::query();
        if (!$isAdmin) {
            $coursesQuery->where('user_id', $user->id);
        }
        
        $courseIds = (clone $coursesQuery)->pluck('id');
        
        return view('filament.resources.courses.header', [
            'totalCourses' => (clone $coursesQuery)->count(),
            'publishedCourses' => (clone $coursesQuery)->where('is_published', true)->count(),
            'draftCourses' => (clone $coursesQuery)->where('is_published', false)->count(),
            'totalStudents' => Enrollment::whereIn('course_id', $courseIds)->count(),
            'completedStudents' => Enrollment::whereIn('course_id', $courseIds)->whereNotNull('completed_at')->count(),
            'totalRevenue' => Order::whereHas('items', fn($q) => $q->whereIn('course_id', $courseIds))->where('status', 'paid')->sum('total'),
            'createUrl' => static::getResource()::getUrl('create'),
            'isAdmin' => $isAdmin,
        ]);
    }
}
