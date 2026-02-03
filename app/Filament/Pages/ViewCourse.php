<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Enrollment;
use App\Services\LessonAccessService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ViewCourse extends Page implements HasTable
{
    use InteractsWithTable;
    
    public ?Course $course = null;
    public ?Enrollment $enrollment = null;
    
    protected static ?string $title = 'عرض الدورة';
    
    protected string $view = 'filament.pages.view-course';
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    public function mount(?int $course = null): void
    {
        if ($course) {
            $this->course = Course::with([
                'sections.lessons.video',
                'sections.lessons.quiz',
                'sections.lessons.files',
                'user'
            ])->findOrFail($course);
            
            $this->enrollment = Enrollment::where('user_id', auth()->id())
                ->where('course_id', $course)
                ->first();
            
            if (!$this->enrollment) {
                \Filament\Notifications\Notification::make()
                    ->title('غير مصرح')
                    ->body('يجب التسجيل في الدورة أولاً')
                    ->danger()
                    ->send();
                redirect()->route('filament.admin.pages.my-courses');
                return;
            }
        }
    }
    
    public function getHeading(): string|Htmlable
    {
        return $this->course ? $this->course->title : 'عرض الدورة';
    }
    
    public function table(Table $table): Table
    {
        if (!$this->course) {
            return $table->query(\App\Models\Lesson::query()->whereRaw('1 = 0'));
        }
        
        return $table
            ->query(
                \App\Models\Lesson::query()
                    ->whereHas('section', function ($q) {
                        $q->where('course_id', $this->course->id);
                    })
                    ->with(['section', 'video', 'quiz', 'files'])
                    ->orderBy('sort_order')
            )
            ->columns([
                TextColumn::make('section.title')
                    ->label('القسم')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('المحاضرة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('access_status')
                    ->label('حالة الوصول')
                    ->getStateUsing(function ($record) {
                        $user = auth()->user();
                        $accessService = app(LessonAccessService::class);
                        
                        if (!$accessService->canAccessLesson($user, $record)) {
                            $incompleteLesson = $accessService->getFirstIncompleteLesson($user, $record);
                            return $incompleteLesson 
                                ? "محظور - أكمل: {$incompleteLesson->title}"
                                : 'محظور';
                        }
                        
                        return 'مفتوح';
                    })
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'مفتوح' => 'success',
                            default => 'warning',
                        };
                    }),
                TextColumn::make('duration_minutes')
                    ->label('المدة')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' دقيقة' : '-')
                    ->sortable(),
                TextColumn::make('has_video')
                    ->label('فيديو')
                    ->getStateUsing(fn ($record) => $record->video ? '✓' : '✗')
                    ->badge()
                    ->color(fn ($record) => $record->video ? 'success' : 'gray'),
                TextColumn::make('has_quiz')
                    ->label('اختبار')
                    ->getStateUsing(fn ($record) => $record->quiz ? '✓' : '✗')
                    ->badge()
                    ->color(fn ($record) => $record->quiz ? 'info' : 'gray'),
                TextColumn::make('has_files')
                    ->label('ملفات')
                    ->getStateUsing(fn ($record) => $record->files->count() > 0 ? $record->files->count() : '✗')
                    ->badge()
                    ->color(fn ($record) => $record->files->count() > 0 ? 'warning' : 'gray'),
            ])
            ->actions([
                Action::make('watch_video')
                    ->label('مشاهدة الفيديو')
                    ->icon('heroicon-o-play')
                    ->url(fn ($record) => $record->video 
                        ? \App\Filament\Pages\WatchVideo::getUrl(['lesson' => $record->id])
                        : null
                    )
                    ->visible(fn ($record) => $record->video !== null)
                    ->color('success'),
                Action::make('take_quiz')
                    ->label('أداء الاختبار')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->url(fn ($record) => $record->quiz 
                        ? \App\Filament\Pages\TakeQuiz::getUrl(['quiz' => $record->quiz->id])
                        : null
                    )
                    ->visible(fn ($record) => $record->quiz !== null)
                    ->color('info'),
            ])
            ->groups([
                \Filament\Tables\Grouping\Group::make('section.title')
                    ->label('القسم')
                    ->collapsible(),
            ])
            ->defaultGroup('section.title');
    }
}
