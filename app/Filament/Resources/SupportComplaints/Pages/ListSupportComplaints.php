<?php

namespace App\Filament\Resources\SupportComplaints\Pages;

use App\Filament\Resources\SupportComplaints\SupportComplaintResource;
use App\Models\SupportComplaint;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListSupportComplaints extends ListRecords
{
    protected static string $resource = SupportComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة شكوى / استفسار')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.support-complaints.header', [
            'total' => SupportComplaint::count(),
            'complaintsCount' => SupportComplaint::where('type', SupportComplaint::TYPE_COMPLAINT)->count(),
            'contactCount' => SupportComplaint::where('type', SupportComplaint::TYPE_CONTACT)->count(),
            'pendingCount' => SupportComplaint::where('status', SupportComplaint::STATUS_PENDING)->count(),
            'resolvedCount' => SupportComplaint::where('status', SupportComplaint::STATUS_RESOLVED)->count(),
            'createUrl' => static::getResource()::getUrl('create'),
        ]);
    }
}
