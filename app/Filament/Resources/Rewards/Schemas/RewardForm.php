<?php

namespace App\Filament\Resources\Rewards\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم المكافأة')
                    ->required()
                    ->maxLength(255),
                    
                Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->columnSpanFull(),
                    
                Select::make('type')
                    ->label('نوع المكافأة')
                    ->options([
                        'discount' => 'خصم',
                        'free_course' => 'دورة مجانية',
                        'badge' => 'شارة',
                        'certificate' => 'شهادة',
                    ])
                    ->required()
                    ->live(),
                    
                TextInput::make('points_required')
                    ->label('النقاط المطلوبة')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->suffix('نقطة'),
                    
                TextInput::make('value')
                    ->label('القيمة')
                    ->numeric()
                    ->helperText('نسبة الخصم (للخصومات)')
                    ->suffix('%')
                    ->visible(fn ($get) => $get('type') === 'discount'),
                    
                Select::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => $get('type') === 'free_course'),
                    
                FileUpload::make('image')
                    ->label('الصورة')
                    ->image()
                    ->directory('rewards'),
                    
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
                    
                TextInput::make('quantity')
                    ->label('الكمية المتاحة')
                    ->numeric()
                    ->helperText('اتركه فارغاً لكمية غير محدودة')
                    ->nullable(),
                    
                DateTimePicker::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->nullable(),
            ]);
    }
}
