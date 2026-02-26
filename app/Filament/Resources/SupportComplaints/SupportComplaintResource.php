<?php

namespace App\Filament\Resources\SupportComplaints;

use App\Filament\Resources\SupportComplaints\Pages\CreateSupportComplaint;
use App\Filament\Resources\SupportComplaints\Pages\EditSupportComplaint;
use App\Filament\Resources\SupportComplaints\Pages\ListSupportComplaints;
use App\Filament\Resources\SupportComplaints\Pages\ViewSupportComplaint;
use App\Filament\Resources\SupportComplaints\Schemas\SupportComplaintForm;
use App\Filament\Resources\SupportComplaints\Schemas\SupportComplaintInfolist;
use App\Filament\Resources\SupportComplaints\Tables\SupportComplaintsTable;
use App\Models\SupportComplaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupportComplaintResource extends Resource
{
    protected static ?string $model = SupportComplaint::class;

    protected static ?string $navigationLabel = 'الشكاوى والاستفسارات';

    protected static ?string $modelLabel = 'شكوى';

    protected static ?string $pluralModelLabel = 'الشكاوى والاستفسارات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function getNavigationGroup(): ?string
    {
        return 'الدعم';
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return SupportComplaintForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SupportComplaintInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupportComplaintsTable::configure($table);
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
            'index' => ListSupportComplaints::route('/'),
            'create' => CreateSupportComplaint::route('/create'),
            'view' => ViewSupportComplaint::route('/{record}'),
            'edit' => EditSupportComplaint::route('/{record}/edit'),
        ];
    }
}
