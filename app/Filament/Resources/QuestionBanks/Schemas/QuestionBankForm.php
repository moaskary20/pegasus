<?php

namespace App\Filament\Resources\QuestionBanks\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuestionBankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('المدرس')
                    ->relationship('user', 'name')
                    ->default(auth()->id())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(fn () => auth()->user()?->hasRole('instructor') && !auth()->user()?->hasRole('admin')),
                Select::make('course_id')
                    ->label('الدورة (اختياري)')
                    ->relationship('course', 'title', fn ($query) => 
                        auth()->user()?->hasRole('instructor') && !auth()->user()?->hasRole('admin')
                            ? $query->where('user_id', auth()->id())
                            : $query
                    )
                    ->searchable()
                    ->preload()
                    ->helperText('اتركه فارغاً لإنشاء بنك عام يمكن استخدامه في جميع الدورات')
                    ->reactive(),
                TextInput::make('title')
                    ->label('عنوان بنك الأسئلة')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->required(),
                Repeater::make('tags')
                    ->label('العلامات')
                    ->schema([
                        TextInput::make('tag')
                            ->label('علامة')
                            ->required(),
                    ])
                    ->defaultItems(0)
                    ->helperText('علامات لتصنيف بنك الأسئلة (مثل: أساسيات، متقدم، إلخ)')
                    ->columnSpanFull(),
            ]);
    }
}
