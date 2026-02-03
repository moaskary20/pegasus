<?php

namespace App\Filament\Pages;

use App\Models\Course;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyProgress extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationLabel = 'التقدم';
    
    protected static ?string $title = 'متابعة التقدم';
    
    protected static ?int $navigationSort = 2;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    
    protected string $view = 'filament.pages.my-progress';
    
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
                TextColumn::make('title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('instructor.name')
                    ->label('المدرس'),
                TextColumn::make('progress')
                    ->label('نسبة الإنجاز')
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
                TextColumn::make('completed_lessons')
                    ->label('المحاضرات المكتملة')
                    ->getStateUsing(function ($record) {
                        $completed = \App\Models\VideoProgress::where('user_id', auth()->id())
                            ->whereHas('lesson.section', function ($q) use ($record) {
                                $q->where('course_id', $record->id);
                            })
                            ->where('completed', true)
                            ->count();
                        $total = $record->sections()->withCount('lessons')->get()->sum('lessons_count');
                        return "{$completed} / {$total}";
                    }),
                TextColumn::make('watch_time')
                    ->label('وقت المشاهدة')
                    ->getStateUsing(function ($record) {
                        $minutes = \App\Models\VideoProgress::where('user_id', auth()->id())
                            ->whereHas('lesson.section', function ($q) use ($record) {
                                $q->where('course_id', $record->id);
                            })
                            ->sum('watch_time_minutes');
                        $hours = floor($minutes / 60);
                        $mins = $minutes % 60;
                        return $hours > 0 ? "{$hours} ساعة و {$mins} دقيقة" : "{$mins} دقيقة";
                    }),
            ])
            ->actions([
                Action::make('continue')
                    ->label('متابعة التعلم')
                    ->icon('heroicon-o-play')
                    ->url(fn (Course $record) => '#'), // TODO: Link to course player
            ])
            ->emptyStateHeading('لا توجد دورات مسجلة')
            ->emptyStateDescription('ابدأ بتسجيل الدورات لمتابعة تقدمك');
    }
}
