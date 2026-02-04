<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('main_image')
                    ->label('')
                    ->square()
                    ->size(60)
                    ->getStateUsing(fn ($record) => $record->main_image ? asset('storage/' . ltrim($record->main_image, '/')) : null)
                    ->defaultImageUrl('https://placehold.co/60x60/8b5cf6/white?text=P'),
                TextColumn::make('name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->name)
                    ->description(fn ($record) => $record->sku),
                TextColumn::make('category.name')
                    ->label('التصنيف')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-tag')
                    ->placeholder('—'),
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2) . ' ج.م')
                    ->color('success')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->track_quantity) {
                            return '—';
                        }
                        $status = $record->stock_status;
                        $label = $record->stock_status_label;
                        return $state . ' (' . $label . ')';
                    })
                    ->color(fn ($record) => match ($record->stock_status ?? '') {
                        'out_of_stock' => 'danger',
                        'low_stock' => 'warning',
                        default => 'gray',
                    })
                    ->badge()
                    ->icon(fn ($record) => match ($record->stock_status ?? '') {
                        'out_of_stock' => 'heroicon-o-x-circle',
                        'low_stock' => 'heroicon-o-exclamation-triangle',
                        default => 'heroicon-o-cube',
                    }),
                TextColumn::make('average_rating')
                    ->label('التقييم')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => ($state ?? 0) > 0 ? number_format((float) $state, 1) . ' ⭐' : '—')
                    ->color(fn ($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        $state > 0 => 'gray',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_featured')
                    ->label('مميز')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
                \Filament\Tables\Filters\SelectFilter::make('stock')
                    ->label('المخزون')
                    ->options([
                        'in_stock' => 'متوفر',
                        'low_stock' => 'مخزون منخفض',
                        'out_of_stock' => 'غير متوفر',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;
                        if (!$value) return $query;
                        return match ($value) {
                            'in_stock' => $query->inStock(),
                            'low_stock' => $query->lowStock(),
                            'out_of_stock' => $query->outOfStock(),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('duplicate')
                    ->label('نسخ')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function ($record) {
                        $newProduct = $record->replicate();
                        $newProduct->name = $record->name . ' (نسخة)';
                        $newProduct->slug = null;
                        $newProduct->sku = null;
                        $newProduct->save();
                        Notification::make()
                            ->title('تم نسخ المنتج بنجاح')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
