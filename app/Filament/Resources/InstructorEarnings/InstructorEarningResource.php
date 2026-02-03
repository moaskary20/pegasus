<?php

namespace App\Filament\Resources\InstructorEarnings;

use App\Filament\Resources\InstructorEarnings\Pages\CreateInstructorEarning;
use App\Filament\Resources\InstructorEarnings\Pages\EditInstructorEarning;
use App\Filament\Resources\InstructorEarnings\Pages\ListInstructorEarnings;
use App\Filament\Resources\InstructorEarnings\Schemas\InstructorEarningForm;
use App\Filament\Resources\InstructorEarnings\Tables\InstructorEarningsTable;
use App\Models\InstructorEarning;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InstructorEarningResource extends Resource
{
    protected static ?string $model = InstructorEarning::class;

    protected static ?string $navigationLabel = 'إدارة الأرباح';
    
    protected static ?string $modelLabel = 'ربح مدرس';
    
    protected static ?string $pluralModelLabel = 'الأرباح';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
    
    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return InstructorEarningForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstructorEarningsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstructorEarnings::route('/'),
            'create' => CreateInstructorEarning::route('/create'),
            'edit' => EditInstructorEarning::route('/{record}/edit'),
        ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        // Hidden from navigation - use InstructorEarningsManagement page instead
        return false;
    }
}
