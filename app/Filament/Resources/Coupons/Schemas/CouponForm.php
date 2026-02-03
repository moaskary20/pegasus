<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('كود الكوبون')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('code', strtoupper($state));
                        }
                    }),
                Select::make('type')
                    ->label('النوع')
                    ->options([
                        'percent' => 'نسبة مئوية',
                        'fixed' => 'مبلغ ثابت',
                    ])
                    ->default('percent')
                    ->required(),
                TextInput::make('value')
                    ->label('القيمة')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->suffix(fn ($get) => $get('type') === 'percent' ? '%' : 'ج.م'),
                TextInput::make('min_purchase')
                    ->label('الحد الأدنى للشراء')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('ج.م')
                    ->helperText('الحد الأدنى لمبلغ الشراء لتفعيل الكوبون'),
                TextInput::make('usage_limit')
                    ->label('حد الاستخدام')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('اتركه فارغاً للسماح بعدد غير محدود من الاستخدامات'),
                DateTimePicker::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->helperText('اتركه فارغاً لعدم تحديد تاريخ انتهاء'),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }
}
