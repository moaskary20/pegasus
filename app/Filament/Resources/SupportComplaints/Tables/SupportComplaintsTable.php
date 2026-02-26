<?php

namespace App\Filament\Resources\SupportComplaints\Tables;

use App\Models\SupportComplaint;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupportComplaintsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->email),
                TextColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state) => $state === SupportComplaint::TYPE_COMPLAINT ? 'شكوى' : 'تواصل / استفسار')
                    ->badge()
                    ->color(fn (string $state) => $state === SupportComplaint::TYPE_COMPLAINT ? 'danger' : 'info')
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('message')
                    ->label('الرسالة')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        SupportComplaint::STATUS_PENDING => 'قيد الانتظار',
                        SupportComplaint::STATUS_IN_PROGRESS => 'قيد المعالجة',
                        SupportComplaint::STATUS_RESOLVED => 'تم الحل',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        SupportComplaint::STATUS_PENDING => 'warning',
                        SupportComplaint::STATUS_IN_PROGRESS => 'info',
                        SupportComplaint::STATUS_RESOLVED => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y H:i')
                    ->description(fn ($record) => $record->created_at->diffForHumans())
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        SupportComplaint::TYPE_COMPLAINT => 'شكوى',
                        SupportComplaint::TYPE_CONTACT => 'تواصل / استفسار',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        SupportComplaint::STATUS_PENDING => 'قيد الانتظار',
                        SupportComplaint::STATUS_IN_PROGRESS => 'قيد المعالجة',
                        SupportComplaint::STATUS_RESOLVED => 'تم الحل',
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
