<?php

namespace App\Filament\Resources\Certificates\Pages;

use App\Filament\Resources\Certificates\CertificateResource;
use App\Models\Certificate;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListCertificates extends ListRecords
{
    protected static string $resource = CertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إصدار شهادة')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getHeader(): ?View
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        
        $query = Certificate::query();
        if (!$isAdmin) {
            $query->whereHas('course', fn($q) => $q->where('user_id', $user->id));
        }
        
        return view('filament.resources.certificates.header', [
            'totalCertificates' => (clone $query)->count(),
            'withPdf' => (clone $query)->whereNotNull('pdf_path')->count(),
            'withoutPdf' => (clone $query)->whereNull('pdf_path')->count(),
            'thisMonth' => (clone $query)->whereMonth('issued_at', now()->month)->whereYear('issued_at', now()->year)->count(),
            'uniqueCourses' => (clone $query)->distinct('course_id')->count('course_id'),
            'createUrl' => static::getResource()::getUrl('create'),
            'isAdmin' => $isAdmin,
        ]);
    }
}
