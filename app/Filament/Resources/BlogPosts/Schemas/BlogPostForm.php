<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المقال')
                    ->schema([
                        TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->label('الرابط (Slug)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Textarea::make('excerpt')
                            ->label('المقتطف')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('ملخص قصير يظهر في قائمة المقالات'),
                        Textarea::make('content')
                            ->label('المحتوى')
                            ->rows(12)
                            ->columnSpanFull(),
                        FileUpload::make('cover_image')
                            ->label('صورة الغلاف')
                            ->image()
                            ->disk('public')
                            ->directory('blog')
                            ->visibility('public')
                            ->imagePreviewHeight('160'),
                        Select::make('author_id')
                            ->label('الكاتب')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->id()),
                        Toggle::make('is_published')
                            ->label('منشور')
                            ->default(false),
                        DateTimePicker::make('published_at')
                            ->label('تاريخ النشر')
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }
}
