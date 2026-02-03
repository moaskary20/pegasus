<?php

namespace App\Filament\Resources\Courses\Traits;

use Filament\Notifications\Notification;

trait GeneratesQuestionsFromBank
{
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
        
        if ($bankQuestions->isEmpty()) {
            Notification::make()
                ->title('تحذير')
                ->body('بنك الأسئلة المحدد لا يحتوي على أسئلة')
                ->warning()
                ->send();
            return;
        }
        
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
        
        Notification::make()
            ->title('تم إنشاء الاختبار')
            ->body('تم اختيار ' . $bankQuestions->count() . ' سؤال من بنك الأسئلة: ' . $bank->title)
            ->success()
            ->send();
    }
}
