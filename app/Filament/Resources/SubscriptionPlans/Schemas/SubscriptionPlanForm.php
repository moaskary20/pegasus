<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الخطة')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull()
                    ->rows(3),

                Select::make('type')
                    ->label('نوع الاشتراك')
                    ->options([
                        'once' => 'اشتراك واحد (120 يوم)',
                        'monthly' => 'اشتراك شهري',
                        'daily' => 'اشتراك يومي (درس واحد)',
                    ])
                    ->required(),

                TextInput::make('price')
                    ->label('السعر')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->inputMode('decimal'),

                TextInput::make('duration_days')
                    ->label('مدة الاشتراك بالأيام')
                    ->numeric()
                    ->required()
                    ->minValue(1),

                TextInput::make('max_lessons')
                    ->label('عدد الدروس الأقصى (اختياري)')
                    ->numeric()
                    ->minValue(1)
                    ->nullable(),
            ]);
    }
}
