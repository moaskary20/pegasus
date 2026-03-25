<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract roles from data
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $data['phone'] = ! empty($data['phone'] ?? null)
            ? User::normalizePhone((string) $data['phone'])
            : null;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Assign roles after user is created
        $roles = $this->form->getState()['roles'] ?? [];
        if (! empty($roles)) {
            $this->record->syncRoles($roles);
        }
    }
}
