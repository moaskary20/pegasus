<?php

namespace App\Filament\Resources\InstructorEarnings\Pages;

use App\Filament\Resources\InstructorEarnings\InstructorEarningResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstructorEarning extends EditRecord
{
    protected static string $resource = InstructorEarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
