<?php

namespace App\Filament\Resources\SupportComplaints\Schemas;

use App\Models\SupportComplaint;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SupportComplaintForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المرسل')
                    ->schema([
                        Select::make('user_id')
                            ->label('المستخدم المسجل')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('— ضيف —'),
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('تفاصيل الشكوى / الاستفسار')
                    ->schema([
                        Select::make('type')
                            ->label('النوع')
                            ->options([
                                SupportComplaint::TYPE_COMPLAINT => 'شكوى',
                                SupportComplaint::TYPE_CONTACT => 'تواصل / استفسار',
                            ])
                            ->required()
                            ->default(SupportComplaint::TYPE_COMPLAINT),
                        TextInput::make('subject')
                            ->label('الموضوع')
                            ->maxLength(255)
                            ->placeholder('—'),
                        Textarea::make('message')
                            ->label('الرسالة / التفاصيل')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                SupportComplaint::STATUS_PENDING => 'قيد الانتظار',
                                SupportComplaint::STATUS_IN_PROGRESS => 'قيد المعالجة',
                                SupportComplaint::STATUS_RESOLVED => 'تم الحل',
                            ])
                            ->required()
                            ->default(SupportComplaint::STATUS_PENDING),
                    ])
                    ->columns(2),
            ]);
    }
}
