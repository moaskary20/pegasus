<?php

namespace App\Filament\Resources\Enrollments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('user.avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->user?->name ?? '?') . '&background=06b6d4&color=fff'),
                    
                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->user?->email),
                    
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->course?->title),
                    
                TextColumn::make('course.instructor.name')
                    ->label('المدرس')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-user'),
                    
                TextColumn::make('price_paid')
                    ->label('المدفوع')
                    ->money('EGP')
                    ->sortable()
                    ->color('success'),
                    
                TextColumn::make('progress_percentage')
                    ->label('التقدم')
                    ->formatStateUsing(fn ($state) => number_format($state, 0) . '%')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'info',
                        $state >= 50 => 'warning',
                        $state > 0 => 'gray',
                        default => 'danger',
                    })
                    ->sortable(),
                    
                IconColumn::make('completed_at')
                    ->label('مكتمل')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->completed_at !== null)
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                    
                TextColumn::make('enrolled_at')
                    ->label('تاريخ التسجيل')
                    ->date('Y/m/d')
                    ->sortable(),
            ])
            ->defaultSort('enrolled_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\TernaryFilter::make('completed')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('مكتمل')
                    ->falseLabel('غير مكتمل')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('completed_at'),
                        false: fn ($query) => $query->whereNull('completed_at'),
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
            ->paginated([10, 25, 50, 100]);
    }
}
