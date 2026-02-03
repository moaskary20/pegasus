<?php

namespace App\Filament\Resources\CourseQuestions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive(),
                Select::make('user_id')
                    ->label('الطالب')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('lesson_id')
                    ->label('المحاضرة (اختياري)')
                    ->relationship('lesson', 'title', fn ($query, $get) => 
                        $query->whereHas('section.course', fn ($q) => 
                            $q->where('id', $get('course_id'))
                        )
                    )
                    ->searchable()
                    ->preload(),
                Textarea::make('question')
                    ->label('السؤال')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Toggle::make('is_answered')
                    ->label('تم الرد')
                    ->default(false),
            ]);
    }
}
