<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الطلب!')
                    ->weight('bold')
                    ->color('primary')
                    ->icon('heroicon-o-shopping-bag'),
                    
                ImageColumn::make('user.avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->user?->name ?? '?') . '&background=ec4899&color=fff'),
                    
                TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->user?->email),
                    
                TextColumn::make('order_items_count')
                    ->label('الدورات')
                    ->counts('orderItems')
                    ->badge()
                    ->color('info')
                    ->suffix(' دورة'),
                    
                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ج.م')
                    ->size('lg')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),
                    
                TextColumn::make('discount')
                    ->label('الخصم')
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 2) . ' ج.م' : '—')
                    ->color('danger')
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'paid' => 'heroicon-o-check-circle',
                        'failed' => 'heroicon-o-x-circle',
                        'refunded' => 'heroicon-o-arrow-uturn-left',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),
                    
                TextColumn::make('payment_gateway')
                    ->label('الدفع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ((string) $state) {
                        'kashier' => 'فيزا / ماستر / ميزة',
                        'manual' => 'يدوي (بانتظار المراجعة)',
                        default => $state ?: '—',
                    })
                    ->color(fn ($state) => match ((string) $state) {
                        'manual' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('—'),

                TextColumn::make('manual_receipt_path')
                    ->label('الإيصال')
                    ->formatStateUsing(fn ($state) => $state ? 'مرفق' : '—')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->url(fn ($record) => $record->manual_receipt_path ? asset('storage/' . ltrim((string) $record->manual_receipt_path, '/')) : null)
                    ->openUrlInNewTab()
                    ->toggleable(),
                    
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->date('Y/m/d')
                    ->description(fn ($record) => $record->created_at->diffForHumans())
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('payment_gateway')
                    ->label('بوابة الدفع')
                    ->options([
                        'kashier' => 'فيزا / ماستر / ميزة',
                        'manual' => 'يدوي',
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
            ->paginated([10, 25, 50, 100]);
    }
}
