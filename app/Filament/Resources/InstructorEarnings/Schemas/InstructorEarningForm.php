<?php

namespace App\Filament\Resources\InstructorEarnings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InstructorEarningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('المدرس')
                    ->options(function () {
                        return \App\Models\User::whereHas('roles', fn ($q) => 
                            $q->where('name', 'instructor')
                        )
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('course_id', null)),
                Select::make('course_id')
                    ->label('الدورة')
                    ->options(function ($get) {
                        if (!$get('user_id')) {
                            return [];
                        }
                        return \App\Models\Course::where('is_published', true)
                            ->where('user_id', $get('user_id'))
                            ->orderBy('title')
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => !empty($get('user_id'))),
                Select::make('earnings_type')
                    ->label('نوع الأرباح')
                    ->options([
                        'percentage' => 'نسبة مئوية',
                        'fixed' => 'مبلغ ثابت',
                    ])
                    ->default('percentage')
                    ->required()
                    ->reactive(),
                TextInput::make('earnings_value')
                    ->label('القيمة')
                    ->numeric()
                    ->required()
                    ->suffix(fn ($get) => $get('earnings_type') === 'percentage' ? '%' : 'ج.م')
                    ->helperText(fn ($get) => 
                        $get('earnings_type') === 'percentage' 
                            ? 'أدخل النسبة المئوية (مثلاً: 70 يعني 70%)'
                            : 'أدخل المبلغ الثابت بالجنيه المصري'
                    ),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->helperText('إذا كان غير نشط، لن يتم حساب الأرباح'),
            ]);
    }
}
