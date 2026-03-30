<?php

namespace App\Filament\Resources\QuestionBanks\Pages;

use App\Filament\Resources\QuestionBanks\QuestionBankResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestionBank extends CreateRecord
{
    protected static string $resource = QuestionBankResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user?->hasRole('instructor') && ! $user?->hasRole('admin')) {
            $data['user_id'] = $user->id;
        }

        // Convert tags repeater to JSON array
        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = array_column($data['tags'], 'tag');
        }

        return $data;
    }
}
