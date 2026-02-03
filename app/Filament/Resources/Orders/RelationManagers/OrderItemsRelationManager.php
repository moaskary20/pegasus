<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';
    
    protected static ?string $title = 'عناصر الطلب';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $course = \App\Models\Course::find($state);
                            if ($course) {
                                $set('price', $course->offer_price ?? $course->price);
                            }
                        }
                    }),
                TextInput::make('price')
                    ->label('السعر')
                    ->numeric()
                    ->required()
                    ->prefix('ج.م')
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('course.title')
            ->columns([
                TextColumn::make('course.title')
                    ->label('الدورة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.instructor.name')
                    ->label('المدرس')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
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
