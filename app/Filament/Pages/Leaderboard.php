<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\PointsService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Leaderboard extends Page
{
    protected static ?string $navigationLabel = 'لوحة المتصدرين';
    
    protected static ?string $title = 'لوحة المتصدرين';
    
    protected static ?int $navigationSort = 11;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;
    
    protected string $view = 'filament.pages.leaderboard';
    
    protected static ?string $slug = 'leaderboard';
    
    public string $period = 'all'; // all, month, week
    
    public static function getNavigationGroup(): ?string
    {
        return 'المكافآت';
    }
    
    public function getLeadersProperty()
    {
        $query = User::where('total_points', '>', 0)
            ->orderByDesc('total_points');
        
        // For period filtering, we'd need to aggregate from point_transactions
        // For simplicity, we'll use total_points for all periods
        
        return $query->limit(50)->get();
    }
    
    public function getTopThreeProperty()
    {
        return $this->leaders->take(3);
    }
    
    public function getRestProperty()
    {
        return $this->leaders->skip(3);
    }
    
    public function getCurrentUserRankProperty(): int
    {
        return app(PointsService::class)->getUserRankPosition(auth()->user());
    }
    
    public function getCurrentUserProperty()
    {
        return auth()->user();
    }
    
    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }
}
