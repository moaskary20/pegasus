<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('العميل')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('order_number')
                    ->label('رقم الطلب')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                TextInput::make('subtotal')
                    ->label('المجموع الفرعي')
                    ->numeric()
                    ->default(0)
                    ->prefix('ج.م')
                    ->required(),
                TextInput::make('discount')
                    ->label('الخصم')
                    ->numeric()
                    ->default(0)
                    ->prefix('ج.م'),
                TextInput::make('total')
                    ->label('الإجمالي')
                    ->numeric()
                    ->default(0)
                    ->prefix('ج.م')
                    ->required(),
                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                    ])
                    ->default('pending')
                    ->required(),
                Select::make('payment_gateway')
                    ->label('بوابة الدفع')
                    ->options([
                        'kashier' => 'فيزا / ماستر / ميزة',
                        'manual' => 'يدوي',
                    ])
                    ->searchable(),
                FileUpload::make('manual_receipt_path')
                    ->label('إيصال الدفع اليدوي')
                    ->disk('public')
                    ->directory('manual-receipts')
                    ->openable()
                    ->downloadable()
                    ->visibility('public')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->helperText('يظهر هنا إيصال الدفع اليدوي المرفوع من صفحة الدفع العامة.'),
                TextInput::make('payment_id')
                    ->label('معرف الدفع'),
                TextInput::make('coupon_code')
                    ->label('كود الكوبون')
                    ->maxLength(50),
                TextInput::make('invoice_url')
                    ->label('رابط الفاتورة')
                    ->url()
                    ->maxLength(255),
                DateTimePicker::make('paid_at')
                    ->label('تاريخ الدفع'),
            ]);
    }
}
