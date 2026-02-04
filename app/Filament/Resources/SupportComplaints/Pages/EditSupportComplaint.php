<?php

namespace App\Filament\Resources\SupportComplaints\Pages;

use App\Filament\Resources\SupportComplaints\SupportComplaintResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSupportComplaint extends EditRecord
{
    protected static string $resource = SupportComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
