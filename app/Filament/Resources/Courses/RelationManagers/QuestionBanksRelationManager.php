<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionBanksRelationManager extends RelationManager
{
    protected static string $relationship = 'questionBanks';
    
    protected static ?string $title = 'بنوك الأسئلة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(255),
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
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('questions_count')
                    ->label('عدد الأسئلة')
                    ->counts('questions')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('نشط')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'نشط' : 'غير نشط')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['course_id'] = $this->getOwnerRecord()->id;
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
                AssociateAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn ($query) => 
                        $query->where(function($q) {
                            $q->whereNull('course_id')
                              ->orWhere('course_id', $this->getOwnerRecord()->id);
                        })
                        ->where('is_active', true)
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                \Filament\Actions\Action::make('view_questions')
                    ->label('عرض الأسئلة')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('info')
                    ->url(fn ($record) => 
                        \App\Filament\Resources\QuestionBanks\QuestionBankResource::getUrl('edit', ['record' => $record->id])
                    )
                    ->openUrlInNewTab(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد بنوك أسئلة')
            ->emptyStateDescription('ابدأ بإنشاء بنك أسئلة جديد أو ربط بنك موجود')
            ->emptyStateIcon('heroicon-o-rectangle-stack');
    }
}
