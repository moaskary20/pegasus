<?php

namespace App\Filament\Resources\StudentSubscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('الطالب')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('subscription_plan_id')
                    ->label('خطة الاشتراك')
                    ->relationship('subscriptionPlan', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('voucher_id')
                    ->label('القسيمة (اختياري)')
                    ->relationship('voucher', 'code')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                DateTimePicker::make('start_date')
                    ->label('تاريخ البدء')
                    ->required(),

                DateTimePicker::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->required(),

                Select::make('status')
                    ->label('حالة الاشتراك')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغى',
                    ])
                    ->required()
                    ->default('active'),

                TextInput::make('final_price')
                    ->label('السعر النهائي')
                    ->numeric()
                    ->minValue(0)
                    ->inputMode('decimal')
                    ->required(),
            ]);
    }
}
