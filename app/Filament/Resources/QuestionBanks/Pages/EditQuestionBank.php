<?php

namespace App\Filament\Resources\QuestionBanks\Pages;

use App\Filament\Resources\QuestionBanks\QuestionBankResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionBank extends EditRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert tags repeater to JSON array
        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = array_column($data['tags'], 'tag');
        }
        
        return $data;
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert tags JSON array to repeater format
        if (isset($data['tags']) && is_array($data['tags']) && !empty($data['tags'])) {
            $data['tags'] = array_map(fn($tag) => ['tag' => $tag], $data['tags']);
        } else {
            $data['tags'] = [];
        }
        
        return $data;
    }
}
