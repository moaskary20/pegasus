<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('الرابط')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('parent_id')
                    ->label('التصنيف الأب')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('اتركه فارغاً للتصنيف الرئيسي'),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull()
                    ->rows(3),
                TextInput::make('icon')
                    ->label('الأيقونة')
                    ->helperText('اسم الأيقونة (مثل: book, video)'),
                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }
}
