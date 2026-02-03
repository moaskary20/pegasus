<?php

namespace App\Filament\Resources\Rewards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RewardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'discount' => 'خصم',
                        'free_course' => 'دورة مجانية',
                        'badge' => 'شارة',
                        'certificate' => 'شهادة',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'discount' => 'warning',
                        'free_course' => 'success',
                        'badge' => 'info',
                        'certificate' => 'primary',
                        default => 'gray',
                    }),
                    
                TextColumn::make('points_required')
                    ->label('النقاط المطلوبة')
                    ->numeric()
                    ->sortable()
                    ->suffix(' نقطة'),
                    
                TextColumn::make('redeemed_count')
                    ->label('مرات الاستبدال')
                    ->numeric()
                    ->sortable(),
                    
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                    
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->placeholder('غير محدود')
                    ->sortable(),
                    
                TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->dateTime('Y/m/d')
                    ->placeholder('بلا انتهاء')
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
