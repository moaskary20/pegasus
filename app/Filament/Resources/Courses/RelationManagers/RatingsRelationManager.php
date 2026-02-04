<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RatingsRelationManager extends RelationManager
{
    protected static string $relationship = 'ratings';

    protected static ?string $title = 'التقييمات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('المقيّم')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('stars')
                    ->label('التقييم')
                    ->options([
                        1 => '⭐ (1)',
                        2 => '⭐⭐ (2)',
                        3 => '⭐⭐⭐ (3)',
                        4 => '⭐⭐⭐⭐ (4)',
                        5 => '⭐⭐⭐⭐⭐ (5)',
                    ])
                    ->default(5)
                    ->required(),
                Textarea::make('review')
                    ->label('التعليق')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stars')
            ->columns([
                TextColumn::make('user.name')
                    ->label('المقيّم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('stars')
                    ->label('التقييم')
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state))
                    ->sortable(),
                TextColumn::make('review')
                    ->label('التعليق')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->review)
                    ->placeholder('بدون تعليق')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->date('Y/m/d')
                    ->sortable()
                    ->since(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['course_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد تقييمات')
            ->emptyStateDescription('التقييمات التي يضيفها المشتركون تظهر هنا. يمكنك أيضاً إضافتها يدوياً.')
            ->emptyStateIcon('heroicon-o-star');
    }
}
