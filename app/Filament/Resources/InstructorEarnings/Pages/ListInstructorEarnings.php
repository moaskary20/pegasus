<?php

namespace App\Filament\Resources\InstructorEarnings\Pages;

use App\Filament\Resources\InstructorEarnings\InstructorEarningResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstructorEarnings extends ListRecords
{
    protected static string $resource = InstructorEarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
