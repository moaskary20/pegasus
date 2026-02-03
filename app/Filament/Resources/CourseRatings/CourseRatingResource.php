<?php

namespace App\Filament\Resources\CourseRatings;

use App\Filament\Resources\CourseRatings\Pages\CreateCourseRating;
use App\Filament\Resources\CourseRatings\Pages\EditCourseRating;
use App\Filament\Resources\CourseRatings\Pages\ListCourseRatings;
use App\Filament\Resources\CourseRatings\Schemas\CourseRatingForm;
use App\Filament\Resources\CourseRatings\Tables\CourseRatingsTable;
use App\Models\CourseRating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CourseRatingResource extends Resource
{
    protected static ?string $model = CourseRating::class;

    protected static ?string $navigationLabel = 'تقييمات الدورات';
    
    protected static ?string $modelLabel = 'تقييم';
    
    protected static ?string $pluralModelLabel = 'تقييمات الدورات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;
    
    protected static ?int $navigationSort = 8;
    
    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الدورات التدريبية';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole(['admin', 'instructor']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return CourseRatingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseRatingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseRatings::route('/'),
            'create' => CreateCourseRating::route('/create'),
            'edit' => EditCourseRating::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        
        // If user is instructor (not admin), show only ratings for their courses
        if (auth()->user()?->hasRole('instructor') && !auth()->user()?->hasRole('admin')) {
            $query->whereHas('course', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }
        
        return $query;
    }
}
