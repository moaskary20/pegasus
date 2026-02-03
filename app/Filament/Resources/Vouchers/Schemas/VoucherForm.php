<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('كود القسيمة')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->placeholder('مثال: SUMMER2025'),

                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull()
                    ->rows(2),

                Select::make('discount_type')
                    ->label('نوع الخصم')
                    ->options([
                        'percentage' => 'نسبة مئوية (%)',
                        'fixed' => 'مبلغ ثابت',
                    ])
                    ->reactive()
                    ->required(),

                TextInput::make('discount_percentage')
                    ->label('نسبة الخصم (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->nullable()
                    ->visible(fn ($get) => $get('discount_type') === 'percentage'),

                TextInput::make('discount_amount')
                    ->label('مبلغ الخصم (ثابت)')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->inputMode('decimal')
                    ->visible(fn ($get) => $get('discount_type') === 'fixed'),

                TextInput::make('max_usage_count')
                    ->label('عدد مرات الاستخدام الأقصى')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->helperText('اتركه فارغاً للعدد غير المحدود'),

                DateTimePicker::make('start_date')
                    ->label('تاريخ البدء')
                    ->nullable(),

                DateTimePicker::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->nullable(),

                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }
}
