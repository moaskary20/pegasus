<?php

namespace App\Filament\Resources\Certificates\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('الطالب')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('uuid')
                    ->label('UUID')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('يتم إنشاؤه تلقائياً'),
                TextInput::make('pdf_path')
                    ->label('مسار ملف PDF')
                    ->disabled()
                    ->dehydrated()
                    ->visible(fn ($record) => $record && $record->pdf_path)
                    ->helperText('يتم إنشاء ملف PDF تلقائياً عند إتمام الدورة'),
                DateTimePicker::make('issued_at')
                    ->label('تاريخ الإصدار')
                    ->default(now())
                    ->required(),
                Textarea::make('intro_text')
                    ->label('النص التمهيدي')
                    ->placeholder('هذا يثبت أن')
                    ->rows(2)
                    ->helperText('النص الذي يظهر قبل اسم الطالب')
                    ->columnSpanFull(),
                Textarea::make('completion_text')
                    ->label('نص الإتمام')
                    ->placeholder('قد أكمل بنجاح دورة')
                    ->rows(2)
                    ->helperText('النص الذي يظهر قبل اسم الدورة')
                    ->columnSpanFull(),
                Textarea::make('award_text')
                    ->label('نص التقدير')
                    ->placeholder('وتم منحه هذه الشهادة تقديراً لجهوده وإنجازه')
                    ->rows(2)
                    ->helperText('النص الذي يظهر في نهاية الشهادة')
                    ->columnSpanFull(),
                TextInput::make('director_name')
                    ->label('اسم المدير العام')
                    ->placeholder('المدير العام')
                    ->helperText('اسم المدير العام للتوقيع'),
                TextInput::make('academic_director_name')
                    ->label('اسم المدير الأكاديمي')
                    ->placeholder('المدير الأكاديمي')
                    ->helperText('اسم المدير الأكاديمي للتوقيع'),
            ]);
    }
}
