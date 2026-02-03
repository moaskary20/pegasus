<?php

namespace App\Filament\Resources\Certificates\Tables;

use App\Filament\Resources\Certificates\CertificateResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CertificatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('user.avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->user?->name ?? '?') . '&background=14b8a6&color=fff'),
                    
                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->user?->email),
                    
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->course?->title)
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-academic-cap'),
                    
                TextColumn::make('uuid')
                    ->label('رقم الشهادة')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الشهادة!')
                    ->limit(12)
                    ->color('gray')
                    ->icon('heroicon-o-finger-print'),
                    
                IconColumn::make('pdf_path')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->pdf_path !== null)
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('warning'),
                    
                TextColumn::make('issued_at')
                    ->label('تاريخ الإصدار')
                    ->date('Y/m/d')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->description(fn ($record) => $record->issued_at?->diffForHumans()),
            ])
            ->defaultSort('issued_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('user_id')
                    ->label('الطالب')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\TernaryFilter::make('has_pdf')
                    ->label('ملف PDF')
                    ->placeholder('الكل')
                    ->trueLabel('متوفر')
                    ->falseLabel('غير متوفر')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('pdf_path'),
                        false: fn ($query) => $query->whereNull('pdf_path'),
                    ),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => CertificateResource::getUrl('view', ['record' => $record]))
                    ->color('info'),
                Action::make('download_pdf')
                    ->label('تحميل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => $record->getPdfUrl())
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->pdf_path),
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
