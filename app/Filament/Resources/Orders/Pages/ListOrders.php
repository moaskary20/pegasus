<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة طلب')
                ->icon('heroicon-o-plus'),
        ];
    }
    
    public function getHeader(): ?View
    {
        return view('filament.resources.orders.header', [
            'totalOrders' => Order::count(),
            'paidOrders' => Order::where('status', 'paid')->count(),
            'pendingOrders' => Order::where('status', 'pending')->count(),
            'failedOrders' => Order::where('status', 'failed')->count(),
            'totalRevenue' => Order::where('status', 'paid')->sum('total'),
            'todayOrders' => Order::whereDate('created_at', today())->count(),
            'createUrl' => static::getResource()::getUrl('create'),
        ]);
    }
}
