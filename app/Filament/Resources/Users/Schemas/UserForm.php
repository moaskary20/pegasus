<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof \App\Filament\Resources\Users\Pages\CreateUser)
                    ->dehydrated(fn ($state) => filled($state)),
                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->maxLength(20)
                    ->placeholder('01xxxxxxxxx'),
                FileUpload::make('avatar')
                    ->label('الصورة الشخصية')
                    ->image()
                    ->directory('avatars')
                    ->visibility('public'),
                TextInput::make('city')
                    ->label('المدينة'),
                TextInput::make('job')
                    ->label('الوظيفة'),
                Textarea::make('skills')
                    ->label('المهارات')
                    ->columnSpanFull()
                    ->helperText('أدخل المهارات مفصولة بفواصل'),
                Textarea::make('interests')
                    ->label('الاهتمامات')
                    ->columnSpanFull()
                    ->helperText('أدخل الاهتمامات مفصولة بفواصل'),
                Select::make('roles')
                    ->label('الأدوار')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable(),
                DateTimePicker::make('email_verified_at')
                    ->label('تاريخ تأكيد البريد'),
                DateTimePicker::make('phone_verified_at')
                    ->label('تاريخ تأكيد الهاتف'),
            ]);
    }
}
