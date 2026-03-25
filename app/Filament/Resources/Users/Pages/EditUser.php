<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Multi relationship Select is not dehydrated; roles sync inside Form::getState() via saveRelationships().
        unset($data['roles']);

        $data['phone'] = ! empty($data['phone'] ?? null)
            ? User::normalizePhone((string) $data['phone'])
            : null;

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load roles into form data
        $data['roles'] = $this->record->roles->pluck('id')->toArray();

        return $data;
    }
}
