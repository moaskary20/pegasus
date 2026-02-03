<?php

namespace App\Filament\Resources\Lessons\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuizRelationManager extends RelationManager
{
    protected static string $relationship = 'quiz';
    
    protected static ?string $title = 'الاختبار';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull()
                    ->rows(3),
                TextInput::make('duration_minutes')
                    ->label('المدة (بالدقائق)')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('اتركه فارغاً لعدم تحديد مدة زمنية'),
                TextInput::make('pass_percentage')
                    ->label('نسبة النجاح')
                    ->numeric()
                    ->default(60)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),
                Toggle::make('allow_retake')
                    ->label('السماح بإعادة المحاولة')
                    ->default(true),
                TextInput::make('max_attempts')
                    ->label('الحد الأقصى للمحاولات')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('اتركه فارغاً للسماح بعدد غير محدود'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('questions_count')
                    ->label('عدد الأسئلة')
                    ->counts('questions'),
                TextColumn::make('duration_minutes')
                    ->label('المدة')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' دقيقة' : 'غير محدود'),
                TextColumn::make('pass_percentage')
                    ->label('نسبة النجاح')
                    ->formatStateUsing(fn ($state) => $state . '%'),
                IconColumn::make('allow_retake')
                    ->label('إعادة المحاولة')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Quizzes\RelationManagers\QuestionsRelationManager::class,
        ];
    }
}
