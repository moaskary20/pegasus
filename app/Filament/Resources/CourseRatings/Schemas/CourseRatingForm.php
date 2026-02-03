<?php

namespace App\Filament\Resources\CourseRatings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CourseRatingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('stars')
                    ->label('التقييم')
                    ->options([
                        1 => '⭐ (1)',
                        2 => '⭐⭐ (2)',
                        3 => '⭐⭐⭐ (3)',
                        4 => '⭐⭐⭐⭐ (4)',
                        5 => '⭐⭐⭐⭐⭐ (5)',
                    ])
                    ->default(5)
                    ->required(),
                Textarea::make('review')
                    ->label('التعليق')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
