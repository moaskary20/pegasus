<?php

namespace App\Filament\Resources\SupportComplaints\Pages;

use App\Filament\Resources\SupportComplaints\SupportComplaintResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupportComplaints extends ListRecords
{
    protected static string $resource = SupportComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
