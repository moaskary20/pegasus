<?php

namespace App\Filament\Resources\StudentSubscriptions\Pages;

use App\Filament\Resources\StudentSubscriptions\StudentSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudentSubscription extends CreateRecord
{
    protected static string $resource = StudentSubscriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
