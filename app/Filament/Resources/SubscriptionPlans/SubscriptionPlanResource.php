<?php

namespace App\Filament\Resources\SubscriptionPlans;

use App\Filament\Resources\SubscriptionPlans\Pages\CreateSubscriptionPlan;
use App\Filament\Resources\SubscriptionPlans\Pages\EditSubscriptionPlan;
use App\Filament\Resources\SubscriptionPlans\Pages\ListSubscriptionPlans;
use App\Filament\Resources\SubscriptionPlans\Schemas\SubscriptionPlanForm;
use App\Filament\Resources\SubscriptionPlans\Tables\SubscriptionPlansTable;
use App\Models\SubscriptionPlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static ?string $navigationLabel = 'خطط الاشتراك';

    protected static ?string $modelLabel = 'خطة اشتراك';

    protected static ?string $pluralModelLabel = 'خطط الاشتراك';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::RectangleStack;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SubscriptionPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubscriptionPlansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionPlans::route('/'),
            'create' => CreateSubscriptionPlan::route('/create'),
            'edit' => EditSubscriptionPlan::route('/{record}/edit'),
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
