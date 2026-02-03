<?php

namespace App\Filament\Resources\Quizzes\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    
    protected static ?string $title = 'الأسئلة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('نوع السؤال')
                    ->options([
                        'mcq' => 'اختيار من متعدد',
                        'fill_blank' => 'ملء الفراغ',
                        'true_false' => 'صح/خطأ',
                        'matching' => 'مطابقة',
                        'short_answer' => 'إجابة قصيرة',
                    ])
                    ->default('mcq')
                    ->required()
                    ->reactive(),
                Textarea::make('question_text')
                    ->label('نص السؤال')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('options')
                    ->label('الخيارات (JSON)')
                    ->helperText('للمتعدد: ["خيار 1", "خيار 2", ...] | للمطابقة: {"مفتاح1": "قيمة1", ...}')
                    ->columnSpanFull()
                    ->visible(fn ($get) => in_array($get('type'), ['mcq', 'matching'])),
                Textarea::make('correct_answer')
                    ->label('الإجابة الصحيحة (JSON)')
                    ->helperText('للمتعدد: ["خيار1"] | للمطابقة: {"مفتاح1": "قيمة1"} | للفراغ: ["إجابة"] | صح/خطأ: ["true"]')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('explanation')
                    ->label('التفسير')
                    ->rows(2)
                    ->columnSpanFull(),
                TextInput::make('points')
                    ->label('النقاط')
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->columns([
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'mcq' => 'متعدد',
                        'fill_blank' => 'فراغ',
                        'true_false' => 'صح/خطأ',
                        'matching' => 'مطابقة',
                        'short_answer' => 'إجابة قصيرة',
                        default => $state,
                    }),
                TextColumn::make('question_text')
                    ->label('السؤال')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('points')
                    ->label('النقاط')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'mcq' => 'اختيار من متعدد',
                        'fill_blank' => 'ملء الفراغ',
                        'true_false' => 'صح/خطأ',
                        'matching' => 'مطابقة',
                        'short_answer' => 'إجابة قصيرة',
                    ]),
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
            ])
            ->defaultSort('sort_order')
            ->emptyStateHeading('لا توجد أسئلة')
            ->emptyStateDescription('ابدأ بإضافة سؤال جديد باستخدام زر "إضافة" في الأعلى')
            ->emptyStateIcon('heroicon-o-question-mark-circle');
    }
}
