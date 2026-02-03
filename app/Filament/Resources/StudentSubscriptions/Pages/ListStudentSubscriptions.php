<?php

namespace App\Filament\Resources\StudentSubscriptions\Pages;

use App\Filament\Resources\StudentSubscriptions\StudentSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentSubscriptions extends ListRecords
{
    protected static string $resource = StudentSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة اشتراك جديد'),
        ];
    }
}
