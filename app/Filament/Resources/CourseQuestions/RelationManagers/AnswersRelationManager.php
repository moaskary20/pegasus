<?php

namespace App\Filament\Resources\CourseQuestions\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    protected static ?string $title = 'الردود';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('answer')
                    ->label('الرد')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('answer')
            ->columns([
                TextColumn::make('user.name')
                    ->label('المدرس')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('answer')
                    ->label('الرد')
                    ->limit(100)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الرد')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    })
                    ->after(function ($record) {
                        $record->question->update(['is_answered' => true]);
                        
                        // إرسال إشعار للطالب عند رد المدرس على سؤاله
                        $question = $record->question;
                        if ($question->user_id !== auth()->id()) {
                            $question->user->notify(new \App\Notifications\CourseQuestionAnsweredNotification($question, 'answered'));
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
            ]);
    }
}
