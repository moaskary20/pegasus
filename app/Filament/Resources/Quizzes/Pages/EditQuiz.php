<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    
    public function getTitle(): string
    {
        return 'تعديل الاختبار: ' . $this->record->title;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove use_question_bank from data as it's not a database field
        unset($data['use_question_bank']);
        
        // If question bank changed, regenerate questions
        if (isset($data['question_bank_id']) && $data['question_bank_id'] !== $this->record->question_bank_id) {
            // Delete existing questions
            $this->record->questions()->delete();
            
            // Generate new questions from bank
            $this->generateQuestionsFromBank($this->record, $data);
        } elseif (isset($data['question_bank_id']) && 
                   ($data['questions_count'] !== $this->record->questions_count || 
                    $data['randomize_questions'] !== $this->record->randomize_questions)) {
            // If count or randomize changed, regenerate
            $this->record->questions()->delete();
            $this->generateQuestionsFromBank($this->record, $data);
        }
        
        return $data;
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Add use_question_bank flag based on whether question_bank_id exists
        $data['use_question_bank'] = !empty($this->record->question_bank_id);
        
        return $data;
    }
    
    protected function generateQuestionsFromBank($quiz, array $data): void
    {
        $bank = \App\Models\QuestionBank::find($data['question_bank_id']);
        
        if (!$bank) {
            return;
        }
        
        $questionsCount = $data['questions_count'] ?? 10;
        $randomize = $data['randomize_questions'] ?? true;
        
        $query = $bank->questions();
        
        if ($randomize) {
            $query->inRandomOrder();
        } else {
            $query->orderBy('sort_order');
        }
        
        $bankQuestions = $query->limit($questionsCount)->get();
        
        $sortOrder = 0;
        foreach ($bankQuestions as $bankQuestion) {
            \App\Models\QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'type' => $bankQuestion->type,
                'question_text' => $bankQuestion->question_text,
                'options' => $bankQuestion->options,
                'correct_answer' => $bankQuestion->correct_answer,
                'explanation' => $bankQuestion->explanation,
                'points' => $bankQuestion->points,
                'sort_order' => $sortOrder++,
            ]);
        }
        
        \Filament\Notifications\Notification::make()
            ->title('تم تحديث الأسئلة')
            ->body('تم اختيار ' . $bankQuestions->count() . ' سؤال من بنك الأسئلة')
            ->success()
            ->send();
    }
}
