<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use App\Models\Coupon;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة كوبون')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getHeader(): ?View
    {
        return view('filament.resources.coupons.header', [
            'totalCoupons' => Coupon::count(),
            'activeCoupons' => Coupon::where('is_active', true)->count(),
            'expiredCoupons' => Coupon::where('expires_at', '<', now())->count(),
            'percentCoupons' => Coupon::where('type', 'percent')->count(),
            'fixedCoupons' => Coupon::where('type', 'fixed')->count(),
            'totalUsage' => Coupon::sum('used_count'),
            'createUrl' => static::getResource()::getUrl('create'),
        ]);
    }
}
