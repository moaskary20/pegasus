<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(30)
                    ->sortable(),

                TextColumn::make('discount_percentage')
                    ->label('نسبة الخصم')
                    ->formatStateUsing(fn($state) => $state ? "$state%" : "-")
                    ->sortable(),

                TextColumn::make('discount_amount')
                    ->label('مبلغ الخصم')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('usage_count')
                    ->label('الاستخدامات')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('max_usage_count')
                    ->label('الحد الأقصى')
                    ->numeric()
                    ->default('-'),

                IconColumn::make('is_active')
                    ->label('النشط')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('نشط فقط'),

                SelectFilter::make('discount_type')
                    ->label('نوع الخصم')
                    ->options([
                        'percentage' => 'نسبة مئوية',
                        'fixed' => 'مبلغ ثابت',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
