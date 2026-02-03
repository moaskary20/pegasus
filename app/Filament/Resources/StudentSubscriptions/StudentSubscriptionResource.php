<?php

namespace App\Filament\Resources\StudentSubscriptions;

use App\Filament\Resources\StudentSubscriptions\Pages\CreateStudentSubscription;
use App\Filament\Resources\StudentSubscriptions\Pages\EditStudentSubscription;
use App\Filament\Resources\StudentSubscriptions\Pages\ListStudentSubscriptions;
use App\Filament\Resources\StudentSubscriptions\Schemas\StudentSubscriptionForm;
use App\Filament\Resources\StudentSubscriptions\Tables\StudentSubscriptionsTable;
use App\Models\StudentSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentSubscriptionResource extends Resource
{
    protected static ?string $model = StudentSubscription::class;

    protected static ?string $navigationLabel = 'اشتراكات الطلاب';

    protected static ?string $modelLabel = 'اشتراك طالب';

    protected static ?string $pluralModelLabel = 'اشتراكات الطلاب';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return StudentSubscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentSubscriptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentSubscriptions::route('/'),
            'create' => CreateStudentSubscription::route('/create'),
            'edit' => EditStudentSubscription::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'الاشتراكات والدفع';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole(['admin']) ?? false;
    }
}
