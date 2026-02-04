<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم المنتج')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                TextInput::make('slug')
                    ->label('الرابط')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('يُولّد تلقائياً من الاسم'),
                TextInput::make('sku')
                    ->label('رمز المنتج (SKU)')
                    ->maxLength(100)
                    ->placeholder('PRD-XXXXX'),
                Textarea::make('short_description')
                    ->label('وصف مختصر')
                    ->rows(2)
                    ->maxLength(500),
                Textarea::make('description')
                    ->label('الوصف الكامل')
                    ->columnSpanFull()
                    ->rows(4),
                Select::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name', fn ($query) => $query->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->placeholder('بدون تصنيف'),
                FileUpload::make('main_image')
                    ->label('الصورة الرئيسية')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public')
                    ->imagePreviewHeight('150'),
                TextInput::make('price')
                    ->label('السعر')
                    ->required()
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0),
                TextInput::make('compare_price')
                    ->label('السعر قبل الخصم')
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0)
                    ->helperText('السعر الأصلي قبل التخفيض'),
                TextInput::make('cost_price')
                    ->label('سعر التكلفة')
                    ->numeric()
                    ->prefix('ج.م')
                    ->minValue(0),
                TextInput::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->visible(fn ($get) => $get('track_quantity') !== false),
                TextInput::make('low_stock_threshold')
                    ->label('حد التنبيه للمخزون')
                    ->numeric()
                    ->default(5)
                    ->minValue(0)
                    ->visible(fn ($get) => $get('track_quantity') !== false),
                TextInput::make('weight')
                    ->label('الوزن (جرام)')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('dimensions')
                    ->label('الأبعاد')
                    ->maxLength(100)
                    ->placeholder('مثال: 10x20x30'),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
                Toggle::make('is_featured')
                    ->label('مميز')
                    ->default(false),
                Toggle::make('track_quantity')
                    ->label('تتبع المخزون')
                    ->default(true)
                    ->reactive(),
                Toggle::make('requires_shipping')
                    ->label('يتطلب شحن')
                    ->default(true),
                Toggle::make('is_digital')
                    ->label('منتج رقمي')
                    ->default(false),
            ]);
    }
}
