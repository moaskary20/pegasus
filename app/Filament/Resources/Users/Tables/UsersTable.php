<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=6366f1&color=fff'),
                    
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),
                    
                TextColumn::make('roles_labels')
                    ->label('الدور')
                    ->getStateUsing(function (User $record): array {
                        $roles = $record->roles;
                        if ($roles->isEmpty()) {
                            return [];
                        }

                        return $roles
                            ->pluck('name')
                            ->unique()
                            ->map(fn (string $name): string => match ($name) {
                                'admin' => 'مدير',
                                'instructor' => 'مدرس',
                                'student' => 'طالب',
                                default => $name,
                            })
                            ->values()
                            ->all();
                    })
                    ->badge()
                    ->placeholder('—')
                    ->color(fn (?string $state): string => match ($state) {
                        'مدير' => 'danger',
                        'مدرس' => 'warning',
                        'طالب' => 'success',
                        default => 'gray',
                    }),
                    
                TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->placeholder('—')
                    ->copyable(),
                    
                TextColumn::make('city')
                    ->label('المدينة')
                    ->searchable()
                    ->icon('heroicon-o-map-pin')
                    ->placeholder('—')
                    ->toggleable(),
                    
                IconColumn::make('email_verified_at')
                    ->label('مؤكد')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('roles')
                    ->label('الدور')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn (\Spatie\Permission\Models\Role $record): string => match ($record->name) {
                        'admin' => 'مدير',
                        'instructor' => 'مدرس',
                        'student' => 'طالب',
                        default => $record->name,
                    }),
                \Filament\Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('البريد المؤكد')
                    ->placeholder('الكل')
                    ->trueLabel('مؤكد فقط')
                    ->falseLabel('غير مؤكد فقط'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
