<?php

namespace App\Filament\Pages;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use BackedEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class TakeQuiz extends Page implements HasForms
{
    use InteractsWithForms;
    
    public ?Quiz $quiz = null;
    public ?QuizAttempt $attempt = null;
    public array $answers = [];
    public bool $submitted = false;
    public ?float $score = null;
    public bool $passed = false;
    public ?int $timeRemaining = null; // in seconds
    public bool $timeExpired = false;
    
    protected static ?string $title = 'أداء الاختبار';
    
    protected string $view = 'filament.pages.take-quiz';
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    public function mount(?int $quiz = null): void
    {
        if ($quiz) {
            $this->quiz = Quiz::with(['questions', 'lesson.section.course'])->findOrFail($quiz);
            
            // Check if user is enrolled
            $user = auth()->user();
            $enrollment = $this->quiz->lesson->section->course->enrollments()
                ->where('user_id', $user->id)
                ->first();
            
            if (!$enrollment) {
                Notification::make()
                    ->title('غير مصرح')
                    ->body('يجب التسجيل في الدورة أولاً')
                    ->danger()
                    ->send();
                redirect()->route('filament.admin.pages.my-courses');
                return;
            }
            
            // Check attempts
            $previousAttempts = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $this->quiz->id)
                ->count();
            
            if ($this->quiz->max_attempts && $previousAttempts >= $this->quiz->max_attempts) {
                Notification::make()
                    ->title('تم تجاوز الحد الأقصى')
                    ->body('لقد استنفدت عدد المحاولات المسموح به')
                    ->warning()
                    ->send();
                redirect()->route('filament.admin.pages.my-courses');
                return;
            }
            
            // Create or get attempt
            $this->attempt = QuizAttempt::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'quiz_id' => $this->quiz->id,
                    'submitted_at' => null,
                ],
                [
                    'started_at' => now(),
                    'answers' => [],
                    'attempt_number' => $previousAttempts + 1,
                ]
            );
            
            if ($this->attempt->submitted_at) {
                $this->submitted = true;
                $this->score = $this->attempt->score;
                $this->passed = $this->attempt->passed;
                $this->answers = $this->attempt->answers;
            } else {
                $this->answers = $this->attempt->answers ?? [];
                
                // Calculate time remaining
                if ($this->quiz->duration_minutes) {
                    $startedAt = $this->attempt->started_at ?? now();
                    $durationSeconds = $this->quiz->duration_minutes * 60;
                    $elapsed = now()->diffInSeconds($startedAt);
                    $this->timeRemaining = max(0, $durationSeconds - $elapsed);
                    
                    if ($this->timeRemaining <= 0) {
                        $this->timeExpired = true;
                        $this->autoSubmit();
                    }
                }
            }
        }
    }
    
    public function form(Schema $schema): Schema
    {
        if (!$this->quiz || $this->submitted) {
            return $schema;
        }
        
        $components = [];
        
        foreach ($this->quiz->questions as $question) {
            $fieldName = "answers.{$question->id}";
            
            switch ($question->type) {
                case 'mcq':
                    $components[] = Radio::make($fieldName)
                        ->label($question->question_text)
                        ->options($question->options ?? [])
                        ->required()
                        ->default($this->answers[$question->id] ?? null);
                    break;
                    
                case 'true_false':
                    $components[] = Radio::make($fieldName)
                        ->label($question->question_text)
                        ->options([
                            'true' => 'صح',
                            'false' => 'خطأ',
                        ])
                        ->required()
                        ->default($this->answers[$question->id] ?? null);
                    break;
                    
                case 'fill_blank':
                    $components[] = Textarea::make($fieldName)
                        ->label($question->question_text)
                        ->required()
                        ->rows(2)
                        ->default($this->answers[$question->id] ?? null);
                    break;
                    
                case 'matching':
                    // For matching, we'll use a simple textarea for now
                    $components[] = Textarea::make($fieldName)
                        ->label($question->question_text)
                        ->helperText('أدخل الإجابات بصيغة JSON: {"key1": "value1", "key2": "value2"}')
                        ->required()
                        ->rows(3)
                        ->default($this->answers[$question->id] ?? null);
                    break;
            }
        }
        
        return $schema->components($components);
    }
    
    public function autoSubmit(): void
    {
        if ($this->submitted) {
            return;
        }
        
        $this->submit();
    }
    
    public function submit(): void
    {
        if (!$this->quiz || $this->submitted || $this->timeExpired) {
            return;
        }
        
        $data = $this->form->getState();
        $answers = $data['answers'] ?? [];
        
        // Calculate score
        $totalPoints = 0;
        $earnedPoints = 0;
        
        foreach ($this->quiz->questions as $question) {
            $totalPoints += $question->points;
            $userAnswer = $answers[$question->id] ?? null;
            
            if ($userAnswer !== null) {
                $correctAnswer = $question->correct_answer;
                
                switch ($question->type) {
                    case 'mcq':
                    case 'true_false':
                        if (is_array($correctAnswer) && in_array($userAnswer, $correctAnswer)) {
                            $earnedPoints += $question->points;
                        }
                        break;
                        
                    case 'fill_blank':
                        if (is_array($correctAnswer)) {
                            $userAnswerLower = strtolower(trim($userAnswer));
                            foreach ($correctAnswer as $correct) {
                                if (strtolower(trim($correct)) === $userAnswerLower) {
                                    $earnedPoints += $question->points;
                                    break;
                                }
                            }
                        }
                        break;
                        
                    case 'matching':
                        // Simple comparison for matching
                        if (is_array($correctAnswer) && is_string($userAnswer)) {
                            $userAnswerArray = json_decode($userAnswer, true);
                            if ($userAnswerArray && is_array($userAnswerArray)) {
                                $matches = 0;
                                foreach ($correctAnswer as $key => $value) {
                                    if (isset($userAnswerArray[$key]) && $userAnswerArray[$key] === $value) {
                                        $matches++;
                                    }
                                }
                                if ($matches === count($correctAnswer)) {
                                    $earnedPoints += $question->points;
                                }
                            }
                        }
                        break;
                }
            }
        }
        
        $score = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        $passed = $score >= $this->quiz->pass_percentage;
        
        // Save attempt
        $this->attempt->update([
            'answers' => $answers,
            'score' => $score,
            'passed' => $passed,
            'submitted_at' => now(),
        ]);
        
        $this->submitted = true;
        $this->score = $score;
        $this->passed = $passed;
        $this->answers = $answers;
        
        Notification::make()
            ->title($passed ? 'تهانينا! نجحت في الاختبار' : 'لم تنجح في الاختبار')
            ->body("نقاطك: " . number_format($score, 1) . "% (المطلوب: {$this->quiz->pass_percentage}%)")
            ->success($passed)
            ->warning(!$passed)
            ->send();
    }
    
    public function getHeading(): string|Htmlable
    {
        return $this->quiz ? $this->quiz->title : 'أداء الاختبار';
    }
}
