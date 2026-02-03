<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\CourseRating;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyRatings extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationLabel = 'التقييمات';
    
    protected static ?string $title = 'تقييمات دوراتي';
    
    protected static ?int $navigationSort = 3;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;
    
    protected string $view = 'filament.pages.my-ratings';
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('instructor') ?? false;
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Course::query()
                    ->where('user_id', auth()->id())
                    ->withCount(['ratings', 'enrollments'])
            )
            ->columns([
                TextColumn::make('title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('متوسط التقييم')
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 1) . ' ⭐' : 'لا توجد تقييمات')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('ratings_count')
                    ->label('عدد التقييمات')
                    ->counts('ratings')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('enrollments_count')
                    ->label('عدد الطلاب')
                    ->counts('enrollments')
                    ->numeric()
                    ->sortable(),
            ])
            ->actions([
                Action::make('view_reviews')
                    ->label('عرض التقييمات')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Course $record) => 
                        \App\Filament\Resources\CourseRatings\CourseRatingResource::getUrl('index', [
                            'tableFilters' => [
                                'course_id' => [
                                    'value' => $record->id,
                                ],
                            ],
                        ])
                    ),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([
                        'high' => '4+ ⭐',
                        'medium' => '3-4 ⭐',
                        'low' => 'أقل من 3 ⭐',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'high') {
                            return $query->where('rating', '>=', 4);
                        } elseif ($data['value'] === 'medium') {
                            return $query->whereBetween('rating', [3, 4]);
                        } elseif ($data['value'] === 'low') {
                            return $query->where('rating', '<', 3);
                        }
                        return $query;
                    }),
            ])
            ->emptyStateHeading('لا توجد تقييمات بعد')
            ->emptyStateDescription('ستظهر تقييمات الطلاب لدوراتك هنا');
    }
}
