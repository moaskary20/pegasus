<?php

namespace App\Filament\Resources\Quizzes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuizzesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lesson.title')
                    ->label('المحاضرة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('questions_count')
                    ->label('عدد الأسئلة')
                    ->counts('questions'),
                TextColumn::make('duration_minutes')
                    ->label('المدة')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' دقيقة' : 'غير محدود')
                    ->sortable(),
                TextColumn::make('pass_percentage')
                    ->label('نسبة النجاح')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(),
                IconColumn::make('allow_retake')
                    ->label('إعادة المحاولة')
                    ->boolean(),
                TextColumn::make('max_attempts')
                    ->label('الحد الأقصى')
                    ->formatStateUsing(fn ($state) => $state ?? 'غير محدود')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
