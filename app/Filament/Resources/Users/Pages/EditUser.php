<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** @var list<int|string> */
    protected array $rolesToSync = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function afterSave(): void
    {
        $this->record->syncRoles($this->rolesToSync);
        $this->record->unsetRelation('roles');
        $this->rolesToSync = [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load roles into form data
        $data['roles'] = $this->record->roles->pluck('id')->toArray();

        return $data;
    }
}
