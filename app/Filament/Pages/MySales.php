<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MySalesStatsWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MySales extends Page
{
    protected static ?string $navigationLabel = 'المبيعات والأرباح';
    
    protected static ?string $title = 'المبيعات والأرباح';
    
    protected static ?int $navigationSort = 2;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
    
    protected string $view = 'filament.pages.my-sales';
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('instructor') ?? false;
    }
    
    public function getHeaderWidgets(): array
    {
        return [
            MySalesStatsWidget::class,
        ];
    }
    
    public function getHeaderWidgetsColumns(): int
    {
        return 3;
    }
}
