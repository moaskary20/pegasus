<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LessonQuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    
    protected static ?string $title = 'أسئلة الدرس (Q&A)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('الطالب')
                    ->relationship('user', 'name', fn ($query) => 
                        $query->whereHas('roles', fn ($q) => 
                            $q->where('name', 'student')
                        )
                    )
                    ->default(auth()->id())
                    ->required()
                    ->searchable()
                    ->preload(),
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
                    ->label('تاريخ السؤال')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_answered')
                    ->label('تم الرد')
                    ->placeholder('الكل')
                    ->trueLabel('تم الرد')
                    ->falseLabel('لم يتم الرد'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['lesson_id'] = $this->getOwnerRecord()->id;
                        $data['course_id'] = $this->getOwnerRecord()->section->course_id;
                        return $data;
                    })
                    ->after(function ($record) {
                        // إرسال إشعار للمدرس عند طرح سؤال
                        $lesson = $record->lesson;
                        if ($lesson && $lesson->section && $lesson->section->course) {
                            $instructor = $lesson->section->course->instructor;
                            if ($instructor && $instructor->id !== auth()->id()) {
                                $instructor->notify(new \App\Notifications\CourseQuestionAnsweredNotification($record, 'new_question'));
                            }
                        }
                    }),
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
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد أسئلة')
            ->emptyStateDescription('ابدأ بطرح سؤال جديد')
            ->emptyStateIcon('heroicon-o-question-mark-circle');
    }
}
