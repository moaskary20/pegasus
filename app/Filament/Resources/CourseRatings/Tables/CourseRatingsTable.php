<?php

namespace App\Filament\Resources\CourseRatings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CourseRatingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('user.avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->user?->name ?? '?') . '&background=eab308&color=fff'),
                    
                TextColumn::make('user.name')
                    ->label('المقيّم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->user?->email),
                    
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->course?->title)
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('stars')
                    ->label('التقييم')
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state))
                    ->size('lg')
                    ->sortable(),
                    
                TextColumn::make('review')
                    ->label('التعليق')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->review)
                    ->placeholder('بدون تعليق')
                    ->toggleable(),
                    
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->date('Y/m/d')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('stars')
                    ->label('التقييم')
                    ->options([
                        5 => '⭐⭐⭐⭐⭐ (5)',
                        4 => '⭐⭐⭐⭐ (4)',
                        3 => '⭐⭐⭐ (3)',
                        2 => '⭐⭐ (2)',
                        1 => '⭐ (1)',
                    ]),
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
