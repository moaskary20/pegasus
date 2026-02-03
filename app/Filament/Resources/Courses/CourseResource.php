<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Filament\Resources\Courses\Schemas\CourseForm;
use App\Filament\Resources\Courses\Tables\CoursesTable;
use App\Models\Course;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationLabel = 'الدورات';
    
    protected static ?string $modelLabel = 'دورة';
    
    protected static ?string $pluralModelLabel = 'الدورات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    
    protected static ?int $navigationSort = 4;
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الدورات التدريبية';
    }

    public static function form(Schema $schema): Schema
    {
        return CourseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoursesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SectionsRelationManager::class,
            RelationManagers\AllLessonsRelationManager::class,
            RelationManagers\QuestionBanksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole(['admin', 'instructor']) ?? false;
    }
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        
        // If user is instructor (not admin), show only their courses
        if (auth()->user()?->hasRole('instructor') && !auth()->user()?->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }
        
        return $query;
    }
}
