<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VideosRelationManager extends RelationManager
{
    protected static string $relationship = 'video';
    
    protected static ?string $title = 'الفيديو';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('ملف الفيديو')
                    ->acceptedFileTypes(['video/mp4', 'video/quicktime', 'video/x-msvideo'])
                    ->directory('videos/original')
                    ->visibility('private')
                    ->required(),
                Select::make('disk')
                    ->label('قرص التخزين')
                    ->options([
                        'local' => 'محلي',
                        's3' => 'S3 / DigitalOcean Spaces',
                    ])
                    ->default('local')
                    ->required(),
                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'processing' => 'قيد المعالجة',
                        'ready' => 'جاهز',
                        'failed' => 'فشل',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('lesson.title')
            ->columns([
                TextColumn::make('lesson.title')
                    ->label('المحاضرة')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'processing' => 'قيد المعالجة',
                        'ready' => 'جاهز',
                        'failed' => 'فشل',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'ready' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('duration_seconds')
                    ->label('المدة')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('H:i:s', $state) : '-')
                    ->sortable(),
                TextColumn::make('disk')
                    ->label('قرص التخزين'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
