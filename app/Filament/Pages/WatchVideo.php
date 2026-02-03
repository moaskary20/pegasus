<?php

namespace App\Filament\Pages;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Video;
use App\Models\VideoProgress;
use App\Services\PointsService;
use App\Services\LessonAccessService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class WatchVideo extends Page
{
    public ?Lesson $lesson = null;
    public ?Video $video = null;
    public ?VideoProgress $progress = null;
    
    protected static ?string $title = 'مشاهدة الفيديو';
    
    protected string $view = 'filament.pages.watch-video';
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    public function mount(?int $lesson = null): void
    {
        if ($lesson) {
            $this->lesson = Lesson::with(['section.course', 'video', 'files'])->findOrFail($lesson);
            $this->video = $this->lesson->video;
            
            // Check enrollment
            $user = auth()->user();
            $enrollment = $this->lesson->section->course->enrollments()
                ->where('user_id', $user->id)
                ->first();
            
            if (!$enrollment) {
                \Filament\Notifications\Notification::make()
                    ->title('غير مصرح')
                    ->body('يجب التسجيل في الدورة أولاً')
                    ->danger()
                    ->send();
                redirect()->route('filament.admin.pages.my-courses');
                return;
            }
            
            // Check if user can access this lesson
            $accessService = app(LessonAccessService::class);
            if (!$accessService->canAccessLesson($user, $this->lesson)) {
                $incompleteLesson = $accessService->getFirstIncompleteLesson($user, $this->lesson);
                
                \Filament\Notifications\Notification::make()
                    ->title('غير مسموح بالوصول')
                    ->body(
                        $incompleteLesson
                            ? "يجب عليك إكمال الدرس السابق: {$incompleteLesson->title}"
                            : 'يجب عليك إكمال الدروس السابقة أولاً'
                    )
                    ->warning()
                    ->send();
                redirect()->route('filament.admin.pages.my-courses');
                return;
            }
            
            // Get or create progress
            $this->progress = VideoProgress::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $this->lesson->id,
                ],
                [
                    'last_position_seconds' => 0,
                    'completed' => false,
                    'watch_time_minutes' => 0,
                ]
            );
        }
    }
    
    public function getHeading(): string|Htmlable
    {
        return $this->lesson ? $this->lesson->title : 'مشاهدة الفيديو';
    }
    
    public function saveProgress(int $position, int $duration): void
    {
        if (!$this->progress) {
            return;
        }
        
        $wasCompleted = $this->progress->completed;
        $isNowCompleted = $position >= ($duration * 0.9);
        
        $this->progress->update([
            'last_position_seconds' => $position,
            'last_watched_at' => now(),
            'completed' => $isNowCompleted, // 90% watched = completed
        ]);
        
        // Update watch time (approximate)
        if ($position > $this->progress->last_position_seconds) {
            $watchedSeconds = $position - $this->progress->last_position_seconds;
            $this->progress->increment('watch_time_minutes', max(1, round($watchedSeconds / 60)));
        }
        
        // Award points for completing lesson (only once)
        if ($isNowCompleted && !$wasCompleted && $this->lesson) {
            app(PointsService::class)->awardLessonCompleted(auth()->user(), $this->lesson);
        }
        
        // Update enrollment progress
        $this->updateEnrollmentProgress();
    }
    
    protected function updateEnrollmentProgress(): void
    {
        if (!$this->lesson) {
            return;
        }
        
        $course = $this->lesson->section->course;
        $user = auth()->user();
        
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
        
        if (!$enrollment) {
            return;
        }
        
        // Calculate total progress
        $totalLessons = $course->sections->sum(fn($section) => $section->lessons->count());
        $completedLessons = VideoProgress::where('user_id', $user->id)
            ->whereHas('lesson.section', function($q) use ($course) {
                $q->where('course_id', $course->id);
            })
            ->where('completed', true)
            ->count();
        
        $progress = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;
        
        $enrollment->update([
            'progress_percentage' => $progress,
            'completed_at' => $progress >= 100 ? now() : null,
        ]);
    }
}
