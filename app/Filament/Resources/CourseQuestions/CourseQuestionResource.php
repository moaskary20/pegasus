<?php

namespace App\Filament\Resources\CourseQuestions;

use App\Filament\Resources\CourseQuestions\Pages\CreateCourseQuestion;
use App\Filament\Resources\CourseQuestions\Pages\EditCourseQuestion;
use App\Filament\Resources\CourseQuestions\Pages\ListCourseQuestions;
use App\Filament\Resources\CourseQuestions\Schemas\CourseQuestionForm;
use App\Filament\Resources\CourseQuestions\Tables\CourseQuestionsTable;
use App\Models\CourseQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CourseQuestionResource extends Resource
{
    protected static ?string $model = CourseQuestion::class;

    protected static ?string $navigationLabel = 'أسئلة الدورات';
    
    protected static ?string $modelLabel = 'سؤال';
    
    protected static ?string $pluralModelLabel = 'أسئلة الدورات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;
    
    protected static ?int $navigationSort = 8;
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return CourseQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseQuestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseQuestions::route('/'),
            'create' => CreateCourseQuestion::route('/create'),
            'edit' => EditCourseQuestion::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        
        // If user is instructor (not admin), show only questions for their courses
        if (auth()->user()?->hasRole('instructor') && !auth()->user()?->hasRole('admin')) {
            $query->whereHas('course', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }
        
        return $query;
    }
}
