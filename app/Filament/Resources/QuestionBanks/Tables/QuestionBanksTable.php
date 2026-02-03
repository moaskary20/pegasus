<?php

namespace App\Filament\Resources\QuestionBanks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionBanksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان البنك')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-rectangle-stack')
                    ->description(fn ($record) => $record->description ? \Str::limit($record->description, 40) : null),
                    
                TextColumn::make('user.name')
                    ->label('المدرس')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-user'),
                    
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->formatStateUsing(fn ($state) => $state ?? 'عام')
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'success')
                    ->icon(fn ($state) => $state ? 'heroicon-o-academic-cap' : 'heroicon-o-globe-alt'),
                    
                TextColumn::make('questions_count')
                    ->label('الأسئلة')
                    ->counts('questions')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->suffix(' سؤال'),
                    
                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
                \Filament\Tables\Filters\TernaryFilter::make('has_course')
                    ->label('النوع')
                    ->placeholder('الكل')
                    ->trueLabel('مرتبط بدورة')
                    ->falseLabel('عام')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('course_id'),
                        false: fn ($query) => $query->whereNull('course_id'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
