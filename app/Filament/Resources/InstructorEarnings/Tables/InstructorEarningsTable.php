<?php

namespace App\Filament\Resources\InstructorEarnings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InstructorEarningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('المدرس')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('earnings_type')
                    ->label('نوع الأرباح')
                    ->formatStateUsing(fn ($state) => $state === 'percentage' ? 'نسبة مئوية' : 'مبلغ ثابت')
                    ->badge()
                    ->color(fn ($state) => $state === 'percentage' ? 'info' : 'success'),
                TextColumn::make('earnings_value')
                    ->label('القيمة')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->earnings_type === 'percentage' 
                            ? number_format($state, 2) . '%'
                            : number_format($state, 2) . ' ج.م';
                    })
                    ->sortable(),
                TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->getStateUsing(fn ($record) => $record->getStudentsCount())
                    ->badge()
                    ->color('info'),
                TextColumn::make('total_payments')
                    ->label('إجمالي المدفوعات')
                    ->getStateUsing(fn ($record) => number_format($record->getTotalPayments(), 2) . ' ج.م')
                    ->sortable(),
                TextColumn::make('total_earnings')
                    ->label('إجمالي الأرباح')
                    ->getStateUsing(fn ($record) => number_format($record->calculateTotalEarnings(), 2) . ' ج.م')
                    ->color('success')
                    ->weight('bold')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('المدرس')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('earnings_type')
                    ->label('نوع الأرباح')
                    ->options([
                        'percentage' => 'نسبة مئوية',
                        'fixed' => 'مبلغ ثابت',
                    ]),
                SelectFilter::make('is_active')
                    ->label('الحالة')
                    ->options([
                        1 => 'نشط',
                        0 => 'غير نشط',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
