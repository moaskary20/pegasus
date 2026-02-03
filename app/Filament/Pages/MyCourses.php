<?php

namespace App\Filament\Pages;

use App\Models\Course;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyCourses extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationLabel = 'دوراتي';
    
    protected static ?string $title = 'دوراتي';
    
    protected static ?int $navigationSort = 1;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    
    protected string $view = 'filament.pages.my-courses';
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('student') ?? false;
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Course::query()
                    ->whereHas('enrollments', function ($query) {
                        $query->where('user_id', auth()->id());
                    })
            )
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('الصورة')
                    ->circular()
                    ->size(50),
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('instructor.name')
                    ->label('المدرس')
                    ->searchable(),
                TextColumn::make('level')
                    ->label('المستوى')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'beginner' => 'مبتدئ',
                        'intermediate' => 'متوسط',
                        'advanced' => 'متقدم',
                        default => $state,
                    }),
                TextColumn::make('progress')
                    ->label('التقدم')
                    ->getStateUsing(function ($record) {
                        $enrollment = $record->enrollments()->where('user_id', auth()->id())->first();
                        return $enrollment ? number_format($enrollment->progress_percentage, 1) . '%' : '0%';
                    })
                    ->badge()
                    ->color(function ($record) {
                        $enrollment = $record->enrollments()->where('user_id', auth()->id())->first();
                        $progress = $enrollment?->progress_percentage ?? 0;
                        return match (true) {
                            $progress >= 100 => 'success',
                            $progress >= 50 => 'warning',
                            default => 'gray',
                        };
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label('عرض الدورة')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Course $record) => 
                        \App\Filament\Pages\ViewCourse::getUrl(['course' => $record->id])
                    ),
            ])
            ->emptyStateHeading('لا توجد دورات مسجلة')
            ->emptyStateDescription('ابدأ بتسجيل الدورات لرؤيتها هنا');
    }
}
