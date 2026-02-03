<?php

namespace App\Filament\Resources\StudentSubscriptions\Pages;

use App\Filament\Resources\StudentSubscriptions\StudentSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentSubscription extends EditRecord
{
    protected static string $resource = StudentSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
