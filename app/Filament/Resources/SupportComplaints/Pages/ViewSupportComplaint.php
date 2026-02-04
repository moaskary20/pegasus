<?php

namespace App\Filament\Resources\SupportComplaints\Pages;

use App\Filament\Resources\SupportComplaints\SupportComplaintResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSupportComplaint extends ViewRecord
{
    protected static string $resource = SupportComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
