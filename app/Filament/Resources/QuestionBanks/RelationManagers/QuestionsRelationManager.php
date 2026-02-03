<?php

namespace App\Filament\Resources\QuestionBanks\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    
    protected static ?string $title = 'أسئلة البنك';

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
                Repeater::make('options')
                    ->label('الخيارات')
                    ->schema([
                        TextInput::make('option')
                            ->label('خيار')
                            ->required(),
                        Toggle::make('is_correct')
                            ->label('إجابة صحيحة')
                            ->default(false),
                    ])
                    ->defaultItems(2)
                    ->minItems(2)
                    ->maxItems(10)
                    ->visible(fn ($get) => $get('type') === 'mcq')
                    ->helperText('أضف الخيارات وحدد الإجابة الصحيحة'),
                Select::make('true_false_answer')
                    ->label('الإجابة الصحيحة')
                    ->options([
                        'true' => 'صح',
                        'false' => 'خطأ',
                    ])
                    ->required()
                    ->visible(fn ($get) => $get('type') === 'true_false'),
                Textarea::make('fill_blank_answers')
                    ->label('الإجابات الصحيحة (مفصولة بفواصل)')
                    ->helperText('أدخل الإجابات الصحيحة مفصولة بفواصل، مثال: إجابة1, إجابة2, إجابة3')
                    ->rows(2)
                    ->visible(fn ($get) => $get('type') === 'fill_blank')
                    ->required(),
                Repeater::make('matching_pairs')
                    ->label('أزواج المطابقة')
                    ->schema([
                        TextInput::make('key')
                            ->label('المفتاح')
                            ->required(),
                        TextInput::make('value')
                            ->label('القيمة')
                            ->required(),
                    ])
                    ->defaultItems(2)
                    ->minItems(2)
                    ->maxItems(20)
                    ->visible(fn ($get) => $get('type') === 'matching')
                    ->helperText('أضف أزواج المطابقة'),
                Textarea::make('short_answer_keywords')
                    ->label('الكلمات المفتاحية للإجابة (مفصولة بفواصل)')
                    ->helperText('أدخل الكلمات المفتاحية التي يجب أن تكون في الإجابة الصحيحة')
                    ->rows(2)
                    ->visible(fn ($get) => $get('type') === 'short_answer'),
                Textarea::make('explanation')
                    ->label('التفسير')
                    ->rows(2)
                    ->columnSpanFull()
                    ->helperText('شرح الإجابة الصحيحة (اختياري)'),
                TextInput::make('points')
                    ->label('النقاط')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),
                Select::make('difficulty')
                    ->label('مستوى الصعوبة')
                    ->options([
                        'easy' => 'سهل',
                        'medium' => 'متوسط',
                        'hard' => 'صعب',
                    ])
                    ->nullable(),
                Repeater::make('tags')
                    ->label('العلامات')
                    ->schema([
                        TextInput::make('tag')
                            ->label('علامة')
                            ->required(),
                    ])
                    ->defaultItems(0)
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->processQuestionData($data);
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->processQuestionData($data);
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert options array to repeater format for MCQ
        if (isset($data['options']) && is_array($data['options']) && $data['type'] === 'mcq') {
            $correctAnswers = $data['correct_answer'] ?? [];
            $data['options'] = array_map(function($option) use ($correctAnswers) {
                return [
                    'option' => $option,
                    'is_correct' => in_array($option, $correctAnswers),
                ];
            }, $data['options']);
        }
        
        // Convert matching pairs
        if (isset($data['options']) && is_array($data['options']) && $data['type'] === 'matching') {
            $data['matching_pairs'] = [];
            foreach ($data['options'] as $key => $value) {
                $data['matching_pairs'][] = ['key' => $key, 'value' => $value];
            }
        }
        
        // Convert fill_blank answers
        if (isset($data['correct_answer']) && is_array($data['correct_answer']) && $data['type'] === 'fill_blank') {
            $data['fill_blank_answers'] = implode(', ', $data['correct_answer']);
        }
        
        // Convert true_false answer
        if (isset($data['correct_answer']) && is_array($data['correct_answer']) && $data['type'] === 'true_false') {
            $data['true_false_answer'] = $data['correct_answer'][0] ?? 'true';
        }
        
        // Convert short_answer keywords
        if (isset($data['correct_answer']) && is_array($data['correct_answer']) && $data['type'] === 'short_answer') {
            $data['short_answer_keywords'] = implode(', ', $data['correct_answer']);
        }
        
        // Convert tags
        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = array_map(fn($tag) => ['tag' => $tag], $data['tags']);
        }
        
        return $data;
    }
    
    protected function processQuestionData(array $data): array
    {
        $options = null;
        $correctAnswer = null;
        
        switch ($data['type'] ?? 'mcq') {
            case 'mcq':
                $options = array_column($data['options'] ?? [], 'option');
                $correctAnswer = [];
                foreach ($data['options'] ?? [] as $option) {
                    if ($option['is_correct'] ?? false) {
                        $correctAnswer[] = $option['option'];
                    }
                }
                break;
                
            case 'true_false':
                $correctAnswer = [$data['true_false_answer'] ?? 'true'];
                break;
                
            case 'fill_blank':
                $answers = array_map('trim', explode(',', $data['fill_blank_answers'] ?? ''));
                $correctAnswer = array_filter($answers);
                break;
                
            case 'matching':
                $options = [];
                $correctAnswer = [];
                foreach ($data['matching_pairs'] ?? [] as $pair) {
                    $options[$pair['key']] = $pair['value'];
                    $correctAnswer[$pair['key']] = $pair['value'];
                }
                break;
                
            case 'short_answer':
                $keywords = array_map('trim', explode(',', $data['short_answer_keywords'] ?? ''));
                $correctAnswer = array_filter($keywords);
                break;
        }
        
        $data['options'] = $options;
        $data['correct_answer'] = $correctAnswer;
        
        // Convert tags repeater to JSON array
        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = array_column($data['tags'], 'tag');
        }
        
        return $data;
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
                TextColumn::make('difficulty')
                    ->label('الصعوبة')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'easy' => 'سهل',
                        'medium' => 'متوسط',
                        'hard' => 'صعب',
                        default => 'غير محدد',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                        default => 'gray',
                    }),
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
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'mcq' => 'اختيار من متعدد',
                        'fill_blank' => 'ملء الفراغ',
                        'true_false' => 'صح/خطأ',
                        'matching' => 'مطابقة',
                        'short_answer' => 'إجابة قصيرة',
                    ]),
                SelectFilter::make('difficulty')
                    ->label('الصعوبة')
                    ->options([
                        'easy' => 'سهل',
                        'medium' => 'متوسط',
                        'hard' => 'صعب',
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
