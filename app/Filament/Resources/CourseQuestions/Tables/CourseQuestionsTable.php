<?php

namespace App\Filament\Resources\CourseQuestions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CourseQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lesson.title')
                    ->label('المحاضرة')
                    ->searchable()
                    ->sortable()
                    ->placeholder('عام'),
                TextColumn::make('question')
                    ->label('السؤال')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                IconColumn::make('is_answered')
                    ->label('تم الرد')
                    ->boolean(),
                TextColumn::make('answers_count')
                    ->label('عدد الردود')
                    ->counts('answers'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\TernaryFilter::make('is_answered')
                    ->label('تم الرد')
                    ->placeholder('الكل')
                    ->trueLabel('تم الرد')
                    ->falseLabel('لم يتم الرد'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
