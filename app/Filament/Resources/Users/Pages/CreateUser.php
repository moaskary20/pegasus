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
        unset($data['roles']);

        $data['phone'] = ! empty($data['phone'] ?? null)
            ? User::normalizePhone((string) $data['phone'])
            : null;

        return $data;
    }
}
