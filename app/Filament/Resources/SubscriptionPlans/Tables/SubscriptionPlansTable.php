<?php

namespace App\Filament\Resources\SubscriptionPlans\Tables;

use App\Models\SubscriptionPlan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الخطة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'once' => 'اشتراك واحد',
                            'monthly' => 'اشتراك شهري',
                            'daily' => 'اشتراك يومي',
                            default => $state,
                        };
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'once' => 'success',
                        'monthly' => 'info',
                        'daily' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('duration_days')
                    ->label('المدة (أيام)')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('max_lessons')
                    ->label('عدد الدروس')
                    ->numeric()
                    ->default('-')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع الاشتراك')
                    ->options([
                        'once' => 'اشتراك واحد',
                        'monthly' => 'اشتراك شهري',
                        'daily' => 'اشتراك يومي',
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
