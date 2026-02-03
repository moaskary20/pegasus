<?php

namespace App\Filament\Resources\StudentSubscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('subscriptionPlan.name')
                    ->label('خطة الاشتراك')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subscriptionPlan.type')
                    ->label('نوع الاشتراك')
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

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn($state) => match($state) {
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغى',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'active' => 'success',
                        'expired' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('final_price')
                    ->label('السعر النهائي')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->date('Y/m/d')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y/m/d')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغى',
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
