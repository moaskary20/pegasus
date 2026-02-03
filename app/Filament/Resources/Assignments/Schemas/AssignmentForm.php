<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Lesson;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('عنوان الواجب')
                ->required()
                ->maxLength(255)
                ->placeholder('مثال: مشروع نهاية الوحدة'),
            
            Select::make('type')
                ->label('النوع')
                ->options(Assignment::getTypes())
                ->default('assignment')
                ->required(),
            
            Select::make('course_id')
                ->label('الدورة')
                ->options(Course::pluck('title', 'id'))
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn ($set) => $set('lesson_id', null)),
            
            Select::make('lesson_id')
                ->label('الدرس')
                ->options(function ($get) {
                    $courseId = $get('course_id');
                    if (!$courseId) return [];
                    return Lesson::whereHas('section', fn($q) => $q->where('course_id', $courseId))
                        ->pluck('title', 'id');
                })
                ->searchable()
                ->required(),
            
            RichEditor::make('description')
                ->label('وصف الواجب')
                ->required()
                ->columnSpanFull(),
            
            RichEditor::make('instructions')
                ->label('تعليمات التسليم')
                ->placeholder('أضف تعليمات واضحة للطلاب حول كيفية إكمال وتسليم الواجب')
                ->columnSpanFull(),
            
            TextInput::make('max_score')
                ->label('الدرجة الكاملة')
                ->numeric()
                ->default(100)
                ->required()
                ->suffix('درجة'),
            
            TextInput::make('passing_score')
                ->label('درجة النجاح')
                ->numeric()
                ->default(60)
                ->required()
                ->suffix('درجة'),
            
            DateTimePicker::make('due_date')
                ->label('موعد التسليم')
                ->native(false)
                ->displayFormat('Y/m/d H:i'),
            
            Toggle::make('allow_late_submission')
                ->label('السماح بالتسليم المتأخر')
                ->default(true)
                ->live(),
            
            TextInput::make('late_penalty_percent')
                ->label('خصم التأخير')
                ->numeric()
                ->default(10)
                ->suffix('%')
                ->visible(fn ($get) => $get('allow_late_submission')),
            
            Toggle::make('allow_resubmission')
                ->label('السماح بإعادة التسليم')
                ->default(true),
            
            TextInput::make('max_submissions')
                ->label('الحد الأقصى للمحاولات')
                ->numeric()
                ->placeholder('غير محدود'),
            
            TagsInput::make('allowed_file_types')
                ->label('أنواع الملفات المسموحة')
                ->placeholder('أضف نوع ملف')
                ->suggestions(['pdf', 'doc', 'docx', 'zip', 'rar', 'jpg', 'png', 'pptx', 'xlsx']),
            
            TextInput::make('max_file_size_mb')
                ->label('الحد الأقصى لحجم الملف')
                ->numeric()
                ->default(10)
                ->suffix('MB'),
            
            Toggle::make('is_published')
                ->label('نشر الواجب')
                ->default(true)
                ->helperText('الواجب سيكون مرئياً للطلاب عند النشر'),
        ]);
    }
}
