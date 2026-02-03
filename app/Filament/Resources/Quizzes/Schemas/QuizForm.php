<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lesson_id')
                    ->label('المحاضرة')
                    ->relationship('lesson', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull()
                    ->rows(3),
                TextInput::make('duration_minutes')
                    ->label('المدة (بالدقائق)')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('اتركه فارغاً لعدم تحديد مدة زمنية'),
                TextInput::make('pass_percentage')
                    ->label('نسبة النجاح')
                    ->numeric()
                    ->default(60)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),
                Toggle::make('allow_retake')
                    ->label('السماح بإعادة المحاولة')
                    ->default(true),
                TextInput::make('max_attempts')
                    ->label('الحد الأقصى للمحاولات')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('اتركه فارغاً للسماح بعدد غير محدود'),
                Toggle::make('use_question_bank')
                    ->label('استخدام بنك أسئلة')
                    ->default(false)
                    ->reactive()
                    ->helperText('تفعيل استخدام بنك أسئلة لاختيار أسئلة عشوائية'),
                Select::make('question_bank_id')
                    ->label('بنك الأسئلة')
                    ->relationship('questionBank', 'title', function ($query, $get) {
                        $lessonId = $get('lesson_id');
                        if ($lessonId) {
                            $lesson = \App\Models\Lesson::find($lessonId);
                            if ($lesson) {
                                $courseId = $lesson->section->course_id;
                                // عرض البنوك العامة + بنوك الدورة
                                return $query->where(function($q) use ($courseId) {
                                    $q->whereNull('course_id')
                                      ->orWhere('course_id', $courseId);
                                })
                                ->where('is_active', true);
                            }
                        }
                        return $query->where('is_active', true);
                    })
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => $get('use_question_bank') === true)
                    ->required(fn ($get) => $get('use_question_bank') === true)
                    ->helperText('اختر بنك الأسئلة المراد استخدامه'),
                TextInput::make('questions_count')
                    ->label('عدد الأسئلة المطلوبة')
                    ->numeric()
                    ->minValue(1)
                    ->default(10)
                    ->required(fn ($get) => $get('use_question_bank') === true)
                    ->visible(fn ($get) => $get('use_question_bank') === true)
                    ->helperText('عدد الأسئلة التي سيتم اختيارها عشوائياً من البنك'),
                Toggle::make('randomize_questions')
                    ->label('اختيار عشوائي للأسئلة')
                    ->default(true)
                    ->visible(fn ($get) => $get('use_question_bank') === true)
                    ->helperText('إذا كان مفعلاً، سيتم اختيار الأسئلة بشكل عشوائي من البنك'),
            ]);
    }
}
