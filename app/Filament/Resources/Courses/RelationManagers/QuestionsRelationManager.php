<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    
    protected static ?string $title = 'أسئلة الدورة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('الطالب')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('lesson_id')
                    ->label('المحاضرة (اختياري)')
                    ->options(function () {
                        return $this->getOwnerRecord()->lessons()
                            ->get()
                            ->mapWithKeys(fn ($lesson) => [
                                $lesson->id => $lesson->section->title . ' - ' . $lesson->title
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->helperText('اختر المحاضرة المرتبطة بهذا السؤال (اختياري)'),
                Textarea::make('question')
                    ->label('السؤال')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Toggle::make('is_answered')
                    ->label('تم الرد')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lesson.title')
                    ->label('المحاضرة')
                    ->formatStateUsing(fn ($record) => 
                        $record->lesson 
                            ? $record->lesson->section->title . ' - ' . $record->lesson->title
                            : 'عام'
                    )
                    ->badge()
                    ->color(fn ($record) => $record->lesson ? 'info' : 'gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('question')
                    ->label('السؤال')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                IconColumn::make('is_answered')
                    ->label('تم الرد')
                    ->boolean(),
                TextColumn::make('answers_count')
                    ->label('عدد الردود')
                    ->counts('answers')
                    ->badge()
                    ->color('success'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('lesson_id')
                    ->label('المحاضرة')
                    ->options(function () {
                        return $this->getOwnerRecord()->lessons()
                            ->get()
                            ->mapWithKeys(fn ($lesson) => [
                                $lesson->id => $lesson->section->title . ' - ' . $lesson->title
                            ])
                            ->toArray();
                    })
                    ->searchable(),
                TernaryFilter::make('is_answered')
                    ->label('تم الرد')
                    ->placeholder('الكل')
                    ->trueLabel('تم الرد')
                    ->falseLabel('لم يتم الرد'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['course_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('view_answers')
                    ->label('عرض الردود')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->url(fn ($record) => 
                        \App\Filament\Resources\CourseQuestions\CourseQuestionResource::getUrl('edit', ['record' => $record->id])
                    )
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->answers()->count() > 0),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد أسئلة')
            ->emptyStateDescription('ابدأ بإضافة سؤال جديد')
            ->emptyStateIcon('heroicon-o-question-mark-circle');
    }
}
