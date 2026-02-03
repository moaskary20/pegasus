<?php

namespace App\Filament\Pages;

use App\Models\PointTransaction;
use App\Services\PointsService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithPagination;

class MyPoints extends Page
{
    use WithPagination;
    
    protected static ?string $navigationLabel = 'نقاطي';
    
    protected static ?string $title = 'نقاطي';
    
    protected static ?int $navigationSort = 10;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;
    
    protected string $view = 'filament.pages.my-points';
    
    protected static ?string $slug = 'my-points';
    
    public string $filter = 'all'; // all, earned, spent
    
    public static function getNavigationGroup(): ?string
    {
        return 'المكافآت';
    }
    
    public static function getNavigationBadge(): ?string
    {
        $points = auth()->user()->available_points ?? 0;
        return number_format($points);
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
    
    public function getTransactionsProperty()
    {
        $query = PointTransaction::where('user_id', auth()->id())
            ->orderByDesc('created_at');
        
        if ($this->filter === 'earned') {
            $query->where('points', '>', 0);
        } elseif ($this->filter === 'spent') {
            $query->where('points', '<', 0);
        }
        
        return $query->paginate(20);
    }
    
    public function getTotalEarnedProperty(): int
    {
        return PointTransaction::where('user_id', auth()->id())
            ->where('points', '>', 0)
            ->sum('points');
    }
    
    public function getTotalSpentProperty(): int
    {
        return abs(PointTransaction::where('user_id', auth()->id())
            ->where('points', '<', 0)
            ->sum('points'));
    }
    
    public function getRankPositionProperty(): int
    {
        return app(PointsService::class)->getUserRankPosition(auth()->user());
    }
    
    public function getPointsForNextRankProperty(): ?int
    {
        return app(PointsService::class)->getPointsForNextRank(auth()->user());
    }
    
    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }
}
