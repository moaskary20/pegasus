<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove use_question_bank from data as it's not a database field
        unset($data['use_question_bank']);
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        
        // If using question bank, generate questions randomly
        if (isset($data['use_question_bank']) && $data['use_question_bank'] && isset($data['question_bank_id'])) {
            $this->generateQuestionsFromBank($this->record, $data);
        }
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
            ->title('تم إنشاء الاختبار')
            ->body('تم اختيار ' . $bankQuestions->count() . ' سؤال من بنك الأسئلة')
            ->success()
            ->send();
    }
}
