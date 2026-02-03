<?php

namespace App\Filament\Pages;

use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Services\PointsService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Rewards extends Page
{
    protected static ?string $navigationLabel = 'استبدال المكافآت';
    
    protected static ?string $title = 'استبدال المكافآت';
    
    protected static ?int $navigationSort = 12;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;
    
    protected string $view = 'filament.pages.rewards';
    
    protected static ?string $slug = 'my-rewards';
    
    public string $tab = 'available'; // available, my-rewards
    
    public static function getNavigationGroup(): ?string
    {
        return 'المكافآت';
    }
    
    public function getAvailableRewardsProperty()
    {
        return Reward::available()
            ->orderBy('points_required')
            ->get();
    }
    
    public function getMyRedemptionsProperty()
    {
        return RewardRedemption::where('user_id', auth()->id())
            ->with('reward')
            ->orderByDesc('created_at')
            ->get();
    }
    
    public function getUserPointsProperty(): int
    {
        return auth()->user()->available_points ?? 0;
    }
    
    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }
    
    public function redeemReward(int $rewardId): void
    {
        $reward = Reward::find($rewardId);
        
        if (!$reward) {
            Notification::make()
                ->title('المكافأة غير موجودة')
                ->danger()
                ->send();
            return;
        }
        
        if (!$reward->isAvailable()) {
            Notification::make()
                ->title('المكافأة غير متاحة')
                ->body('هذه المكافأة لم تعد متاحة للاستبدال')
                ->danger()
                ->send();
            return;
        }
        
        $user = auth()->user();
        
        if ($user->available_points < $reward->points_required) {
            Notification::make()
                ->title('نقاط غير كافية')
                ->body('تحتاج إلى ' . number_format($reward->points_required - $user->available_points) . ' نقطة إضافية')
                ->warning()
                ->send();
            return;
        }
        
        $redemption = app(PointsService::class)->redeemReward($user, $reward);
        
        if ($redemption) {
            Notification::make()
                ->title('تم الاستبدال بنجاح!')
                ->body('لقد حصلت على: ' . $reward->name)
                ->success()
                ->send();
            
            $this->tab = 'my-rewards';
        } else {
            Notification::make()
                ->title('فشل الاستبدال')
                ->body('حدث خطأ أثناء استبدال المكافأة')
                ->danger()
                ->send();
        }
    }
}
