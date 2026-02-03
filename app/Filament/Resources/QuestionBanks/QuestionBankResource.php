<?php

namespace App\Filament\Resources\QuestionBanks;

use App\Filament\Resources\QuestionBanks\Pages\CreateQuestionBank;
use App\Filament\Resources\QuestionBanks\Pages\EditQuestionBank;
use App\Filament\Resources\QuestionBanks\Pages\ListQuestionBanks;
use App\Filament\Resources\QuestionBanks\Schemas\QuestionBankForm;
use App\Filament\Resources\QuestionBanks\Tables\QuestionBanksTable;
use App\Models\QuestionBank;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuestionBankResource extends Resource
{
    protected static ?string $model = QuestionBank::class;

    protected static ?string $navigationLabel = 'بنوك الأسئلة';
    
    protected static ?string $modelLabel = 'بنك أسئلة';
    
    protected static ?string $pluralModelLabel = 'بنوك الأسئلة';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    
    protected static ?int $navigationSort = 7;
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الدورات التدريبية';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole(['admin', 'instructor']) ?? false;
    }
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        
        // If user is instructor (not admin), show only their question banks
        if (auth()->user()?->hasRole('instructor') && !auth()->user()?->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }
        
        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return QuestionBankForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionBanksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestionBanks::route('/'),
            'create' => CreateQuestionBank::route('/create'),
            'edit' => EditQuestionBank::route('/{record}/edit'),
        ];
    }
}
