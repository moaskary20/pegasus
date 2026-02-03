<?php

namespace App\Filament\Resources\Lessons\RelationManagers;

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

class LessonFilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';
    
    protected static ?string $title = 'الملفات المرفقة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('path')
                    ->label('الملف')
                    ->directory('lesson-files')
                    ->visibility('public')
                    ->required(),
                Select::make('type')
                    ->label('النوع')
                    ->options([
                        'pdf' => 'PDF',
                        'doc' => 'Word Document',
                        'image' => 'صورة',
                    ])
                    ->default('pdf')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pdf' => 'PDF',
                        'doc' => 'Word',
                        'image' => 'صورة',
                        default => $state,
                    }),
                TextColumn::make('size')
                    ->label('الحجم')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2) . ' KB' : '-')
                    ->sortable(),
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
