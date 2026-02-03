<?php

namespace App\Filament\Resources\Enrollments\Pages;

use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Models\Enrollment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة اشتراك')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getHeader(): ?View
    {
        return view('filament.resources.enrollments.header', [
            'totalEnrollments' => Enrollment::count(),
            'completedEnrollments' => Enrollment::whereNotNull('completed_at')->count(),
            'inProgressEnrollments' => Enrollment::whereNull('completed_at')->where('progress_percentage', '>', 0)->count(),
            'notStartedEnrollments' => Enrollment::where('progress_percentage', 0)->count(),
            'totalRevenue' => Enrollment::sum('price_paid'),
            'thisMonthEnrollments' => Enrollment::whereMonth('enrolled_at', now()->month)->whereYear('enrolled_at', now()->year)->count(),
            'createUrl' => static::getResource()::getUrl('create'),
        ]);
    }
}
