<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
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
        // Extract roles from data
        $roles = $data['roles'] ?? [];
        unset($data['roles']);
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        // Sync roles after user is updated
        $roles = $this->form->getState()['roles'] ?? [];
        $this->record->syncRoles($roles);
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load roles into form data
        $data['roles'] = $this->record->roles->pluck('id')->toArray();
        
        return $data;
    }
}
