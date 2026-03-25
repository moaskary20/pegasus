<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @var list<int|string> */
    protected array $rolesToSync = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $raw = $data['roles'] ?? null;
        if (is_array($raw)) {
            $this->rolesToSync = array_values(array_filter($raw, fn ($id) => $id !== null && $id !== ''));
        } elseif ($raw !== null && $raw !== '') {
            $this->rolesToSync = [$raw];
        } else {
            $this->rolesToSync = [];
        }
        unset($data['roles']);

        $data['phone'] = ! empty($data['phone'] ?? null)
            ? User::normalizePhone((string) $data['phone'])
            : null;

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->rolesToSync !== []) {
            $this->record->syncRoles($this->rolesToSync);
            $this->record->unsetRelation('roles');
        }
        $this->rolesToSync = [];
    }
}
