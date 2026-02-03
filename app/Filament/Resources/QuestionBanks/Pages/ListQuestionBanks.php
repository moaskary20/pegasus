<?php

namespace App\Filament\Resources\QuestionBanks\Pages;

use App\Filament\Resources\QuestionBanks\QuestionBankResource;
use App\Models\QuestionBank;
use App\Models\QuestionBankQuestion;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListQuestionBanks extends ListRecords
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة بنك أسئلة')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getHeader(): ?View
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        
        $query = QuestionBank::query();
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }
        
        $bankIds = (clone $query)->pluck('id');
        
        return view('filament.resources.question-banks.header', [
            'totalBanks' => (clone $query)->count(),
            'activeBanks' => (clone $query)->where('is_active', true)->count(),
            'totalQuestions' => QuestionBankQuestion::whereIn('question_bank_id', $bankIds)->count(),
            'courseBanks' => (clone $query)->whereNotNull('course_id')->count(),
            'generalBanks' => (clone $query)->whereNull('course_id')->count(),
            'createUrl' => static::getResource()::getUrl('create'),
            'isAdmin' => $isAdmin,
        ]);
    }
}
