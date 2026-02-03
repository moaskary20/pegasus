<?php

namespace App\Filament\Resources\CourseRatings\Pages;

use App\Filament\Resources\CourseRatings\CourseRatingResource;
use App\Models\CourseRating;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListCourseRatings extends ListRecords
{
    protected static string $resource = CourseRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة تقييم')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getHeader(): ?View
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        
        $query = CourseRating::query();
        if (!$isAdmin) {
            $query->whereHas('course', fn($q) => $q->where('user_id', $user->id));
        }
        
        $avgRating = (clone $query)->avg('stars') ?? 0;
        
        return view('filament.resources.course-ratings.header', [
            'totalRatings' => (clone $query)->count(),
            'avgRating' => round($avgRating, 1),
            'fiveStars' => (clone $query)->where('stars', 5)->count(),
            'fourStars' => (clone $query)->where('stars', 4)->count(),
            'threeStars' => (clone $query)->where('stars', 3)->count(),
            'lowRatings' => (clone $query)->where('stars', '<=', 2)->count(),
            'createUrl' => static::getResource()::getUrl('create'),
            'isAdmin' => $isAdmin,
        ]);
    }
}
