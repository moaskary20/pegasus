<?php

namespace App\Filament\Resources\SupportComplaints\Schemas;

use App\Models\SupportComplaint;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SupportComplaintInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المرسل')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('المستخدم المسجل')
                            ->placeholder('— ضيف —'),
                        TextEntry::make('name')
                            ->label('الاسم'),
                        TextEntry::make('email')
                            ->label('البريد الإلكتروني')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label('رقم الهاتف')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('تفاصيل الشكوى / الاستفسار')
                    ->schema([
                        TextEntry::make('type')
                            ->label('النوع')
                            ->formatStateUsing(fn (string $state) => $state === SupportComplaint::TYPE_COMPLAINT ? 'شكوى' : 'تواصل / استفسار')
                            ->badge()
                            ->color(fn (string $state) => $state === SupportComplaint::TYPE_COMPLAINT ? 'danger' : 'info'),
                        TextEntry::make('subject')
                            ->label('الموضوع')
                            ->placeholder('—'),
                        TextEntry::make('message')
                            ->label('الرسالة / التفاصيل')
                            ->columnSpanFull(),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                SupportComplaint::STATUS_PENDING => 'قيد الانتظار',
                                SupportComplaint::STATUS_IN_PROGRESS => 'قيد المعالجة',
                                SupportComplaint::STATUS_RESOLVED => 'تم الحل',
                                default => $state,
                            })
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                SupportComplaint::STATUS_PENDING => 'warning',
                                SupportComplaint::STATUS_IN_PROGRESS => 'info',
                                SupportComplaint::STATUS_RESOLVED => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')
                            ->label('تاريخ الإرسال')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                    ])
                    ->columns(2),
            ]);
    }
}
