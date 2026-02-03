<?php

namespace App\Filament\Resources\Assignments\Tables;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Models\Assignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->description(fn ($record) => $record->lesson?->title),
                
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Assignment::getTypes()[$state] ?? $state)
                    ->color(fn ($state) => $state === 'project' ? 'warning' : 'info')
                    ->icon(fn ($state) => $state === 'project' ? 'heroicon-o-folder' : 'heroicon-o-document-text'),
                
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->course?->title)
                    ->toggleable(),
                
                TextColumn::make('max_score')
                    ->label('الدرجة')
                    ->formatStateUsing(fn ($state, $record) => "{$record->passing_score}/{$state}")
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('submissions_count')
                    ->label('التسليمات')
                    ->counts('submissions')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-arrow-up-tray'),
                
                TextColumn::make('pending_count')
                    ->label('بانتظار التقييم')
                    ->getStateUsing(fn ($record) => $record->submissions()->where('status', 'submitted')->count())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),
                
                TextColumn::make('due_date')
                    ->label('موعد التسليم')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : 'gray')
                    ->icon(fn ($record) => $record->isOverdue() ? 'heroicon-o-exclamation-circle' : 'heroicon-o-clock')
                    ->placeholder('بدون موعد'),
                
                IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options(Assignment::getTypes()),
                
                SelectFilter::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                
                TernaryFilter::make('is_published')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('منشور')
                    ->falseLabel('مسودة'),
                
                TernaryFilter::make('has_pending')
                    ->label('تسليمات معلقة')
                    ->placeholder('الكل')
                    ->trueLabel('يوجد')
                    ->falseLabel('لا يوجد')
                    ->queries(
                        true: fn ($query) => $query->whereHas('submissions', fn ($q) => $q->where('status', 'submitted')),
                        false: fn ($query) => $query->whereDoesntHave('submissions', fn ($q) => $q->where('status', 'submitted')),
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('submissions')
                    ->label('التسليمات')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->url(fn ($record) => AssignmentResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
