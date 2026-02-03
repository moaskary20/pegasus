<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';
    
    protected static ?string $title = 'التعليقات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->default(auth()->id())
                    ->required()
                    ->searchable()
                    ->preload(),
                Textarea::make('body')
                    ->label('التعليق')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('body')
                    ->label('التعليق')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('replies_count')
                    ->label('عدد الردود')
                    ->counts('replies')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('تاريخ التعليق')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['lesson_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    })
                    ->after(function ($record) {
                        // إرسال إشعار للمدرس عند إضافة تعليق
                        $lesson = $record->lesson;
                        if ($lesson && $lesson->section && $lesson->section->course) {
                            $instructor = $lesson->section->course->instructor;
                            if ($instructor && $instructor->id !== auth()->id()) {
                                $instructor->notify(new \App\Notifications\LessonCommentNotification($record));
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
            ->emptyStateHeading('لا توجد تعليقات')
            ->emptyStateDescription('ابدأ بإضافة تعليق جديد')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}
