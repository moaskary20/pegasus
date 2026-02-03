<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('كود الكوبون')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('تم نسخ الكود!')
                    ->weight('bold')
                    ->color('primary')
                    ->icon('heroicon-o-ticket'),
                    
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percent' => 'نسبة مئوية',
                        'fixed' => 'مبلغ ثابت',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'percent' => 'info',
                        'fixed' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'percent' => 'heroicon-o-receipt-percent',
                        'fixed' => 'heroicon-o-banknotes',
                        default => 'heroicon-o-tag',
                    }),
                    
                TextColumn::make('value')
                    ->label('الخصم')
                    ->formatStateUsing(function ($record) {
                        return $record->type === 'percent' 
                            ? number_format($record->value, 0) . '%'
                            : number_format($record->value, 2) . ' ج.م';
                    })
                    ->sortable()
                    ->size('lg')
                    ->weight('bold')
                    ->color('danger'),
                    
                TextColumn::make('min_purchase')
                    ->label('الحد الأدنى')
                    ->money('EGP')
                    ->sortable()
                    ->placeholder('بدون حد'),
                    
                TextColumn::make('used_count')
                    ->label('الاستخدام')
                    ->formatStateUsing(function ($record) {
                        $limit = $record->usage_limit;
                        return $limit 
                            ? "{$record->used_count} / {$limit}"
                            : $record->used_count . ' مرة';
                    })
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->usage_limit && $record->used_count >= $record->usage_limit ? 'danger' : 'gray'),
                    
                TextColumn::make('expires_at')
                    ->label('الانتهاء')
                    ->date('Y/m/d')
                    ->sortable()
                    ->color(fn ($record) => $record->expires_at && $record->expires_at < now() ? 'danger' : 'success')
                    ->icon(fn ($record) => $record->expires_at && $record->expires_at < now() ? 'heroicon-o-x-circle' : 'heroicon-o-clock')
                    ->placeholder('بدون انتهاء'),
                    
                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'percent' => 'نسبة مئوية',
                        'fixed' => 'مبلغ ثابت',
                    ]),
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
                \Filament\Tables\Filters\TernaryFilter::make('expired')
                    ->label('الصلاحية')
                    ->placeholder('الكل')
                    ->trueLabel('منتهية')
                    ->falseLabel('صالحة')
                    ->queries(
                        true: fn ($query) => $query->where('expires_at', '<', now()),
                        false: fn ($query) => $query->where(function ($q) {
                            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                        }),
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
